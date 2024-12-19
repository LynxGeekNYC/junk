<?php
session_start();

// Generate a unique directory for each upload session
$user_id = session_id();
$unique_id = uniqid(); // Generate a unique ID for this upload
$uploads_dir = "uploads/{$user_id}_{$unique_id}";
$results_dir = "results/{$user_id}_{$unique_id}";
$status_file = "$results_dir/status.txt";

// Attempt to create the directories with error handling
if (!is_dir($uploads_dir) && !mkdir($uploads_dir, 0777, true)) {
    die("Error: Unable to create uploads directory. Check permissions.");
}

if (!is_dir($results_dir) && !mkdir($results_dir, 0777, true)) {
    die("Error: Unable to create results directory. Check permissions.");
}

// Write initial status
if (!file_put_contents($status_file, "Starting upload...\n")) {
    die("Error: Unable to write to status file. Check directory permissions.");
}

// Process uploaded files
$file_paths = [];
foreach ($_FILES['pdf_files']['tmp_name'] as $index => $tmp_name) {
    $filename = basename($_FILES['pdf_files']['name'][$index]);
    $destination = "$uploads_dir/$filename";

    if (move_uploaded_file($tmp_name, $destination)) {
        $file_paths[] = $destination;
        file_put_contents($status_file, "Uploaded file: $filename\n", FILE_APPEND);
    } else {
        die("Failed to upload file: $filename");
    }
}

// Call the C++ script and send JSON data to OpenAI API
foreach ($file_paths as $file_path) {
    file_put_contents($status_file, "Processing file: $file_path\n", FILE_APPEND);

    // Call the C++ script to extract JSON data
    $json_data = shell_exec("./extract_to_json_with_ocr " . escapeshellarg($file_path));
    if (!$json_data) {
        die("Failed to process file: $file_path");
    }

    file_put_contents($status_file, "Sending data to OpenAI API.\n", FILE_APPEND);

    // Prepare OpenAI API call
    $api_key = 'OPENAI - KEY';
    $api_url = 'https://api.openai.com/v1/chat/completions';
    $data = [
        'model' => 'gpt-4',
        'messages' => [['role' => 'user', 'content' => $json_data]]
    ];

    // Make the API request
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . 'Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch) || $http_code !== 200) {
        $error_message = curl_error($ch) ?: "HTTP $http_code error from OpenAI";
        curl_close($ch);
        die("Error during OpenAI API call: $error_message");
    }

    curl_close($ch);

    // Save response to result file
    file_put_contents("$results_dir/result.json", $response);
    file_put_contents($status_file, "OpenAI API response received.\n", FILE_APPEND);
}

// Final status
file_put_contents($status_file, "Processing complete. Ready to download.\n", FILE_APPEND);
echo '<a href="generate_report.php?session=' . urlencode("{$user_id}_{$unique_id}") . '" class="btn btn-success">Download Report</a>';
?>

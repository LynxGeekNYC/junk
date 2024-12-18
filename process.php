<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = bin2hex(random_bytes(16));
}

$targetDir = "uploads/" . $_SESSION['user_id'] . "/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$files = $_FILES['files'];
$maxFileSize = 99 * 1024 * 1024; // 99 MB

foreach ($files['name'] as $index => $name) {
    if ($files['size'][$index] > $maxFileSize) {
        http_response_code(400);
        echo "File size too large.";
        exit;
    }

    $tempPath = $files['tmp_name'][$index];
    $newName = $targetDir . uniqid() . "-" . basename($name);
    move_uploaded_file($tempPath, $newName);
}

// Call C++ binary to process files
$command = "process_files " . escapeshellarg($targetDir) . " output.json";
exec($command, $output, $returnVar);

if ($returnVar !== 0) {
    http_response_code(500);
    echo "Error processing files.";
    exit;
}

// Read and sanitize JSON
$jsonData = json_decode(file_get_contents("output.json"), true);

// Submit to OpenAI API
$apiResponse = submitToOpenAI($jsonData);

// Generate PDF using FPDF
generatePDF($apiResponse);

function submitToOpenAI($data) {
    // Use cURL to call OpenAI API
    $apiKey = "your_openai_api_key";
    $url = "https://api.openai.com/v1/completions";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response, true);
}

function generatePDF($analysis) {
    require('fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, 'Analysis Report');
    $pdf->Ln();

    foreach ($analysis as $key => $value) {
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 10, "$key: $value");
    }

    $filePath = "reports/" . $_SESSION['user_id'] . ".pdf";
    $pdf->Output('F', $filePath);

    echo json_encode(["report" => $filePath]);
}
?>

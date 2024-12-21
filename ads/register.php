<?php
include('db.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $zip_code = $_POST['zip_code'];

    $stmt = $pdo->prepare("INSERT INTO users (email, password, zip_code) VALUES (?, ?, ?)");
    $stmt->execute([$email, $password, $zip_code]);

    header('Location: index.php');
    exit;
}
?>
<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="text" name="zip_code" placeholder="Zip Code" required>
    <button type="submit">Register</button>
</form>

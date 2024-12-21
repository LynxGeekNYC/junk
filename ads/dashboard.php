<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<a href="buy_ad.php">Buy Ad Space</a>
<a href="logout.php">Logout</a>

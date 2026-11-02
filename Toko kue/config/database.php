<?php
$host = 'localhost';
$dbname = 'toko_kue';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi helper untuk keamanan
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
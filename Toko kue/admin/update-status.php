<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = (int)$_POST['id_pesanan'];
    $status = clean_input($_POST['status']);
    
    $stmt = $pdo->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
    $stmt->execute([$status, $id_pesanan]);
}

header('Location: index.php');
exit;
?>
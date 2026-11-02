<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kue Rumahan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/admin.css">
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
<div class="admin-header">
    <div class="logo">
        <span>⚡</span> Admin <span>Kue</span>
        <span class="badge-admin">v1.0</span>
    </div>
    <nav>
        <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Dashboard</a>
        <a href="produk.php" class="<?= basename($_SERVER['PHP_SELF']) == 'produk.php' ? 'active' : '' ?>">Produk</a>
        <a href="pesanan.php" class="<?= basename($_SERVER['PHP_SELF']) == 'pesanan.php' ? 'active' : '' ?>">Pesanan</a>
        <a href="pelanggan.php" class="<?= basename($_SERVER['PHP_SELF']) == 'pelanggan.php' ? 'active' : '' ?>">Pelanggan</a>
        <a href="logout.php" class="logout-link">Keluar</a>
    </nav>
</div>
<div class="admin-wrapper">
    <div class="admin-content">
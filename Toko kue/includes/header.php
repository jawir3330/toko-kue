<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kue Rumahan - Toko Kue Buatan Rumah</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset\css/style.css">
</head>
<body>
<header>
    <a href="index.php" class="logo">Kue<span>Rumahan</span></a>
    <nav>
        <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Beranda</a>
        <a href="produk.php" class="<?= basename($_SERVER['PHP_SELF']) == 'produk.php' ? 'active' : '' ?>">Produk</a>
        <a href="keranjang.php" class="<?= basename($_SERVER['PHP_SELF']) == 'keranjang.php' ? 'active' : '' ?>">Keranjang</a>
    </nav>
</header>
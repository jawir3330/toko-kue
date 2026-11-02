<?php
require_once 'config/database.php';
include 'includes/header.php';

$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

if ($kategori_filter) {
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE kategori = ?");
    $stmt->execute([$kategori_filter]);
} else {
    $stmt = $pdo->query("SELECT * FROM produk");
}
$produk_list = $stmt->fetchAll();

// Ambil daftar kategori untuk filter
$kategori_list = $pdo->query("SELECT DISTINCT kategori FROM produk WHERE kategori IS NOT NULL")->fetchAll();
?>
<div class="container">
    <h1 style="text-align:center;margin-bottom:1rem;">🍰 Semua Produk</h1>
    
    <!-- Filter Kategori -->
    <div style="text-align:center;margin-bottom:2rem;">
        <a href="produk.php" class="btn btn-secondary" style="margin:0.2rem;">Semua</a>
        <?php foreach ($kategori_list as $k): ?>
            <a href="produk.php?kategori=<?= urlencode($k['kategori']) ?>" class="btn btn-secondary" style="margin:0.2rem;">
                <?= $k['kategori'] ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="product-grid">
        <?php foreach ($produk_list as $produk): ?>
        <div class="product-card">
            <?php if ($produk['foto']): ?>
                <img src="assets/images/<?= $produk['foto'] ?>" alt="<?= $produk['nama_produk'] ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/300x200/FFD93D/2D3436?text=🍰+Kue" alt="Kue">
            <?php endif; ?>
            <div class="info">
                <span class="category"><?= $produk['kategori'] ?></span>
                <h3><?= $produk['nama_produk'] ?></h3>
                <div class="price">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></div>
                <a href="detail-produk.php?id=<?= $produk['id_produk'] ?>" class="btn">Pesan Sekarang</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
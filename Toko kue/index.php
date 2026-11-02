<?php
require_once 'config/database.php';
include 'includes/header.php';

// Ambil 4 produk terbaru
$stmt = $pdo->query("SELECT * FROM produk ORDER BY id_produk DESC LIMIT 4");
$produk_terbaru = $stmt->fetchAll();
?>
<div class="container">
    <div class="hero">
        <h1>🍰 Kue Buatan Rumah</h1>
        <p>Rasakan kelezatan kue homemade dengan cinta dan bahan berkualitas. Pesan sekarang untuk berbagai acara!</p>
        <a href="produk.php" class="btn">Lihat Semua Produk</a>
    </div>

    <h2 style="text-align:center;margin-bottom:2rem;">🔥 Produk Terbaru</h2>
    <div class="product-grid">
        <?php foreach ($produk_terbaru as $produk): ?>
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
                <a href="detail-produk.php?id=<?= $produk['id_produk'] ?>" class="btn">Lihat Detail</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
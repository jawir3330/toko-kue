<?php
require_once 'config/database.php';
include 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM produk WHERE id_produk = ?");
$stmt->execute([$id]);
$produk = $stmt->fetch();

if (!$produk) {
    echo "<div class='container'><h2>Produk tidak ditemukan</h2></div>";
    include 'includes/footer.php';
    exit;
}

// Cek apakah produk sudah di keranjang
$sudah_di_cart = false;
if (isset($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $item) {
        if ($item['id_produk'] == $id) {
            $sudah_di_cart = true;
            break;
        }
    }
}
?>
<div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.1);">
        <div>
            <?php if ($produk['foto']): ?>
                <img src="assets/images/<?= $produk['foto'] ?>" alt="<?= $produk['nama_produk'] ?>" style="width:100%;border-radius:8px;">
            <?php else: ?>
                <img src="https://via.placeholder.com/500x400/FFD93D/2D3436?text=🍰+Kue" style="width:100%;border-radius:8px;">
            <?php endif; ?>
        </div>
        <div>
            <span class="category"><?= $produk['kategori'] ?></span>
            <h1 style="font-size:2rem;margin:0.5rem 0;"><?= $produk['nama_produk'] ?></h1>
            <div style="font-size:2rem;color:var(--primary);font-weight:bold;">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></div>
            <p style="margin:1rem 0;color:#666;"><?= nl2br($produk['deskripsi']) ?></p>
            <p><strong>Stok:</strong> <?= $produk['stok'] > 0 ? $produk['stok'] . ' tersedia' : 'Habis' ?></p>
            
            <?php if ($produk['stok'] > 0): ?>
                <form method="POST" action="keranjang.php" style="margin-top:1rem;">
                    <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
                    <input type="hidden" name="aksi" value="tambah">
                    <div style="display:flex;gap:1rem;align-items:center;">
                        <label>Jumlah:</label>
                        <input type="number" name="jumlah" value="1" min="1" max="<?= $produk['stok'] ?>" style="width:80px;padding:0.5rem;border:2px solid #ddd;border-radius:8px;">
                        <button type="submit" class="btn">🛒 Masukkan Keranjang</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-danger">Maaf, stok produk ini sedang habis.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
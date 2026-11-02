<?php
session_start();
require_once 'config/database.php';
include 'includes/header.php';

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Proses tambah ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    if ($_POST['aksi'] === 'tambah') {
        $id_produk = (int)$_POST['id_produk'];
        $jumlah = (int)$_POST['jumlah'];
        
        // Cek stok
        $stmt = $pdo->prepare("SELECT stok FROM produk WHERE id_produk = ?");
        $stmt->execute([$id_produk]);
        $stok = $stmt->fetchColumn();
        
        if ($stok < $jumlah) {
            echo "<div class='container'><div class='alert alert-danger'>Stok tidak mencukupi!</div></div>";
            include 'includes/footer.php';
            exit;
        }
        
        // Cek apakah produk sudah ada di keranjang
        $found = false;
        foreach ($_SESSION['keranjang'] as &$item) {
            if ($item['id_produk'] == $id_produk) {
                $item['jumlah'] += $jumlah;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            // Ambil data produk
            $stmt = $pdo->prepare("SELECT * FROM produk WHERE id_produk = ?");
            $stmt->execute([$id_produk]);
            $produk = $stmt->fetch();
            
            $_SESSION['keranjang'][] = [
                'id_produk' => $produk['id_produk'],
                'nama_produk' => $produk['nama_produk'],
                'harga' => $produk['harga'],
                'jumlah' => $jumlah
            ];
        }
    } elseif ($_POST['aksi'] === 'hapus') {
        $id_produk = (int)$_POST['id_produk'];
        $_SESSION['keranjang'] = array_filter($_SESSION['keranjang'], function($item) use ($id_produk) {
            return $item['id_produk'] != $id_produk;
        });
        $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
    } elseif ($_POST['aksi'] === 'update') {
        $id_produk = (int)$_POST['id_produk'];
        $jumlah = (int)$_POST['jumlah'];
        foreach ($_SESSION['keranjang'] as &$item) {
            if ($item['id_produk'] == $id_produk) {
                $item['jumlah'] = $jumlah;
                break;
            }
        }
    }
    header('Location: keranjang.php');
    exit;
}

// Hitung total
$total = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total += $item['harga'] * $item['jumlah'];
}
?>

<div class="container">
    <h1 style="text-align:center;margin-bottom:2rem;">🛒 Keranjang Belanja</h1>
    
    <?php if (empty($_SESSION['keranjang'])): ?>
        <div style="text-align:center;padding:3rem;background:#fff;border-radius:12px;">
            <h2>Keranjang kosong</h2>
            <p>Yuk, pilih kue favoritmu!</p>
            <a href="produk.php" class="btn">Lihat Produk</a>
        </div>
    <?php else: ?>
        <?php foreach ($_SESSION['keranjang'] as $item): ?>
        <div class="cart-item">
            <div>
                <strong><?= $item['nama_produk'] ?></strong>
                <br>
                Rp <?= number_format($item['harga'], 0, ',', '.') ?> x 
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id_produk" value="<?= $item['id_produk'] ?>">
                    <input type="hidden" name="aksi" value="update">
                    <input type="number" name="jumlah" value="<?= $item['jumlah'] ?>" min="1" style="width:60px;padding:0.2rem;border:2px solid #ddd;border-radius:5px;" onchange="this.form.submit()">
                </form>
            </div>
            <div>
                <strong>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></strong>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id_produk" value="<?= $item['id_produk'] ?>">
                    <input type="hidden" name="aksi" value="hapus">
                    <button type="submit" style="background:none;border:none;color:#FF6B6B;font-size:1.2rem;cursor:pointer;">✕</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="cart-total">
            Total: Rp <?= number_format($total, 0, ',', '.') ?>
        </div>
        
        <div style="text-align:right;margin-top:1rem;">
            <a href="checkout.php" class="btn btn-accent">Checkout →</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
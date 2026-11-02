<?php
session_start();
require_once 'config/database.php';
include 'includes/header.php';

if (empty($_SESSION['keranjang'])) {
    header('Location: keranjang.php');
    exit;
}

// Hitung total
$total = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total += $item['harga'] * $item['jumlah'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean_input($_POST['nama']);
    $no_hp = clean_input($_POST['no_hp']);
    $alamat = clean_input($_POST['alamat']);
    $tanggal_ambil = clean_input($_POST['tanggal_ambil']);
    $catatan = clean_input($_POST['catatan']);
    
    // Simpan pelanggan
    $stmt = $pdo->prepare("INSERT INTO pelanggan (nama, no_hp, alamat) VALUES (?, ?, ?)");
    $stmt->execute([$nama, $no_hp, $alamat]);
    $id_pelanggan = $pdo->lastInsertId();
    
    // Simpan pesanan
    foreach ($_SESSION['keranjang'] as $item) {
        $subtotal = $item['harga'] * $item['jumlah'];
        $stmt = $pdo->prepare("INSERT INTO pesanan (id_pelanggan, id_produk, jumlah, subtotal, tanggal_ambil, catatan) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_pelanggan, $item['id_produk'], $item['jumlah'], $subtotal, $tanggal_ambil, $catatan]);
        
        // Kurangi stok
        $stmt = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id_produk = ?");
        $stmt->execute([$item['jumlah'], $item['id_produk']]);
    }
    
    // Kosongkan keranjang
    $_SESSION['keranjang'] = [];
    
    echo "<div class='container'>";
    echo "<div class='alert alert-success' style='text-align:center;padding:3rem;'>";
    echo "<h2>✅ Pesanan Berhasil!</h2>";
    echo "<p>Terima kasih, $nama. Pesanan Anda akan segera diproses.</p>";
    echo "<a href='index.php' class='btn'>Kembali ke Beranda</a>";
    echo "</div></div>";
    include 'includes/footer.php';
    exit;
}
?>
<div class="container">
    <div class="form-card">
        <h2>📝 Form Pemesanan</h2>
        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" name="nama" required>
            </div>
            <div class="form-group">
                <label>Nomor HP *</label>
                <input type="text" name="no_hp" required>
            </div>
            <div class="form-group">
                <label>Alamat Pengiriman *</label>
                <textarea name="alamat" required></textarea>
            </div>
            <div class="form-group">
                <label>Tanggal Ambil *</label>
                <input type="date" name="tanggal_ambil" required>
            </div>
            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="catatan" placeholder="Misal: tulisan di kue, alergi, dll."></textarea>
            </div>
            <div style="padding:1rem;background:#f5f5f5;border-radius:8px;margin-bottom:1rem;">
                <h4>Ringkasan Pesanan</h4>
                <?php foreach ($_SESSION['keranjang'] as $item): ?>
                    <p><?= $item['nama_produk'] ?> x <?= $item['jumlah'] ?> = Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></p>
                <?php endforeach; ?>
                <strong>Total: Rp <?= number_format($total, 0, ',', '.') ?></strong>
            </div>
            <button type="submit" class="btn" style="width:100%;font-size:1.1rem;">Pesan Sekarang</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
include 'template/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT 
        p.*,
        pl.nama as pelanggan_nama,
        pl.email as pelanggan_email,
        pl.no_hp as pelanggan_hp,
        pl.alamat as pelanggan_alamat,
        pr.nama_produk,
        pr.harga,
        pr.foto
    FROM pesanan p
    JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
    JOIN produk pr ON p.id_produk = pr.id_produk
    WHERE p.id_pesanan = ?
");
$stmt->execute([$id]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    echo "<div class='alert alert-danger'>Pesanan tidak ditemukan</div>";
    include 'template/footer.php';
    exit;
}
?>

<style>
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
.detail-section {
    background: var(--admin-white);
    border-radius: var(--admin-radius);
    padding: 1.5rem;
    box-shadow: var(--admin-shadow);
    border: 1px solid rgba(108,92,231,0.05);
}
.detail-section h4 {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--admin-gray);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--admin-light);
}
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--admin-light);
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-row .label {
    color: var(--admin-gray);
    font-weight: 500;
    font-size: 0.85rem;
}
.detail-row .value {
    font-weight: 600;
    color: var(--admin-dark);
    font-size: 0.9rem;
    text-align: right;
}
.detail-row .value .highlight {
    color: var(--admin-primary);
}
.status-big {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.status-big.baru { background: rgba(253,203,110,0.2); color: #B7950B; }
.status-big.diproses { background: rgba(116,185,255,0.2); color: #2980B9; }
.status-big.siap { background: rgba(0,184,148,0.2); color: #00A381; }
.status-big.selesai { background: var(--admin-light); color: var(--admin-gray); }
@media (max-width: 768px) {
    .detail-grid { grid-template-columns: 1fr; }
}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;margin-bottom:1.5rem;">
    <div>
        <a href="pesanan.php" class="back-link" style="display:inline-flex;">← Kembali ke Daftar</a>
    </div>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        <span class="status-big <?= $pesanan['status'] ?>">● <?= $pesanan['status'] ?></span>
    </div>
</div>

<div class="detail-grid">
    <!-- Kolom Kiri: Informasi Pesanan -->
    <div class="detail-section">
        <h4>📋 Informasi Pesanan</h4>
        <div class="detail-row">
            <span class="label">ID Pesanan</span>
            <span class="value">#<?= $pesanan['id_pesanan'] ?></span>
        </div>
        <div class="detail-row">
            <span class="label">Tanggal Pesan</span>
            <span class="value"><?= date('d F Y H:i', strtotime($pesanan['created_at'])) ?></span>
        </div>
        <div class="detail-row">
            <span class="label">Tanggal Ambil</span>
            <span class="value"><?= date('d F Y', strtotime($pesanan['tanggal_ambil'])) ?></span>
        </div>
        <div class="detail-row">
            <span class="label">Produk</span>
            <span class="value"><?= htmlspecialchars($pesanan['nama_produk']) ?></span>
        </div>
        <div class="detail-row">
            <span class="label">Jumlah</span>
            <span class="value"><?= $pesanan['jumlah'] ?></span>
        </div>
        <div class="detail-row">
            <span class="label">Harga Satuan</span>
            <span class="value">Rp <?= number_format($pesanan['harga'], 0, ',', '.') ?></span>
        </div>
        <div class="detail-row" style="border-bottom:none;padding-top:0.75rem;">
            <span class="label" style="font-weight:700;">Total</span>
            <span class="value" style="font-size:1.1rem;color:var(--admin-primary);">
                Rp <?= number_format($pesanan['subtotal'], 0, ',', '.') ?>
            </span>
        </div>
    </div>

    <!-- Kolom Kanan: Informasi Pelanggan & Update Status -->
    <div>
        <div class="detail-section" style="margin-bottom:1.5rem;">
            <h4>👤 Informasi Pelanggan</h4>
            <div class="detail-row">
                <span class="label">Nama</span>
                <span class="value"><?= htmlspecialchars($pesanan['pelanggan_nama']) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Email</span>
                <span class="value"><?= htmlspecialchars($pesanan['pelanggan_email'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Nomor HP</span>
                <span class="value"><?= htmlspecialchars($pesanan['pelanggan_hp']) ?></span>
            </div>
            <div class="detail-row" style="border-bottom:none;">
                <span class="label">Alamat</span>
                <span class="value" style="text-align:right;max-width:200px;"><?= nl2br(htmlspecialchars($pesanan['pelanggan_alamat'])) ?></span>
            </div>
        </div>

        <?php if ($pesanan['catatan']): ?>
        <div class="detail-section" style="margin-bottom:1.5rem;">
            <h4>📝 Catatan</h4>
            <p style="color:var(--admin-dark);font-size:0.9rem;margin:0;"><?= nl2br(htmlspecialchars($pesanan['catatan'])) ?></p>
        </div>
        <?php endif; ?>

        <div class="detail-section">
            <h4>🔄 Update Status</h4>
            <form method="POST" action="update-status.php" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
                <input type="hidden" name="id_pesanan" value="<?= $pesanan['id_pesanan'] ?>">
                <select name="status" class="status-select" style="flex:1;padding:0.5rem 1rem;font-size:0.9rem;">
                    <option value="baru" <?= $pesanan['status']=='baru'?'selected':'' ?>>Baru</option>
                    <option value="diproses" <?= $pesanan['status']=='diproses'?'selected':'' ?>>Diproses</option>
                    <option value="siap" <?= $pesanan['status']=='siap'?'selected':'' ?>>Siap Diambil</option>
                    <option value="selesai" <?= $pesanan['status']=='selesai'?'selected':'' ?>>Selesai</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </form>
        </div>
    </div>
</div>

<?php include 'template/footer.php'; ?>
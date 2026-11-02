<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
include 'template/header.php';

// Statistik
$total_produk = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
$total_pesanan = $pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
$pesanan_baru = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'baru'")->fetchColumn();
$total_pelanggan = $pdo->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn();
$total_pendapatan = $pdo->query("SELECT SUM(subtotal) FROM pesanan WHERE status != 'baru'")->fetchColumn();
?>

<!-- ==========================================
     QUICK ACTIONS
     ========================================== -->
<div class="quick-actions">
    <a href="produk.php" class="quick-action-card">
        <span class="qa-icon">📦</span>
        <div class="qa-title">Kelola Produk</div>
        <div class="qa-desc">Tambah, edit, hapus produk</div>
    </a>
    <a href="pesanan.php" class="quick-action-card">
        <span class="qa-icon">📋</span>
        <div class="qa-title">Kelola Pesanan</div>
        <div class="qa-desc">Lihat & update status pesanan</div>
    </a>
    <a href="pelanggan.php" class="quick-action-card">
        <span class="qa-icon">👤</span>
        <div class="qa-title">Kelola Pelanggan</div>
        <div class="qa-desc">Lihat & edit data pelanggan</div>
    </a>
    <a href="pesanan.php?status=baru" class="quick-action-card" style="border-color:rgba(225,112,85,0.2);">
        <span class="qa-icon">🔔</span>
        <div class="qa-title">Pesanan Baru</div>
        <div class="qa-desc" style="color:var(--admin-danger);font-weight:600;"><?= $pesanan_baru ?> pesanan menunggu</div>
    </a>
</div>

<!-- ==========================================
     STATISTICS
     ========================================== -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-icon">📦</span>
        <div class="stat-number"><?= number_format($total_produk) ?></div>
        <div class="stat-label">Total Produk</div>
    </div>
    <div class="stat-card gold">
        <span class="stat-icon">📋</span>
        <div class="stat-number"><?= number_format($total_pesanan) ?></div>
        <div class="stat-label">Total Pesanan</div>
    </div>
    <div class="stat-card danger">
        <span class="stat-icon">🔄</span>
        <div class="stat-number"><?= $pesanan_baru ?></div>
        <div class="stat-label">Pesanan Baru</div>
    </div>
    <div class="stat-card purple">
        <span class="stat-icon">👤</span>
        <div class="stat-number"><?= number_format($total_pelanggan) ?></div>
        <div class="stat-label">Total Pelanggan</div>
    </div>
    <div class="stat-card green">
        <span class="stat-icon">💰</span>
        <div class="stat-number">Rp <?= number_format($total_pendapatan ?? 0, 0, ',', '.') ?></div>
        <div class="stat-label">Total Pendapatan</div>
    </div>
</div>

<!-- ==========================================
     PESANAN TERBARU
     ========================================== -->
<div class="admin-card">
    <div class="card-header">
        <h2>
            📋 Pesanan Terbaru
            <span class="badge-count">10 terakhir</span>
        </h2>
        <div class="card-actions">
            <a href="pesanan.php" class="btn btn-primary btn-sm">Lihat Semua →</a>
        </div>
    </div>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Produk</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT p.*, pl.nama as pelanggan_nama, pr.nama_produk 
                                      FROM pesanan p 
                                      JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan 
                                      JOIN produk pr ON p.id_produk = pr.id_produk 
                                      ORDER BY p.id_pesanan DESC LIMIT 10");
                $has_pesanan = false;
                while ($row = $stmt->fetch()):
                    $has_pesanan = true;
                ?>
                <tr>
                    <td><strong>#<?= $row['id_pesanan'] ?></strong></td>
                    <td><?= htmlspecialchars($row['pelanggan_nama']) ?></td>
                    <td><?= htmlspecialchars($row['nama_produk']) ?> ×<?= $row['jumlah'] ?></td>
                    <td>Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                    <td>
                        <span class="badge badge-<?= $row['status'] === 'baru' ? 'warning' : ($row['status'] === 'diproses' ? 'info' : ($row['status'] === 'siap' ? 'success' : 'gray')) ?>">
                            <?= $row['status'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="table-actions" style="justify-content:center;">
                            <a href="detail-pesanan.php?id=<?= $row['id_pesanan'] ?>" class="btn-action btn-action-view">👁️ Detail</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if (!$has_pesanan): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <h3>Belum Ada Pesanan</h3>
                            <p>Belum ada pesanan yang masuk dari pelanggan.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'template/footer.php'; ?>
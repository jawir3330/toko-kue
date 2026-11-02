<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
include 'template/header.php';

// Proses CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    if ($_POST['aksi'] === 'hapus') {
        $id = (int)$_POST['id_pesanan'];
        $stmt = $pdo->prepare("DELETE FROM pesanan WHERE id_pesanan = ?");
        $stmt->execute([$id]);
        $success = "Pesanan berhasil dihapus!";
    }
    elseif ($_POST['aksi'] === 'update_status') {
        $id = (int)$_POST['id_pesanan'];
        $status = clean_input($_POST['status']);
        $allowed = ['baru', 'diproses', 'siap', 'selesai'];
        if (in_array($status, $allowed)) {
            $stmt = $pdo->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
            $stmt->execute([$status, $id]);
            $success = "Status pesanan berhasil diperbarui!";
        }
    }
}

// Filter
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$filter_keyword = isset($_GET['keyword']) ? clean_input($_GET['keyword']) : '';

$sql = "
    SELECT 
        p.id_pesanan, p.jumlah, p.subtotal, p.tanggal_ambil, p.catatan, p.status, p.created_at,
        pl.nama as pelanggan_nama, pl.no_hp as pelanggan_hp, pl.alamat as pelanggan_alamat,
        pr.nama_produk, pr.harga
    FROM pesanan p
    JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
    JOIN produk pr ON p.id_produk = pr.id_produk
    WHERE 1=1
";

$params = [];
if ($filter_status) {
    $sql .= " AND p.status = ?";
    $params[] = $filter_status;
}
if ($filter_keyword) {
    $sql .= " AND (pl.nama LIKE ? OR pr.nama_produk LIKE ? OR p.id_pesanan LIKE ?)";
    $kw = "%$filter_keyword%";
    $params = array_merge($params, [$kw, $kw, $kw]);
}

$sql .= " ORDER BY p.id_pesanan DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pesanan_list = $stmt->fetchAll();

// Statistik
$total_pesanan = count($pesanan_list);
$total_pendapatan = 0;
$status_count = ['baru' => 0, 'diproses' => 0, 'siap' => 0, 'selesai' => 0];
foreach ($pesanan_list as $row) {
    $total_pendapatan += $row['subtotal'];
    $status_count[$row['status']]++;
}
?>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<!-- ==========================================
     STATISTICS
     ========================================== -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-icon">📋</span>
        <div class="stat-number"><?= $total_pesanan ?></div>
        <div class="stat-label">Total Pesanan</div>
    </div>
    <div class="stat-card gold">
        <span class="stat-icon">🔄</span>
        <div class="stat-number"><?= $status_count['baru'] ?></div>
        <div class="stat-label">Baru</div>
    </div>
    <div class="stat-card blue">
        <span class="stat-icon">⚙️</span>
        <div class="stat-number"><?= $status_count['diproses'] ?></div>
        <div class="stat-label">Diproses</div>
    </div>
    <div class="stat-card green">
        <span class="stat-icon">✅</span>
        <div class="stat-number"><?= $status_count['siap'] ?></div>
        <div class="stat-label">Siap Diambil</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">💰</span>
        <div class="stat-number">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
        <div class="stat-label">Pendapatan</div>
    </div>
</div>

<!-- ==========================================
     FILTER & SEARCH
     ========================================== -->
<div class="admin-card">
    <div class="table-container">
        <div class="table-toolbar">
            <div class="toolbar-left">
                <div class="search-box">
                    <form method="GET" style="display:flex;gap:0.5rem;align-items:center;">
                        <input type="text" name="keyword" placeholder="🔍 Cari pelanggan atau produk..." value="<?= htmlspecialchars($filter_keyword) ?>">
                        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                        <?php if ($filter_keyword || $filter_status): ?>
                            <a href="pesanan.php" class="btn btn-secondary btn-sm">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>
                <select onchange="window.location.href='pesanan.php?status='+this.value">
                    <option value="">Semua Status</option>
                    <option value="baru" <?= $filter_status === 'baru' ? 'selected' : '' ?>>Baru</option>
                    <option value="diproses" <?= $filter_status === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="siap" <?= $filter_status === 'siap' ? 'selected' : '' ?>>Siap</option>
                    <option value="selesai" <?= $filter_status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div>
                <span style="font-size:0.8rem;color:var(--admin-gray);">Ditemukan: <?= $total_pesanan ?> pesanan</span>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     DAFTAR PESANAN
     ========================================== -->
<div class="table-container">
    <?php if (count($pesanan_list) > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Produk</th>
                <th>Jml</th>
                <th>Total</th>
                <th>Tgl Ambil</th>
                <th>Status</th>
                <th style="text-align:center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pesanan_list as $row): ?>
            <tr>
                <td><strong>#<?= $row['id_pesanan'] ?></strong></td>
                <td>
                    <?= htmlspecialchars($row['pelanggan_nama']) ?>
                    <br><small style="color:var(--admin-gray);font-size:0.7rem;"><?= htmlspecialchars($row['pelanggan_hp']) ?></small>
                </td>
                <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                <td><?= $row['jumlah'] ?></td>
                <td>Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal_ambil'])) ?></td>
                <td>
                    <span class="badge badge-<?= $row['status'] === 'baru' ? 'warning' : ($row['status'] === 'diproses' ? 'info' : ($row['status'] === 'siap' ? 'success' : 'gray')) ?>">
                        <?= $row['status'] ?>
                    </span>
                </td>
                <td>
                    <div class="table-actions" style="justify-content:center;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="aksi" value="update_status">
                            <input type="hidden" name="id_pesanan" value="<?= $row['id_pesanan'] ?>">
                            <select name="status" onchange="this.form.submit()" class="status-select">
                                <option value="baru" <?= $row['status']=='baru'?'selected':'' ?>>Baru</option>
                                <option value="diproses" <?= $row['status']=='diproses'?'selected':'' ?>>Diproses</option>
                                <option value="siap" <?= $row['status']=='siap'?'selected':'' ?>>Siap</option>
                                <option value="selesai" <?= $row['status']=='selesai'?'selected':'' ?>>Selesai</option>
                            </select>
                        </form>
                        <a href="detail-pesanan.php?id=<?= $row['id_pesanan'] ?>" class="btn-action btn-action-view">👁️</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus pesanan ini?')">
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="id_pesanan" value="<?= $row['id_pesanan'] ?>">
                            <button type="submit" class="btn-action btn-action-delete">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>Belum Ada Pesanan</h3>
        <p>Belum ada pesanan yang masuk dari pelanggan.</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'template/footer.php'; ?>
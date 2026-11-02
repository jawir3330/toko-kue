<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
include 'template/header.php';

// ==========================================
// PROSES CRUD
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];
    
    if ($aksi === 'tambah') {
        $nama = clean_input($_POST['nama_produk']);
        $harga = (float)$_POST['harga'];
        $deskripsi = clean_input($_POST['deskripsi']);
        $kategori = clean_input($_POST['kategori']);
        $stok = (int)$_POST['stok'];
        
        $foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $target_dir = "../assets/images/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $foto = time() . '_' . basename($_FILES['foto']['name']);
            move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto);
        }
        
        $stmt = $pdo->prepare("INSERT INTO produk (nama_produk, harga, deskripsi, kategori, stok, foto) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $harga, $deskripsi, $kategori, $stok, $foto]);
        $success = "Produk berhasil ditambahkan!";
    }
    
    elseif ($aksi === 'edit') {
        $id = (int)$_POST['id_produk'];
        $nama = clean_input($_POST['nama_produk']);
        $harga = (float)$_POST['harga'];
        $deskripsi = clean_input($_POST['deskripsi']);
        $kategori = clean_input($_POST['kategori']);
        $stok = (int)$_POST['stok'];
        
        $stmt = $pdo->prepare("SELECT foto FROM produk WHERE id_produk = ?");
        $stmt->execute([$id]);
        $foto_lama = $stmt->fetchColumn();
        $foto = $foto_lama;
        
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $target_dir = "../asset/images/";
            $foto = time() . '_' . basename($_FILES['foto']['name']);
            move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto);
            if ($foto_lama && file_exists($target_dir . $foto_lama)) {
                unlink($target_dir . $foto_lama);
            }
        }
        
        $stmt = $pdo->prepare("UPDATE produk SET nama_produk = ?, harga = ?, deskripsi = ?, kategori = ?, stok = ?, foto = ? WHERE id_produk = ?");
        $stmt->execute([$nama, $harga, $deskripsi, $kategori, $stok, $foto, $id]);
        $success = "Produk berhasil diperbarui!";
    }
    
    elseif ($aksi === 'hapus') {
        $id = (int)$_POST['id_produk'];
        $stmt = $pdo->prepare("SELECT foto FROM produk WHERE id_produk = ?");
        $stmt->execute([$id]);
        $foto = $stmt->fetchColumn();
        if ($foto && file_exists("../assets/images/" . $foto)) {
            unlink("../assets/images/" . $foto);
        }
        $stmt = $pdo->prepare("DELETE FROM produk WHERE id_produk = ?");
        $stmt->execute([$id]);
        $success = "Produk berhasil dihapus!";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE id_produk = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}

$produk_list = $pdo->query("SELECT * FROM produk ORDER BY id_produk DESC")->fetchAll();
$kategori_list = $pdo->query("SELECT DISTINCT kategori FROM produk WHERE kategori IS NOT NULL")->fetchAll();
$total_produk = count($produk_list);
$total_stok = $pdo->query("SELECT SUM(stok) FROM produk")->fetchColumn();
?>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<!-- ==========================================
     STATISTICS
     ========================================== -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-icon">📦</span>
        <div class="stat-number"><?= $total_produk ?></div>
        <div class="stat-label">Total Produk</div>
    </div>
    <div class="stat-card gold">
        <span class="stat-icon">📊</span>
        <div class="stat-number"><?= $total_stok ?></div>
        <div class="stat-label">Total Stok</div>
    </div>
    <div class="stat-card green">
        <span class="stat-icon">🏷️</span>
        <div class="stat-number"><?= count($kategori_list) ?></div>
        <div class="stat-label">Kategori</div>
    </div>
</div>

<!-- ==========================================
     FORM TAMBAH / EDIT
     ========================================== -->
<div class="admin-card">
    <div class="card-header">
        <h2>
            <?= $edit_data ? '✏️ Edit Produk' : '➕ Tambah Produk Baru' ?>
            <?php if ($edit_data): ?>
                <span class="badge-count">ID #<?= $edit_data['id_produk'] ?></span>
            <?php endif; ?>
        </h2>
        <?php if ($edit_data): ?>
            <div class="card-actions">
                <a href="produk.php" class="btn btn-secondary btn-sm">← Kembali</a>
            </div>
        <?php endif; ?>
    </div>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="aksi" value="<?= $edit_data ? 'edit' : 'tambah' ?>">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id_produk" value="<?= $edit_data['id_produk'] ?>">
        <?php endif; ?>
        
        <div class="form-grid">
            <div class="form-group">
                <label>Nama Produk <span class="required">*</span></label>
                <input type="text" name="nama_produk" required value="<?= $edit_data ? htmlspecialchars($edit_data['nama_produk']) : '' ?>" placeholder="Contoh: Black Forest Cake">
            </div>
            <div class="form-group">
                <label>Harga (Rp) <span class="required">*</span></label>
                <input type="number" name="harga" required value="<?= $edit_data ? $edit_data['harga'] : '' ?>" placeholder="250000">
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <input type="text" name="kategori" value="<?= $edit_data ? htmlspecialchars($edit_data['kategori']) : '' ?>" placeholder="Tart, Kue Kering, Bolu">
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stok" value="<?= $edit_data ? $edit_data['stok'] : '0' ?>" min="0">
            </div>
            <div class="form-group full-width">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="3" placeholder="Deskripsi produk..."><?= $edit_data ? htmlspecialchars($edit_data['deskripsi']) : '' ?></textarea>
            </div>
            <div class="form-group full-width">
                <label>Foto Produk</label>
                <input type="file" name="foto" accept="image/*">
                <?php if ($edit_data && $edit_data['foto']): ?>
                    <div class="current-photo">
                        <span class="photo-label">Foto saat ini:</span>
                        <img src="/asset/images/<?= $edit_data['foto'] ?>" alt="Foto produk">
                    </div>
                <?php endif; ?>
                <span class="form-hint">Upload foto dengan format JPG, PNG. Maksimal 2MB.</span>
            </div>
        </div>
        
        <div style="display:flex;gap:0.75rem;margin-top:1.5rem;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary">
                <?= $edit_data ? '💾 Update Produk' : '➕ Tambah Produk' ?>
            </button>
            <?php if ($edit_data): ?>
                <a href="produk.php" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- ==========================================
     DAFTAR PRODUK
     ========================================== -->
<div class="admin-card">
    <div class="card-header">
        <h2>
            📋 Daftar Produk
            <span class="badge-count"><?= $total_produk ?></span>
        </h2>
        <div class="card-actions">
            <div style="display:flex;gap:0.3rem;flex-wrap:wrap;">
                <a href="produk.php" class="btn btn-secondary btn-sm">Semua</a>
                <?php foreach ($kategori_list as $k): ?>
                    <a href="produk.php?kategori=<?= urlencode($k['kategori']) ?>" class="btn btn-secondary btn-sm">
                        <?= htmlspecialchars($k['kategori']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
                $displayed = 0;
                foreach ($produk_list as $produk):
                    if ($filter_kategori && $produk['kategori'] !== $filter_kategori) continue;
                    $displayed++;
                ?>
                <tr>
                    <td>
                        <?php if ($produk['foto']): ?>
                            <img src="../assets/images/<?= $produk['foto'] ?>" alt="<?= $produk['nama_produk'] ?>" class="table-thumb">
                        <?php else: ?>
                            <div class="table-thumb-placeholder">No Image</div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($produk['nama_produk']) ?></strong></td>
                    <td><span class="badge badge-gray"><?= htmlspecialchars($produk['kategori'] ?? '-') ?></span></td>
                    <td>Rp <?= number_format($produk['harga'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($produk['stok'] > 10): ?>
                            <span class="badge badge-success"><?= $produk['stok'] ?></span>
                        <?php elseif ($produk['stok'] > 0): ?>
                            <span class="badge badge-warning"><?= $produk['stok'] ?></span>
                        <?php else: ?>
                            <span class="badge badge-danger">Habis</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="table-actions" style="justify-content:center;">
                            <a href="produk.php?edit=<?= $produk['id_produk'] ?>" class="btn-action btn-action-edit">✏️ Edit</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
                                <button type="submit" class="btn-action btn-action-delete">🗑️ Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if ($displayed === 0): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <h3>Belum Ada Produk</h3>
                            <p>Mulai tambahkan produk kue pertama Anda!</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'template/footer.php'; ?>
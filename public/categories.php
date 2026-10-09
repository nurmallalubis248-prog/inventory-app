<?php
require_once '../database/config/database.php';

// 1. PROSES POST HARUS DI PALING ATAS SEBELUM HEADER HTML
$editData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $id = $_POST['id'] ?? '';

    if (!empty($name)) {
        if (!empty($id)) {
            $stmt = $pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
            $stmt->execute(['name' => $name, 'id' => $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt->execute(['name' => $name]);
        }
        header('Location: categories.php');
        exit();
    }
}

// Ambil data untuk Edit
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $editData = $stmt->fetch();
}

// Proses Hapus
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute(['id' => $_GET['delete']]);
    header('Location: categories.php');
    exit();
}

// 2. INCLUDE HEADER SETELAH LOGIKA POST SELESAI
include '../includes/header.php';

$search = $_GET['search'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM categories WHERE name LIKE :s ORDER BY id DESC");
$stmt->execute(['s' => "%$search%"]);
$categories = $stmt->fetchAll();
?>

<div class="card">
    <h3><?= $editData ? 'Edit Kategori' : 'Tambah Kategori Baru'; ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $editData['id'] ?? ''; ?>">
        <div class="form-group" style="max-width: 400px; margin-bottom: 12px;">
            <label>Nama Kategori</label>
            <input type="text" name="name" value="<?= htmlspecialchars($editData['name'] ?? ''); ?>" placeholder="Contoh: Elektronik" required autocomplete="off">
        </div>
        <button type="submit" class="btn-pink"><?= $editData ? 'Perbarui Kategori' : 'Simpan Kategori'; ?></button>
        <?php if ($editData): ?>
            <a href="categories.php" style="margin-left: 10px; color: #64748b; text-decoration: none; font-size: 14px;">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="search-bar">
        <h3>Daftar Kategori</h3>
        <form method="GET"><input type="text" name="search" placeholder="Cari kategori..." value="<?= htmlspecialchars($search); ?>"></form>
    </div>
    <table>
        <thead><tr><th>NO</th><th>NAMA KATEGORI</th><th>AKSI</th></tr></thead>
        <tbody>
            <?php if (count($categories) > 0): foreach ($categories as $i => $c): ?>
            <tr>
                <td><?= $i + 1; ?></td>
                <td><strong><?= htmlspecialchars($c['name']); ?></strong></td>
                <td>
                    <a href="categories.php?edit=<?= $c['id']; ?>" class="action-btn" style="color: #0284c7;">Ubah</a>
                    <a href="categories.php?delete=<?= $c['id']; ?>" class="action-btn del" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="3" style="text-align: center; color: #94a3b8;">Belum ada data kategori.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
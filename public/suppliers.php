<?php
require_once '../database/config/database.php';

// 1. PROSES POST HARUS DI PALING ATAS SEBELUM HEADER HTML
$editData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $id = $_POST['id'] ?? '';

    if (!empty($name)) {
        if (!empty($id)) {
            $stmt = $pdo->prepare("UPDATE suppliers SET name = :n, phone = :p, address = :a WHERE id = :id");
            $stmt->execute(['n' => $name, 'p' => $phone, 'a' => $address, 'id' => $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO suppliers (name, phone, address) VALUES (:n, :p, :a)");
            $stmt->execute(['n' => $name, 'p' => $phone, 'a' => $address]);
        }
        header('Location: suppliers.php');
        exit();
    }
}

// Ambil data untuk Edit
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $editData = $stmt->fetch();
}

// Proses Hapus
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = :id");
    $stmt->execute(['id' => $_GET['delete']]);
    header('Location: suppliers.php');
    exit();
}

// 2. INCLUDE HEADER SETELAH LOGIKA POST SELESAI
include '../includes/header.php';

$search = $_GET['search'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE name LIKE :s ORDER BY id DESC");
$stmt->execute(['s' => "%$search%"]);
$suppliers = $stmt->fetchAll();
?>

<div class="card">
    <h3><?= $editData ? 'Edit Supplier' : 'Tambah Supplier Baru'; ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $editData['id'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group"><label>Nama Supplier</label><input type="text" name="name" value="<?= htmlspecialchars($editData['name'] ?? ''); ?>" required autocomplete="off"></div>
            <div class="form-group"><label>No Telepon</label><input type="text" name="phone" value="<?= htmlspecialchars($editData['phone'] ?? ''); ?>" autocomplete="off"></div>
        </div>
        <div class="form-group" style="margin-bottom: 12px;"><label>Alamat</label><input type="text" name="address" value="<?= htmlspecialchars($editData['address'] ?? ''); ?>" autocomplete="off"></div>
        <button type="submit" class="btn-pink"><?= $editData ? 'Perbarui Supplier' : 'Simpan Supplier'; ?></button>
        <?php if ($editData): ?>
            <a href="suppliers.php" style="margin-left: 10px; color: #64748b; text-decoration: none; font-size: 14px;">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="search-bar">
        <h3>Daftar Supplier</h3>
        <form method="GET"><input type="text" name="search" placeholder="Cari supplier..." value="<?= htmlspecialchars($search); ?>"></form>
    </div>
    <table>
        <thead><tr><th>NO</th><th>NAMA</th><th>TELEPON</th><th>ALAMAT</th><th>AKSI</th></tr></thead>
        <tbody>
            <?php if (count($suppliers) > 0): foreach ($suppliers as $i => $s): ?>
            <tr>
                <td><?= $i + 1; ?></td>
                <td><strong><?= htmlspecialchars($s['name']); ?></strong></td>
                <td><?= htmlspecialchars($s['phone']); ?></td>
                <td><?= htmlspecialchars($s['address']); ?></td>
                <td>
                    <a href="suppliers.php?edit=<?= $s['id']; ?>" class="action-btn" style="color: #0284c7;">Ubah</a>
                    <a href="suppliers.php?delete=<?= $s['id']; ?>" class="action-btn del" onclick="return confirm('Hapus supplier ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="5" style="text-align: center; color: #94a3b8;">Belum ada data supplier.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
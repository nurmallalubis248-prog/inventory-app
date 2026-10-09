<?php
require_once '../database/config/database.php';

// 1. PROSES POST HARUS DI PALING ATAS SEBELUM HEADER HTML
$editData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?: null;
    $stock = intval($_POST['stock'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $id = $_POST['id'] ?? '';

    if (!empty($name)) {
        if (!empty($id)) {
            $stmt = $pdo->prepare("UPDATE products SET code = :code, name = :name, category_id = :cat, stock = :stock, price = :price WHERE id = :id");
            $stmt->execute(['code' => $code, 'name' => $name, 'cat' => $category_id, 'stock' => $stock, 'price' => $price, 'id' => $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (code, name, category_id, stock, price) VALUES (:code, :name, :cat, :stock, :price)");
            $stmt->execute(['code' => $code, 'name' => $name, 'cat' => $category_id, 'stock' => $stock, 'price' => $price]);
        }
        header('Location: products.php');
        exit();
    }
}

// 2. AMBIL DATA EDIT & HAPUS
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $editData = $stmt->fetch();
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $_GET['delete']]);
    header('Location: products.php');
    exit();
}

// 3. INCLUDE HEADER SETELAH LOGIKA POST SELESAI
include '../includes/header.php';

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

$search = $_GET['search'] ?? '';
$searchTerm = "%$search%";
$stmt = $pdo->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.name LIKE :s1 OR p.code LIKE :s2 ORDER BY p.id DESC");
$stmt->execute([
    's1' => $searchTerm,
    's2' => $searchTerm
]);
$products = $stmt->fetchAll();
?>

<div class="card">
    <h3><?= $editData ? 'Edit Produk' : 'Tambah Produk Baru'; ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $editData['id'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group"><label>Kode Produk</label><input type="text" name="code" value="<?= htmlspecialchars($editData['code'] ?? ''); ?>" required autocomplete="off"></div>
            <div class="form-group"><label>Nama Produk</label><input type="text" name="name" value="<?= htmlspecialchars($editData['name'] ?? ''); ?>" required autocomplete="off"></div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="category_id">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id']; ?>" <?= ($editData['category_id'] ?? '') == $c['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Stok</label><input type="number" name="stock" value="<?= $editData['stock'] ?? 0; ?>" min="0" required></div>
            <div class="form-group"><label>Harga (Rp)</label><input type="number" name="price" value="<?= $editData['price'] ?? 0; ?>" min="0" required></div>
        </div>
        <button type="submit" class="btn-pink"><?= $editData ? 'Perbarui Produk' : 'Simpan Produk'; ?></button>
        <?php if ($editData): ?>
            <a href="products.php" style="margin-left: 10px; color: #64748b; text-decoration: none; font-size: 14px;">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="search-bar">
        <h3>Daftar Produk</h3>
        <form method="GET"><input type="text" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search); ?>"></form>
    </div>
    <table>
        <thead><tr><th>NO</th><th>KODE</th><th>NAMA PRODUK</th><th>KATEGORI</th><th>STOK</th><th>HARGA</th><th>AKSI</th></tr></thead>
        <tbody>
            <?php if (count($products) > 0): foreach ($products as $i => $p): ?>
            <tr>
                <td><?= $i + 1; ?></td>
                <td><?= htmlspecialchars($p['code']); ?></td>
                <td><strong><?= htmlspecialchars($p['name']); ?></strong></td>
                <td><?= htmlspecialchars($p['cat_name'] ?? 'Tanpa Kategori'); ?></td>
                <td><strong><?= $p['stock']; ?></strong></td>
                <td>Rp <?= number_format($p['price'], 0, ',', '.'); ?></td>
                <td>
                    <a href="products.php?edit=<?= $p['id']; ?>" class="action-btn" style="color: #0284c7;">Ubah</a>
                    <a href="products.php?delete=<?= $p['id']; ?>" class="action-btn del" onclick="return confirm('Hapus produk ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7" style="text-align: center; color: #94a3b8;">Belum ada data produk.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
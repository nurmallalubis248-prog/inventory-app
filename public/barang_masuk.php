<?php
ob_start(); // Tambahkan ini di baris paling atas agar header warning hilang
require_once '../database/config/database.php';
include '../includes/header.php';

$products = $pdo->query("SELECT * FROM products")->fetchAll();
$editData = null;

// Proses Tambah / Update Barang Masuk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = $_POST['product_id'];
    $qty = intval($_POST['qty']);
    $reference = trim($_POST['reference'] ?? 'Barang Masuk');
    $id = $_POST['id'] ?? '';

    if ($pid && $qty > 0) {
        if (!empty($id)) {
            // Edit transaksi barang masuk (sesuaikan selisih stok)
            $oldStmt = $pdo->prepare("SELECT * FROM stock_movements WHERE id = :id");
            $oldStmt->execute(['id' => $id]);
            $oldData = $oldStmt->fetch();

            if ($oldData) {
                $selisih = $qty - $oldData['qty'];
                // Update stok produk
                $pdo->prepare("UPDATE products SET stock = stock + :selisih WHERE id = :pid")->execute(['selisih' => $selisih, 'pid' => $pid]);
                // Update record mutasi
                $pdo->prepare("UPDATE stock_movements SET product_id = :pid, qty = :qty, reference = :ref WHERE id = :id")->execute(['pid' => $pid, 'qty' => $qty, 'ref' => $reference, 'id' => $id]);
            }
        } else {
            // Tambah barang masuk baru
            $pdo->prepare("INSERT INTO stock_movements (product_id, type, qty, reference, created_at) VALUES (:pid, 'IN', :qty, :ref, CURDATE())")
                ->execute(['pid' => $pid, 'qty' => $qty, 'ref' => $reference]);
            // Tambah stok produk otomatis
            $pdo->prepare("UPDATE products SET stock = stock + :qty WHERE id = :pid")
                ->execute(['qty' => $qty, 'pid' => $pid]);
        }
        header('Location: barang_masuk.php');
        exit();
    }
}

// Ambil data untuk Edit
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM stock_movements WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $editData = $stmt->fetch();
}

// Hapus transaksi barang masuk
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT * FROM stock_movements WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $m = $stmt->fetch();
    if ($m) {
        // Kembalikan stok produk semula
        $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :pid")->execute(['qty' => $m['qty'], 'pid' => $m['product_id']]);
        $pdo->prepare("DELETE FROM stock_movements WHERE id = :id")->execute(['id' => $id]);
    }
    header('Location: barang_masuk.php');
    exit();
}

$search = $_GET['search'] ?? '';
$stmt = $pdo->prepare("SELECT sm.*, p.name as product_name FROM stock_movements sm JOIN products p ON sm.product_id = p.id WHERE sm.type = 'IN' AND p.name LIKE :s ORDER BY sm.id DESC");
$stmt->execute(['s' => "%$search%"]);
$items = $stmt->fetchAll();
?>

<div class="card">
    <h3><?= $editData ? 'Edit Barang Masuk' : 'Catat Barang Masuk'; ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $editData['id'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Pilih Produk</label>
                <select name="product_id" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach($products as $p): ?>
                        <option value="<?= $p['id']; ?>" <?= ($editData['product_id'] ?? '') == $p['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($p['name']); ?> (Stok: <?= $p['stock']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah Masuk (Qty)</label>
                <input type="number" name="qty" value="<?= $editData['qty'] ?? 1; ?>" min="1" required>
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 12px;">
            <label>Keterangan / Supplier</label>
            <input type="text" name="reference" value="<?= htmlspecialchars($editData['reference'] ?? 'Barang Masuk'); ?>" required>
        </div>
        <button type="submit" class="btn-pink"><?= $editData ? 'Perbarui Barang Masuk' : 'Tambah Stok Masuk'; ?></button>
        <?php if ($editData): ?>
            <a href="barang_masuk.php" style="margin-left: 10px; color: #64748b; text-decoration: none;">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="search-bar">
        <h3>Riwayat Barang Masuk</h3>
        <form method="GET"><input type="text" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search); ?>"></form>
    </div>
    <table>
        <thead><tr><th>NO</th><th>PRODUK</th><th>JUMLAH</th><th>KETERANGAN</th><th>TANGGAL</th><th>AKSI</th></tr></thead>
        <tbody>
            <?php if(count($items) > 0): foreach($items as $i => $item): ?>
            <tr>
                <td><?= $i+1; ?></td>
                <td><strong><?= htmlspecialchars($item['product_name']); ?></strong></td>
                <td><strong style="color:#db2777;">+<?= $item['qty']; ?></strong></td>
                <td><?= htmlspecialchars($item['reference']); ?></td>
                <td><?= htmlspecialchars($item['created_at']); ?></td>
                <td>
                    <a href="barang_masuk.php?edit=<?= $item['id']; ?>" class="action-btn" style="color: #0284c7;">Ubah</a>
                    <a href="barang_masuk.php?delete=<?= $item['id']; ?>" class="action-btn del" onclick="return confirm('Hapus transaksi ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6" style="text-align: center; color: #94a3b8;">Belum ada riwayat barang masuk.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>
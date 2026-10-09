<?php
require_once '../database/config/database.php';

// 1. PROSES POST & VALIDASI STOK
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = $_POST['product_id'] ?? '';
    $qty = intval($_POST['qty'] ?? 0);
    $reference = trim($_POST['reference'] ?? 'Barang Keluar');
    $id = $_POST['id'] ?? '';

    if ($pid && $qty > 0) {
        // Ambil data stok produk saat ini
        $stmt = $pdo->prepare("SELECT stock, name FROM products WHERE id = :id");
        $stmt->execute(['id' => $pid]);
        $prod = $stmt->fetch();

        if ($prod) {
            if (!empty($id)) {
                // Mode Edit Transaksi Keluar
                $oldStmt = $pdo->prepare("SELECT * FROM stock_movements WHERE id = :id");
                $oldStmt->execute(['id' => $id]);
                $oldData = $oldStmt->fetch();

                if ($oldData) {
                    $selisih = $qty - $oldData['qty'];
                    // Cek apakah stok cukup jika ada penambahan selisih
                    if ($prod['stock'] < $selisih) {
                        $errorMsg = "Gagal! Stok produk '{$prod['name']}' tidak mencukupi. Sisa stok saat ini: {$prod['stock']}.";
                    } else {
                        $pdo->prepare("UPDATE products SET stock = stock - :selisih WHERE id = :pid")->execute(['selisih' => $selisih, 'pid' => $pid]);
                        $pdo->prepare("UPDATE stock_movements SET product_id = :pid, qty = :qty, reference = :ref WHERE id = :id")->execute(['pid' => $pid, 'qty' => $qty, 'ref' => $reference, 'id' => $id]);
                        header('Location: barang_keluar.php');
                        exit();
                    }
                }
            } else {
                // Mode Tambah Transaksi Keluar Baru (Validasi Stok Cukup)
                if ($prod['stock'] < $qty) {
                    $errorMsg = "Gagal! Stok produk '{$prod['name']}' tidak mencukupi. Anda meminta mengeluarkan $qty, tetapi sisa stok hanya {$prod['stock']}.";
                } else {
                    $pdo->prepare("INSERT INTO stock_movements (product_id, type, qty, reference, created_at) VALUES (:pid, 'OUT', :qty, :ref, CURDATE())")
                        ->execute(['pid' => $pid, 'qty' => $qty, 'ref' => $reference]);
                    $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :pid")
                        ->execute(['qty' => $qty, 'pid' => $pid]);
                    header('Location: barang_keluar.php');
                    exit();
                }
            }
        }
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM stock_movements WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $editData = $stmt->fetch();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT * FROM stock_movements WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $m = $stmt->fetch();
    if ($m) {
        $pdo->prepare("UPDATE products SET stock = stock + :qty WHERE id = :pid")->execute(['qty' => $m['qty'], 'pid' => $m['product_id']]);
        $pdo->prepare("DELETE FROM stock_movements WHERE id = :id")->execute(['id' => $id]);
    }
    header('Location: barang_keluar.php');
    exit();
}

// 2. INCLUDE HEADER SETELAH LOGIKA POST SELESAI
include '../includes/header.php';

$products = $pdo->query("SELECT * FROM products")->fetchAll();

$search = $_GET['search'] ?? '';
$stmt = $pdo->prepare("SELECT sm.*, p.name as product_name FROM stock_movements sm JOIN products p ON sm.product_id = p.id WHERE sm.type = 'OUT' AND p.name LIKE :s ORDER BY sm.id DESC");
$stmt->execute(['s' => "%$search%"]);
$items = $stmt->fetchAll();
?>

<!-- TAMPILAN PESAN ERROR JIKA STOK TIDAK CUKUP -->
<?php if (!empty($errorMsg)): ?>
<div style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 10px;">
    <i class="fa-solid fa-triangle-exclamation" style="font-size: 18px;"></i>
    <span><?= htmlspecialchars($errorMsg); ?></span>
</div>
<?php endif; ?>

<div class="card">
    <h3><?= $editData ? 'Edit Barang Keluar' : 'Catat Barang Keluar'; ?></h3>
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
                <label>Jumlah Keluar (Qty)</label>
                <input type="number" name="qty" value="<?= $editData['qty'] ?? 1; ?>" min="1" required>
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 12px;">
            <label>Keterangan / Keperluan</label>
            <input type="text" name="reference" value="<?= htmlspecialchars($editData['reference'] ?? 'Barang Keluar'); ?>" required>
        </div>
        <button type="submit" class="btn-pink"><?= $editData ? 'Perbarui Barang Keluar' : 'Kurangi Stok Keluar'; ?></button>
        <?php if ($editData): ?>
            <a href="barang_keluar.php" style="margin-left: 10px; color: #64748b; text-decoration: none;">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="search-bar">
        <h3>Riwayat Barang Keluar</h3>
        <form method="GET"><input type="text" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search); ?>"></form>
    </div>
    <table>
        <thead><tr><th>NO</th><th>PRODUK</th><th>JUMLAH</th><th>KETERANGAN</th><th>TANGGAL</th><th>AKSI</th></tr></thead>
        <tbody>
            <?php if(count($items) > 0): foreach($items as $i => $item): ?>
            <tr>
                <td><?= $i+1; ?></td>
                <td><strong><?= htmlspecialchars($item['product_name']); ?></strong></td>
                <td><strong style="color:#e11d48;">-<?= $item['qty']; ?></strong></td>
                <td><?= htmlspecialchars($item['reference']); ?></td>
                <td><?= htmlspecialchars($item['created_at']); ?></td>
                <td>
                    <a href="barang_keluar.php?edit=<?= $item['id']; ?>" class="action-btn" style="color: #0284c7;">Ubah</a>
                    <a href="barang_keluar.php?delete=<?= $item['id']; ?>" class="action-btn del" onclick="return confirm('Hapus transaksi ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6" style="text-align: center; color: #94a3b8;">Belum ada riwayat barang keluar.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>
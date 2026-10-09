<?php
require_once '../../config/database.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/validation.php';
require_once '../../helpers/escape.php';

// Pastikan user sudah login
check_auth();

$error = '';
$success = '';

// Proses ketika form disubmit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input menggunakan helper validation
    $product_id = validate_int($_POST['product_id'] ?? 0, 1);
    $quantity   = validate_int($_POST['quantity'] ?? 0, 1);
    $type       = $_POST['type'] ?? ''; // 'IN' atau 'OUT'
    $user_id    = $_SESSION['user']['id'];
    $notes      = validate_string($_POST['notes'] ?? '', 255);

    if ($product_id === false || $quantity === false || !in_array($type, ['IN', 'OUT'], true)) {
        $error = 'Data input transaksi tidak valid.';
    } else {
        try {
            // Mulai Database Transaction untuk menjaga integritas data & stok
            $pdo->beginTransaction();

            // 1. Ambil stok saat ini dengan teknik row locking (FOR UPDATE)
            // Mencegah race condition jika ada transaksi bersamaan
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $product_id]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception('Produk dengan ID tersebut tidak ditemukan.');
            }

            $current_stock = (int)$product['stock'];

            // 2. Terapkan Business Rules (Aturan Bisnis)
            if ($type === 'OUT' && $quantity > $current_stock) {
                throw new Exception('Gagal: Barang keluar melebihi jumlah stok yang tersedia.');
            }

            // Hitung stok baru berdasarkan tipe transaksi
            $new_stock = ($type === 'IN') ? ($current_stock + $quantity) : ($current_stock - $quantity);

            // Validasi mutlak stok tidak boleh negatif
            if ($new_stock < 0) {
                throw new Exception('Gagal: Stok akhir tidak boleh bernilai negatif.');
            }

            // 3. Update nilai stok produk pada tabel products
            $updateStmt = $pdo->prepare("UPDATE products SET stock = :stock WHERE id = :id");
            $updateStmt->execute([
                'stock' => $new_stock,
                'id'    => $product_id
            ]);

            // 4. Catat histori transaksi ke tabel stock_movements
            $logStmt = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, notes) VALUES (:product_id, :user_id, :type, :quantity, :notes)");
            $logStmt->execute([
                'product_id' => $product_id,
                'user_id'    => $user_id,
                'type'       => $type,
                'quantity'   => $quantity,
                'notes'      => $notes !== false ? $notes : ''
            ]);

            // Jika semua proses berhasil tanpa error, commit transaksi ke database
            $pdo->commit();
            $success = 'Transaksi stok berhasil dicatat dan stok telah diperbarui!';

        } catch (Exception $e) {
            // Jika ada kesalahan di tengah jalan, batalkan seluruh perubahan (Rollback)
            $pdo->rollBack();
            $error = 'Transaksi Dibatalkan (Rollback): ' . $e->getMessage();
        }
    }
}

// Ambil daftar produk untuk pilihan di form dropdown
$products = $pdo->query("SELECT id, sku, name, stock FROM products ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Stok - Inventory Management</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #fdf2f4; margin: 40px; color: #333; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); max-width: 600px; margin: auto; border-top: 4px solid #d81b60; }
        h2 { color: #880e4f; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        select, input[type="number"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #d81b60; color: white; padding: 10px 20px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; width: 100%; }
        button:hover { background-color: #ad1457; }
        .alert-error { color: #d81b60; background: #ffebee; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-success { color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .back-link { display: inline-block; margin-top: 15px; text-decoration: none; color: #d81b60; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Form Transaksi Barang Masuk & Keluar</h2>
        <p>Operator: <strong><?= e($_SESSION['user']['username']); ?></strong></p>
        <hr style="margin-bottom: 20px; border: 0; border-top: 1px solid #eee;">

        <?php if ($error !== ''): ?>
            <div class="alert-error"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="alert-success"><?= e($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Pilih Produk:</label>
                <select name="product_id" required>
                    <option value="">-- Pilih Produk & Cek Stok --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id']; ?>">
                            [SKU: <?= e($p['sku']); ?>] <?= e($p['name']); ?> (Stok Saat Ini: <?= $p['stock']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Tipe Transaksi:</label>
                <select name="type" required>
                    <option value="IN">Barang Masuk (IN)</option>
                    <option value="OUT">Barang Keluar (OUT)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Jumlah (Quantity):</label>
                <input type="number" name="quantity" min="1" required placeholder="Masukkan jumlah barang...">
            </div>

            <div class="form-group">
                <label>Catatan / Keterangan (Opsional):</label>
                <textarea name="notes" rows="3" placeholder="Contoh: Pembelian dari Supplier A / Pengiriman ke Cabang B"></textarea>
            </div>

            <button type="submit">Simpan Transaksi</button>
        </form>

        <a href="../dashboard.php" class="back-link">← Kembali ke Dashboard</a>
    </div>

</body>
</html>
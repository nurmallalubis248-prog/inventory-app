<?php
require_once '../database/config/database.php';
include '../includes/header.php';

$products = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
$totalVal = 0; 
foreach($products as $p) { 
    $totalVal += ($p['stock'] * $p['price']); 
}
?>

<div class="card">
    <h3>Laporan Stok & Valuasi Aset Inventory</h3>
    <div style="background:#fdf2f8; border:1px solid #fbcfe8; padding:20px; border-radius:8px; margin-bottom:24px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <p style="font-size:13px; color:#881337; font-weight:600; text-transform:uppercase;">Total Keseluruhan Valuasi Aset</p>
            <h2 style="color:#db2777; font-size:28px; margin-top:4px;">Rp <?= number_format($totalVal, 0, ',', '.'); ?></h2>
        </div>
        <div>
            <span style="background: #db2777; color: white; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                Total Jenis Barang: <?= count($products); ?>
            </span>
        </div>
    </div>
    <table>
        <thead><tr><th>NO</th><th>NAMA PRODUK</th><th>KATEGORI</th><th>STOK</th><th>HARGA SATUAN</th><th>TOTAL NILAI</th></tr></thead>
        <tbody>
            <?php if(count($products) > 0): foreach ($products as $i => $p): $sub = $p['stock'] * $p['price']; ?>
            <tr>
                <td><?= $i+1; ?></td>
                <td><strong><?= htmlspecialchars($p['name']); ?></strong></td>
                <td><?= htmlspecialchars($p['cat_name'] ?? 'Tanpa Kategori'); ?></td>
                <td><strong><?= $p['stock']; ?></strong></td>
                <td>Rp <?= number_format($p['price'], 0, ',', '.'); ?></td>
                <td><strong>Rp <?= number_format($sub, 0, ',', '.'); ?></strong></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6" style="text-align: center; color: #94a3b8;">Belum ada data produk untuk dilaporkan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>
<?php
require_once '../database/config/database.php';
include '../includes/header.php';

$search = $_GET['search'] ?? '';
$stmt = $pdo->prepare("SELECT sm.*, p.name as product_name FROM stock_movements sm JOIN products p ON sm.product_id = p.id WHERE p.name LIKE :s ORDER BY sm.id DESC");
$stmt->execute(['s' => "%$search%"]);
$movements = $stmt->fetchAll();
?>

<div class="card">
    <div class="search-bar">
        <h3>Semua Riwayat Transaksi Stok</h3>
        <form method="GET"><input type="text" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search); ?>"></form>
    </div>
    <table>
        <thead><tr><th>NO</th><th>PRODUK</th><th>TIPE</th><th>JUMLAH</th><th>KETERANGAN</th><th>TANGGAL</th></tr></thead>
        <tbody>
            <?php if(count($movements) > 0): foreach ($movements as $i => $m): ?>
            <tr>
                <td><?= $i+1; ?></td>
                <td><strong><?= htmlspecialchars($m['product_name']); ?></strong></td>
                <td><span style="padding:4px 8px; border-radius:4px; font-weight:bold; font-size:12px; background:<?= $m['type']=='IN'?'#dcfce7;color:#166534;':'#fee2e2;color:#991b1b;'; ?>"><?= $m['type']=='IN'?'MASUK':'KELUAR'; ?></span></td>
                <td><strong><?= $m['qty']; ?></strong></td>
                <td><?= htmlspecialchars($m['reference']); ?></td>
                <td><?= htmlspecialchars($m['created_at']); ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6" style="text-align: center; color: #94a3b8;">Belum ada transaksi tercatat.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>
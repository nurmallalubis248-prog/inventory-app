<?php
require_once '../database/config/database.php';
include '../includes/header.php';

// Mengambil data statistik real-time dari database
try {
    $totalProduk   = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() ?? 0;
    $totalKategori = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() ?? 0;
    $totalSupplier = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn() ?? 0;
    
    // Total Nilai Valuasi Aset
    $totalNilai    = $pdo->query("SELECT SUM(stock * price) FROM products")->fetchColumn() ?? 0;
    
    // Produk dengan stok menipis (<= 5)
    $stokRendah    = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 5")->fetchColumn() ?? 0;
} catch (Exception $e) {
    $totalProduk   = 0;
    $totalKategori = 0;
    $totalSupplier = 0;
    $totalNilai    = 0;
    $stokRendah    = 0;
}
?>

<!-- Kartu Sambutan Dashboard -->
<div class="card" style="background: linear-gradient(135deg, #831843 0%, #db2777 100%); color: white; border: none; padding: 30px; margin-bottom: 25px;">
    <h2 style="font-size: 24px; margin-bottom: 8px; color: white;">Selamat Datang, <?= htmlspecialchars($currentUser['username']); ?>!</h2>
    <p style="color: #fbcfe8; font-size: 15px;">Sistem manajemen inventaris Stockify aktif dan terhubung penuh dengan database. Pilih menu di sebelah kiri untuk mengelola data produk, kategori, supplier, dan transaksi.</p>
</div>

<!-- Grid Statistik Data (Terhubung ke SQL) -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px;">
    <div class="card" style="border-left: 5px solid #db2777; margin-bottom: 0;">
        <h3 style="color: #881337; font-size: 13px; margin-bottom: 8px;">Total Produk</h3>
        <div style="font-size: 26px; font-weight: bold; color: #831843;"><?= $totalProduk; ?></div>
    </div>
    
    <div class="card" style="border-left: 5px solid #3b82f6; margin-bottom: 0;">
        <h3 style="color: #881337; font-size: 13px; margin-bottom: 8px;">Total Kategori</h3>
        <div style="font-size: 26px; font-weight: bold; color: #1e40af;"><?= $totalKategori; ?></div>
    </div>

    <div class="card" style="border-left: 5px solid #8b5cf6; margin-bottom: 0;">
        <h3 style="color: #881337; font-size: 13px; margin-bottom: 8px;">Total Supplier</h3>
        <div style="font-size: 26px; font-weight: bold; color: #5b21b6;"><?= $totalSupplier; ?></div>
    </div>

    <div class="card" style="border-left: 5px solid #f59e0b; margin-bottom: 0;">
        <h3 style="color: #881337; font-size: 13px; margin-bottom: 8px;">Stok Menipis (&le; 5)</h3>
        <div style="font-size: 26px; font-weight: bold; color: #b45309;"><?= $stokRendah; ?></div>
    </div>
</div>

<!-- Informasi Valuasi Aset -->
<div class="card">
    <h3 style="margin-bottom: 10px;">Ringkasan Valuasi Aset Inventaris</h3>
    <p style="color: #64748b; font-size: 14px; margin-bottom: 15px;">Total nilai keseluruhan aset produk yang tersimpan di dalam sistem saat ini:</p>
    <div style="font-size: 26px; font-weight: bold; color: #065f46; background: #ecfdf5; padding: 15px 20px; border-radius: 8px; border: 1px solid #a7f3d0; display: inline-block;">
        Rp <?= number_format($totalNilai, 0, ',', '.'); ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
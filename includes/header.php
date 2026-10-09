<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$currentUser = $_SESSION['user'];
$role = $currentUser['role'] ?? 'staff';
$username = $currentUser['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Stockify - Inventory System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #fff1f2; display: flex; height: 100vh; overflow: hidden; color: #334155; }
        
        /* Sidebar Warna Pink Elegan */
        .sidebar { width: 260px; background-color: #831843; color: #ffffff; display: flex; flex-direction: column; }
        .sidebar-brand { padding: 24px 20px; font-size: 24px; font-weight: bold; background-color: #500724; border-bottom: 1px solid #9f1239; }
        .sidebar-brand span { display: block; font-size: 13px; color: #fbcfe8; font-weight: normal; margin-top: 4px; text-transform: capitalize; }
        .sidebar-menu { list-style: none; padding: 15px 0; flex-grow: 1; overflow-y: auto; }
        .sidebar-menu li a { display: block; padding: 12px 24px; color: #fbcfe8; text-decoration: none; font-size: 15px; transition: 0.2s; border-left: 4px solid transparent; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: #9f1239; color: #ffffff; border-left-color: #f472b6; }
        .sidebar-menu li.logout a { color: #fecdd3; margin-top: 10px; }
        .sidebar-menu li.logout a:hover { background-color: #4c0519; color: #ffffff; border-left-color: #fb7185; }

        /* Main Content */
        .main-content { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; background: #fffdfd; }
        .topbar { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #fce7f3; display: flex; justify-content: space-between; align-items: center; }
        .topbar h1 { font-size: 22px; color: #831843; }
        .user-badge { background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; text-transform: uppercase; }

        .content-body { padding: 30px; }
        
        /* Komponen Umum CRUD & Tabel */
        .card { background: #ffffff; border: 1px solid #fce7f3; padding: 24px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(131, 24, 67, 0.05); margin-bottom: 24px; }
        .card h3 { font-size: 18px; color: #831843; margin-bottom: 16px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; color: #881337; font-weight: 600; margin-bottom: 6px; }
        .form-group input, .form-group select { width: 100%; padding: 10px 14px; border: 1px solid #fbcfe8; border-radius: 6px; font-size: 14px; outline: none; }
        .form-group input:focus { border-color: #db2777; box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.1); }
        
        .btn-pink { background-color: #db2777; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-pink:hover { background-color: #be185d; }
        
        .search-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .search-bar input { width: 300px; padding: 10px 14px; border: 1px solid #fbcfe8; border-radius: 6px; outline: none; }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #fff1f2; padding: 12px 16px; font-size: 13px; color: #881337; border-bottom: 2px solid #fce7f3; font-weight: 700; }
        td { padding: 12px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9; }
        tr:hover { background: #fff5f7; }
        
        .action-btn { color: #db2777; text-decoration: none; font-weight: 600; margin-right: 10px; }
        .action-btn.del { color: #e11d48; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            Stockify
            <span><?= htmlspecialchars($role); ?></span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">Produk</a></li>
            <li><a href="categories.php" class="<?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">Kategori</a></li>
            <li><a href="suppliers.php" class="<?= basename($_SERVER['PHP_SELF']) == 'suppliers.php' ? 'active' : ''; ?>">Supplier</a></li>
            <li><a href="barang_masuk.php" class="<?= basename($_SERVER['PHP_SELF']) == 'barang_masuk.php' ? 'active' : ''; ?>">Barang Masuk</a></li>
            <li><a href="barang_keluar.php" class="<?= basename($_SERVER['PHP_SELF']) == 'barang_keluar.php' ? 'active' : ''; ?>">Barang Keluar</a></li>
            <li><a href="riwayat.php" class="<?= basename($_SERVER['PHP_SELF']) == 'riwayat.php' ? 'active' : ''; ?>">Riwayat Transaksi</a></li>
            <li><a href="laporan.php" class="<?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>">Laporan</a></li>

            <?php if ($role === 'admin'): ?>
                <li><a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">Pengguna</a></li>
            <?php endif; ?>

            <li class="logout"><a href="login.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h1>Sistem Inventaris Barang</h1>
            <div class="user-badge">Role: <?= htmlspecialchars($role); ?></div>
        </div>
        <div class="content-body">
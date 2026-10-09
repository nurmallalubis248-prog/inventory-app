<?php
// Pastikan session aktif jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #fdf2f4; 
            margin: 0; 
            padding: 0; 
            color: #333; 
        }
        header { 
            background-color: #d81b60; 
            color: white; 
            padding: 15px 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        header h2 { 
            margin: 0; 
            font-size: 20px; 
        }
        nav a { 
            color: white; 
            text-decoration: none; 
            margin-left: 20px; 
            font-weight: bold; 
        }
        nav a:hover { 
            text-decoration: underline; 
        }
        .container { 
            max-width: 900px; 
            margin: 30px auto; 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.05); 
            border-top: 4px solid #d81b60; 
        }
    </style>
</head>
<body>

    <header>
        <h2>Inventory App</h2>
        <?php if (isset($_SESSION['user'])): ?>
            <nav>
                <a href="/inventory-app/public/dashboard.php">Dashboard</a>
                <a href="/inventory-app/public/products/index.php">Produk</a>
                <a href="/inventory-app/public/stock/process.php">Transaksi Stok</a>
                <a href="/inventory-app/public/logout.php" style="background: #ad1457; padding: 6px 12px; border-radius: 4px;">Logout</a>
            </nav>
        <?php endif; ?>
    </header>

    <div class="container">
<!-- Header selesai, konten halaman akan dilanjutkan di bawah file ini -->
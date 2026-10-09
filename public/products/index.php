<?php
require_once '../../config/database.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/validation.php';
require_once '../../helpers/escape.php';

// Pastikan user sudah login
check_auth();

// 1. Ambil Parameter Search, Filter, Sorting, dan Pagination dari URL
$search = trim($_GET['search'] ?? '');
$category_id = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT) ?: 0;

// Allowlist kolom sorting untuk mencegah SQL Injection pada ORDER BY
$allowed_columns = ['name', 'price', 'stock', 'sku'];
$sort_col = validate_sort_column($_GET['sort'] ?? 'name', $allowed_columns);
$sort_dir = validate_sort_direction($_GET['dir'] ?? 'ASC');

// Pagination setup
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// 2. Build Query dengan Kondisi (Search & Filter)
$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(p.name LIKE :search OR p.sku LIKE :search)";
    $params['search'] = "%$search%";
}

if ($category_id > 0) {
    $whereClauses[] = "p.category_id = :category_id";
    $params['category_id'] = $category_id;
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// 3. Hitung Total Data untuk Pagination
$countSql = "SELECT COUNT(*) FROM products p $whereSql";
$countStmt = $pdo->prepare($countSql);
// Bind parameter pencarian/filter (kecuali limit/offset)
foreach ($params as $key => $val) {
    $countStmt->bindValue(":$key", $val);
}
$countStmt->execute();
$totalData = $countStmt->fetchColumn();
$totalPages = ceil($totalData / $perPage);

// 4. Ambil Data Produk dengan JOIN, Sorting, dan LIMIT/OFFSET
$dataSql = "SELECT p.*, c.name AS category_name 
            FROM products p 
            JOIN categories c ON c.id = p.category_id 
            $whereSql 
            ORDER BY $sort_col $sort_dir 
            LIMIT :limit OFFSET :offset";

$dataStmt = $pdo->prepare($dataSql);
foreach ($params as $key => $val) {
    $dataStmt->bindValue(":$key", $val);
}
// Bind nilai integer untuk limit dan offset secara eksplisit
$dataStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$dataStmt->execute();
$products = $dataStmt->fetchAll();

// Ambil daftar kategori untuk dropdown filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Produk - Inventory System</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .pagination { margin-top: 15px; }
        .pagination a { margin-right: 5px; padding: 5px 10px; border: 1px solid #ccc; text-decoration: none; }
        .pagination a.active { background-color: #007bff; color: white; border-color: #007bff; }
    </style>
</head>
<body>
    <h1>Daftar Produk</h1>
    <p><a href="../dashboard.php">← Kembali ke Dashboard</a></p>

    <!-- Form Search dan Filter -->
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Cari nama atau SKU..." value="<?= e($search); ?>">
        
        <select name="category_id">
            <option value="0">-- Semua Kategori --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id']; ?>" <?= ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                    <?= e($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Cari / Filter</button>
    </form>

    <!-- Tabel Data Produk -->
    <table>
        <thead>
            <tr>
                <th><a href="?sort=sku&dir=<?= ($sort_col === 'sku' && $sort_dir === 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?= e($search); ?>&category_id=<?= $category_id; ?>">SKU</a></th>
                <th><a href="?sort=name&dir=<?= ($sort_col === 'name' && $sort_dir === 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?= e($search); ?>&category_id=<?= $category_id; ?>">Nama Produk</a></th>
                <th>Kategori</th>
                <th><a href="?sort=price&dir=<?= ($sort_col === 'price' && $sort_dir === 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?= e($search); ?>&category_id=<?= $category_id; ?>">Harga</a></th>
                <th><a href="?sort=stock&dir=<?= ($sort_col === 'stock' && $sort_dir === 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?= e($search); ?>&category_id=<?= $category_id; ?>">Stok</a></th>
                <th>Min Stok</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= e($p['sku']); ?></td>
                    <td><?= e($p['name']); ?></td>
                    <td><?= e($p['category_name']); ?></td>
                    <td>Rp <?= number_format($p['price'], 2, ',', '.'); ?></td>
                    <td style="<?= ($p['stock'] <= $p['minimum_stock']) ? 'color: red; font-weight: bold;' : ''; ?>">
                        <?= $p['stock']; ?>
                    </td>
                    <td><?= $p['minimum_stock']; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada data produk ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i; ?>&search=<?= e($search); ?>&category_id=<?= $category_id; ?>&sort=<?= e($sort_col); ?>&dir=<?= e($sort_dir); ?>" 
               class="<?= ($page == $i) ? 'active' : ''; ?>">
               <?= $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</body>
</html>
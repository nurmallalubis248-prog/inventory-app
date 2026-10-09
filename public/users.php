<?php
ob_start(); // Tambahkan ini di baris paling atas agar header warning hilang
require_once '../database/config/database.php';
include '../includes/header.php';

if ($role !== 'admin') { 
    echo "<script>alert('Akses khusus Admin!'); window.location='dashboard.php';</script>"; 
    exit(); 
}

$editData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $user_role = $_POST['role'];
    $id = $_POST['id'] ?? '';

    if (!empty($username)) {
        if (!empty($id)) {
            if (!empty($password)) {
                $stmt = $pdo->prepare("UPDATE users SET username = :u, password_hash = :p, role = :r WHERE id = :id");
                $stmt->execute(['u' => $username, 'p' => $password, 'r' => $user_role, 'id' => $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = :u, role = :r WHERE id = :id");
                $stmt->execute(['u' => $username, 'r' => $user_role, 'id' => $id]);
            }
        } else {
            if (!empty($password)) {
                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:u, :p, :r)");
                $stmt->execute(['u' => $username, 'p' => $password, 'r' => $user_role]);
            }
        }
        header('Location: users.php');
        exit();
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $editData = $stmt->fetch();
}

if (isset($_GET['delete']) && $_GET['delete'] != $currentUser['id']) {
    $pdo->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $_GET['delete']]);
    header('Location: users.php');
    exit();
}

$search = $_GET['search'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE :s ORDER BY id DESC");
$stmt->execute(['s' => "%$search%"]);
$usersList = $stmt->fetchAll();
?>

<div class="card">
    <h3><?= $editData ? 'Edit Pengguna' : 'Tambah Pengguna Sistem Baru'; ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $editData['id'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($editData['username'] ?? ''); ?>" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Password <?= $editData ? '(Kosongkan jika tidak diubah)' : ''; ?></label>
                <input type="text" name="password" placeholder="Masukkan password" <?= $editData ? '' : 'required'; ?>>
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label>Hak Akses (Role)</label>
                <select name="role">
                    <option value="staff" <?= ($editData['role'] ?? '') == 'staff' ? 'selected' : ''; ?>>Staff</option>
                    <option value="admin" <?= ($editData['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn-pink"><?= $editData ? 'Perbarui Pengguna' : 'Simpan Pengguna'; ?></button>
        <?php if ($editData): ?>
            <a href="users.php" style="margin-left: 10px; color: #64748b; text-decoration: none;">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="search-bar">
        <h3>Daftar Pengguna Terdaftar</h3>
        <form method="GET"><input type="text" name="search" placeholder="Cari username..." value="<?= htmlspecialchars($search); ?>"></form>
    </div>
    <table>
        <thead><tr><th>NO</th><th>USERNAME</th><th>ROLE</th><th>AKSI</th></tr></thead>
        <tbody>
            <?php foreach ($usersList as $i => $u): ?>
            <tr>
                <td><?= $i+1; ?></td>
                <td><strong><?= htmlspecialchars($u['username']); ?></strong></td>
                <td>
                    <span style="padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; background: <?= $u['role'] === 'admin' ? '#fdf2f8; color: #be185d; border: 1px solid #fbcfe8;' : '#f0fdf4; color: #166534; border: 1px solid #bbf7d0;'; ?>">
                        <?= strtoupper($u['role']); ?>
                    </span>
                </td>
                <td>
                    <?php if($u['id'] != $currentUser['id']): ?>
                        <a href="users.php?edit=<?= $u['id']; ?>" class="action-btn" style="color: #0284c7;">Ubah</a>
                        <a href="users.php?delete=<?= $u['id']; ?>" class="action-btn del" onclick="return confirm('Hapus pengguna ini?')">Hapus</a>
                    <?php else: ?>
                        <span style="color: #94a3b8; font-size: 13px;">(Akun Anda)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>
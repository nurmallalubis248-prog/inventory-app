<?php
require_once '../database/config/database.php';
require_once '../helpers/escape.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Mencocokkan password secara langsung dengan teks biasa di database XAMPP
        if ($user && $password === $user['password_hash']) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Semua kolom wajib diisi.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Inventory Management System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #fdf2f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 350px; border-top: 4px solid #d81b60; }
        h2 { color: #880e4f; margin-top: 0; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; background-color: #d81b60; color: white; padding: 10px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        button:hover { background-color: #ad1457; }
        .error { color: #d81b60; background: #ffebee; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .info { margin-top: 15px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Login Inventory</h2>
        
        <?php if ($error !== ''): ?>
            <div class="error"><?= e($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit">Masuk</button>
        </form>

        <div class="info">
            <p>Admin / Staff: password <code>password123</code></p>
        </div>
    </div>

</body>
</html>
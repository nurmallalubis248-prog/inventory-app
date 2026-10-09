<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_auth() {
    if (!isset($_SESSION['user'])) {
        header('Location: /inventory-app/public/login.php');
        exit();
    }
}

function check_role($required_role) {
    check_auth();
    if ($_SESSION['user']['role'] !== $required_role) {
        http_response_code(403);
        exit('Akses Ditolak: Anda tidak memiliki hak akses ke halaman ini.');
    }
}
?>
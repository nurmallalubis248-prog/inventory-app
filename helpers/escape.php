<?php
/**
 * Helper untuk output encoding / escaping agar aman dari XSS
 */

function e($string) {
    if ($string === null) {
        return '';
    }
    // Menggunakan htmlspecialchars dengan flags ENT_QUOTES dan charset UTF-8
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Fungsi opsional untuk meng-escape array sekaligus (jika menampilkan data multi-baris)
function e_array(array $data) {
    $escaped = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $escaped[$key] = e_array($value);
        } else {
            $escaped[$key] = is_string($value) ? e($value) : $value;
        }
    }
    return $escaped;
}
?>
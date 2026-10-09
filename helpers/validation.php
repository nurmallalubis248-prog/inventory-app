<?php
/**
 * Helper untuk validasi input server-side
 */

// Validasi bilangan bulat positif (untuk ID, stok, quantity)
function validate_int($value, $min = 0) {
    $filtered = filter_var($value, FILTER_VALIDATE_INT);
    if ($filtered === false || $filtered < $min) {
        return false;
    }
    return $filtered;
}

// Validasi angka desimal positif (untuk harga, nilai mata uang)
function validate_float($value, $min = 0) {
    $filtered = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($filtered === false || $filtered < $min) {
        return false;
    }
    return $filtered;
}

// Validasi teks/string biasa (membersihkan spasi ekstra & panjang maksimal)
function validate_string($value, $max_length = 255) {
    $trimmed = trim($value);
    if ($trimmed === '' || mb_strlen($trimmed) > $max_length) {
        return false;
    }
    return $trimmed;
}

// Validasi allowlist untuk kolom sorting (mencegah SQL Injection pada ORDER BY)
function validate_sort_column($column, array $allowed_columns) {
    if (in_array($column, $allowed_columns, true)) {
        return $column;
    }
    return $allowed_columns[0]; // Kembalikan default jika tidak valid
}

// Validasi arah sorting (ASC atau DESC)
function validate_sort_direction($direction) {
    $dir = strtoupper(trim($direction));
    return ($dir === 'DESC') ? 'DESC' : 'ASC';
}
?>
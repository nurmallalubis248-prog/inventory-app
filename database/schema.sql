SET FOREIGN_KEY_CHECKS = 0;

-- Hapus tabel lama jika ada agar tidak bentrok
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- 1. Buat tabel users (Manajemen Pengguna Admin & Staff)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff'
);

-- 2. Buat tabel categories (Kategori Produk)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- 3. Buat tabel suppliers (Data Supplier/Vendor)
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT
);

-- 4. Buat tabel products (Data Master Produk)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,
    name VARCHAR(150) NOT NULL,
    category_id INT,
    stock INT DEFAULT 0,
    price DECIMAL(12,2) DEFAULT 0.00,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- 5. Buat tabel stock_movements (Mutasi Barang Masuk & Keluar)
CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    type ENUM('IN', 'OUT') NOT NULL,
    qty INT NOT NULL,
    reference VARCHAR(100),
    created_at DATE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 6. Masukkan Akun Default (Admin & Staff dengan password: password123)
INSERT INTO users (id, username, password_hash, role) VALUES 
(1, 'admin', 'password123', 'admin'),
(2, 'staff', 'password123', 'staff');

-- 7. Masukkan Data Contoh (Dummy Data) agar Menu Langsung Ada Isinya
INSERT INTO categories (id, name) VALUES 
(1, 'Elektronik'),
(2, 'Pakaian'),
(3, 'Alat Tulis Kantor');

INSERT INTO suppliers (id, name, phone, address) VALUES 
(1, 'PT Jaya Elektronik', '081234567890', 'Jl. Merdeka No. 10, Jakarta'),
(2, 'CV Maju Bersama', '089876543210', 'Jl. Ahmad Yani No. 45, Bandung');

INSERT INTO products (id, code, name, category_id, stock, price) VALUES 
(1, 'ELK-001', 'Laptop ASUS Vivobook', 1, 12, 7500000.00),
(2, 'ELK-002', 'Mouse Wireless Logitech', 1, 45, 150000.00),
(3, 'PAK-001', 'Kemeja Formal Pria', 2, 30, 120000.00),
(4, 'ATK-001', 'Kertas HVS A4 70gr', 3, 50, 45000.00);

INSERT INTO stock_movements (product_id, type, qty, reference, created_at) VALUES 
(1, 'IN', 12, 'Barang Masuk', CURDATE()),
(2, 'IN', 45, 'Barang Masuk', CURDATE());

SET FOREIGN_KEY_CHECKS = 1;
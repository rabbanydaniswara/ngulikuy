<?php
// Detail koneksi database
$host = 'localhost';       // Ganti jika server DB Anda berbeda
$db   = 'ngulikuy_db';   // Nama database yang Anda buat di Langkah 2
$user = 'root';            // User database Anda
$pass = '';            // Password database Anda
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // Buat objek koneksi PDO global
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Jika koneksi gagal, hentikan aplikasi
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Variabel $pdo sekarang tersedia untuk file lain yang me-'require' file ini

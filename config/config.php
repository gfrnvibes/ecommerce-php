<?php
// File: config/config.php
date_default_timezone_set('Asia/Jakarta');

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Ganti dengan username database Anda
define('DB_PASSWORD', ''); // Ganti dengan password database Anda
define('DB_NAME', 'toko_maksum'); // Ganti dengan nama database Anda

// Membuat koneksi ke database
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
$host = $_SERVER['HTTP_HOST']; // otomatis dapatkan host:port
// Menghitung base path dinamis yang andal berdasarkan lokasi file sistem.
// Ini memastikan BASE_URL selalu menunjuk ke root proyek, tidak peduli dari mana skrip dijalankan.
$basePath = str_replace(
    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
    '',
    str_replace('\\', '/', dirname(__DIR__))
);

define('BASE_URL', $protocol . "://" . $host . $basePath . "/");

?>


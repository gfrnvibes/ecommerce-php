<?php

// Pastikan skrip dijalankan dari CLI
if (php_sapi_name() !== 'cli') {
    die("Skrip ini hanya bisa dijalankan dari Command Line Interface (CLI).");
}

require_once __DIR__ . '/../config/config.php';

echo "Memulai proses reset database...\n";

// Nonaktifkan pemeriksaan kunci asing sementara
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    echo "Pemeriksaan kunci asing dinonaktifkan.\n";
} catch (PDOException $e) {
    echo "Error menonaktifkan pemeriksaan kunci asing: " . $e->getMessage() . "\n";
    exit(1);
}

// Daftar tabel yang akan dikosongkan (sesuaikan dengan tabel Anda)
$tables = ['users', 'products', 'orders', 'order_items', 'carts', 'inventory_movements'];

foreach ($tables as $table) {
    try {
        $pdo->exec("TRUNCATE TABLE `{$table}`;");
        echo "Tabel `{$table}` berhasil dikosongkan.\n";
    } catch (PDOException $e) {
        echo "Error mengosongkan tabel `{$table}`: " . $e->getMessage() . "\n";
        // Aktifkan kembali kunci asing sebelum keluar jika terjadi error
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        exit(1);
    }
}

echo "Proses reset database selesai. Database siap digunakan dengan data awal.\n";

?>
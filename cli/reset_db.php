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

// Aktifkan kembali pemeriksaan kunci asing
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Pemeriksaan kunci asing diaktifkan kembali.\n";
} catch (PDOException $e) {
    echo "Error mengaktifkan kembali pemeriksaan kunci asing: " . $e->getMessage() . "\n";
    exit(1);
}

// --- Sisipkan Data Awal ---

// 1. Tambahkan pengguna admin
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
try {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Admin User', 'admin@example.com', $admin_password, 'admin']);
    echo "Pengguna admin berhasil ditambahkan.\n";
} catch (PDOException $e) {
    echo "Error menambahkan pengguna admin: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Tambahkan beberapa kategori
try {
    $pdo->exec("INSERT INTO categories (name) VALUES ('Elektronik'), ('Pakaian'), ('Makanan'), ('Minuman');");
    echo "Kategori berhasil ditambahkan.\n";
} catch (PDOException $e) {
    echo "Error menambahkan kategori: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Tambahkan beberapa produk (contoh)
// Ambil ID kategori untuk produk
$stmt_cat = $pdo->query("SELECT id, name FROM categories");
$categories_map = [];
while ($row = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
    $categories_map[$row['name']] = $row['id'];
}

$products_data = [
    ['Laptop Gaming', 'Laptop canggih untuk gaming.', 12000000, 50, 'uploads/laptop_gaming.jpg', $categories_map['Elektronik'] ?? null],
    ['Kaos Distro', 'Kaos katun nyaman dengan desain unik.', 150000, 200, 'uploads/kaos_distro.jpg', $categories_map['Pakaian'] ?? null],
    ['Nasi Goreng Spesial', 'Nasi goreng dengan bumbu rahasia.', 25000, 100, 'uploads/nasi_goreng.jpg', $categories_map['Makanan'] ?? null],
    ['Kopi Arabika', 'Biji kopi arabika pilihan.', 50000, 150, 'uploads/kopi_arabika.jpg', $categories_map['Minuman'] ?? null]
];

try {
    $stmt_prod = $pdo->prepare("INSERT INTO products (name, description, price, stock, image, category_id) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($products_data as $product) {
        $stmt_prod->execute($product);
    }
    echo "Produk contoh berhasil ditambahkan.\n";
} catch (PDOException $e) {
    echo "Error menambahkan produk contoh: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Proses reset database selesai. Database siap digunakan dengan data awal.\n";

?>

// Pastikan skrip dijalankan dari CLI
if (php_sapi_name() !== 'cli') {
die("Skrip ini hanya bisa dijalankan dari Command Line Interface (CLI).");
}

require_once __DIR__ . '/../config/config.php';

echo "Memulai proses pengosongan database...\n";

try {
// Nonaktifkan pemeriksaan kunci asing sementara
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');

// Dapatkan daftar semua tabel
$stmt = $pdo->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($tables)) {
echo "Tidak ada tabel yang ditemukan di database.\n";
} else {
// Hapus setiap tabel
foreach ($tables as $table) {
echo "Menghapus tabel: {$table}\n";
$pdo->exec("DROP TABLE IF EXISTS `{$table}`");
}
echo "Semua tabel berhasil dihapus.\n";
}

// Aktifkan kembali pemeriksaan kunci asing
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');

echo "Database berhasil dikosongkan.\n";

// Opsional: Jalankan kembali migrasi atau inisialisasi database jika ada
// Karena tidak ada sistem migrasi yang jelas, langkah ini dilewati.
// Jika Anda memiliki file SQL untuk inisialisasi, Anda bisa menjalankannya di sini.
// Contoh: $pdo->exec(file_get_contents('path/to/your/schema.sql'));

} catch (PDOException $e) {
echo "Error: " . $e->getMessage() . "\n";
// Pastikan kunci asing diaktifkan kembali meskipun ada kesalahan
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
exit(1);
}

?>
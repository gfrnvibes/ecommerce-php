<?php

// Pastikan skrip dijalankan dari CLI
if (php_sapi_name() !== 'cli') {
    die("Skrip ini hanya bisa dijalankan dari Command Line Interface (CLI).");
}

require_once __DIR__ . '/../config/config.php';

// Fungsi untuk menampilkan bantuan
function show_help() {
    echo "Penggunaan: php add_user.php --name=<nama> --email=<email> --password=<password> --role=<peran>\n";
    echo "\nArgumen:\n";
    echo "  --name     Nama pengguna (wajib)\n";
    echo "  --email    Email pengguna (wajib, harus unik)\n";
    echo "  --password Kata sandi pengguna (wajib, minimal 6 karakter)\n";
    echo "  --role     Peran pengguna (wajib, contoh: admin, customer)\n";
    echo "\nContoh:\n";
    echo "  php add_user.php --name=\"John Doe\" --email=\"john.doe@example.com\" --password=\"password123\" --role=\"customer\"\n";
    echo "  php add_user.php --name=\"Admin User\" --email=\"admin@example.com\" --password=\"admin123\" --role=\"admin\"\n";
    exit(1);
}

// Parsing argumen baris perintah
$options = getopt('', ['name:', 'email:', 'password:', 'role:']);

$name = $options['name'] ?? null;
$email = $options['email'] ?? null;
$password = $options['password'] ?? null;
$role = $options['role'] ?? null;

// Validasi argumen
$errors = [];
if (empty($name)) {
    $errors[] = "Nama tidak boleh kosong.";
}
if (empty($email)) {
    $errors[] = "Email tidak boleh kosong.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Format email tidak valid.";
}
if (empty($password)) {
    $errors[] = "Kata sandi tidak boleh kosong.";
} elseif (strlen($password) < 6) {
    $errors[] = "Kata sandi minimal 6 karakter.";
}
if (empty($role)) {
    $errors[] = "Peran tidak boleh kosong.";
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "Error: " . $error . "\n";
    }
    show_help();
}

// Cek apakah email sudah terdaftar
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo "Error: Email '{$email}' sudah terdaftar.\n";
        exit(1);
    }
} catch (PDOException $e) {
    echo "Error database saat memeriksa email: " . $e->getMessage() . "\n";
    exit(1);
}

// Hash kata sandi
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Masukkan pengguna ke database
try {
    $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $insert_stmt->execute([$name, $email, $hashed_password, $role]);

    echo "Pengguna '{$name}' dengan peran '{$role}' berhasil ditambahkan.\n";
} catch (PDOException $e) {
    echo "Error database saat menambahkan pengguna: " . $e->getMessage() . "\n";
    exit(1);
}

?>
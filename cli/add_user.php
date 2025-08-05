<?php

require_once __DIR__ . '/../config/config.php';

function add_user($name, $email, $password, $role)
{
    global $pdo;

    // Validasi input
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
        return ['success' => false, 'errors' => $errors];
    }

    // Cek apakah email sudah terdaftar
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'errors' => ["Email '{$email}' sudah terdaftar."]];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'errors' => ["Error database saat memeriksa email: " . $e->getMessage()]];
    }

    // Hash kata sandi
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Masukkan pengguna ke database
    try {
        $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $insert_stmt->execute([$name, $email, $hashed_password, $role]);

        return ['success' => true, 'message' => "Pengguna '{$name}' dengan peran '{$role}' berhasil ditambahkan."];
    } catch (PDOException $e) {
        return ['success' => false, 'errors' => ["Error database saat menambahkan pengguna: " . $e->getMessage()]];
    }
}

// CLI functionality (optional)
if (php_sapi_name() === 'cli') {
    // Fungsi untuk menampilkan bantuan
    function show_help()
    {
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

    $result = add_user($name, $email, $password, $role);

    if ($result['success']) {
        echo $result['message'] . "\n";
    } else {
        foreach ($result['errors'] as $error) {
            echo "Error: " . $error . "\n";
        }
        exit(1);
    }
}

?>
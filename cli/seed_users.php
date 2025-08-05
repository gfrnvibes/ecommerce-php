<?php
require_once __DIR__ . '/../config/config.php';

// Data pengguna untuk diinsert
$users_to_insert = [
    [
        'name' => 'Admin',
        'email' => 'admin@gmail.com',
        'password' => 'password',
        'role' => 'admin'
    ],
    [
        'name' => 'Pemilik Toko',
        'email' => 'pakmaksum@gmail.com',
        'password' => 'password',
        'role' => 'pemilik'
    ],
    [
        'name' => 'Maulida Maya Sari',
        'email' => 'maulida@gmail.com',
        'password' => 'password',
        'role' => 'customer'
    ],
];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");

    foreach ($users_to_insert as $user) {
        $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
        $stmt->execute([
            $user['name'],
            $user['email'],
            $hashed_password,
            $user['role']
        ]);
    }

    $pdo->commit();
    echo count($users_to_insert) . " pengguna berhasil diinsert ke database.\n";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Error saat menginsert pengguna: " . $e->getMessage() . "\n";
    exit(1);
}

?>
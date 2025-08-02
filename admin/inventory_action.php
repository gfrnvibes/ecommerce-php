<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'stock_in') {
        $product_id = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? null;
        $notes = $_POST['notes'] ?? null;

        if (!$product_id || !$quantity || $quantity <= 0) {
            $_SESSION['error'] = 'Data tidak valid. Harap isi semua kolom yang diperlukan.';
            header('Location: inventory_in_form.php');
            exit;
        }

        try {
            $pdo->beginTransaction();

            // 1. Update product stock
            $stmt_update = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $stmt_update->execute([$quantity, $product_id]);

            // 2. Log the inventory movement
            $stmt_log = $pdo->prepare("INSERT INTO inventory_movements (product_id, type, quantity, notes) VALUES (?, 'in', ?, ?)");
            $stmt_log->execute([$product_id, $quantity, $notes]);

            $pdo->commit();

            $_SESSION['success'] = 'Stok berhasil ditambahkan.';
            header('Location: inventory.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Gagal menambahkan stok: ' . $e->getMessage();
            header('Location: inventory_in_form.php');
            exit;
        }
    }

    // Add other actions like 'stock_out' if needed in the future

    $_SESSION['error'] = 'Aksi tidak dikenal.';
    header('Location: inventory.php');
    exit;
}

// Redirect if accessed directly
header('Location: inventory.php');
exit;
?>
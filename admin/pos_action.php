<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

if (!isset($_SESSION['pos_cart'])) {
    $_SESSION['pos_cart'] = [];
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Add item to POS cart
if ($action == 'add' && $id) {
    if (isset($_SESSION['pos_cart'][$id])) {
        // Check stock before increasing quantity
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product && $_SESSION['pos_cart'][$id]['quantity'] < $product['stock']) {
            $_SESSION['pos_cart'][$id]['quantity']++;
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND stock > 0");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            $_SESSION['pos_cart'][$id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1,
                'stock' => $product['stock']
            ];
        }
    }
}

// Update item quantity
if ($action == 'update' && $id && isset($_POST['quantity'])) {
    $quantity = (int)$_POST['quantity'];
    if (isset($_SESSION['pos_cart'][$id])) {
        if ($quantity > 0 && $quantity <= $_SESSION['pos_cart'][$id]['stock']) {
            $_SESSION['pos_cart'][$id]['quantity'] = $quantity;
        } else {
            // If quantity is invalid, remove the item
            unset($_SESSION['pos_cart'][$id]);
        }
    }
}

// Remove item from cart
if ($action == 'remove' && $id) {
    if (isset($_SESSION['pos_cart'][$id])) {
        unset($_SESSION['pos_cart'][$id]);
    }
}

// Clear the entire cart
if ($action == 'clear') {
    $_SESSION['pos_cart'] = [];
}

header('Location: pos.php');
exit();
?>
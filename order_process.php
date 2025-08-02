<?php
require_once 'config/config.php';

// Pastikan user sudah login dan form disubmit
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$shipping_address = $_POST['shipping_address'];
$payment_method = $_POST['payment_method'];

// Ambil item dari keranjang
$stmt = $pdo->prepare("
    SELECT p.id as product_id, p.price, c.quantity, p.stock
    FROM carts c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (count($cart_items) === 0) {
    header('Location: cart.php');
    exit();
}

$total_amount = 0;

// Mulai transaksi database
$pdo->beginTransaction();

try {
    // 1. Cek stok produk
    foreach ($cart_items as $item) {
        if ($item['quantity'] > $item['stock']) {
            throw new Exception('Stok produk ' . $item['name'] . ' tidak mencukupi.');
        }
        $total_amount += $item['price'] * $item['quantity'];
    }

    // 2. Buat pesanan baru di tabel 'orders'
    $payment_due = date('Y-m-d H:i:s', strtotime('+24 hours')); // Batas waktu pembayaran 24 jam
    $order_stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, order_type, status, payment_method, payment_status, shipping_address, payment_due)
        VALUES (?, ?, 'online', 'awaiting_payment', ?, 'unpaid', ?, ?)
    ");
    $order_stmt->execute([$user_id, $total_amount, $payment_method, $shipping_address, $payment_due]);
    $order_id = $pdo->lastInsertId();

    // Simpan alamat pengiriman ke profil pengguna untuk penggunaan di masa mendatang
    $stmt_update_user = $pdo->prepare("UPDATE users SET shipping_address = ? WHERE id = ?");
    $stmt_update_user->execute([$shipping_address, $user_id]);

    // 3. Pindahkan item dari keranjang ke 'order_items' dan kurangi stok
    $item_stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($cart_items as $item) {
        // Masukkan ke order_items
        $item_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        // Kurangi stok produk
        $stock_stmt->execute([$item['quantity'], $item['product_id']]);
    }

    // 4. Kosongkan keranjang user
    $clear_cart_stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ?");
    $clear_cart_stmt->execute([$user_id]);

    // Jika semua berhasil, commit transaksi
    $pdo->commit();

    $_SESSION['message'] = ['type' => 'success', 'text' => 'Pesanan Anda berhasil dibuat! Silakan lakukan pembayaran.'];
    header('Location: order_detail.php?id=' . $order_id);
    exit();

} catch (Exception $e) {
    // Jika ada error, rollback transaksi
    $pdo->rollBack();
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage()];
    header('Location: checkout.php');
    exit();
}
?>
<?php
require_once 'config/config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'cancel':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
            $order_id = $_POST['order_id'];

            $pdo->beginTransaction();
            try {
                // 1. Ambil data pesanan untuk memastikan valid
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'awaiting_payment'");
                $stmt->execute([$order_id, $user_id]);
                $order = $stmt->fetch();

                if ($order) {
                    // 2. Ubah status pesanan menjadi 'cancelled'
                    $update_order = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
                    $update_order->execute([$order_id]);

                    // 3. Kembalikan stok produk
                    $items_stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                    $items_stmt->execute([$order_id]);
                    $order_items = $items_stmt->fetchAll();

                    $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                    foreach ($order_items as $item) {
                        $stock_stmt->execute([$item['quantity'], $item['product_id']]);
                    }

                    $pdo->commit();
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Pesanan berhasil dibatalkan.'];
                    header('Location: order_detail.php?id=' . $order_id);
                } else {
                    throw new Exception('Pesanan tidak dapat dibatalkan.');
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Gagal membatalkan pesanan: ' . $e->getMessage()];
                header('Location: my_orders.php');
            }
        }
        break;

    default:
        header('Location: index.php');
        break;
}

exit();
?>
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

$action = $_GET['action'] ?? '';

if ($action == 'update_status' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_GET['id'];
    $order_status = $_POST['status'];
    $payment_status = $_POST['payment_status'];

    // Get current order status to check if stock needs to be restored
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $current_order = $stmt->fetch();

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?");
        $stmt->execute([$order_status, $payment_status, $order_id]);

        // If order is cancelled, restore stock and log it
        if ($order_status == 'cancelled' && $current_order['status'] != 'cancelled') {
            $stmt_items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmt_items->execute([$order_id]);
            $order_items = $stmt_items->fetchAll();

            $stmt_stock = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $stmt_log = $pdo->prepare("INSERT INTO inventory_movements (product_id, order_id, type, quantity, notes) VALUES (?, ?, 'in', ?, ?)");

            foreach ($order_items as $item) {
                $stmt_stock->execute([$item['quantity'], $item['product_id']]);
                $notes = 'Pesanan #' . $order_id . ' dibatalkan';
                $stmt_log->execute([$item['product_id'], $order_id, $item['quantity'], $notes]);
            }
        }
        // Else if order is being fulfilled, reduce stock and log it
        else if (in_array($order_status, ['completed', 'shipped', 'processing']) && !in_array($current_order['status'], ['completed', 'shipped', 'processing', 'cancelled'])) {
            $stmt_items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmt_items->execute([$order_id]);
            $order_items = $stmt_items->fetchAll();

            $stmt_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt_log = $pdo->prepare("INSERT INTO inventory_movements (product_id, order_id, type, quantity, notes) VALUES (?, ?, 'out', ?, ?)");

            foreach ($order_items as $item) {
                // Here you might want to check if stock is sufficient before reducing
                $stmt_stock->execute([$item['quantity'], $item['product_id']]);
                $notes = 'Pesanan Online #' . $order_id;
                $stmt_log->execute([$item['product_id'], $order_id, $item['quantity'], $notes]);
            }
        }

        $pdo->commit();
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Status pesanan berhasil diperbarui.'];

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Gagal memperbarui status pesanan: ' . $e->getMessage()];
    }

    header('Location: order_detail_admin.php?id=' . $order_id);
    exit();
}

header('Location: orders.php');
exit();
?>
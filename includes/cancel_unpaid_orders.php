<?php
require_once __DIR__ . '/../config/config.php';

function cancelOverdueOrders($pdo)
{
    $now = date('Y-m-d H:i:s');

    // 1. Temukan pesanan yang kedaluwarsa
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE status = 'awaiting_payment' AND payment_due < ?");
    $stmt->execute([$now]);
    $overdue_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($overdue_orders)) {
        return; // Tidak ada yang perlu dilakukan
    }

    $order_ids = array_column($overdue_orders, 'id');
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));

    try {
        $pdo->beginTransaction();

        // 2. Kembalikan stok
        $stmt_items = $pdo->prepare(
            "SELECT product_id, quantity, order_id FROM order_items WHERE order_id IN ($placeholders)"
        );
        $stmt_items->execute($order_ids);
        $items_to_restock = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items_to_restock as $item) {
            // Update stok produk
            $stmt_update_stock = $pdo->prepare(
                "UPDATE products SET stock = stock + ? WHERE id = ?"
            );
            $stmt_update_stock->execute([$item['quantity'], $item['product_id']]);

            // Catat pergerakan inventaris
            $stmt_movement = $pdo->prepare(
                'INSERT INTO inventory_movements (product_id, type, quantity, notes) VALUES (?, ?, ?, ?)'
            );
            $stmt_movement->execute([
                $item['product_id'],
                'in',
                $item['quantity'],
                'Order #' . $item['order_id'] . ' cancelled (payment overdue)'
            ]);
        }

        // 3. Update status pesanan menjadi 'cancelled'
        $stmt_cancel = $pdo->prepare(
            "UPDATE orders SET status = 'cancelled' WHERE id IN ($placeholders)"
        );
        $stmt_cancel->execute($order_ids);

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        // Mungkin perlu logging error di sini
        error_log('Failed to cancel overdue orders: ' . $e->getMessage());
    }
}

// Jalankan fungsi pembatalan
cancelOverdueOrders($pdo);
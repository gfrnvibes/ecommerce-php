<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

if (!isset($_GET['order_id'])) {
    header('Location: pos.php');
    exit();
}

$order_id = $_GET['order_id'];

// Get order details
$stmt = $pdo->prepare("SELECT orders.*, users.name as customer_name FROM orders JOIN users ON orders.user_id = users.id WHERE orders.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Pesanan tidak ditemukan.'];
    header('Location: pos.php');
    exit();
}

// Get order items
$stmt = $pdo->prepare("SELECT order_items.*, products.name as product_name FROM order_items JOIN products ON order_items.product_id = products.id WHERE order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

require_once __DIR__ . '/../templates/header_admin.php';
?>

<div class="container px-4">
    <div class="text-center mb-4">
        <h1 class="mt-4">Struk Pembayaran</h1>
        <p class="mb-1"><?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?></p>
        <p class="mb-1">No. Pesanan: #<?php echo $order_id; ?></p>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></th>
                    </tr>
                    <?php if ($order['payment_method'] === 'cash'): ?>
                        <tr>
                            <th colspan="3" class="text-end">Dibayar:</th>
                            <th>Rp
                                <?php echo number_format($order['amount_paid'], 0, ',', '.'); ?>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Kembalian:</th>
                            <th>Rp <?php echo number_format($order['change_amount'], 0, ',', '.'); ?>
                            </th>
                        </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="text-center mb-4">
        <p>Terima kasih telah berbelanja!</p>
        <button class="btn btn-primary" onclick="window.print()">Cetak Struk</button>
        <a href="pos.php" class="btn btn-secondary">Kembali ke POS</a>
    </div>
</div>

<?php
if (!isset($_GET['print'])) {
    require_once __DIR__ . '/../templates/footer_admin.php';
}
?>
<style>
@media print {
    .navbar, .footer, .btn {
        display: none !important;
    }
    body {
        padding: 0;
        margin: 0;
    }
    .container {
        width: 100%;
        max-width: 100%;
        padding: 0;
    }
}
</style>
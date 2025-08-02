<?php
require_once __DIR__ . '/includes/cancel_unpaid_orders.php';
require_once 'config/config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'templates/header.php';

$user_id = $_SESSION['user_id'];

// Ambil semua pesanan user
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Ambil semua produk terkait untuk semua order user (biar efisien 1 query saja)
$order_ids = array_column($orders, 'id');
$products_by_order = [];

if (count($order_ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));

    $stmt_items = $pdo->prepare("
        SELECT oi.order_id, oi.quantity, p.name 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id IN ($placeholders)
    ");
    $stmt_items->execute($order_ids);
    $items = $stmt_items->fetchAll();

    foreach ($items as $item) {
        $products_by_order[$item['order_id']][] = [
            'name' => $item['name'],
            'quantity' => $item['quantity'],
        ];
    }
}

$statusMap = [
    'awaiting_payment' => ['label' => 'Menunggu Pembayaran', 'badge' => 'bg-warning'],
    'processing' => ['label' => 'Sedang Diproses', 'badge' => 'bg-primary'],
    'shipped' => ['label' => 'Dikirim', 'badge' => 'bg-info'],
    'completed' => ['label' => 'Selesai', 'badge' => 'bg-success'],
    'cancelled' => ['label' => 'Dibatalkan', 'badge' => 'bg-secondary'],
    'rejected' => ['label' => 'Ditolak', 'badge' => 'bg-danger'],
];

$paymentStatusMap = [
    'unpaid' => ['label' => 'Belum Dibayar', 'badge' => 'bg-danger'],
    'pending_verification' => ['label' => 'Menunggu Verifikasi', 'badge' => 'bg-warning'],
    'paid' => ['label' => 'Sudah Dibayar', 'badge' => 'bg-success'],
];

?>

<div class="container mt-5 min-h-screen">
    <h2>Pesanan Saya</h2>
    <hr>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?>" role="alert">
            <?php echo $_SESSION['message']['text']; ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Status Pesanan</th>
                <th>Status Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>

                        <!-- Produk -->
                        <td>
                            <?php
                            if (isset($products_by_order[$order['id']])) {
                                foreach ($products_by_order[$order['id']] as $product) {
                                    echo htmlspecialchars($product['name']) . '<br>';
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>

                        <!-- Jumlah -->
                        <td>
                            <?php
                            if (isset($products_by_order[$order['id']])) {
                                foreach ($products_by_order[$order['id']] as $product) {
                                    echo htmlspecialchars($product['quantity']) . '<br>';
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>

                        <td>Rp <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></td>
                            <?php
                            $status = $order['status'];
                            $label = $statusMap[$status]['label'] ?? 'Status Tidak Diketahui';
                            $badgeClass = $statusMap[$status]['badge'] ?? 'bg-dark';

                            $payment = $order['payment_status'];
                            $paymentLabel = $paymentStatusMap[$payment]['label'] ?? 'Tidak Diketahui';
                            $paymentBadge = $paymentStatusMap[$payment]['badge'] ?? 'bg-secondary';
                            ?>
                        <td>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <?php echo $label; ?>
                            </span>
                        </td>
                        <td>

                            <span class="badge <?= $paymentBadge ?>"><?= $paymentLabel ?></span>
                        </td>
                        <td>
                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">Anda belum memiliki pesanan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once 'templates/footer.php';
?>
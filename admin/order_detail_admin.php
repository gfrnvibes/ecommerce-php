<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = $_GET['id'];

// Get order details
$stmt = $pdo->prepare("SELECT orders.*, users.name as customer_name, users.email as customer_email FROM orders JOIN users ON orders.user_id = users.id WHERE orders.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Pesanan tidak ditemukan.'];
    header('Location: orders.php');
    exit();
}

// Get order items
$stmt = $pdo->prepare("SELECT order_items.*, products.name as product_name FROM order_items JOIN products ON order_items.product_id = products.id WHERE order_items.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

require_once __DIR__ . '/../templates/header_admin.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Pesanan #<?php echo $order['id']; ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="orders.php">Pesanan Online</a></li>
        <li class="breadcrumb-item active">Detail Pesanan</li>
    </ol>

    <?php
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        echo '<div class="alert alert-' . $message['type'] . ' alert-dismissible fade show" role="alert">';
        echo $message['text'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['message']);
    }
    ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-receipt me-1"></i>
                    Item Pesanan
                </div>
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
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                    <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i>
                    Detail Pelanggan & Pengiriman
                </div>
                <div class="card-body">
                    <p><strong>Nama:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                    <p><strong>Alamat
                            Pengiriman:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Update Status
                </div>
                <div class="card-body">
                    <form action="order_action_admin.php?action=update_status&id=<?php echo $order['id']; ?>"
                        method="POST">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status Pesanan</label>
                            <select name="status" id="status" class="form-select">
                                <option value="awaiting_payment" <?php echo $order['status'] == 'awaiting_payment' ? 'selected' : ''; ?>>Awaiting Payment</option>
                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>
                                    Shipped</option>
                                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="rejected" <?php echo $order['status'] == 'rejected' ? 'selected' : ''; ?>>
                                    Rejected</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Status Pembayaran</label>
                            <select name="payment_status" id="payment_status" class="form-select">
                                <option value="unpaid" <?php echo $order['payment_status'] == 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                <option value="pending_verification" <?php echo $order['payment_status'] == 'pending_verification' ? 'selected' : ''; ?>>Pending
                                    Verification</option>
                                <option value="paid" <?php echo $order['payment_status'] == 'paid' ? 'selected' : ''; ?>>
                                    Paid</option>
                                <option value="failed" <?php echo $order['payment_status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-invoice-dollar me-1"></i>
                    Bukti Pembayaran
                </div>
                <div class="card-body">
                    <?php if ($order['payment_proof_url']): ?>
                        <a href="<?php echo BASE_URL . 'uploads/payments/' . $order['payment_proof_url']; ?>"
                            target="_blank">
                            <img src="<?php echo BASE_URL . 'uploads/payments/' . $order['payment_proof_url']; ?>"
                                alt="Bukti Pembayaran" class="img-fluid">
                        </a>
                    <?php else: ?>
                        <p>Belum ada bukti pembayaran yang diunggah.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer_admin.php';
?>
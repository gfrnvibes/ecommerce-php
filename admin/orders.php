<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

$stmt = $pdo->query("SELECT orders.*, users.name as customer_name FROM orders JOIN users ON orders.user_id = users.id ORDER BY created_at DESC");
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../templates/header_admin.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manajemen Pesanan Online</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Pesanan Online</li>
    </ol>

    <?php if (isset($_SESSION['message'])):
    ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']['text']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-shopping-cart me-1"></i>
            Daftar Pesanan
        </div>
        <div class="card-body">
            <table id="datatablesSimple">
                <thead>
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status Pesanan</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order):
                    ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                        <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                        <td><span class="badge bg-<?php echo get_order_status_class($order['status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                        <td><span class="badge bg-<?php echo get_payment_status_class($order['payment_status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $order['payment_status'])); ?></span></td>
                        <td>
                            <a href="order_detail_admin.php?id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
function get_order_status_class($status) {
    switch ($status) {
        case 'awaiting_payment': return 'warning';
        case 'processing': return 'info';
        case 'shipped': return 'primary';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}

function get_payment_status_class($status) {
    switch ($status) {
        case 'unpaid': return 'warning';
        case 'pending_verification': return 'info';
        case 'paid': return 'success';
        case 'failed': return 'danger';
        default: return 'secondary';
    }
}

require_once __DIR__ . '/../templates/footer_admin.php';
?>
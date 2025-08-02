<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

$sql = "SELECT orders.*, users.name as customer_name FROM orders JOIN users ON orders.user_id = users.id WHERE 1=1";
$params = [];

if (!empty($_GET['start_date'])) {
    $sql .= " AND created_at >= :start_date";
    $params[':start_date'] = $_GET['start_date'];
}

if (!empty($_GET['end_date'])) {
    $sql .= " AND created_at <= :end_date";
    $params[':end_date'] = $_GET['end_date'];
}

if (!empty($_GET['status'])) {
    $sql .= " AND status = :status";
    $params[':status'] = $_GET['status'];
}

if (!empty($_GET['order_type'])) {
    $sql .= " AND order_type = :order_type";
    $params[':order_type'] = $_GET['order_type'];
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

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

require_once __DIR__ . '/../templates/header_admin.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Pesanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Pesanan</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter Pesanan
        </div>
        <div class="card-body">
            <form method="GET" action="orders.php">
                <div class="row align-items-end">
                    <div class="col mb-3">
                        <label for="start_date" class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                    </div>
                    <div class="col mb-3">
                        <label for="end_date" class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                            value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                    </div>
                    <div class="col mb-3">
                        <label for="status" class="form-label">Status Pesanan</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="awaiting_payment" <?php echo (($_GET['status'] ?? '') == 'awaiting_payment') ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                            <option value="processing" <?php echo (($_GET['status'] ?? '') == 'processing') ? 'selected' : ''; ?>>Diproses</option>
                            <option value="shipped" <?php echo (($_GET['status'] ?? '') == 'shipped') ? 'selected' : ''; ?>>Dikirim</option>
                            <option value="completed" <?php echo (($_GET['status'] ?? '') == 'completed') ? 'selected' : ''; ?>>Selesai</option>
                            <option value="cancelled" <?php echo (($_GET['status'] ?? '') == 'cancelled') ? 'selected' : ''; ?>>Dibatalkan</option>
                            <option value="rejected" <?php echo (($_GET['status'] ?? '') == 'rejected') ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="col mb-3">
                        <label for="order_type" class="form-label">Tipe Pesanan</label>
                        <select class="form-select" id="order_type" name="order_type">
                            <option value="">Semua Tipe</option>
                            <option value="online" <?php echo (($_GET['order_type'] ?? '') == 'online') ? 'selected' : ''; ?>>Online</option>
                            <option value="pos" <?php echo (($_GET['order_type'] ?? '') == 'pos') ? 'selected' : ''; ?>>
                                POS</option>
                        </select>
                    </div>
                    <div class="col mb-3">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="orders.php" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

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

                            <?php
                            $status = $order['status'];
                            $label = $statusMap[$status]['label'] ?? 'Status Tidak Diketahui';
                            $badgeClass = $statusMap[$status]['badge'] ?? 'bg-dark';

                            $payment = $order['payment_status'];
                            $paymentLabel = $paymentStatusMap[$payment]['label'] ?? 'Tidak Diketahui';
                            $paymentBadge = $paymentStatusMap[$payment]['badge'] ?? 'bg-secondary';
                            ?>

                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                            <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                            <td><span
                                    class="badge bg-<?php echo get_order_status_class($order['status']); ?>"><?php echo $label; ?></span>
                            </td>
                            <td><span
                                    class="badge bg-<?php echo get_payment_status_class($order['payment_status']); ?>"><?= $paymentLabel ?></span>
                            </td>
                            <td>
                                <a href="order_detail_admin.php?id=<?php echo $order['id']; ?>"
                                    class="btn btn-info btn-sm">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
function get_order_status_class($status)
{
    switch ($status) {
        case 'awaiting_payment':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        case 'rejected':
            return 'danger';
        default:
            return 'secondary';
    }
}

function get_payment_status_class($status)
{
    switch ($status) {
        case 'unpaid':
            return 'warning';
        case 'pending_verification':
            return 'info';
        case 'paid':
            return 'success';
        case 'failed':
            return 'danger';
        default:
            return 'secondary';
    }
}

require_once __DIR__ . '/../templates/footer_admin.php';
?>
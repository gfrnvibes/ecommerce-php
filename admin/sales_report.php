<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$status = $_GET['status'] ?? 'completed'; // Default to 'completed'
$order_type = $_GET['order_type'] ?? 'all'; // Default to all

// Base query parts
$where_clause = "WHERE DATE(o.created_at) BETWEEN ? AND ?";
$params = [$start_date, $end_date];

if ($status !== 'all') {
    $where_clause .= " AND o.status = ?";
    $params[] = $status;
}

if ($order_type !== 'all') {
    $where_clause .= " AND o.order_type = ?";
    $params[] = $order_type;
}

// Fetch sales data
$stmt = $pdo->prepare("SELECT 
                        COUNT(DISTINCT o.id) as total_orders, 
                        SUM(o.total_amount) as total_revenue,
                        (SELECT SUM(quantity) FROM order_items oi_sum JOIN orders o_sum ON oi_sum.order_id = o_sum.id WHERE DATE(o_sum.created_at) BETWEEN ? AND ? " . ($status !== 'all' ? "AND o_sum.status = ?" : "") . ") as total_items_sold
                     FROM orders o
                     $where_clause");
$report_params = array_merge([$start_date, $end_date], ($status !== 'all' ? [$status] : []));
$full_params = array_merge($report_params, $params);
//This is a bit tricky because of the subquery. Let's simplify.

// Simplified approach
$report_sql = "SELECT
    COUNT(o.id) AS total_orders,
    SUM(o.total_amount) AS total_revenue,
    SUM(oi.quantity) AS total_items_sold
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    $where_clause";

$stmt = $pdo->prepare($report_sql);
$stmt->execute($params);
$report = $stmt->fetch();


// Fetch top selling products
$stmt_products = $pdo->prepare("SELECT p.name, SUM(oi.quantity) as total_sold
                               FROM order_items oi
                               JOIN products p ON oi.product_id = p.id
                               JOIN orders o ON oi.order_id = o.id
                               $where_clause
                               GROUP BY p.name
                               ORDER BY total_sold DESC
                               LIMIT 10");
$stmt_products->execute($params);
$top_products = $stmt_products->fetchAll();

require_once __DIR__ . '/../templates/header_admin.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan Penjualan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Laporan Penjualan</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" id="start_date" name="start_date" class="form-control"
                        value="<?php echo $start_date; ?>">
                </div>
                <div class="col-auto">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input type="date" id="end_date" name="end_date" class="form-control"
                        value="<?php echo $end_date; ?>">
                </div>
                <div class="col-auto">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="all" <?php echo ($status === 'all') ? 'selected' : ''; ?>>Semua</option>
                        <option value="awaiting_payment" <?php echo ($status === 'awaiting_payment') ? 'selected' : ''; ?>>Menunggu Pembayaran
                        </option>
                        <option value="processing" <?php echo ($status === 'processing') ? 'selected' : ''; ?>>Diproses
                        </option>
                        <option value="shipped" <?php echo ($status === 'shipped') ? 'selected' : ''; ?>>Dikirim</option>
                        <option value="completed" <?php echo ($status === 'completed') ? 'selected' : ''; ?>>Selesai
                        </option>
                        <option value="cancelled" <?php echo ($status === 'cancelled') ? 'selected' : ''; ?>>Dibatalkan
                        </option>
                        <option value="rejected" <?php echo ($status === 'rejected') ? 'selected' : ''; ?>>Ditolak
                        </option>
                    </select>
                </div>
                <div class="col-auto">
                    <div class="col-auto">
                        <label for="order_type" class="form-label">Jenis Order</label>
                        <select id="order_type" name="order_type" class="form-select">
                            <option value="all" <?php echo ($order_type === 'all') ? 'selected' : ''; ?>>Semua</option>
                            <option value="online" <?php echo ($order_type === 'online') ? 'selected' : ''; ?>>Pesanan
                                Online</option>
                            <option value="pos" <?php echo ($order_type === 'pos') ? 'selected' : ''; ?>>POS</option>
                        </select>
                    </div>


                </div>
                <div class="col-auto mt-5">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#printModal">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4>Total Pendapatan</h4>
                    <h2>Rp <?php echo number_format($report['total_revenue'] ?? 0, 0, ',', '.'); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4>Total Pesanan Selesai</h4>
                    <h2><?php echo $report['total_orders'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h4>Total Item Terjual</h4>
                    <h2><?php echo $report['total_items_sold'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-bar me-1"></i>
            Produk Terlaris (Top 10)
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jumlah Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo $product['total_sold']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($top_products)): ?>
                        <tr>
                            <td colspan="2" class="text-center">Tidak ada data penjualan pada rentang tanggal ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
require_once __DIR__ . '/../templates/footer_admin.php';
?>

<!-- Print Modal -->
<div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printModalLabel">Cetak Laporan Penjualan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="print_report.php" method="GET" target="_blank">
                    <div class="mb-3">
                        <label for="print_start_date" class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" id="print_start_date" name="start_date"
                            value="<?php echo date('Y-m-01'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="print_end_date" class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="print_end_date" name="end_date"
                            value="<?php echo date('Y-m-t'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="print_report_type" class="form-label">Jenis Laporan</label>
                        <select class="form-select" id="print_report_type" name="report_type" required>
                            <option value="online">Laporan Pesanan Online</option>
                            <option value="pos">Laporan POS</option>
                            <option value="all">Semua Laporan</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Cetak</button>
                </form>
            </div>
        </div>
    </div>
</div>
?>
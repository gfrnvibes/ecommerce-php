<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

// Cek otentikasi dan otorisasi admin
check_auth(true);

require_once __DIR__ . '/../templates/header_admin.php';

// Ambil data statistik untuk dashboard
$total_products = $pdo->query('SELECT count(*) FROM products')->fetchColumn();
$total_customers = $pdo->query("SELECT count(*) FROM users WHERE role = 'customer'")->fetchColumn();
$pending_orders = $pdo->query("SELECT count(*) FROM orders WHERE status = 'processing' OR status = 'awaiting_payment'")->fetchColumn();
$total_sales = $pdo->query('SELECT SUM(total_amount) FROM orders WHERE status = \'completed\'')->fetchColumn();

?>

<div class="container-fluid min-h-screen">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">Total Produk</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white stretched-link"><?php echo $total_products; ?></span>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">Pesanan Pending</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white stretched-link"><?php echo $pending_orders; ?></span>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">Total Penjualan</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white stretched-link">Rp <?php echo number_format($total_sales ?? 0, 0, ',', '.'); ?>
</span>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">Total Pelanggan</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white stretched-link"><?php echo $total_customers; ?></span>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Chart dan tabel lain bisa ditambahkan di sini -->
</div>

<?php
require_once __DIR__ . '/../templates/footer_admin.php';
?>
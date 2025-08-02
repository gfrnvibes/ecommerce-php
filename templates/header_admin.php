<?php
// File: templates/header_admin.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Admin - Toko Bapak Maksum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>public/css/styles_admin.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#adminSidebar" aria-controls="adminSidebar">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand ps-3" href="<?php echo BASE_URL; ?>admin/dashboard.php">Admin Panel</a>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>index.php" target="_blank">Lihat Situs</a>
                    </li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="adminSidebar"
        aria-labelledby="adminSidebarLabel">
        <!-- Header -->
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title" id="adminSidebarLabel">Admin Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>

        <!-- Body -->
        <div class="offcanvas-body d-flex flex-column p-0">
            <nav class="nav flex-column px-3 py-2">
                <!-- Section: Core -->
                <div class="sb-sidenav-menu-heading text-uppercase small text-secondary mt-3">Core</div>
                <a class="nav-link text-white d-flex align-items-center"
                    href="<?php echo BASE_URL; ?>admin/dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>

                <!-- Section: Manajemen -->
                <div class="sb-sidenav-menu-heading text-uppercase small text-secondary mt-4">Manajemen</div>
                <a class="nav-link text-white d-flex align-items-center" href="<?php echo BASE_URL; ?>admin/pos.php">
                    <i class="fas fa-cash-register me-2"></i> Kasir (POS)
                </a>
                <a class="nav-link text-white d-flex align-items-center" href="<?php echo BASE_URL; ?>admin/orders.php">
                    <i class="fas fa-shopping-cart me-2"></i> Pesanan
                </a>
                <a class="nav-link text-white d-flex align-items-center"
                    href="<?php echo BASE_URL; ?>admin/products.php">
                    <i class="fas fa-box me-2"></i> Produk
                </a>
                <a class="nav-link text-white d-flex align-items-center"
                    href="<?php echo BASE_URL; ?>admin/inventory.php">
                    <i class="fas fa-boxes me-2"></i> Inventaris
                </a>

                <!-- Section: Laporan -->
                <div class="sb-sidenav-menu-heading text-uppercase small text-secondary mt-4">Laporan</div>
                <a class="nav-link text-white d-flex align-items-center"
                    href="<?php echo BASE_URL; ?>admin/sales_report.php">
                    <i class="fas fa-chart-area me-2"></i> Laporan Penjualan
                </a>
            </nav>

            <!-- Footer -->
            <div
                class="sb-sidenav-footer mt-auto bg-secondary bg-opacity-10 text-white px-3 py-3 border-top border-secondary">
                <div class="small">Logged in as:</div>
                <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
            </div>
        </div>
    </div>


    <main class="container-fluid mt-4">
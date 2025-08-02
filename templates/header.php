<?php
// File: templates/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    // Jika config.php belum di-include, coba include dari path relatif
    require_once __DIR__ . '/../config/config.php';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Online Bapak Maksum</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary-pink">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">Toko Bapak Maksum</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link text-light" href="<?php echo BASE_URL; ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light" href="<?php echo BASE_URL; ?>products.php">Produk</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link text-light" href="<?php echo BASE_URL; ?>cart.php">Keranjang</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link text-light dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <?php if ($_SESSION['user_role'] == 'admin'): ?>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard Admin</a>
                            <?php endif; ?>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>my_orders.php">Pesanan Saya</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="<?php echo BASE_URL; ?>auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="<?php echo BASE_URL; ?>auth/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
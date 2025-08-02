<?php
require_once 'config/config.php';
require_once 'templates/header.php';
?>

<div class="min-h-screen">
    <div class="mb-5 p-5 text-center" style="background-color: #f6e7ffff;">
        <h1>Selamat Datang di Toko Bapak Maksum!😉</h1>
        <p class="lead">Temukan berbagai produk berkualitas dengan harga terbaik.</p>
        <p>Silakan lihat-lihat produk kami.</p>
        <a class="btn btn-primary fw-bold" href="products.php" role="button">Lihat Produk</a>
    </div>

    <div class="container">
        <h2 class="mb-3">Produk Terbaru</h2>
        <div class="row">
            <?php
            // Ambil beberapa produk untuk ditampilkan di landing page
            $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 4");
            while ($row = $stmt->fetch()) {
                echo '<div class="col-md-3 mb-4">';
                echo '    <div class="card">';
                echo '        <img src="' . ($row['image_url'] ? 'uploads/products/' . $row['image_url'] : 'public/images/placeholder.png') . '" class="card-img-top" alt="' . htmlspecialchars($row['name']) . '">';
                echo '        <div class="card-body">';
                echo '            <h5 class="card-title">' . htmlspecialchars($row['name']) . '</h5>';
                echo '            <p class="card-text text-success">Rp ' . number_format($row['price'], 0, ',', '.') . '</p>';
                echo '            <a href="product_detail.php?id=' . $row['id'] . '" class="btn btn-outline-primary">Detail</a>';
                echo '            <a href="cart_action.php?action=add&id=' . $row['id'] . '" class="btn btn-primary">+ Keranjang</a>';
                echo '        </div>';
                echo '    </div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
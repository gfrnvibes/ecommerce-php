<?php
require_once 'config/config.php';
require_once 'templates/header.php';
?>

<div class="container mt-5">
    <h2>Semua Produk</h2>
    <hr>
    <div class="row">
        <?php
        // Ambil semua produk dari database
        $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch()) {
                echo '<div class="col-md-4 col-lg-3 mb-4">';
                echo '    <div class="card h-100">';
                echo '        <a href="product_detail.php?id=' . $row['id'] . '">';
                echo '            <img src="' . ($row['image_url'] ? 'uploads/products/' . $row['image_url'] : 'public/images/placeholder.png') . '" class="card-img-top" alt="' . htmlspecialchars($row['name']) . '">';
                echo '        </a>';
                echo '        <div class="card-body d-flex flex-column">';
                echo '            <h5 class="card-title"><a href="product_detail.php?id=' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</a></h5>';
                echo '            <p class="card-text">Rp ' . number_format($row['price'], 2, ',', '.') . '</p>';
                echo '            <div class="mt-auto">';
                echo '                <a href="cart_action.php?action=add&id=' . $row['id'] . '" class="btn btn-primary btn-block">+ Tambah ke Keranjang</a>';
                echo '            </div>';
                echo '        </div>';
                echo '    </div>';
                echo '</div>';
            }
        } else {
            echo '<div class="col-12"><p class="text-center">Belum ada produk yang tersedia.</p></div>';
        }
        ?>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
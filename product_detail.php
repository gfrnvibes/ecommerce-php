<?php
require_once 'config/config.php';

// Cek apakah ID produk ada
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];

// Ambil detail produk dari database
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Jika produk tidak ditemukan, redirect ke halaman produk
if (!$product) {
    header("Location: products.php");
    exit();
}

require_once 'templates/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo ($product['image_url'] ? 'uploads/products/' . $product['image_url'] : 'public/images/placeholder.png'); ?>" class="img-fluid img-thumbnail" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <h4 class="text-success">Rp <?php echo number_format($product['price'], 2, ',', '.'); ?></h4>
            <p><strong>Stok:</strong> <?php echo $product['stock']; ?></p>
            <hr>
            <h5>Deskripsi Produk</h5>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <hr>
            <form action="cart_action.php?action=add" method="post">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div class="form-group row">
                    <label for="quantity" class="col-sm-3 col-form-label">Jumlah:</label>
                    <div class="col-sm-4">
                        <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg mt-4"><i class="fas fa-shopping-cart"></i> Tambah ke Keranjang</button>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
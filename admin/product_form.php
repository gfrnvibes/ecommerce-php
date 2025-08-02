<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

$product = [
    'id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'stock' => '',
    'image_url' => ''
];
$form_action = 'product_action.php?action=create';
$page_title = 'Tambah Produk Baru';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch();
    if (!$product) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Produk tidak ditemukan.'];
        header('Location: products.php');
        exit();
    }
    $form_action = 'product_action.php?action=update&id=' . $product['id'];
    $page_title = 'Edit Produk';
}

require_once __DIR__ . '/../templates/header_admin.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $page_title; ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="products.php">Produk</a></li>
        <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-box me-1"></i>
            Formulir Produk
        </div>
        <div class="card-body">
            <form action="<?php echo $form_action; ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Harga</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?php echo $product['price']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stok</label>
                    <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Gambar Produk</label>
                    <input type="file" class="form-control" id="image" name="image">
                    <?php if ($product['image_url']): ?>
                        <div class="mt-2">
                            <img src="<?php echo BASE_URL . 'uploads/products/' . $product['image_url']; ?>" alt="" width="100">
                            <p>Gambar saat ini: <?php echo $product['image_url']; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="products.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer_admin.php';
?>
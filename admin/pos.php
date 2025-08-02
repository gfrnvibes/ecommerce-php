<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

// Start or resume the POS session
if (!isset($_SESSION['pos_cart'])) {
    $_SESSION['pos_cart'] = [];
}

$products_stmt = $pdo->query("SELECT * FROM products WHERE stock > 0 ORDER BY name ASC");
$products = $products_stmt->fetchAll();

require_once __DIR__ . '/../templates/header_admin.php';
?>

<div class="container-fluid px-4 min-h-screen">
    <h1 class="mt-4">Point of Sale (POS)</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">POS</li>
    </ol>

    <?php if (isset($_SESSION['message'])):
        ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']['text']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Product List -->
        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-boxes me-1"></i>
                    Daftar Produk
                </div>
                <div class="card-body">
                    <input type="text" id="productSearch" class="form-control mb-3" placeholder="Cari produk...">
                    <div id="product-list" class="list-group" style="max-height: 500px; overflow-y: auto;">
                        <?php foreach ($products as $product): ?>
                            <a href="pos_action.php?action=add&id=<?php echo $product['id']; ?>"
                                class="list-group-item list-group-item-action product-item">
                                <div class="d-flex border rounded p-3 mb-3 align-items-center"
                                    style="background-color: #f9f9f9;">
                                    <!-- Gambar Produk -->
                                    <div class="me-3">
                                        <img src="../uploads/products/<?php echo $product['image_url']; ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-thumbnail"
                                            style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>

                                    <!-- Info Produk -->
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="mb-0 product-name"><?php echo htmlspecialchars($product['name']); ?>
                                            </h5>
                                            <small class="text-muted">Stok: <?php echo $product['stock']; ?></small>
                                        </div>
                                        <p class="mb-0 text-primary fw-bold">Rp
                                            <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                                    </div>
                                </div>

                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- POS Cart -->
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shopping-cart me-1"></i>
                    Keranjang Transaksi
                </div>
                <div class="card-body">
                    <?php if (empty($_SESSION['pos_cart'])): ?>
                        <p class="text-center">Keranjang masih kosong.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = 0;
                                foreach ($_SESSION['pos_cart'] as $id => $item):
                                    $total += $item['price'] * $item['quantity'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td>
                                            <form action="pos_action.php?action=update&id=<?php echo $id; ?>" method="POST"
                                                class="d-inline">
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>"
                                                    min="1" max="<?php echo $item['stock']; ?>"
                                                    class="form-control form-control-sm" style="width: 70px;"
                                                    onchange="this.form.submit()">
                                            </form>
                                        </td>
                                        <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                                        </td>
                                        <td>
                                            <a href="pos_action.php?action=remove&id=<?php echo $id; ?>"
                                                class="btn btn-danger btn-sm">&times;</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <hr>
                        <h4>Total: Rp <?php echo number_format($total, 0, ',', '.'); ?></h4>
                        <div class="d-grid gap-2 mt-3">
                            <a href="pos_checkout.php" class="btn btn-primary">Proses Pembayaran</a>
                            <a href="pos_action.php?action=clear" class="btn btn-danger">Batalkan Transaksi</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('productSearch').addEventListener('keyup', function () {
        let filter = this.value.toLowerCase();
        let items = document.querySelectorAll('#product-list .product-item');
        items.forEach(function (item) {
            let name = item.querySelector('.product-name').textContent.toLowerCase();
            if (name.includes(filter)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
</script>

<?php
require_once __DIR__ . '/../templates/footer_admin.php';
?>
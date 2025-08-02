<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

// Fetch all products for dropdown
$stmt_products = $pdo->query("SELECT id, name, stock FROM products ORDER BY name");
$products = $stmt_products->fetchAll();

require_once __DIR__ . '/../templates/header_admin.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Tambah Stok (Barang Masuk)</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="inventory.php">Inventaris</a></li>
        <li class="breadcrumb-item active">Tambah Stok</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus-circle me-1"></i>
            Formulir Barang Masuk
        </div>
        <div class="card-body">
            <form action="inventory_action.php" method="POST">
                <input type="hidden" name="action" value="stock_in">

                <div class="mb-3">
                    <label for="product_id" class="form-label">Produk</label>
                    <select id="product_id" name="product_id" class="form-select" required>
                        <option value="">Pilih Produk...</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?php echo $p['id']; ?>" data-stock="<?php echo $p['stock']; ?>">
                                <?php echo htmlspecialchars($p['name']); ?> (Stok Saat Ini: <?php echo $p['stock']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="quantity" class="form-label">Jumlah Masuk</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" min="1" required>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Catatan (Opsional)</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="inventory.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer_admin.php';
?>
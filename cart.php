<?php
require_once 'config/config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Anda harus login untuk melihat keranjang.'];
    header('Location: auth/login.php');
    exit();
}

require_once 'templates/header.php';

$user_id = $_SESSION['user_id'];

// Ambil item dari keranjang
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, p.image_url, p.stock, c.quantity
    FROM carts c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total_price = 0;
?>

<div class="container mt-5 min-h-screen">
    <h2>Keranjang Belanja Anda</h2>
    <hr>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?>" role="alert">
            <?php echo $_SESSION['message']['text']; ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (count($cart_items) > 0): ?>
        <form action="cart_action.php?action=update" method="post">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <?php $subtotal = $item['price'] * $item['quantity']; ?>
                        <?php $total_price += $subtotal; ?>
                        <tr>
                            <td>
                                <img src="<?php echo ($item['image_url'] ? 'uploads/products/' . $item['image_url'] : 'public/images/placeholder.png'); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                    style="width: 50px; height: 50px; object-fit: cover;">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>
                            <td>Rp <?php echo number_format($item['price'], 2, ',', '.'); ?></td>
                            <td>
                            <td>
                                <input type="number" name="quantities[<?php echo $item['id']; ?>]" class="form-control"
                                    value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>"
                                    oninvalid="this.setCustomValidity('Jumlah tidak boleh lebih dari <?php echo $item['stock']; ?>')"
                                    oninput="this.setCustomValidity('')">
                            </td>
                            </td>
                            <td>Rp <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                            <td>
                                <a href="cart_action.php?action=remove&id=<?php echo $item['id']; ?>"
                                    class="btn btn-danger btn-sm">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="row justify-content-between align-items-center mt-4">
                <div class="col-md-6">
                    <div class="text-danger mb-3">Klik "Perbarui Keranjang" jika ada perubahan Jumlah.</div>
                    <a href="products.php" class="btn btn-secondary">Lanjut Belanja</a>
                    <button type="submit" class="btn btn-primary">Perbarui Keranjang</button>
                </div>
                <div class="col-md-6 text-end">
                    <h4>Total: Rp <?php echo number_format($total_price, 2, ',', '.'); ?></h4>
                    <a href="checkout.php" class="btn btn-primary btn-lg">Lanjut ke Checkout</a>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            Keranjang belanja Anda masih kosong. Yuk, mulai <a href="products.php">belanja</a>!
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer.php';
?>
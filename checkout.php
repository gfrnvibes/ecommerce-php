<?php
require_once 'config/config.php';

// Pastikan user sudah login dan punya item di keranjang
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data pengguna saat ini, termasuk alamat pengiriman terakhir
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

// Inisialisasi variabel alamat dari data pengguna atau biarkan kosong
$shipping_address = $user['shipping_address'] ?? '';

// Ambil item dari keranjang
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, c.quantity
    FROM carts c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Jika keranjang kosong, redirect
if (count($cart_items) === 0) {
    header('Location: cart.php');
    exit();
}

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

require_once 'templates/header.php';
?>

<div class="container mt-5">
    <h2>Checkout</h2>
    <hr>
    <div class="row">
        <div class="col-md-7">
            <h4>Alamat Pengiriman</h4>
            <form action="order_process.php" method="post">
                <div class="form-group mb-3">
                    <label for="shipping_address" class="form-label">Alamat Lengkap</label>
                    <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3"
                        required><?php echo htmlspecialchars($shipping_address); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="payment_method" class="form-label">Metode Pembayaran</label>
                    <select class="form-control" id="payment_method" name="payment_method" required>
                        <option value="manual_transfer">Transfer Bank Manual</option>
                        <!-- Opsi pembayaran lain bisa ditambahkan di sini -->
                    </select>
                </div>
                <input type="hidden" name="total_amount" value="<?php echo $total_price; ?>">
                <button type="submit" class="btn btn-primary btn-lg mt-4">Buat Pesanan</button>
            </form>
        </div>
        <div class="col-md-5">
            <h4>Ringkasan Pesanan</h4>
            <table class="table">
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</td>
                            <td class="text-right">Rp
                                <?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="font-weight-bold">
                        <td>Total</td>
                        <td class="text-right">Rp <?php echo number_format($total_price, 2, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
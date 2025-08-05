<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

if (empty($_SESSION['pos_cart'])) {
    header('Location: pos.php');
    exit();
}

$cart_items = $_SESSION['pos_cart'];
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount_paid = filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT);

    if ($amount_paid === false || $amount_paid < $total_amount) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Jumlah yang dibayarkan tidak valid atau kurang dari total belanja.'];
        header('Location: pos_checkout.php');
        exit();
    }

    $change_amount = $amount_paid - $total_amount;

    $pdo->beginTransaction();
    try {
        // Create a dummy customer for POS sales if needed, or use a default one.
        // For simplicity, we'll assume a default 'pos_customer' user with ID 0 or a specific ID.
        // Let's create one if it doesn't exist.
        $pos_user_email = 'pos@localhost';
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$pos_user_email]);
        $pos_user = $stmt->fetch();

        if (!$pos_user) {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['POS Customer', $pos_user_email, password_hash('password', PASSWORD_DEFAULT), 'customer']);
            $pos_user_id = $pdo->lastInsertId();
        } else {
            $pos_user_id = $pos_user['id'];
        }

        // Insert into orders table
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, amount_paid, change_amount, order_type, status, payment_status, payment_method, shipping_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$pos_user_id, $total_amount, $amount_paid, $change_amount, 'pos', 'completed', 'paid', 'cash', 'In-store purchase']);
        $order_id = $pdo->lastInsertId();

        // Insert into order_items, update stock and log inventory movement
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt_log_inventory = $pdo->prepare("INSERT INTO inventory_movements (product_id, order_id, type, quantity, notes) VALUES (?, ?, 'out', ?, ?)");

        foreach ($cart_items as $product_id => $item) {
            $stmt_item->execute([$order_id, $product_id, $item['quantity'], $item['price']]);
            $stmt_stock->execute([$item['quantity'], $product_id]);
            $notes = 'Penjualan POS';
            $stmt_log_inventory->execute([$product_id, $order_id, $item['quantity'], $notes]);
        }

        $pdo->commit();

        // Clear POS cart
        $_SESSION['pos_cart'] = [];

        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Transaksi berhasil! Pesanan #' . $order_id . ' telah dibuat. Jumlah dibayar: Rp ' . number_format($amount_paid, 0, ',', '.') . '. Kembalian: Rp ' . number_format($change_amount, 0, ',', '.') . '.'
        ];
        header('Location: receipt.php?order_id=' . $order_id);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Transaksi gagal: ' . $e->getMessage()];
        header('Location: pos_checkout.php');
        exit();
    }
}

require_once __DIR__ . '/../templates/header_admin.php';
?>

<div class="container px-4">
    <h1 class="mt-4">Checkout POS</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="pos.php">POS</a></li>
        <li class="breadcrumb-item active">Checkout</li>
    </ol>

    <?php if (isset($_SESSION['message'])):
        ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']['text']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-receipt me-1"></i>
            Ringkasan Transaksi
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total Belanja:</th>
                        <th>Rp <?php echo number_format($total_amount, 0, ',', '.'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer text-end">
            <form method="POST">
                <div class="col-3 mb-3 ms-auto me-5">
                    <label for="amount_paid" class="form-label">Jumlah Dibayar (Rp)</label>
                    <input type="number" class="form-control form-control-lg" id="amount_paid" name="amount_paid"
                        value="1000" min="0" step="any" required>
                </div>
                <div class="mb-3 text-end me-5">
                    <p class="fw-bold">Kembalian: <span id="change_amount_display">Rp 0</span></p>
                </div>
                <div class="mb-4 me-5 mt-4">
                    <button type="submit" class="btn btn-success btn-lg">Selesaikan Transaksi</button>
                    <a href="pos.php" class="btn btn-secondary btn-lg">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const totalAmount = <?php echo $total_amount; ?>;
        const amountPaidInput = document.getElementById('amount_paid');
        const changeAmountDisplay = document.getElementById('change_amount_display');

        amountPaidInput.addEventListener('input', function () {
            let amountPaid = parseFloat(this.value);
            if (isNaN(amountPaid)) {
                amountPaid = 0;
            }
            const change = amountPaid - totalAmount;
            changeAmountDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(change);
        });
    });
</script>

<?php
require_once __DIR__ . '/../templates/footer_admin.php';
?>
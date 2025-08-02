<?php
require_once __DIR__ . '/includes/cancel_unpaid_orders.php';
require_once 'config/config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: my_orders.php');
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil data pesanan, pastikan pesanan ini milik user yang login
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Pesanan tidak ditemukan.'];
    header('Location: my_orders.php');
    exit();
}

// Ambil item pesanan
$items_stmt = $pdo->prepare("
    SELECT p.name, oi.quantity, oi.price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll();

require_once 'templates/header.php';
?>

<div class="container mt-5">
    <h2>Detail Pesanan #<?php echo $order['id']; ?></h2>
    <hr>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?>" role="alert">
            <?php echo $_SESSION['message']['text']; ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <h4>Item yang Dipesan</h4>
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
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>Rp <?php echo number_format($item['price'], 2, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <h4>Ringkasan</h4>
            <p><strong>Total Pesanan:</strong> <span class="float-right">Rp
                    <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></span></p>
            <p><strong>Status Pesanan:</strong> <span
                    class="float-right badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span>
            </p>
            <p><strong>Status Pembayaran:</strong> <span
                    class="float-right badge bg-warning"><?php echo ucfirst(str_replace('_', ' ', $order['payment_status'])); ?></span>
            </p>

            <?php if ($order['status'] == 'awaiting_payment' && !empty($order['payment_due'])): ?>
                <div id="payment-countdown" class="alert alert-warning mt-3">
                    Batas waktu pembayaran: <span id="timer"></span>
                </div>
            <?php endif; ?>
            <p><strong>Alamat Pengiriman:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
            </p>

            <?php if ($order['status'] == 'rejected'): ?>
                <div class="alert alert-danger">
                    <strong>Alasan Penolakan:</strong> <?php echo htmlspecialchars($order['rejection_reason']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($order['status'] == 'awaiting_payment'): ?>
        <hr>
        <div class="card">
            <div class="card-header">Konfirmasi Pembayaran</div>
            <div class="card-body">
                <p>Silakan transfer sejumlah <strong>Rp
                        <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></strong> ke rekening berikut:</p>
                <p><strong>Bank ABC</strong><br>No. Rekening: 123-456-7890<br>Atas Nama: TokoKu</p>
                <hr>
                <form action="payment_upload.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <div class="form-group">
                        <label for="payment_proof" class="form-label">Upload Bukti Pembayaran</label>
                        <input type="file" class="form-control" id="payment_proof" name="payment_proof" required>
                        <small class="form-text text-muted">Format file: JPG, PNG. Ukuran maks: 2MB.</small>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">Kirim Bukti Pembayaran</button>
                </form>
            </div>
        </div>
    <?php elseif ($order['payment_status'] == 'pending_verification'): ?>
        <hr>
        <div class="alert alert-info">Bukti pembayaran Anda sedang diverifikasi oleh admin.</div>
    <?php elseif ($order['payment_status'] == 'paid'): ?>
        <hr>
        <div class="alert alert-success">Pembayaran Anda telah kami terima. Pesanan akan segera diproses.</div>
    <?php elseif ($order['status'] == 'cancelled'): ?>
        <hr>
        <div class="alert alert-danger">Pesanan ini telah dibatalkan.</div>
    <?php endif; ?>

    <?php if ($order['status'] == 'awaiting_payment'): ?>
        <hr>
        <form action="order_action.php?action=cancel" method="post" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
            <button type="submit" class="btn btn-danger">Batalkan Pesanan</button>
        </form>
    <?php endif; ?>



</div>

<?php
require_once 'templates/footer.php';
?>

<?php if ($order['status'] == 'awaiting_payment' && !empty($order['payment_due'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const timerElement = document.getElementById('timer');
            const countdownDiv = document.getElementById('payment-countdown');

            if (!timerElement || !countdownDiv) {
                return;
            }

            const paymentDue = new Date('<?php echo $order['payment_due']; ?>').getTime();

            const countdown = setInterval(function () {
                const now = new Date().getTime();
                const distance = paymentDue - now;

                if (distance < 0) {
                clearInterval(countdown);
                // Cek status terbaru dari server, jika belum 'cancelled', maka refresh.
                // Ini mencegah loop refresh jika status sudah berhasil diubah.
                if ('<?php echo $order['status']; ?>' === 'awaiting_payment') {
                    countdownDiv.innerHTML = "<strong>Waktu pembayaran telah habis.</strong> Halaman akan dimuat ulang untuk memperbarui status.";
                    countdownDiv.classList.remove('alert-warning');
                    countdownDiv.classList.add('alert-danger');
                    setTimeout(() => window.location.reload(), 3000);
                } else {
                     countdownDiv.innerHTML = "<strong>Waktu pembayaran telah habis.</strong>";
                     countdownDiv.classList.remove('alert-warning');
                     countdownDiv.classList.add('alert-danger');
                }
                return;
            }

                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                timerElement.innerHTML = ('0' + hours).slice(-2) + ":" + ('0' + minutes).slice(-2) + ":" + ('0' + seconds).slice(-2);
            }, 1000);
        });
    </script>
<?php endif; ?>
<?php
require_once 'config/config.php';

// Pastikan user sudah login dan form disubmit
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'];

// Validasi bahwa pesanan ini milik user yang login
$stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'awaiting_payment'");
$stmt->execute([$order_id, $user_id]);
if ($stmt->rowCount() == 0) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Pesanan tidak valid atau sudah diproses.'];
    header('Location: my_orders.php');
    exit();
}

// Proses upload file
if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
    $target_dir = "uploads/payments/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $file_extension = pathinfo($_FILES["payment_proof"]["name"], PATHINFO_EXTENSION);
    $new_filename = "payment_" . $order_id . "_" . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    $allowed_types = ['jpg', 'jpeg', 'png'];
    $max_size = 2 * 1024 * 1024; // 2MB

    // Validasi file
    if (!in_array(strtolower($file_extension), $allowed_types)) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Format file tidak didukung. Hanya JPG, JPEG, PNG yang diizinkan.'];
    } elseif ($_FILES['payment_proof']['size'] > $max_size) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Ukuran file terlalu besar. Maksimal 2MB.'];
    } else {
        // Pindahkan file yang diupload
        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_file)) {
            // Update status pesanan di database
            $update_stmt = $pdo->prepare("UPDATE orders SET payment_proof_url = ?, payment_status = 'pending_verification' WHERE id = ?");
            $update_stmt->execute([$new_filename, $order_id]);

            $_SESSION['message'] = ['type' => 'success', 'text' => 'Bukti pembayaran berhasil diunggah. Admin akan segera memverifikasi.'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Terjadi kesalahan saat mengunggah file.'];
        }
    }
} else {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Tidak ada file yang diunggah atau terjadi error.'];
}

header('Location: order_detail.php?id=' . $order_id);
exit();
?>
<?php
require_once 'config/config.php';

// Pastikan user sudah login untuk bisa mengakses keranjang
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Anda harus login untuk menambahkan item ke keranjang.'];
    header('Location: auth/login.php');
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = $_SESSION['user_id'];

// Validate that the user from the session still exists in the database
$stmt_user_check = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt_user_check->execute([$user_id]);
if ($stmt_user_check->rowCount() === 0) {
    // If user is not found, their account might have been deleted. 
    // Destroy the session and force a new login.
    session_unset();
    session_destroy();

    // Start a new session just to pass a message to the login page
    session_start();
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Sesi Anda tidak valid atau pengguna telah dihapus. Silakan login kembali.'];
    header('Location: auth/login.php');
    exit();
}

switch ($action) {
    case 'add':
        $product_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : (isset($_POST['product_id']) ? $_POST['product_id'] : null);
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

        if ($product_id && $quantity > 0) {
            // Cek apakah produk sudah ada di keranjang user
            $stmt = $pdo->prepare("SELECT * FROM carts WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            $cart_item = $stmt->fetch();

            if ($cart_item) {
                // Jika sudah ada, update quantity
                $new_quantity = $cart_item['quantity'] + $quantity;
                $update_stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?");
                $update_stmt->execute([$new_quantity, $cart_item['id']]);
            } else {
                // Jika belum ada, tambahkan item baru
                $insert_stmt = $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $insert_stmt->execute([$user_id, $product_id, $quantity]);
            }
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Produk berhasil ditambahkan ke keranjang.'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Gagal menambahkan produk.'];
        }
        header('Location: cart.php');
        break;

    case 'update':
        // Logika untuk update quantity di keranjang
        if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
            foreach ($_POST['quantities'] as $product_id => $quantity) {
                $quantity = (int) $quantity;
                if ($quantity > 0) {
                    $stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$quantity, $user_id, $product_id]);
                } else {
                    // Jika quantity 0 atau kurang, hapus item
                    $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$user_id, $product_id]);
                }
            }
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Keranjang berhasil diperbarui.'];
        }
        header('Location: cart.php');
        break;

    case 'remove':
        $product_id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($product_id) {
            $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Produk berhasil dihapus dari keranjang.'];
        }
        header('Location: cart.php');
        break;

    default:
        header('Location: index.php');
        break;
}

exit();
?>
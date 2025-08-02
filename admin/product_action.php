<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

$action = $_GET['action'] ?? '';

// Handle image upload
function handle_image_upload($file) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // No file uploaded
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Terjadi kesalahan saat mengunggah file.'];
        header('Location: products.php');
        exit();
    }

    $target_dir = __DIR__ . "/../uploads/products/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;

    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'File bukan gambar.'];
        header('Location: product_form.php');
        exit();
    }

    // Check file size (max 2MB)
    if ($file["size"] > 2000000) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Ukuran file terlalu besar. Maksimal 2MB.'];
        header('Location: product_form.php');
        exit();
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Hanya format JPG, JPEG, & PNG yang diperbolehkan.'];
        header('Location: product_form.php');
        exit();
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Terjadi kesalahan saat memindahkan file yang diunggah.'];
        header('Location: product_form.php');
        exit();
    }
}

if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image_url = null;

    if (isset($_FILES['image'])) {
        $image_url = handle_image_upload($_FILES['image']);
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $description, $price, $stock, $image_url])) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Produk berhasil ditambahkan.'];
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Gagal menambahkan produk.'];
    }
    header('Location: products.php');
    exit();
}

if ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_GET['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    $image_url = $product['image_url'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Delete old image if exists
        if ($image_url && file_exists(__DIR__ . '/../uploads/products/' . $image_url)) {
            unlink(__DIR__ . '/../uploads/products/' . $image_url);
        }
        $image_url = handle_image_upload($_FILES['image']);
    }

    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image_url = ? WHERE id = ?");
    if ($stmt->execute([$name, $description, $price, $stock, $image_url, $id])) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Produk berhasil diperbarui.'];
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Gagal memperbarui produk.'];
    }
    header('Location: products.php');
    exit();
}

if ($action == 'delete') {
    $id = $_GET['id'];

    // Delete image file first
    $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if ($product && $product['image_url'] && file_exists(__DIR__ . '/../uploads/products/' . $product['image_url'])) {
        unlink(__DIR__ . '/../uploads/products/' . $product['image_url']);
    }

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Produk berhasil dihapus.'];
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Gagal menghapus produk.'];
    }
    header('Location: products.php');
    exit();
}
?>
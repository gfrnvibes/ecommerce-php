<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

check_auth(true);

// Filtering
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$type = $_GET['type'] ?? 'all';
$product_id = $_GET['product_id'] ?? 'all';

$sql = "SELECT im.*, p.name as product_name 
        FROM inventory_movements im
        JOIN products p ON im.product_id = p.id";

$where_clauses = [];
$params = [];

if ($start_date) {
    $where_clauses[] = "DATE(im.created_at) >= ?";
    $params[] = $start_date;
}
if ($end_date) {
    $where_clauses[] = "DATE(im.created_at) <= ?";
    $params[] = $end_date;
}
if ($type !== 'all') {
    $where_clauses[] = "im.type = ?";
    $params[] = $type;
}
if ($product_id !== 'all') {
    $where_clauses[] = "im.product_id = ?";
    $params[] = $product_id;
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY im.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movements = $stmt->fetchAll();

// Fetch all products for filter dropdown
$stmt_products = $pdo->query("SELECT id, name FROM products ORDER BY name");
$products = $stmt_products->fetchAll();

require_once __DIR__ . '/../templates/header_admin.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manajemen Inventaris</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Inventaris</li>
    </ol>

    <div class="mb-4">
        <a href="inventory_in_form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Stok (Barang
            Masuk)</a>
        <a href="inventory_report.php" class="btn btn-success" target="_blank"><i class="fas fa-print"></i> Cetak
            Laporan</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter Riwayat Stok
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" id="start_date" name="start_date" class="form-control"
                        value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input type="date" id="end_date" name="end_date" class="form-control"
                        value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label">Tipe</label>
                    <select id="type" name="type" class="form-select">
                        <option value="all">Semua</option>
                        <option value="in" <?php echo ($type === 'in') ? 'selected' : ''; ?>>Barang Masuk</option>
                        <option value="out" <?php echo ($type === 'out') ? 'selected' : ''; ?>>Barang Keluar</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="product_id" class="form-label">Produk</label>
                    <select id="product_id" name="product_id" class="form-select">
                        <option value="all">Semua Produk</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($product_id == $p['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Riwayat Pergerakan Stok
        </div>
        <div class="card-body">
            <table id="datatablesSimple">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>ID Pesanan</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $movement): ?>
                        <tr>
                            <td><?php echo date('d M Y, H:i', strtotime($movement['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($movement['product_name']); ?></td>
                            <td>
                                <?php if ($movement['type'] == 'in'): ?>
                                    <span class="badge bg-success">Masuk</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Keluar</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $movement['quantity']; ?></td>
                            <td>
                                <?php if ($movement['order_id']): ?>
                                    <a
                                        href="order_detail_admin.php?id=<?php echo $movement['order_id']; ?>">#<?php echo $movement['order_id']; ?></a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($movement['notes'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer_admin.php';
?>
<?php
require_once __DIR__ . '/../config/config.php';

// Get parameters
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$type = $_GET['type'] ?? 'all';
$product_id = $_GET['product_id'] ?? 'all';

// Base query
$sql = "SELECT im.*, p.name as product_name 
        FROM inventory_movements im
        JOIN products p ON im.product_id = p.id";

$where_clauses = [];
$params = [];
$report_title = 'Laporan Pergerakan Stok';

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
    $report_title = $type == 'in' ? 'Laporan Barang Masuk' : 'Laporan Barang Keluar';
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

if ($product_id !== 'all') {
    $stmt_prod = $pdo->prepare("SELECT name FROM products WHERE id = ?");
    $stmt_prod->execute([$product_id]);
    $product_name = $stmt_prod->fetchColumn();
    $report_title .= ' - ' . $product_name;
}

require_once __DIR__ . '/../templates/header_admin.php';

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($report_title); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            width: 90%;
            margin: auto;
        }

        h1,
        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        @media print {

            .no-print,
            .navbar,
            .footer,
            .btn {
                display: none !important;
            }

            body {
                padding: 0;
                margin: 0;
            }

            .container {
                width: 100%;
                padding: 0;
                margin: 0;
            }
        }

        .print-button {
            display: block;
            width: 100px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
<form method="GET" class="no-print d-flex flex-wrap align-items-end gap-3 mb-3">
    <div>
        <label for="start_date" class="form-label">Dari Tanggal:</label>
        <input type="date" id="start_date" name="start_date" class="form-control"
            value="<?php echo htmlspecialchars($start_date); ?>">
    </div>
    <div>
        <label for="end_date" class="form-label">Sampai Tanggal:</label>
        <input type="date" id="end_date" name="end_date" class="form-control"
            value="<?php echo htmlspecialchars($end_date); ?>">
    </div>
    <div>
        <label for="type" class="form-label">Tipe:</label>
        <select name="type" id="type" class="form-select">
            <option value="all" <?php echo ($type === 'all') ? 'selected' : ''; ?>>Semua</option>
            <option value="in" <?php echo ($type === 'in') ? 'selected' : ''; ?>>Barang Masuk</option>
            <option value="out" <?php echo ($type === 'out') ? 'selected' : ''; ?>>Barang Keluar</option>
        </select>
    </div>
    <div>
        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
    </div>
</form>


        <h1><?php echo htmlspecialchars($report_title); ?></h1>
        <h2>Periode: <?php echo htmlspecialchars($start_date ?: 'Semua'); ?> s/d
            <?php echo htmlspecialchars($end_date ?: 'Semua'); ?>
        </h2>

        <table>
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
                <?php if (empty($movements)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">Tidak ada data untuk filter yang dipilih.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movements as $movement): ?>
                        <tr>
                            <td><?php echo date('d M Y, H:i', strtotime($movement['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($movement['product_name']); ?></td>
                            <td><?php echo $movement['type'] == 'in' ? 'Masuk' : 'Keluar'; ?></td>
                            <td><?php echo $movement['quantity']; ?></td>
                            <td><?php echo $movement['order_id'] ? '#' . $movement['order_id'] : '-'; ?></td>
                            <td><?php echo htmlspecialchars($movement['notes'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="javascript:window.print()" class="no-print print-button">Cetak</a>
    </div>
</body>

</html>
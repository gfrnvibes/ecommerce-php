<?php
require_once __DIR__ . '/../config/config.php';

// Get parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$report_type = $_GET['report_type'] ?? 'all';

// Base query
$sql = "SELECT o.*, u.name as customer_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE DATE(o.created_at) BETWEEN ? AND ?";

$params = [$start_date, $end_date];

// Filter by report type
if ($report_type !== 'all') {
    $sql .= " AND o.order_type = ?";
    $params[] = $report_type;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Calculate total revenue
$total_revenue = 0;
foreach ($orders as $order) {
    $total_revenue += $order['total_amount'];
}

$report_title = 'Laporan Penjualan';
if ($report_type === 'online') {
    $report_title = 'Laporan Pesanan Online';
} elseif ($report_type === 'pos') {
    $report_title = 'Laporan Penjualan POS';
}

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
        h1, h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tfoot th, tfoot td {
            font-weight: bold;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none;
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
        <h1><?php echo htmlspecialchars($report_title); ?></h1>
        <h2>Periode: <?php echo htmlspecialchars($start_date); ?> s/d <?php echo htmlspecialchars($end_date); ?></h2>

        <table>
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
 <th>Jenis</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">Tidak ada data untuk periode dan jenis laporan ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                            <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $order['status']))); ?></td>
                            <td><?php echo htmlspecialchars(strtoupper($order['order_type'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" style="text-align:right;">Total Pendapatan</th>
                    <td colspan="3">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>

        <a href="javascript:window.print()" class="no-print print-button">Cetak</a>
    </div>
</body>
</html>
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

// Check if user is logged in and is admin
check_auth(true);

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) FROM users WHERE role = 'customer'";
$sql = "SELECT * FROM users WHERE role = 'customer'";

if ($search) {
    $sql_count .= " AND (username LIKE ? OR email LIKE ?)";
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
}

$stmt_count = $pdo->prepare($sql_count);
if (!$stmt_count) {
    die('Prepare failed for count query: ' . implode(' - ', $pdo->errorInfo()));
}
$stmt = $pdo->prepare($sql);
if (!$stmt) {
    die('Prepare failed for main query: ' . implode(' - ', $pdo->errorInfo()));
}

if ($search) {
    $search_param = '%' . $search . '%';
    $stmt_count->bindParam(1, $search_param, PDO::PARAM_STR);
    $stmt_count->bindParam(2, $search_param, PDO::PARAM_STR);
    $stmt->bindParam(1, $search_param, PDO::PARAM_STR);
    $stmt->bindParam(2, $search_param, PDO::PARAM_STR);
}

$stmt_count->execute();
$total_customers = $stmt_count->fetchColumn();
$stmt_count->closeCursor();

$total_pages = ceil($total_customers / $limit);

$sql .= " LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);

if ($search) {
    $stmt->bindParam(1, $search_param, PDO::PARAM_STR);
    $stmt->bindParam(2, $search_param, PDO::PARAM_STR);
    $stmt->bindParam(3, $limit, PDO::PARAM_INT);
    $stmt->bindParam(4, $offset, PDO::PARAM_INT);
} else {
    $stmt->bindParam(1, $limit, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
}
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();
$pdo = null;

require_once __DIR__ . '/../templates/header_admin.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Daftar Pelanggan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Customers</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-1"></i>
            Customers
        </div>
        <div class="card-body">
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0 mb-3"
                action="customers.php" method="GET">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Search for customers..."
                        aria-label="Search for customers..." aria-describedby="btnNavbarSearch" name="search"
                        value="<?php echo htmlspecialchars($search); ?>" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="submit"><i
                            class="fas fa-search"></i></button>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Tanggal Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['id']); ?></td>
                                <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo htmlspecialchars($customer['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No customers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link"
                                href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link"
                                href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link"
                                href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer_admin.php'; ?>
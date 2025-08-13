<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/functions.php';

// Verify permissions
if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
    header("Location: ../dashboard/");
    exit();
}

$title = "Archived Purchases";

// Pagination configuration
$per_page = 20;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE pa.reference_no LIKE ? OR pa.supplier_id IN (SELECT id FROM suppliers WHERE name LIKE ?)";
    $params = ["%$search%", "%$search%"];
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) FROM purchases_archive pa $where";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_items = $stmt->fetchColumn();
$total_pages = max(1, ceil($total_items / $per_page));

// Ensure current page is within valid range
$current_page = min($current_page, $total_pages);

// Get archived purchases with pagination
$sql = "
    SELECT pa.*, u.username as archived_by_name 
    FROM purchases_archive pa
    LEFT JOIN users u ON pa.archived_by = u.id
    $where
    ORDER BY pa.archived_at DESC
    LIMIT $offset, $per_page
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$archived_purchases = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Archived Purchases</h3>
        <div class="card-tools">
            <form method="GET" class="input-group" style="width: 300px;">
                <input type="text" name="search" class="form-control" placeholder="Search archives..." 
                       value="<?= htmlspecialchars($search) ?>" style="margin-top: 20px;">
                <input type="hidden" name="page" value="1">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary" style="margin-left: -45px; margin-top: -33px; position: absolute;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Date Ordered</th>
                        <th>Date Received</th>
                        <th>Archived By</th>
                        <th>Archived At</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($archived_purchases)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No archived purchases found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($archived_purchases as $purchase): ?>
                        <tr>
                            <td><?= htmlspecialchars($purchase['reference_no']) ?></td>
                            <td><?= getSupplierName($purchase['supplier_id']) ?></td>
                            <td><?= date('M d, Y', strtotime($purchase['date_ordered'])) ?></td>
                            <td><?= date('M d, Y', strtotime($purchase['date_received'])) ?></td>
                            <td><?= htmlspecialchars($purchase['archived_by_name']) ?></td>
                            <td><?= date('M d, Y H:i', strtotime($purchase['archived_at'])) ?></td>
                            <td><?= htmlspecialchars($purchase['archive_reason']) ?></td>
                            <td>
                                <a href="archive_detail.php?id=<?= $purchase['id_original'] ?>" 
                                   class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer clearfix">
        <div class="float-left">
            Showing <?= ($offset + 1) ?> to <?= min($offset + $per_page, $total_items) ?> of <?= $total_items ?> entries
        </div>
        <?php 
        // Generate pagination links
        $pagination_url = "archive_view.php?";
        if (!empty($search)) {
            $pagination_url .= "search=" . urlencode($search) . "&";
        }
        echo pagination($current_page, $total_pages, $pagination_url);
        ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
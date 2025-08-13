<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Check if user has permission to view reports
if ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'manager') {
    header("Location: ../dashboard/");
    exit();
}

$title = "Purchase Reports";

// Default filter values
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$supplier_id = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Get filter lists
$suppliers = getSuppliers();

// Build query with filters
$query = "SELECT p.*, s.name as supplier_name 
          FROM purchases p
          LEFT JOIN suppliers s ON p.supplier_id = s.id
          WHERE p.date_ordered BETWEEN :start_date AND :end_date";

$params = [
    'start_date' => $start_date . ' 00:00:00',
    'end_date' => $end_date . ' 23:59:59'
];

if (!empty($supplier_id)) {
    $query .= " AND p.supplier_id = :supplier_id";
    $params['supplier_id'] = $supplier_id;
}

if (!empty($status) && in_array($status, ['pending', 'received', 'cancelled'])) {
    $query .= " AND p.status = :status";
    $params['status'] = $status;
}

$query .= " ORDER BY p.date_ordered DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$purchases = $stmt->fetchAll();

// Calculate totals
$total_amount = 0;
$total_items = 0;
foreach ($purchases as $purchase) {
    $total_amount += $purchase['total_amount'];
    $total_items++;
}


include '../../includes/header.php';
?>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            box-shadow: none;
        }
        .no-print, .no-print * {
            display: none !important;
        }
        .card-header, .card-footer {
            border: none;
        }
        .table {
            font-size: 12px;
        }
        .badge {
            border: 1px solid #000;
            color: #000;
            background-color: transparent !important;
        }
        .badge-warning {
            border-color: #ffc107;
        }
        .badge-success {
            border-color: #28a745;
        }
        .badge-danger {
            border-color: #dc3545;
        }
    }
</style>

<div class="card">
    <div class="card-header no-print">
        <h3 class="card-title">Purchase Reports</h3>
        <div class="card-tools">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-4 no-print">
             <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="supplier_id">Supplier</label>
                        <select class="form-control" id="supplier_id" name="supplier_id">
                            <option value="">All Suppliers</option>
                            <?php foreach ($suppliers as $supplier) : ?>
                                <option value="<?php echo $supplier['id']; ?>" <?php echo $supplier_id == $supplier['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($supplier['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="received" <?php echo $status == 'received' ? 'selected' : ''; ?>>Received</option>
                            <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="purchases.php" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="alert alert-info no-print">
            <strong>Report Summary:</strong> 
            <?php echo $total_items; ?> purchases totaling ₱<?php echo number_format($total_amount, 2); ?>
            from <?php echo date('M j, Y', strtotime($start_date)); ?> to <?php echo date('M j, Y', strtotime($end_date)); ?>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>PO #</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($purchases)) : ?>
                        <tr>
                            <td colspan="6" class="text-center">No purchases found for the selected filters</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($purchases as $purchase) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($purchase['reference_no']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($purchase['date_ordered'])); ?></td>
                            <td><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                            <td class="text-right">₱ <?php echo number_format($purchase['total_amount'], 2); ?></td>
                            <td>
                                <?php if ($purchase['status'] == 'pending') : ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php elseif ($purchase['status'] == 'received') : ?>
                                    <span class="badge badge-success">Received</span>
                                <?php else : ?>
                                    <span class="badge badge-danger">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td class="no-print">
                                <a href="../purchases/view.php?id=<?php echo $purchase['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th class="text-right">₱ <?php echo number_format($total_amount, 2); ?></th>
                        <th colspan="2" class="no-print"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
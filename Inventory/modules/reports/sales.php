<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Check if user has permission to view reports
if ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'manager') {
    header("Location: ../dashboard/");
    exit();
}

$title = "Sales Reports";

// Default filter values
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$payment_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';

// Build query with filters
$query = "SELECT * FROM sales 
          WHERE created_at BETWEEN :start_date AND :end_date";

$params = [
    'start_date' => $start_date . ' 00:00:00',
    'end_date' => $end_date . ' 23:59:59'
];

// Add payment method filter if selected
if (!empty($payment_method) && in_array($payment_method, ['cash', 'card', 'transfer'])) {
    $query .= " AND payment_method = :payment_method";
    $params['payment_method'] = $payment_method;
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sales = $stmt->fetchAll();

// Calculate totals
$total_amount = 0;
$total_sales = 0;
$total_items = 0;
$method_totals = ['cash' => 0, 'card' => 0, 'transfer' => 0];

foreach ($sales as $sale) {
    $total_amount += $sale['total_amount'];
    $total_sales++;
    
    // Track totals by payment method
    if (array_key_exists($sale['payment_method'], $method_totals)) {
        $method_totals[$sale['payment_method']] += $sale['total_amount'];
    }
    
    // Get item count for this sale
    $stmt = $pdo->prepare("SELECT SUM(quantity) as item_count FROM sale_items WHERE sale_id = ?");
    $stmt->execute([$sale['id']]);
    $result = $stmt->fetch();
    $total_items += $result['item_count'] ?? 0;
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
            font-size: 11px;
        }
        .badge {
            border: 1px solid #000;
            color: #000;
            background-color: transparent !important;
            padding: 3px 6px;
        }
        .badge-success {
            border-color: #28a745;
        }
        .badge-primary {
            border-color: #007bff;
        }
        .badge-info {
            border-color: #17a2b8;
        }
        .dashboard-card, .payment-method-cards {
            display: none !important;
        }
        .print-summary {
            display: block !important;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
    }
    
    .print-summary {
        display: none;
    }
</style>

<div class="card">
    <div class="card-header no-print">
        <h3 class="card-title">Sales Reports</h3>
        <div class="card-tools">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
            <button class="btn btn-success" id="exportBtn">
                <i class="fas fa-file-excel"></i> Export to Excel
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-4 no-print">
                        <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?= htmlspecialchars($start_date) ?>" max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?= htmlspecialchars($end_date) ?>" max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="">All Methods</option>
                            <option value="cash" <?= $payment_method == 'cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="card" <?= $payment_method == 'card' ? 'selected' : '' ?>>Card</option>
                            <option value="transfer" <?= $payment_method == 'transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="sales.php" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="alert alert-info no-print">
            <strong>Report Summary:</strong> 
            <?= $total_sales ?> sales totaling ₱ <?= number_format($total_amount, 2) ?>
            (<?= $total_items ?> items sold) from <?= date('M j, Y', strtotime($start_date)) ?> to <?= date('M j, Y', strtotime($end_date)) ?>
            
            <?php if (!empty($payment_method)): ?>
                <br><strong>Filtered by:</strong> <?= ucfirst($payment_method) ?> payments only
            <?php endif; ?>
        </div>

        <!-- Print-only summary -->
        <div class="print-summary">
            <h4>Sales Report Summary</h4>
            <p><strong>Period:</strong> <?= date('M j, Y', strtotime($start_date)) ?> to <?= date('M j, Y', strtotime($end_date)) ?></p>
            <p><strong>Total Sales:</strong> ₱<?= number_format($total_amount, 2) ?></p>
            <p><strong>Transactions:</strong> <?= $total_sales ?></p>
            <p><strong>Items Sold:</strong> <?= $total_items ?></p>
            <?php if (!empty($payment_method)): ?>
                <p><strong>Payment Method:</strong> <?= ucfirst($payment_method) ?> only</p>
            <?php else: ?>
                <p><strong>Payment Methods:</strong> 
                    Cash (₱<?= number_format($method_totals['cash'], 2) ?>), 
                    Card (₱<?= number_format($method_totals['card'], 2) ?>), 
                    Transfer (₱<?= number_format($method_totals['transfer'], 2) ?>)
                </p>
            <?php endif; ?>
            <p><strong>Report Generated:</strong> <?= date('M j, Y H:i') ?></p>
        </div>

        <div class="row mb-4 no-print">
            <div class="col-md-3">
                <div class="dashboard-card primary">
                    <i class="fas fa-cash-register"></i>
                    <div class="value">₱ <?= number_format($total_amount) ?></div>
                    <div class="label">Total Sales</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card success">
                    <i class="fas fa-shopping-basket"></i>
                    <div class="value"><?= $total_sales ?></div>
                    <div class="label">Transactions</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card warning">
                    <i class="fas fa-boxes"></i>
                    <div class="value"><?= $total_items ?></div>
                    <div class="label">Items Sold</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card info">
                    <i class="fas fa-money-bill-wave"></i>
                    <div class="value"><?= !empty($payment_method) ? ucfirst($payment_method) : 'All' ?></div>
                    <div class="label">Payment Method</div>
                </div>
            </div>
        </div>

        <?php if (empty($payment_method)): ?>
        <div class="row mb-4 no-print">
             <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title">Cash Payments</h3>
                    </div>
                    <div class="card-body text-center">
                        <h2>₱ <?= number_format($method_totals['cash']) ?></h2><br>
                        <a href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&payment_method=cash" 
                           class="btn btn-sm btn-success">
                           View Cash Sales
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Card Payments</h3>
                    </div>
                    <div class="card-body text-center">
                        <h2>₱ <?= number_format($method_totals['card']) ?></h2><br>
                        <a href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&payment_method=card" 
                           class="btn btn-sm btn-primary">
                           View Card Sales
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">Bank Transfers</h3>
                    </div><br>
                    <div class="card-body text-center">
                        <h2>₱ <?= number_format($method_totals['transfer']) ?></h2><br>
                        <a href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&payment_method=transfer" 
                           class="btn btn-sm btn-info">
                           View Transfers
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped" id="salesTable">
                <thead class="thead-dark">
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th class="text-right">Amount</th>
                        <th>Payment Method</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sales)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No sales found for the selected filters</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?= htmlspecialchars($sale['invoice_no']) ?></td>
                            <td><?= date('M j, Y H:i', strtotime($sale['created_at'])) ?></td>
                            <td><?= $sale['customer_name'] ? htmlspecialchars($sale['customer_name']) : 'Walk-in' ?></td>
                            <td class="text-right">₱ <?= number_format($sale['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge 
                                    <?= $sale['payment_method'] == 'cash' ? 'badge-success' : 
                                       ($sale['payment_method'] == 'card' ? 'badge-primary' : 'badge-info') ?>">
                                    <?= ucfirst($sale['payment_method']) ?>
                                </span>
                            </td>
                            <td class="no-print">
                                <a href="../sales/view.php?id=<?= $sale['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="../sales/receipt.php?id=<?= $sale['id'] ?>" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-receipt"></i> Receipt
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="font-weight-bold">
                    <tr>
                        <td colspan="3">Total</td>
                        <td class="text-right">₱ <?= number_format($total_amount, 2) ?></td>
                        <td colspan="2" class="no-print"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Export to Excel functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
        // Get table data
        const table = document.getElementById('salesTable');
        const rows = table.querySelectorAll('tr');
        let csv = [];
        
        // Add headers
        const headers = [];
        table.querySelectorAll('thead th').forEach(header => {
            headers.push(header.textContent.trim());
        });
        csv.push(headers.join(','));
        
        // Add data rows
        table.querySelectorAll('tbody tr').forEach(row => {
            const rowData = [];
            row.querySelectorAll('td').forEach(cell => {
                // Remove action buttons from export
                if (!cell.querySelector('a')) {
                    // Clean up badge formatting
                    let text = cell.textContent.trim();
                    text = text.replace(/\$/g, '')
                              .replace(/₱/g, '')
                              .replace(/Cash|Card|Transfer/, function(match) {
                                  return match.toUpperCase();
                              });
                    rowData.push(text);
                }
            });
            csv.push(rowData.join(','));
        });
        
        // Add totals row
        const totals = [];
        table.querySelectorAll('tfoot td').forEach(cell => {
            let text = cell.textContent.trim();
            text = text.replace(/₱/g, '');
            totals.push(text);
        });
        csv.push(totals.join(','));
        
        // Create download link
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', 'sales_report_<?= date('Y-m-d') ?>_<?= $payment_method ? $payment_method : 'all' ?>.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Date validation
    document.getElementById('start_date').addEventListener('change', function() {
        const endDate = document.getElementById('end_date');
        if (this.value > endDate.value) {
            endDate.value = this.value;
        }
    });

    document.getElementById('end_date').addEventListener('change', function() {
        const startDate = document.getElementById('start_date');
        if (this.value < startDate.value) {
            startDate.value = this.value;
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
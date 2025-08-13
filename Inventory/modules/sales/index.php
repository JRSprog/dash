<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$title = "Manage Sales";
$sales = getSales();

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Sales Transactions</h3>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Sale
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table" id="search">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['invoice_no']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($sale['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($sale['customer_name'] ?: 'Walk-in'); ?></td>
                        <td>₱ <?php echo number_format($sale['total_amount']); ?></td>
                        <td><?php echo ucfirst($sale['payment_method']); ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $sale['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="receipt.php?id=<?php echo $sale['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-receipt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../../assets/js/form-validate.js"></script>
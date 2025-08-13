<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Validate sale ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?error=Invalid sale ID");
    exit();
}

$sale_id = (int)$_GET['id'];
$sale = getSaleById($sale_id);

// Check if sale exists
if (!$sale) {
    header("Location: index.php?error=Sale not found");
    exit();
}

$title = "Sale Invoice #" . htmlspecialchars($sale['invoice_no'] ?? '');

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Sale Details</h3>
        <div class="card-tools">
            <a href="receipt.php?id=<?= $sale_id ?>" class="btn btn-info" target="_blank">
                <i class="fas fa-receipt"></i> Print Receipt
            </a>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Sales
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h4>Invoice Information</h4>
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Invoice #</th>
                        <td><?= htmlspecialchars($sale['invoice_no'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td><?= date('M d, Y H:i', strtotime($sale['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <th>Processed By</th>
                        <td><?= htmlspecialchars($sale['created_by_name'] ?? $sale['created_by_username'] ?? 'System') ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h4>Customer Information</h4>
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Customer</th>
                        <td>
                            <?php if (!empty($sale['customer_name'])): ?>
                                <?= htmlspecialchars($sale['customer_name']) ?>
                                <?php if (!empty($sale['customer_email'])): ?>
                                    <br><small><?= htmlspecialchars($sale['customer_email']) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                Walk-in Customer
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Payment Method</th>
                        <td><?= ucfirst($sale['payment_method'] ?? 'cash') ?></td>
                    </tr>
                    <tr>
                        <th>Payment Status</th>
                        <td>
                            <?php if (($sale['amount_paid'] ?? 0) >= ($sale['total_amount'] ?? 0)): ?>
                                <span class="badge badge-success">Paid in Full</span>
                            <?php else: ?>
                                <span class="badge badge-warning">
                                    Partial Payment (Due: ₱ <?= number_format(($sale['total_amount'] ?? 0) - ($sale['amount_paid'] ?? 0)) ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <h4 class="mb-3">Items Sold</h4>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sale['items'] as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                            <?= htmlspecialchars($item['product_name']) ?>
                        </td>
                        <td><?= htmlspecialchars($item['product_sku']) ?></td>
                        <td class="text-right">₱ <?= number_format($item['unit_price']) ?></td>
                        <td class="text-center"><?= $item['quantity'] ?></td>
                        <td class="text-right">₱ <?= number_format($item['total_price']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right">Subtotal:</th>
                        <th class="text-right">₱ <?= number_format($sale['total_amount']) ?></th>
                    </tr>
                    <?php if (($sale['discount'] ?? 0) > 0): ?>
                    <tr>
                        <th colspan="5" class="text-right">Discount:</th>
                        <th class="text-right">-₱ <?= number_format($sale['discount']) ?></th>
                    </tr>
                    <?php endif; ?>
                    <?php if (($sale['tax_amount'] ?? 0) > 0): ?>
                    <tr>
                        <th colspan="5" class="text-right">Tax:</th>
                        <th class="text-right">₱ <?= number_format($sale['tax_amount']) ?></th>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th colspan="5" class="text-right">Grand Total:</th>
                        <th class="text-right">₱ <?= number_format(($sale['total_amount'] - ($sale['discount'] ?? 0) + ($sale['tax_amount'] ?? 0))) ?></th>
                    </tr>
                    <tr>
                        <th colspan="5" class="text-right">Amount Paid:</th>
                        <th class="text-right">₱ <?= number_format($sale['amount_paid']) ?></th>
                    </tr>
                    <tr>
                        <th colspan="5" class="text-right">Change Due:</th>
                        <th class="text-right">
                            ₱ <?= number_format(max(0, $sale['amount_paid'] - ($sale['total_amount'] - ($sale['discount'] ?? 0) + ($sale['tax_amount'] ?? 0)))) ?>
                        </th>
                    </tr>
                    <?php if (isset($sale['profit'])): ?>
                    <tr>
                        <th colspan="5" class="text-right">Estimated Profit:</th>
                        <th class="text-right">₱ <?= number_format($sale['profit']) ?></th>
                    </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <?php if ($sale['amount_paid'] < $sale['total_amount']): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-circle"></i> Outstanding Balance</h5>
                        <p class="mb-0">
                            Balance Due: $<?= number_format($sale['total_amount'] - $sale['amount_paid'], 2) ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
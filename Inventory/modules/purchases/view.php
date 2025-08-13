<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

if(isset($_GET['debug'])) {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$purchase_id = intval($_GET['id']);
$purchase = getPurchaseById($purchase_id);

if (!$purchase) {
    header("Location: index.php");
    exit();
}

// Get purchase items
$stmt = $pdo->prepare("
    SELECT pi.*, p.name as product_name, p.sku as product_sku, 
           p.quantity as current_stock, p.image_path
    FROM purchase_items pi
    JOIN products p ON pi.product_id = p.id
    WHERE pi.purchase_id = ?
");
$stmt->execute([$purchase_id]);
$purchase_items = $stmt->fetchAll();

// Get supplier info
$supplier = getSupplierById($purchase['supplier_id']);

$title = "Purchase Order #" . htmlspecialchars($purchase['reference_no']);

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">'.htmlspecialchars($_SESSION['error']).'</div>';
    unset($_SESSION['error']);
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Purchase Order Details</h3>
        <div class="card-tools">
            <?php if ($purchase['status'] == 'pending' && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'manager')) : ?>
                <button class="btn btn-success" data-toggle="modal" data-target="#receiveModal">
                    <i class="fas fa-check-circle"></i> Mark as Received
                </button>
            <?php endif; ?>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Purchases
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h4>Order Information</h4>
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">PO Number</th>
                        <td><?php echo htmlspecialchars($purchase['reference_no']); ?></td>
                    </tr>
                    <tr>
                        <th>Order Date</th>
                        <td><?php echo date('M d, Y', strtotime($purchase['date_ordered'])); ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <?php if ($purchase['status'] == 'pending') : ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php elseif ($purchase['status'] == 'received') : ?>
                                <span class="badge badge-success">Received</span>
                            <?php else : ?>
                                <span class="badge badge-danger">Cancelled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Processed By</th>
                        <td><?php echo htmlspecialchars(getUserName($purchase['created_by'] ?? 0)); ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h4>Supplier Information</h4>
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Supplier</th>
                        <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                    </tr>
                    <tr>
                        <th>Contact Person</th>
                        <td><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <h4 class="mb-3">Order Items</h4>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-center">Qty Ordered</th>
                        <th class="text-right">Total</th>
                        <th>Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchase_items as $index => $item) : ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($item['product_sku']); ?></td>
                        <td class="text-right">₱ <?php echo number_format($item['unit_price']); ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-right">₱ <?php echo number_format($item['total_price']); ?></td>
                        <td class="text-center">
                            <?php echo $item['current_stock']; ?>
                            <?php if ($item['current_stock'] <= 0) : ?>
                                <span class="badge badge-danger ml-2">Out of Stock</span>
                            <?php elseif ($item['current_stock'] < 5) : ?>
                                <span class="badge badge-warning ml-2">Low Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right">Subtotal:</th>
                        <th class="text-right">₱ <?php echo number_format($purchase['total_amount']); ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <?php if ($purchase['status'] == 'received') : ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Order Received</h5>
                        <p class="mb-0">
                            Received on: <?php echo date('M d, Y', strtotime($purchase['date_received'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Receive Order Modal -->
<!-- Receive Order Modal -->
<div class="modal fade" id="receiveModal" tabindex="-1" role="dialog" aria-labelledby="receiveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiveModalLabel">Purchase Order Actions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Receive Form -->
                <form action="receive.php" method="POST" id="receiveForm" onsubmit="return confirm('Are you sure you want to mark this order as received?')">
                    <input type="hidden" name="purchase_id" value="<?= $purchase_id ?>">
                    <div class="form-group">
                        <label for="receive_date">Receive Date</label>
                        <input type="date" class="form-control" id="receive_date" name="receive_date" 
                            value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="submit_receive" id="submitReceive" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Confirm Receipt
                        </button>
                    </div>
                </form><br>
                
                <!-- Cancel Form -->
                <form action="cancel.php" method="POST" id="cancelForm" class="mt-3" onsubmit="return confirm('Are you sure you want to Cancel this Order?')">
                    <input type="hidden" name="purchase_id" value="<?= $purchase_id ?>">
                    <div class="modal-footer">
                        <button type="submit" name="submit_cancel" id="submitCancel" class="btn btn-danger">
                            <i class="fa-solid fa-circle-xmark"></i> Cancel Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<?php include '../../includes/footer.php'; ?>
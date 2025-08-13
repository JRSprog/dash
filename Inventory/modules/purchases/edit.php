<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Check if purchase ID is provided and user has permission
if (!isset($_GET['id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header("Location: index.php");
    exit();
}

$purchase_id = $_GET['id'];
$title = "Edit Purchase Order";

// Fetch the purchase data
$purchase = getPurchaseById($purchase_id);
if (!$purchase || $purchase['status'] != 'pending') {
    header("Location: index.php?error=Purchase order not found or not editable");
    exit();
}

// Fetch all suppliers for dropdown
$suppliers = getSuppliers();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_id = $_POST['supplier_id'] ?: null;
    $reference_no = trim($_POST['reference_no']);
    $date_ordered = trim($_POST['date_ordered']);
    $items = $_POST['items'] ?? [];
    
    // Only update date_received if status is 'received'
    $date_received = ($status == 'received') ? date('Y-m-d') : null;
    
    try {
        $pdo->beginTransaction();
        
        // Update purchase header
        $stmt = $pdo->prepare("UPDATE purchases SET 
            supplier_id = ?, 
            reference_no = ?, 
            date_ordered = ?,
            date_received = ?
            WHERE id = ?");
        
        $stmt->execute([
            $supplier_id, $reference_no, $date_ordered, 
            $date_received, $purchase_id
        ]);
        
        // Update purchase items
        foreach ($items as $product_id => $quantity) {
            // Get current item data
            $item = getPurchaseItem($purchase_id, $product_id);
            if ($item) {
                // Update existing item
                $stmt = $pdo->prepare("UPDATE purchase_items SET 
                    quantity = ?,
                    total_price = quantity * unit_price
                    WHERE purchase_id = ? AND product_id = ?");
                $stmt->execute([$quantity, $purchase_id, $product_id]);
            }
        }
        
        $pdo->commit();
        
        header("Location: index.php?success=Purchase order updated successfully");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Database error: " . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Purchase Order: <?php echo htmlspecialchars($purchase['reference_no']); ?></h3>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Purchases
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reference_no">PO Reference Number *</label>
                        <input type="text" class="form-control" id="reference_no" name="reference_no" 
                            value="<?php echo htmlspecialchars($purchase['reference_no']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="supplier_id">Supplier *</label>
                        <select class="form-control" id="supplier_id" name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $supplier) : ?>
                                <option value="<?php echo $supplier['id']; ?>"
                                    <?php if ($supplier['id'] == $purchase['supplier_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($supplier['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_ordered">Date Ordered *</label>
                        <input type="date" class="form-control" id="date_ordered" name="date_ordered" 
                            value="<?php echo htmlspecialchars($purchase['date_ordered']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select class="form-control" id="status" name="status" disabled>
                            <option value="pending" <?php if ($purchase['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="received" <?php if ($purchase['status'] == 'received') echo 'selected'; ?>>Received</option>
                            <option value="cancelled" <?php if ($purchase['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <h4>Purchase Items</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $items = getPurchaseItems($purchase_id);
                            foreach ($items as $item) : 
                                $product = getProductById($item['product_id']);
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>
                                        <input type="number" 
                                            class="form-control" 
                                            name="items[<?php echo $item['product_id']; ?>]" 
                                            value="<?php echo $item['quantity']; ?>" 
                                            min="1" 
                                            required>
                                    </td>
                                    <td>₱ <?php echo number_format($item['unit_price']); ?></td>
                                    <td>₱ <?php echo number_format($item['quantity'] * $item['unit_price']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Purchase Order
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
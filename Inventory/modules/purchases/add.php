<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Only admin and manager can add purchases
if ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'manager') {
    header("Location: ../dashboard/");
    exit();
}

$title = "Create Purchase Order";
$suppliers = getSuppliers();
$products = getProducts();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_id = intval($_POST['supplier_id']);
    $reference_no = trim($_POST['reference_no']);
    $date_ordered = $_POST['date_ordered'];
    $items = $_POST['items'];
    
    // Calculate total amount
    $total_amount = 0;
    foreach ($items as $item) {
        $total_amount += floatval($item['unit_price']) * intval($item['quantity']);
    }
    
    try {
        $pdo->beginTransaction();
        
        // Insert purchase
        $stmt = $pdo->prepare("INSERT INTO purchases (supplier_id, reference_no, total_amount, date_ordered, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$supplier_id, $reference_no, $total_amount, $date_ordered, $_SESSION['user_id']]);
        $purchase_id = $pdo->lastInsertId();
        
        // Insert purchase items
        foreach ($items as $item) {
            $product_id = intval($item['product_id']);
            $quantity = intval($item['quantity']);
            $unit_price = floatval($item['unit_price']);
            $total_price = $unit_price * $quantity;
            
            $stmt = $pdo->prepare("INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$purchase_id, $product_id, $quantity, $unit_price, $total_price]);
        }
        
        $pdo->commit();
        
        header("Location: index.php?success=" . urlencode("Purchase order created successfully"));
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
        <h3 class="card-title">Create Purchase Order</h3>
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
        
        <form id="purchaseForm" method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reference_no">PO Number</label>
                        <input type="text" class="form-control" id="reference_no" name="reference_no" value="PO-<?php echo date('Ymd') . '-' . strtoupper(uniqid()); ?>" required readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="supplier_id">Supplier</label>
                        <select class="form-control" id="supplier_id" name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $supplier) : ?>
                                <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_ordered">Order Date</label>
                        <input type="date" class="form-control" id="date_ordered" name="date_ordered" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
            </div>
            
            <h4 class="mt-4 mb-3">Items</h4>
            <div id="itemsContainer">
                <div class="item-row row mb-3">
                    <div class="col-md-5">
                        <select class="form-control product-select" name="items[0][product_id]" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $product) : ?>
                                <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['cost_price']; ?>">
                                    <?php echo htmlspecialchars($product['name'] . ' (' . $product['sku'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control quantity" name="items[0][quantity]" min="1" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" class="form-control unit-price" name="items[0][unit_price]" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-item" disabled>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <button type="button" id="addItem" class="btn btn-secondary">
                <i class="fas fa-plus"></i> Add Item
            </button>
            
            <div class="row mt-4">
                <div class="col-md-6 offset-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label font-weight-bold">Total Amount:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext total-amount" value="₱ 0.00" readonly>
                            <input type="hidden" id="total_amount" name="total_amount" value="0">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Purchase Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 1;
    
    // Add new item row
    document.getElementById('addItem').addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'item-row row mb-3';
        newRow.innerHTML = `
            <div class="col-md-5">
                <select class="form-control product-select" name="items[${itemCount}][product_id]" required>
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product) : ?>
                        <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['cost_price']; ?>">
                            <?php echo htmlspecialchars($product['name'] . ' (' . $product['sku'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control quantity" name="items[${itemCount}][quantity]" min="1" value="1" required>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" class="form-control unit-price" name="items[${itemCount}][unit_price]" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-item">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        document.getElementById('itemsContainer').appendChild(newRow);
        itemCount++;
        
        // Enable remove buttons for all but first row
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.disabled = false;
        });
        document.querySelectorAll('.remove-item')[0].disabled = true;
        
        // Add event listeners to new row
        addProductSelectListener(newRow.querySelector('.product-select'));
        addQuantityListener(newRow.querySelector('.quantity'));
        addUnitPriceListener(newRow.querySelector('.unit-price'));
        addRemoveItemListener(newRow.querySelector('.remove-item'));
    });
    
    // Add event listeners to initial row
    addProductSelectListener(document.querySelector('.product-select'));
    addQuantityListener(document.querySelector('.quantity'));
    addUnitPriceListener(document.querySelector('.unit-price'));
    addRemoveItemListener(document.querySelector('.remove-item'));
    
    function addProductSelectListener(select) {
        select.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const unitPrice = this.closest('.item-row').querySelector('.unit-price');
                unitPrice.value = selectedOption.getAttribute('data-price');
                calculateTotal();
            }
        });
    }
    
    function addQuantityListener(input) {
        input.addEventListener('input', calculateTotal);
    }
    
    function addUnitPriceListener(input) {
        input.addEventListener('input', calculateTotal);
    }
    
    function addRemoveItemListener(button) {
        button.addEventListener('click', function() {
            this.closest('.item-row').remove();
            calculateTotal();
            
            // Disable remove button if only one row left
            if (document.querySelectorAll('.item-row').length === 1) {
                document.querySelector('.remove-item').disabled = true;
            }
        });
    }
    
    function calculateTotal() {
        let total = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
            total += quantity * unitPrice;
        });
        
        document.querySelector('.total-amount').value = '$' + total.toFixed(2);
        document.getElementById('total_amount').value = total;
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
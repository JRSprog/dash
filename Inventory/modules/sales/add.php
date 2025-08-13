<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$title = "Create Sale";
$products = getProducts();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $payment_method = $_POST['payment_method'];
    $amount_paid = floatval($_POST['amount_paid']);
    $items = $_POST['items'];
    
    // Calculate total amount
    $total_amount = 0;
    foreach ($items as $item) {
        $total_amount += floatval($item['unit_price']) * intval($item['quantity']);
    }
    
    // Generate invoice number
    $invoice_no = 'INV-' . date('Ymd') . '-' . strtoupper(uniqid());
    
    try {
        $pdo->beginTransaction();
        
        // Insert sale
        $stmt = $pdo->prepare("INSERT INTO sales (invoice_no, customer_name, total_amount, amount_paid, payment_method, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$invoice_no, $customer_name, $total_amount, $amount_paid, $payment_method, $_SESSION['user_id']]);
        $sale_id = $pdo->lastInsertId();
        
        // Insert sale items and update product quantities
        foreach ($items as $item) {
            $product_id = intval($item['product_id']);
            $quantity = intval($item['quantity']);
            $unit_price = floatval($item['unit_price']);
            $total_price = $unit_price * $quantity;
            
            // Insert sale item
            $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$sale_id, $product_id, $quantity, $unit_price, $total_price]);
            
            // Update product quantity
            $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->execute([$quantity, $product_id]);
        }
        
        $pdo->commit();
        
        header("Location: receipt.php?id=" . $sale_id . "&print=true");
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
        <h3 class="card-title">Create Sale</h3>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Sales
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form id="saleForm" method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="customer_name">Customer Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Walk-in customer (leave blank for anonymous)">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="transfer">Bank Transfer</option>
                        </select>
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
                                <?php if ($product['quantity'] > 0) : ?>
                                    <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['selling_price']; ?>" data-quantity="<?php echo $product['quantity']; ?>">
                                        <?php echo htmlspecialchars($product['name'] . ' (' . $product['sku'] . ') - ' . $product['quantity'] . ' in stock'); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control quantity" name="items[0][quantity]" min="1" value="1" required>
                        <small class="text-muted available-quantity">Max: 0</small>
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
                        <label class="col-sm-4 col-form-label font-weight-bold">Subtotal:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext subtotal" value="₱ 0.00" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label font-weight-bold">Amount Paid:</label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" class="form-control amount-paid" name="amount_paid" value="₱ 0.00" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label font-weight-bold">Change Due:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext change-due" value="₱ 0.00" readonly>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check-circle"></i> Complete Sale
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
                        <?php if ($product['quantity'] > 0) : ?>
                            <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['selling_price']; ?>" data-quantity="<?php echo $product['quantity']; ?>">
                                <?php echo htmlspecialchars($product['name'] . ' (' . $product['sku'] . ') - ' . $product['quantity'] . ' in stock'); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control quantity" name="items[${itemCount}][quantity]" min="1" value="1" required>
                <small class="text-muted available-quantity">Max: 0</small>
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
    
    // Add event listener for amount paid
    document.querySelector('.amount-paid').addEventListener('input', calculateChange);
    
    function addProductSelectListener(select) {
        select.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const unitPrice = this.closest('.item-row').querySelector('.unit-price');
                const quantityInput = this.closest('.item-row').querySelector('.quantity');
                const availableQuantity = this.closest('.item-row').querySelector('.available-quantity');
                
                unitPrice.value = selectedOption.getAttribute('data-price');
                availableQuantity.textContent = 'Max: ' + selectedOption.getAttribute('data-quantity');
                quantityInput.setAttribute('max', selectedOption.getAttribute('data-quantity'));
                
                calculateTotal();
            }
        });
    }
    
    function addQuantityListener(input) {
        input.addEventListener('input', function() {
            const max = parseInt(this.getAttribute('max'));
            if (parseInt(this.value) > max) {
                this.value = max;
            }
            calculateTotal();
        });
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
        let subtotal = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
            subtotal += quantity * unitPrice;
        });
        
        document.querySelector('.subtotal').value = '$' + subtotal.toFixed(2);
        calculateChange();
    }
    
    function calculateChange() {
        const subtotalText = document.querySelector('.subtotal').value;
        const subtotal = parseFloat(subtotalText.replace('$', '')) || 0;
        const amountPaid = parseFloat(document.querySelector('.amount-paid').value) || 0;
        const change = amountPaid - subtotal;
        
        document.querySelector('.change-due').value = change >= 0 ? '$' + change.toFixed(2) : '($' + Math.abs(change).toFixed(2) + ')';
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
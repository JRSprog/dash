<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$title = "Add New Product";
$error = '';

// Get categories or create default one if none exists
try {
    $categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
    if (empty($categories)) {
        $pdo->exec("INSERT INTO categories (name) VALUES ('Uncategorized')");
        $categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
    }
} catch (PDOException $e) {
    $error = "Failed to initialize categories: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    // Validate and sanitize inputs
    $sku = trim($_POST['sku']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $cost_price = (float)$_POST['cost_price'];
    $selling_price = (float)$_POST['selling_price'];
    $quantity = (int)$_POST['quantity'];
    $min_quantity = (int)$_POST['min_quantity'];
    $barcode = trim($_POST['barcode']);
    
    // Basic validation
    if (empty($sku)) {
        $error = "SKU is required.";
    } elseif (empty($name)) {
        $error = "Product name is required.";
    } elseif (empty($selling_price)) {
        $error = "Selling price is required.";
    } elseif ($cost_price < 0 || $selling_price < 0) {
        $error = "Prices cannot be negative.";
    } elseif ($selling_price < $cost_price) {
        $error = "Selling price cannot be less than cost price.";
    }

    // Handle image upload if no errors
    $image_path = null;
    if (empty($error) && isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/images/products/';
        
        // Validate image file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $_FILES['image']['tmp_name']);
        finfo_close($file_info);
        
        if (!in_array($mime_type, $allowed_types)) {
            $error = "Only JPG, PNG, and GIF images are allowed.";
        } elseif ($_FILES['image']['size'] > 5000000) {
            $error = "Image size must be less than 5MB.";
        } else {
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('prod_', true) . '.' . $file_ext;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = 'assets/images/products/' . $filename;
            } else {
                $error = "Failed to upload image.";
            }
        }
    }

    // Insert product if no errors
    if (empty($error)) {
        try {
            $pdo->beginTransaction();
            
            // If no category selected, use the first available category (Uncategorized)
            if ($category_id === null && !empty($categories)) {
                $category_id = $categories[0]['id'];
            }
            
            $stmt = $pdo->prepare("INSERT INTO products 
                (sku, name, description, category_id, cost_price, selling_price, quantity, min_quantity, barcode, image_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
            $stmt->execute([
                $sku,
                $name,
                $description,
                $category_id,
                $cost_price,
                $selling_price,
                $quantity,
                $min_quantity,
                $barcode,
                $image_path
            ]);
            
            $pdo->commit();
            header("Location: index.php?success=" . urlencode("Product added successfully"));
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            
            // Clean up uploaded file if there was an error
            if (!empty($image_path)) {
                @unlink('../../' . $image_path);
            }
            
            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                $error = "Invalid category selected. The system will now create a default category and retry.";
                // Create default category and refresh
                $pdo->exec("INSERT INTO categories (name) VALUES ('Uncategorized')");
                header("Refresh:0");
                exit();
            } else {
                $error = "Error saving product: " . $e->getMessage();
            }
        }
    }
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Add New Product</h3>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
    <div class="card-body">
        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="productForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sku">SKU <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sku" name="sku" 
                               value="<?= isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : '' ?>" required>
                        <small class="form-text text-muted">Unique product identifier</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">--- Select Category ---</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int)$category['id'] ?>" 
                                    <?= (isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$category['id'] ? 'selected' : '') ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cost_price">Cost Price</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="cost_price" name="cost_price" 
                               value="<?= isset($_POST['cost_price']) ? htmlspecialchars($_POST['cost_price']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="selling_price">Selling Price <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" id="selling_price" name="selling_price" 
                               value="<?= isset($_POST['selling_price']) ? htmlspecialchars($_POST['selling_price']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Initial Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="0" class="form-control" id="quantity" name="quantity" 
                               value="<?= isset($_POST['quantity']) ? (int)$_POST['quantity'] : '0' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="min_quantity">Minimum Quantity</label>
                        <input type="number" min="0" class="form-control" id="min_quantity" name="min_quantity" 
                               value="<?= isset($_POST['min_quantity']) ? (int)$_POST['min_quantity'] : '5' ?>">
                        <small class="form-text text-muted">Default: 5</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="barcode">Barcode</label>
                        <input type="text" class="form-control" id="barcode" name="barcode" 
                               value="<?= isset($_POST['barcode']) ? htmlspecialchars($_POST['barcode']) : '' ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Product
                </button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Update file input label
document.getElementById('image').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : "Choose file";
    document.querySelector('.custom-file-label').textContent = fileName;
});

// Form validation
document.getElementById('productForm').addEventListener('submit', function(e) {
    const sellingPrice = parseFloat(document.getElementById('selling_price').value);
    const costPrice = parseFloat(document.getElementById('cost_price').value);
    
    if (costPrice > 0 && sellingPrice < costPrice) {
        alert('Selling price cannot be less than cost price');
        e.preventDefault();
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
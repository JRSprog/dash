<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];
$title = "Edit Product";
$categories = getCategories();

// Fetch the product data using your existing function
$product = getProductById($product_id);
if (!$product) {
    header("Location: index.php?error=Product not found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sku = trim($_POST['sku']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];
    $cost_price = floatval($_POST['cost_price']);
    $selling_price = floatval($_POST['selling_price']);
    $quantity = intval($_POST['quantity']);
    $min_quantity = intval($_POST['min_quantity']);
    $barcode = trim($_POST['barcode']);
    
    // Handle image upload
    $image_path = $product['image_path']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/images/products/';
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_ext;
        $target_file = $upload_dir . $filename;
        
        // Delete old image if exists
        if ($image_path && file_exists('../../' . $image_path)) {
            unlink('../../' . $image_path);
        }
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'assets/images/products/' . $filename;
        }
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE products SET 
            sku = ?, 
            name = ?, 
            description = ?, 
            category_id = ?, 
            cost_price = ?, 
            selling_price = ?, 
            quantity = ?, 
            min_quantity = ?, 
            barcode = ?, 
            image_path = ?
            WHERE id = ?");
        
        $stmt->execute([
            $sku, $name, $description, $category_id, $cost_price, 
            $selling_price, $quantity, $min_quantity, $barcode, 
            $image_path, $product_id
        ]);
        
        header("Location: index.php?success=" . urlencode("Product updated successfully"));
        exit();
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Product: <?php echo htmlspecialchars($product['name']); ?></h3>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sku">SKU</label>
                        <input type="text" class="form-control" id="sku" name="sku" 
                            value="<?php echo htmlspecialchars($product['sku']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                            value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php 
                            echo htmlspecialchars($product['description']); 
                        ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php if ($category['id'] == $product['category_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cost_price">Cost Price</label>
                        <input type="number" step="0.01" class="form-control" id="cost_price" name="cost_price" 
                            value="<?php echo htmlspecialchars($product['cost_price']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="selling_price">Selling Price</label>
                        <input type="number" step="0.01" class="form-control" id="selling_price" name="selling_price" 
                            value="<?php echo htmlspecialchars($product['selling_price']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                            value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="min_quantity">Minimum Quantity</label>
                        <input type="number" class="form-control" id="min_quantity" name="min_quantity" 
                            value="<?php echo htmlspecialchars($product['min_quantity']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="barcode">Barcode</label>
                        <input type="text" class="form-control" id="barcode" name="barcode" 
                            value="<?php echo htmlspecialchars($product['barcode']); ?>">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Product
            </button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
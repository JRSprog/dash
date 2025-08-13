<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$title = "Manage Products";
$products = getProducts(); // Function to fetch all products

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Product List</h3>
        <a href="add.php?modules/products/add=product" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
    <div class="card-body">
        
        <div class="table-responsive">
            <table class="table" id="search">
                <thead>
                    <tr>
                        <th>Barcode</th>
                        <th>SKU</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product) : ?>
                    <tr>
                         <td>
                            <svg class="barcode" 
                                 jsbarcode-value="<?php echo htmlspecialchars($product['barcode'] ?: $product['sku']); ?>"
                                 jsbarcode-format="CODE128"
                                 jsbarcode-displayValue="true"
                                 jsbarcode-width="1"
                                 jsbarcode-height="30"
                                 jsbarcode-fontSize="10">
                            </svg>
                        </td>
                        <td><?php echo htmlspecialchars($product['sku']); ?></td>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars(getCategoryName($product['category_id'])); ?></td>
                        <td>₱ <?php echo number_format($product['selling_price']); ?></td>
                        <td><?php echo $product['quantity']; ?></td>
                        <td>
                            <?php if ($product['quantity'] <= 0) : ?>
                                <span class="badge badge-danger">Out of Stock</span>
                            <?php elseif ($product['quantity'] < $product['min_quantity']) : ?>
                                <span class="badge badge-warning">Low Stock</span>
                            <?php else : ?>
                                <span class="badge badge-success">In Stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $product['id']; ?>%modules/products/edit=product" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-danger delete-product" data-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script src="../../assets/js/form-validate.js"></script>
<?php include '../../includes/footer.php'; ?>
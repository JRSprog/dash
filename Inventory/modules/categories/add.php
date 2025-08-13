<?php
// /modules/categories/add.php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    
    if (empty($name)) {
        $_SESSION['error'] = "Category name is required";
    } else {
        if (addCategory($pdo, $name, $parent_id)) {
            $_SESSION['message'] = "Category added successfully";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Error adding category";
        }
    }
}

// Get all parent categories for dropdown
$parent_categories = getAllParentCategories($pdo);

require_once '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Category</h3>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Cancel
        </a>
        <?php if (isset($_SESSION['error'])): ?>
    <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>
    </div>


    <form method="post">
        <div class="row">
            <div class="col-md-6">          
                <div class="form-group">
                    <label for="name">Category Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
        
                <div class="form-group">
                    <label for="parent_id">Parent Category (optional):</label>
                    <select id="parent_id" name="parent_id">
                        <option value="">-- No Parent --</option>
                        <?php foreach ($parent_categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn">Add Category</button>
                </div>
            </div>
        </div>        
    </form>

<?php require_once '../../includes/footer.php'; ?>
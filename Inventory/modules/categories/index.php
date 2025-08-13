<?php
// /modules/categories/index.php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth-check.php';

// Handle delete action first - before any output
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    if (deleteCategory($pdo, $_GET['id'])) {
        $_SESSION['message'] = "Category deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting category";
    }
    header("Location: index.php");
    exit();
}

// Now include the header which will start output
require_once '../../includes/header.php';

// Get all categories
$categories = getAllCategories($pdo);
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Categories</h3>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <a href="add.php" class="btn">Add New Category</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Parent Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <?php 
                    $parent_name = '';
                    if ($category['parent_id']) {
                        $parent = getCategoryById($pdo, $category['parent_id']);
                        $parent_name = $parent['name'];
                    }
                    ?>
                    <tr>
                        <td><?php echo $category['id']; ?></td>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td><?php echo htmlspecialchars($parent_name); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a href="index.php?action=delete&id=<?php echo $category['id']; ?>" 
                               class="btn danger" 
                               onclick="return confirm('Are you sure you want to delete this category?')"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
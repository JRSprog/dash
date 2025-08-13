<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$title = "Manage Suppliers";
$suppliers = getSuppliers();

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Suppliers List</h3>
        <a href="add.php" class="btn btn-primary float-right">
            <i class="fas fa-plus"></i> Add Supplier
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        
        <table class="table table-bordered" id="search">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $supplier): ?>
                <tr>
                    <td><?= $supplier['id'] ?></td>
                    <td><?= htmlspecialchars($supplier['name']) ?></td>
                    <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
                    <td><?= htmlspecialchars($supplier['email']) ?></td>
                    <td><?= htmlspecialchars($supplier['phone']) ?></td>
                    <td>
                        <a href="view.php?id=<?= $supplier['id'] ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="edit.php?id=<?= $supplier['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="delete.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $supplier['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../../assets/js/form-validate.js"></script>
<?php include '../../includes/footer.php'; ?>
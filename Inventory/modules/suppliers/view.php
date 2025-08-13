<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$supplier = getSupplierById($id);

if (!$supplier) {
    header("Location: index.php");
    exit();
}

$title = "Supplier Details: " . htmlspecialchars($supplier['name']);

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Supplier Details</h3>
        <a href="index.php" class="btn btn-secondary float-right">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>Supplier ID</th>
                        <td><?= $supplier['id'] ?></td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td><?= htmlspecialchars($supplier['name']) ?></td>
                    </tr>
                    <tr>
                        <th>Contact Person</th>
                        <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>Email</th>
                        <td><?= htmlspecialchars($supplier['email']) ?></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td><?= htmlspecialchars($supplier['phone']) ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><?= $supplier['is_active'] ? 'Active' : 'Inactive' ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="form-group">
            <label>Address</label>
            <div class="well well-sm">
                <?= nl2br(htmlspecialchars($supplier['address'])) ?>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="edit.php?id=<?= $supplier['id'] ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Supplier
            </a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
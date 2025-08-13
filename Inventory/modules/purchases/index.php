<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$title = "Manage Purchases";
$purchases = getPurchases();

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Purchase Orders</h3>
        <a href="add.php?add&modules/purchases/add=purchase" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Purchase
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table" id="search">
                <thead>
                    <tr>
                        <th>PO #</th>
                        <th>Supplier</th>
                        <th>Date Ordered</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date Received</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($purchase['reference_no']); ?></td>
                        <td><?php echo htmlspecialchars(getSupplierName($purchase['supplier_id'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($purchase['date_ordered'])); ?></td>
                        <td>₱ <?php echo number_format($purchase['total_amount'], 2); ?></td>
                        <td>
                            <?php if ($purchase['status'] == 'pending') : ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php elseif ($purchase['status'] == 'received') : ?>
                                <span class="badge badge-success">Received</span>
                            <?php else : ?>
                                <span class="badge badge-danger">Cancelled</span>
                            <?php endif; ?>
                        </td>
                          <td><?= !empty($purchase['date_received']) ? date('M d, Y', strtotime($purchase['date_received'])) : 'N/A' ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $purchase['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($purchase['status'] == 'pending' && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'manager')) : ?>
                                <a href="edit.php?id=<?php echo $purchase['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>

                           <?php if (in_array($purchase['status'], ['received', 'cancelled']) && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'manager')) : ?>
                                <a href="../archives/archive.php?id=<?= $purchase['id'] ?>" 
                                class="btn btn-sm btn-secondary"
                                onclick="return confirm('Are you sure you want to archive this purchase?')">
                                <i class="fas fa-archive"></i> Archive
                                </a>
                            <?php endif; ?>
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
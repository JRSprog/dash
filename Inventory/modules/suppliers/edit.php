<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Check if supplier ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$supplier_id = $_GET['id'];
$title = "Edit Supplier";

// Fetch the supplier data
$supplier = getSupplierById($supplier_id);
if (!$supplier) {
    header("Location: index.php?error=Supplier not found");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $contact_person = trim($_POST['contact_person']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE suppliers SET 
            name = ?, 
            contact_person = ?, 
            phone = ?, 
            email = ?, 
            address = ?,
            is_active = ?
            WHERE id = ?");
        
        $stmt->execute([
            $name, $contact_person, $phone, $email, 
            $address, $is_active, $supplier_id
        ]);
        
        header("Location: index.php?success=" . urlencode("Supplier updated successfully"));
        exit();
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Supplier: <?php echo htmlspecialchars($supplier['name']); ?></h3>
        <a href="index.php" class="btn btn-primary float-right">
            <i class="fas fa-arrow-left"></i> Back to Suppliers
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Supplier Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                            value="<?php echo htmlspecialchars($supplier['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" 
                            value="<?php echo htmlspecialchars($supplier['contact_person']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                            value="<?php echo htmlspecialchars($supplier['phone']); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                            value="<?php echo htmlspecialchars($supplier['email']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php 
                            echo htmlspecialchars($supplier['address']); 
                        ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" style="margin-left: -75px;"
                                <?php echo ($supplier['is_active'] ? 'checked' : ''); ?>>
                            <label class="custom-control-label" for="is_active" style="margin-top: -22px;">Active Supplier</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Supplier
            </button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
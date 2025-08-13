<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$title = "Add New Supplier";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $contact_person = trim($_POST['contact_person']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO suppliers 
                             (name, email, phone, address, contact_person) 
                             VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $address, $contact_person]);
        
        header("Location: index.php?success=Supplier added successfully");
        exit();
    } catch (PDOException $e) {
        $error = "Error adding supplier: " . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Supplier</h3>
        <a href="index.php" class="btn btn-secondary float-right">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">Supplier Name *</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="contact_person">Contact Person</label>
                <input type="text" class="form-control" id="contact_person" name="contact_person">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Supplier
            </button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
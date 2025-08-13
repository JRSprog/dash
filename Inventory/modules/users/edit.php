<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Only admin can access user management
if ($_SESSION['user_role'] != 'admin') {
    header("Location: ../dashboard/");
    exit();
}

$title = "Edit User";

// Check if user ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php?error=User ID not provided");
    exit();
}

$user_id = $_GET['id'];

// Fetch user data
try {
    $user = getUserById($user_id);
    if (!$user) {
        header("Location: index.php?error=User not found");
        exit();
    }
} catch (Exception $e) {
    error_log("Error fetching user: " . $e->getMessage());
    header("Location: index.php?error=Error loading user data");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password']; // May be empty if not changing
    
    // Basic validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!empty($password) && strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    // Check for duplicate username/email (excluding current user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = "Username or email already exists";
    }
    
    if (empty($errors)) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            if (!empty($password)) {
                // Update with password change
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET 
                    username = ?, email = ?, full_name = ?, role = ?, is_active = ?, password = ?
                    WHERE id = ?");
                $stmt->execute([$username, $email, $full_name, $role, $is_active, $hashed_password, $user_id]);
            } else {
                // Update without password change
                $stmt = $pdo->prepare("UPDATE users SET 
                    username = ?, email = ?, full_name = ?, role = ?, is_active = ?
                    WHERE id = ?");
                $stmt->execute([$username, $email, $full_name, $role, $is_active, $user_id]);
            }
            
            $pdo->commit();
            
            $_SESSION['success'] = "User updated successfully";
            header("Location: index.php");
            exit();
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error updating user: " . $e->getMessage());
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit User: <?php echo htmlspecialchars($user['username']); ?></h3>
        <a href="index.php" class="btn btn-primary float-right">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)) : ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" 
                            value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                            value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="manager" <?php echo $user['role'] == 'manager' ? 'selected' : ''; ?>>Manager</option>
                            <option value="staff" <?php echo $user['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" style="margin-left: -100px;" 
                                <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="is_active" style="margin-top: -22px;">Active User :</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update User
            </button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
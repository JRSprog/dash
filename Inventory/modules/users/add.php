<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Only admin can access this page
if ($_SESSION['user_role'] != 'admin') {
    header("Location: ../dashboard/");
    exit();
}

$title = "Add New User";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers and underscores";
    }
    
    if ($email === false) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    } elseif ($password != $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (!in_array($role, ['admin', 'manager', 'staff'])) {
        $errors[] = "Invalid role selected";
    }
    
    // Only check database if basic validation passes
    if (empty($errors)) {
        try {
            // Check if username exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = "Username already exists";
            }
            
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email already exists";
            }
            
            // If still no errors, create user
            if (empty($errors)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users 
                    (username, email, password, full_name, role, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, 1, NOW())");
                
                $success = $stmt->execute([$username, $email, $hashed_password, $full_name, $role]);
                
                if ($success && $stmt->rowCount() > 0) {
                    $_SESSION['success'] = "User created successfully";
                    header("Location: index.php");
                    exit();
                } else {
                    $errors[] = "Failed to create user. Please try again.";
                    error_log("User creation failed - no rows affected");
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Database error occurred. Please try again.";
            error_log("User creation error: " . $e->getMessage());
        }
    }
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New User</h3>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)) : ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" 
                            value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" 
                            required autocomplete="off">
                        <small class="text-muted">At least 4 characters (letters, numbers, _)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                            required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                            value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">At least 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin" <?php echo (isset($role) && $role == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="manager" <?php echo (isset($role) && $role == 'manager') ? 'selected' : ''; ?>>Manager</option>
                            <option value="staff" <?php echo (isset($role) && $role == 'staff') ? 'selected' : ''; ?>>Staff</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const passwordHelp = document.createElement('div');
    passwordHelp.className = 'password-strength mt-1';
    passwordInput.parentNode.appendChild(passwordHelp);
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        let text = '';
        let color = '';
        
        switch(strength) {
            case 0:
            case 1:
                text = 'Weak';
                color = '#e74c3c';
                break;
            case 2:
                text = 'Moderate';
                color = '#f39c12';
                break;
            case 3:
                text = 'Good';
                color = '#3498db';
                break;
            case 4:
            case 5:
                text = 'Strong';
                color = '#2ecc71';
                break;
        }
        
        passwordHelp.innerHTML = `Strength: <span style="color: ${color}; font-weight: bold;">${text}</span>`;
    });
    
    // Confirm password validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    const confirmHelp = document.createElement('div');
    confirmHelp.className = 'password-match mt-1';
    confirmPasswordInput.parentNode.appendChild(confirmHelp);
    
    function checkPasswordMatch() {
        if (passwordInput.value && confirmPasswordInput.value) {
            if (passwordInput.value === confirmPasswordInput.value) {
                confirmHelp.innerHTML = '<span style="color: #2ecc71;"><i class="fas fa-check-circle"></i> Passwords match</span>';
            } else {
                confirmHelp.innerHTML = '<span style="color: #e74c3c;"><i class="fas fa-times-circle"></i> Passwords do not match</span>';
            }
        } else {
            confirmHelp.innerHTML = '';
        }
    }
    
    passwordInput.addEventListener('input', checkPasswordMatch);
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
});
</script>

<?php include '../../includes/footer.php'; ?>
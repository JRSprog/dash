<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$title = "My Profile";

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: ../dashboard/");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    // Only validate passwords if any password field is filled
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password";
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }
        
        if (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters";
        }
        
        if ($new_password != $confirm_password) {
            $errors[] = "New passwords do not match";
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
                // Update with new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, password = ? WHERE id = ?");
                $stmt->execute([$full_name, $hashed_password, $_SESSION['user_id']]);
            } else {
                // Update without changing password
                $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
                $stmt->execute([$full_name, $_SESSION['user_id']]);
            }
            
            // Update session data
            $_SESSION['user_name'] = $full_name ?: $user['username'];
            
            $success = true;
            $user['full_name'] = $full_name;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">My Profile</h3>
    </div>
    <div class="card-body">
        <?php if ($success) : ?>
            <div class="alert alert-success">
                Profile updated successfully!
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)) : ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <input type="text" class="form-control" id="role" value="<?php echo ucfirst($user['role']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="last_login">Last Login</label>
                        <input type="text" class="form-control" id="last_login" 
                               value="<?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="current_password">Current Password (to change password)</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <small class="text-muted">Leave blank to keep current password</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                </div>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password strength indicator
    const newPasswordInput = document.getElementById('new_password');
    const passwordHelp = document.createElement('div');
    passwordHelp.className = 'password-strength mt-1';
    newPasswordInput.parentNode.appendChild(passwordHelp);
    
    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        if (!password) {
            passwordHelp.innerHTML = '';
            return;
        }
        
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
        if (newPasswordInput.value && confirmPasswordInput.value) {
            if (newPasswordInput.value === confirmPasswordInput.value) {
                confirmHelp.innerHTML = '<span style="color: #2ecc71;"><i class="fas fa-check-circle"></i> Passwords match</span>';
            } else {
                confirmHelp.innerHTML = '<span style="color: #e74c3c;"><i class="fas fa-times-circle"></i> Passwords do not match</span>';
            }
        } else {
            confirmHelp.innerHTML = '';
        }
    }
    
    newPasswordInput.addEventListener('input', checkPasswordMatch);
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
});
</script>

<?php include '../../includes/footer.php'; ?>
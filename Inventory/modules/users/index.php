<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Only admin can access user management
if ($_SESSION['user_role'] != 'admin') {
    header("Location: ../dashboard/");
    exit();
}

$title = "Manage Users";

try {
    $users = getUsers();
} catch (Exception $e) {
    error_log("Error loading users: " . $e->getMessage());
    $users = [];
    $_SESSION['error'] = "Failed to load users. Please try again.";
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">User List</h3>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>&nbsp;
                            <?php if ($user['id'] != $_SESSION['user_id']) : ?>
                                <button class="btn btn-sm btn-danger delete-user" data-id="<?php echo $user['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.delete-user').forEach(button => {
    button.addEventListener('click', function() {
        const userId = this.getAttribute('data-id');
        if (confirm('Are you sure you want to delete this user?')) {
            fetch(`delete.php?id=${userId}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php?success=' + encodeURIComponent(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
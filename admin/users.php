<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Handle delete action
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Prevent admin from deleting themselves
    if($user_id == $_SESSION['admin_id']) {
        $delete_error = "You cannot delete your own account!";
    } else {
        try {
            // Start transaction
            $db->beginTransaction();
            
            // First, delete user's applications (due to foreign key cascade)
            $delete_apps = $db->prepare("DELETE FROM applications WHERE user_id = ?");
            $delete_apps->execute([$user_id]);
            
            // Then delete the user
            $delete_user = $db->prepare("DELETE FROM users WHERE id = ?");
            $delete_user->execute([$user_id]);
            
            $db->commit();
            $delete_success = "User deleted successfully!";
            
        } catch(PDOException $e) {
            $db->rollBack();
            $delete_error = "Error deleting user: " . $e->getMessage();
        }
    }
}

// Pagination
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$where_clause = "WHERE 1=1";
$params = [];

if(!empty($search)) {
    $where_clause .= " AND (name LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

// Get total records
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
$count_stmt = $db->prepare($count_query);
foreach($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_records = $count_stmt->fetch()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get users
$query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT :offset, :limit";
$stmt = $db->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <h2 class="mb-4">
        <i class="bi bi-people"></i> Users Management
    </h2>
    
    <!-- Show delete messages -->
    <?php if(isset($delete_success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?php echo $delete_success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($delete_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?php echo $delete_error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Show session messages -->
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?> alert-dismissible fade show">
            <i class="bi bi-<?php echo ($_SESSION['message_type'] ?? 'info') == 'success' ? 'check-circle' : 'info-circle'; ?>"></i> 
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php 
        // Clear session messages
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>
    
    <!-- Search Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search users by name or email" 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <a href="users.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Clear Search
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list"></i> Users List (<?php echo $total_records; ?> total)
            </h5>
            <a href="../auth/register.php" class="btn btn-success btn-sm">
                <i class="bi bi-person-plus"></i> Add New User
            </a>
        </div>
        <div class="card-body">
            <?php if(empty($users)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No users found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Registered</th>
                                <th>Applications</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $index => $user): ?>
                                <tr id="user-row-<?php echo $user['id']; ?>">
                                    <td><?php echo $offset + $index + 1; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                        <?php if($user['id'] == $_SESSION['admin_id']): ?>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-star"></i> Current Admin
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] == 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php
                                        // Count user's applications
                                        $app_stmt = $db->prepare("SELECT COUNT(*) as count FROM applications WHERE user_id = ?");
                                        $app_stmt->execute([$user['id']]);
                                        $app_count = $app_stmt->fetch()['count'];
                                        ?>
                                        <span class="badge bg-info"><?php echo $app_count; ?> applications</span>
                                    </td>
                                    <td>
                                        <?php if($user['id'] != $_SESSION['admin_id']): ?>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="editUser(<?php echo $user['id']; ?>,'<?php echo htmlspecialchars(addslashes($user['name'])); ?>','<?php echo htmlspecialchars(addslashes($user['email'])); ?>','<?php echo $user['role']; ?>')"
                                                        title="Edit User">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-warning" 
                                                        onclick="makeAdmin(<?php echo $user['id']; ?>,'<?php echo htmlspecialchars(addslashes($user['name'])); ?>')"
                                                        title="Make Admin">
                                                    <i class="bi bi-shield-check"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="confirmDelete(<?php echo $user['id']; ?>,'<?php echo htmlspecialchars(addslashes($user['name'])); ?>', <?php echo $app_count; ?>)"
                                                        title="Delete User">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            </li>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="update_user.php" id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="editUserId">
                    
                    <div class="mb-3">
                        <label for="editUserName" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="editUserName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editUserEmail" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="editUserEmail" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editUserRole" class="form-label">Role *</label>
                        <select class="form-select" id="editUserRole" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editUserPassword" class="form-label">New Password (Leave blank to keep current)</label>
                        <input type="password" class="form-control" id="editUserPassword" name="password">
                        <div class="form-text">Minimum 6 characters</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Changing password will log the user out of all sessions.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Make Admin Confirmation Modal -->
<div class="modal fade" id="makeAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="makeAdminMessage"></p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Warning:</strong> This user will have full admin privileges including:
                    <ul class="mb-0 mt-2">
                        <li>Access to admin dashboard</li>
                        <li>Ability to manage all applications</li>
                        <li>Ability to manage other users</li>
                        <li>Ability to delete users and applications</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="make_admin.php" id="makeAdminForm">
                    <input type="hidden" name="user_id" id="makeAdminUserId">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-check"></i> Make Admin
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteUserMessage"></p>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>This action cannot be undone!</strong> All data associated with this user will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i> Delete User
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Function to open edit modal
function editUser(userId, userName, userEmail, userRole) {
    document.getElementById('editUserId').value = userId;
    document.getElementById('editUserName').value = userName;
    document.getElementById('editUserEmail').value = userEmail;
    document.getElementById('editUserRole').value = userRole;
    
    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}

// Function to make user admin
function makeAdmin(userId, userName) {
    document.getElementById('makeAdminUserId').value = userId;
    document.getElementById('makeAdminMessage').textContent = 
        `Are you sure you want to make "${userName}" an administrator?`;
    
    const modal = new bootstrap.Modal(document.getElementById('makeAdminModal'));
    modal.show();
}

// Function to confirm and delete user
function confirmDelete(userId, userName, appCount) {
    let message = `Are you sure you want to delete user "${userName}"?`;
    
    if(appCount > 0) {
        message += `\n\nThis will also delete ${appCount} application(s) submitted by this user.`;
    }
    
    document.getElementById('deleteUserMessage').textContent = message;
    
    // Set delete URL with all parameters
    const searchParam = '<?php echo urlencode($search); ?>';
    const pageParam = '<?php echo $page; ?>';
    let deleteUrl = `users.php?delete=${userId}`;
    
    if(searchParam) {
        deleteUrl += `&search=${searchParam}`;
    }
    if(pageParam) {
        deleteUrl += `&page=${pageParam}`;
    }
    
    document.getElementById('confirmDeleteBtn').href = deleteUrl;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    modal.show();
}

// Form validation for edit modal
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    const password = document.getElementById('editUserPassword').value;
    if(password && password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long');
        return false;
    }
    
    // Show loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
    submitBtn.disabled = true;
    
    // Re-enable after 3 seconds if still there (in case of error)
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 3000);
});

// Form submission for make admin
document.getElementById('makeAdminForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
    submitBtn.disabled = true;
    
    // Re-enable after 3 seconds
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 3000);
});

// Show alert function
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    if(container.firstChild && container.firstChild.classList && container.firstChild.classList.contains('alert')) {
        container.removeChild(container.firstChild);
    }
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if(alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Check URL for success/error parameters
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('success')) {
        showAlert('success', urlParams.get('success'));
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    if(urlParams.has('error')) {
        showAlert('danger', urlParams.get('error'));
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>

<?php include '../includes/footer.php'; ?>
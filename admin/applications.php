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

// Pagination variables
$records_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Filter variables
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$experience_filter = isset($_GET['experience']) ? $_GET['experience'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build WHERE clause
$where_clause = "WHERE 1=1";
$params = [];

if(!empty($role_filter)) {
    $where_clause .= " AND a.role = :role";
    $params[':role'] = $role_filter;
}

if(!empty($experience_filter)) {
    $where_clause .= " AND a.experience = :experience";
    $params[':experience'] = $experience_filter;
}

if(!empty($status_filter)) {
    $where_clause .= " AND a.status = :status";
    $params[':status'] = $status_filter;
}

if(!empty($search)) {
    $where_clause .= " AND (u.name LIKE :search OR u.email LIKE :search OR a.mobile LIKE :search)";
    $params[':search'] = "%$search%";
}

// Get total records for pagination
$count_query = "SELECT COUNT(*) as total FROM applications a 
                INNER JOIN users u ON a.user_id = u.id $where_clause";
$count_stmt = $db->prepare($count_query);
foreach($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get applications with JOIN
$query = "SELECT a.*, u.name, u.email, u.created_at as user_created 
          FROM applications a 
          INNER JOIN users u ON a.user_id = u.id 
          $where_clause 
          ORDER BY a.created_at DESC 
          LIMIT :offset, :limit";

$stmt = $db->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique roles for filter
$role_query = "SELECT DISTINCT role FROM applications ORDER BY role";
$role_stmt = $db->query($role_query);
$roles = $role_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <h2 class="mb-4">
        <i class="bi bi-file-earmark-text"></i> Applications Management
    </h2>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Search
                    </label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search by name, email or mobile">
                </div>
                <div class="col-md-2">
                    <label for="role" class="form-label">
                        <i class="bi bi-briefcase"></i> Role
                    </label>
                    <select class="form-select" id="role" name="role">
                        <option value="">All Roles</option>
                        <?php foreach($roles as $role): ?>
                            <option value="<?php echo $role; ?>" 
                                <?php echo ($role_filter == $role) ? 'selected' : ''; ?>>
                                <?php echo $role; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="experience" class="form-label">
                        <i class="bi bi-graph-up"></i> Experience
                    </label>
                    <select class="form-select" id="experience" name="experience">
                        <option value="">All Levels</option>
                        <option value="Beginner" <?php echo ($experience_filter == 'Beginner') ? 'selected' : ''; ?>>Beginner</option>
                        <option value="Intermediate" <?php echo ($experience_filter == 'Intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="Advanced" <?php echo ($experience_filter == 'Advanced') ? 'selected' : ''; ?>>Advanced</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">
                        <i class="bi bi-info-circle"></i> Status
                    </label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="reviewed" <?php echo ($status_filter == 'reviewed') ? 'selected' : ''; ?>>Reviewed</option>
                        <option value="accepted" <?php echo ($status_filter == 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                        <option value="rejected" <?php echo ($status_filter == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                    <a href="applications.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Applications Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list"></i> Applications (<?php echo $total_records; ?> total)
            </h5>
            <a href="dashboard.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <div class="card-body">
            <?php if(empty($applications)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No applications found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Role</th>
                                <th>Experience</th>
                                <th>Skills</th>
                                <th>Portfolio</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($applications as $index => $app): ?>
                                <tr id="app-row-<?php echo $app['id']; ?>">
                                    <td><?php echo $offset + $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($app['name']); ?></td>
                                    <td><?php echo htmlspecialchars($app['email']); ?></td>
                                    <td><?php echo htmlspecialchars($app['mobile']); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $app['role']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?php echo $app['experience'] == 'Beginner' ? 'bg-info' : 
                                                    ($app['experience'] == 'Intermediate' ? 'bg-warning' : 'bg-success'); ?>">
                                            <?php echo $app['experience']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo substr(htmlspecialchars($app['skills']), 0, 50) . '...'; ?></td>
                                    <td>
                                        <?php if(!empty($app['portfolio_link'])): ?>
                                            <a href="<?php echo htmlspecialchars($app['portfolio_link']); ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-box-arrow-up-right"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php echo $app['status'] == 'pending' ? 'bg-secondary' : 
                                                    ($app['status'] == 'accepted' ? 'bg-success' : 
                                                    ($app['status'] == 'rejected' ? 'bg-danger' : 'bg-warning')); ?>"
                                            id="status-badge-<?php echo $app['id']; ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="viewApplication(<?php echo $app['id']; ?>)"
                                                    title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" 
                                                    onclick="updateStatus(<?php echo $app['id']; ?>, 'accepted', this)"
                                                    title="Accept">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="updateStatus(<?php echo $app['id']; ?>, 'rejected', this)"
                                                    title="Reject">
                                                <i class="bi bi-x"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" 
                                                    onclick="updateStatus(<?php echo $app['id']; ?>, 'reviewed', this)"
                                                    title="Mark as Reviewed">
                                                <i class="bi bi-clock"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" 
                                                    onclick="updateStatus(<?php echo $app['id']; ?>, 'pending', this)"
                                                    title="Mark as Pending">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </div>
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
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&role=<?php echo $role_filter; ?>&experience=<?php echo $experience_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            </li>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&role=<?php echo $role_filter; ?>&experience=<?php echo $experience_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&role=<?php echo $role_filter; ?>&experience=<?php echo $experience_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
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

<!-- Modal for Viewing Application Details -->
<div class="modal fade" id="applicationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Application Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalApplicationDetails">
                Loading application details...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Function to update application status
function updateStatus(appId, status, buttonElement) {
    if(!confirm('Are you sure you want to change status to "' + status + '"?')) {
        return;
    }
    
    // Show loading on the button
    const originalHTML = buttonElement.innerHTML;
    buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    buttonElement.disabled = true;
    
    // Send AJAX request
    const formData = new FormData();
    formData.append('id', appId);
    formData.append('status', status);
    
    fetch('process_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Update the status badge
            const statusBadge = document.getElementById('status-badge-' + appId);
            const statusText = status.charAt(0).toUpperCase() + status.slice(1);
            
            // Update badge color and text
            statusBadge.textContent = statusText;
            statusBadge.className = 'badge ' + getStatusClass(status);
            
            // Show success message
            showAlert('success', `Application status updated to "${statusText}" successfully!`);
        } else {
            showAlert('danger', 'Error: ' + data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'Network error: ' + error.message);
    })
    .finally(() => {
        // Restore button
        buttonElement.innerHTML = originalHTML;
        buttonElement.disabled = false;
    });
}

// Function to view application details
function viewApplication(appId) {
    // Show loading in modal
    document.getElementById('modalApplicationDetails').innerHTML = 
        '<div class="text-center"><div class="spinner-border text-primary"></div><p>Loading...</p></div>';
    
    // Fetch application details
    fetch('get_application.php?id=' + appId)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showApplicationModal(data.application);
            } else {
                document.getElementById('modalApplicationDetails').innerHTML = 
                    '<div class="alert alert-danger">Error: ' + data.message + '</div>';
            }
        })
        .catch(error => {
            document.getElementById('modalApplicationDetails').innerHTML = 
                '<div class="alert alert-danger">Error loading application details</div>';
        });
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('applicationModal'));
    modal.show();
}

// Function to show application details in modal
function showApplicationModal(application) {
    const modalBody = document.getElementById('modalApplicationDetails');
    
    // Format date
    const appliedDate = new Date(application.created_at);
    const formattedDate = appliedDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Create HTML content
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Applicant Information</h6>
                <p><strong>Name:</strong> ${application.name}</p>
                <p><strong>Email:</strong> ${application.email}</p>
                <p><strong>Mobile:</strong> ${application.mobile}</p>
            </div>
            <div class="col-md-6">
                <h6>Application Details</h6>
                <p><strong>Role:</strong> <span class="badge bg-primary">${application.role}</span></p>
                <p><strong>Experience:</strong> <span class="badge ${getExperienceClass(application.experience)}">${application.experience}</span></p>
                <p><strong>Status:</strong> <span class="badge ${getStatusClass(application.status)}">${application.status.charAt(0).toUpperCase() + application.status.slice(1)}</span></p>
                <p><strong>Applied:</strong> ${formattedDate}</p>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-12">
                <h6>Skills</h6>
                <div class="p-3 bg-light rounded">
                    ${application.skills.replace(/\n/g, '<br>')}
                </div>
            </div>
        </div>
        
        ${application.portfolio_link ? `
        <div class="row mt-3">
            <div class="col-12">
                <h6>Portfolio / GitHub Link</h6>
                <p><a href="${application.portfolio_link}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right"></i> ${application.portfolio_link}
                </a></p>
            </div>
        </div>
        ` : ''}
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="btn-group">
                    <button class="btn btn-success" onclick="updateStatusModal(${application.id}, 'accepted')">
                        <i class="bi bi-check"></i> Accept
                    </button>
                    <button class="btn btn-danger" onclick="updateStatusModal(${application.id}, 'rejected')">
                        <i class="bi bi-x"></i> Reject
                    </button>
                    <button class="btn btn-warning" onclick="updateStatusModal(${application.id}, 'reviewed')">
                        <i class="bi bi-clock"></i> Mark Reviewed
                    </button>
                    <button class="btn btn-secondary" onclick="updateStatusModal(${application.id}, 'pending')">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset to Pending
                    </button>
                </div>
            </div>
        </div>
    `;
    
    modalBody.innerHTML = html;
}

// Function to update status from modal
function updateStatusModal(appId, status) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('applicationModal'));
    if(confirm('Change status to "' + status + '"?')) {
        // Find and click the corresponding button on the table
        const button = document.querySelector(`button[onclick*="updateStatus(${appId}, '${status}'"]`);
        if(button) {
            button.click();
        }
        modal.hide();
    }
}

// Helper function to get status CSS class
function getStatusClass(status) {
    switch(status) {
        case 'pending': return 'bg-secondary';
        case 'reviewed': return 'bg-warning';
        case 'accepted': return 'bg-success';
        case 'rejected': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// Helper function to get experience CSS class
function getExperienceClass(experience) {
    switch(experience.toLowerCase()) {
        case 'beginner': return 'bg-info';
        case 'intermediate': return 'bg-warning';
        case 'advanced': return 'bg-success';
        default: return 'bg-secondary';
    }
}

// Function to show alert messages
function showAlert(type, message) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the card body
    const cardBody = document.querySelector('.card-body');
    if(cardBody.firstChild && cardBody.firstChild.classList && cardBody.firstChild.classList.contains('alert')) {
        // Remove existing alert
        cardBody.removeChild(cardBody.firstChild);
    }
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
    
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
});
</script>

<?php include '../includes/footer.php'; ?>
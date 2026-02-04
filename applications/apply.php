<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Check if user already applied today
$query = "SELECT id FROM applications WHERE user_id = :user_id AND DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();

if($stmt->rowCount() > 0) {
    $error = "You can only submit one application per day";
}

if($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {
    $mobile = trim($_POST['mobile']);
    $role = $_POST['role'];
    $experience = $_POST['experience'];
    $skills = trim($_POST['skills']);
    $portfolio_link = trim($_POST['portfolio_link']);
    
    // Validation
    if(empty($mobile) || empty($role) || empty($experience) || empty($skills)) {
        $error = "Please fill all required fields";
    } elseif(!preg_match('/^[0-9]{10,15}$/', $mobile)) {
        $error = "Invalid mobile number (10-15 digits required)";
    } elseif(!empty($portfolio_link) && !filter_var($portfolio_link, FILTER_VALIDATE_URL)) {
        $error = "Invalid portfolio URL";
    } else {
        // Insert application
        $query = "INSERT INTO applications (user_id, mobile, role, experience, skills, portfolio_link) 
                  VALUES (:user_id, :mobile, :role, :experience, :skills, :portfolio_link)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);
        $stmt->bindParam(":mobile", $mobile);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":experience", $experience);
        $stmt->bindParam(":skills", $skills);
        $stmt->bindParam(":portfolio_link", $portfolio_link);
        
        if($stmt->execute()) {
            $success = "Application submitted successfully!";
        } else {
            $error = "Failed to submit application. Please try again.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">
                    <i class="bi bi-file-earmark-text"></i> Internship Application Form
                </h3>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                        <div class="mt-3">
                            <a href="my_applications.php" class="btn btn-primary">
                                <i class="bi bi-eye"></i> View My Applications
                            </a>
                            <a href="../index.php" class="btn btn-outline-primary">
                                <i class="bi bi-house"></i> Go to Home
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="mobile" class="form-label">
                                <i class="bi bi-phone"></i> Mobile Number *
                            </label>
                            <input type="tel" class="form-control" id="mobile" name="mobile" required
                                   pattern="[0-9]{10,15}" placeholder="10 digits">
                            <small class="text-muted">Enter your mobile number without country code</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">
                                <i class="bi bi-briefcase"></i> Internship Role *
                            </label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="PHP Developer">PHP Developer</option>
                                <option value="Video Editor">Video Editor</option>
                                <option value="Mobile App Developer">Mobile App Developer</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="experience" class="form-label">
                                <i class="bi bi-graph-up"></i> Experience Level *
                            </label>
                            <select class="form-select" id="experience" name="experience" required>
                                <option value="">Select Experience</option>
                                <option value="Beginner">Beginner (0-1 years)</option>
                                <option value="Intermediate">Intermediate (1-3 years)</option>
                                <option value="Advanced">Advanced (3+ years)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="skills" class="form-label">
                                <i class="bi bi-tools"></i> Skills *
                            </label>
                            <textarea class="form-control" id="skills" name="skills" rows="4" required
                                      placeholder="List your skills (e.g., PHP, MySQL, JavaScript, Photoshop, React Native)"></textarea>
                            <small class="text-muted">Separate skills with commas</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="portfolio_link" class="form-label">
                                <i class="bi bi-link"></i> Portfolio / GitHub Link
                            </label>
                            <input type="url" class="form-control" id="portfolio_link" name="portfolio_link"
                                   placeholder="https://github.com/username or https://portfolio.example.com">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-send"></i> Submit Application
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
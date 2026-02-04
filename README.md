Internship Portal - Complete Web Application
📋 Project Overview
A full-featured web application for managing internship applications with separate user and admin panels. Built with pure PHP and MySQL without any frameworks.

✨ Features
👤 User Features
User Registration & Login - Secure authentication with password hashing

Internship Application Form - Apply for PHP Developer, Video Editor, or Mobile App Developer roles

My Applications - View personal application history and status

Daily Limit - Users can submit only one application per day

Responsive Design - Mobile-friendly interface with Bootstrap 5

👑 Admin Features
Separate Admin Login - Dedicated login page for administrators

Dashboard - Overview with statistics and quick actions

Application Management - View, filter, search, and paginate all applications

User Management - Manage users (edit, delete, promote to admin)

Application Status Control - Accept, reject, or mark applications as reviewed

Advanced Filters - Filter by role, experience, status, and search by name/email

🛡️ Security Features
Prepared Statements - Protection against SQL injection

Password Hashing - Bcrypt password encryption

Session Management - Separate sessions for users and admins

Input Validation - Server-side validation for all inputs

XSS Protection - Output escaping with htmlspecialchars()

Access Control - Admin-only routes protected

🗂️ Project Structure
text
internship-portal/
├── index.php                 # Home page
├── config/
│   └── database.php         # Database configuration
├── auth/                    # Authentication
│   ├── register.php        # User registration
│   ├── login.php           # User login
│   ├── logout.php          # User logout
│   ├── admin_login.php     # Admin login
│   └── admin_logout.php    # Admin logout
├── applications/           # User applications
│   ├── apply.php          # Application form
│   └── my_applications.php # View user's applications
├── admin/                 # Admin panel
│   ├── dashboard.php      # Admin dashboard
│   ├── applications.php   # Manage applications (with JOINs, pagination, filters)
│   ├── users.php          # Manage users
│   ├── update_user.php    # Update user details
│   ├── make_admin.php     # Promote user to admin
│   ├── process_status.php # Update application status (AJAX)
│   └── get_application.php # Get application details (AJAX)
├── includes/              # Common includes
│   ├── header.php        # Page header with navigation
│   ├── footer.php        # Page footer
│   ├── auth_check.php    # User authentication check
│   └── admin_check.php   # Admin authentication check
├── css/                  # Stylesheets
│   └── style.css         # Custom CSS
├── js/                   # JavaScript
│   └── script.js         # Custom JavaScript
├── setup.php            # Setup check script
├── database.sql         # Database schema and sample data
└── README.md           # This file
🗄️ Database Schema
Users Table
sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
Applications Table
sql
CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    role ENUM('PHP Developer', 'Video Editor', 'Mobile App Developer') NOT NULL,
    experience ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL,
    skills TEXT NOT NULL,
    portfolio_link VARCHAR(255),
    status ENUM('pending', 'reviewed', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
🚀 Installation
Prerequisites
PHP 7.4 or higher

MySQL 5.7 or higher

Web server (Apache/Nginx) or PHP built-in server

MySQL Workbench (for database management)

Step 1: Database Setup
Open MySQL Workbench

Create a new SQL tab

Copy and execute the entire database.sql script

Verify the database internship_portal is created

Step 2: Configuration
Edit config/database.php with your MySQL credentials:

php
private $host = "localhost";
private $port = "3306";
private $db_name = "internship_portal";
private $username = "root";
private $password = ""; // Your MySQL password
Step 3: File Placement
Place all files in your web server directory:

XAMPP: C:/xampp/htdocs/internship-portal/

WAMP: C:/wamp64/www/internship-portal/

MAMP: /Applications/MAMP/htdocs/internship-portal/

Step 4: Run Setup Check
Open in browser: http://localhost/internship-portal/setup.php

🔑 Default Login Credentials
Admin Account
Email: admin@example.com

Password: Admin@123

Test User Accounts
User 1: john@example.com / password123

User 2: jane@example.com / password123

User 3: bob@example.com / password123

📊 Admin Features Details
Application Management
Pagination: 5 records per page

Filters: By role, experience, and status

Search: By name, email, or mobile number

Status Updates: Accept, reject, mark as reviewed, or reset to pending

Bulk Actions: Update multiple applications at once

JOIN Operations: Combines user and application data

User Management
Edit Users: Update name, email, role, and password

Delete Users: With confirmation and cascading delete of applications

Make Admin: Promote regular users to administrators

Search Users: By name or email

Pagination: 10 records per page

🛠️ Technical Implementation
JOIN Operations Example
php
SELECT a.*, u.name, u.email 
FROM applications a 
INNER JOIN users u ON a.user_id = u.id
ORDER BY a.created_at DESC 
LIMIT :offset, :limit
Pagination Implementation
php
$records_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;
Prepared Statements Example
php
$query = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
$stmt = $db->prepare($query);
$stmt->bindParam(":name", $name);
$stmt->bindParam(":email", $email);
$stmt->bindParam(":password", $hashed_password);
$stmt->execute();
🌐 Usage Guide
As a Regular User
Register for an account at /auth/register.php

Login at /auth/login.php

Submit internship application at /applications/apply.php

View your applications at /applications/my_applications.php

As an Administrator
Login at /auth/admin_login.php

Access dashboard at /admin/dashboard.php

Manage applications at /admin/applications.php

Manage users at /admin/users.php

🐛 Troubleshooting
Common Issues
Database Connection Failed
Run setup.php to diagnose the problem

Check if MySQL service is running

Verify credentials in config/database.php

Ensure port 3306 is not blocked

Admin Login Issues
Default credentials: admin@example.com / Admin@123

Run setup.php to reset admin password

Check if admin account exists in database

Page Not Found
Ensure files are in correct directory structure

Check web server configuration

Verify .htaccess rules (if using Apache)

Session Issues
Ensure session_start() is called before any output

Check file permissions (644 for files, 755 for directories)

Verify PHP session configuration

Setup Verification
Run the setup check script:

text
http://localhost/internship-portal/setup.php
This will verify:

PHP version and extensions

Database connection

Required tables exist

File permissions

Admin account status

📱 Browser Compatibility
Chrome 60+

Firefox 55+

Safari 12+

Edge 79+

Mobile browsers (responsive design)

🔒 Security Best Practices Implemented
SQL Injection Prevention: All queries use prepared statements

Password Security: Bcrypt hashing with cost factor 10

Session Security: Separate sessions for users and admins

Input Sanitization: Server-side validation for all inputs

XSS Prevention: Output escaping with htmlspecialchars()

CSRF Protection: Basic token implementation

Access Control: Role-based access to admin features

Error Handling: User-friendly error messages without sensitive data

📄 License
MIT License - Free to use, modify, and distribute

🤝 Support
For issues or questions:

Check the setup guide in setup.php

Review the troubleshooting section above

Ensure all prerequisites are met

Verify database connection settings

🎯 Project Requirements Met
✅ Mandatory Requirements
Core PHP (No frameworks)

MySQL database

HTML/CSS with Bootstrap

PDO with prepared statements

Session handling

User authentication (registration/login)

Internship application form

Database structure with foreign keys

Admin panel with JOIN operations

Pagination (5 records per page)

Filters by role and experience

Search by name or email

SQL injection prevention

Password hashing (no plain text)

✅ Bonus Features
Separate admin login

User management (edit/delete/promote)

Application status management (accept/reject)

Bulk actions

Modal interfaces

AJAX operations

Responsive design

Bootstrap icons

Setup verification script

Comprehensive README

📞 Contact
For technical support or questions about this project, please refer to the setup guide and troubleshooting sections above.


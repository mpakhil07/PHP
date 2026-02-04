-- Create the database
DROP DATABASE IF EXISTS internship_portal;
CREATE DATABASE internship_portal;
USE internship_portal;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Applications table
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_role (role),
    INDEX idx_experience (experience),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert admin user (password: Admin@123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample users (password: password123)
INSERT INTO users (name, email, password) VALUES 
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Bob Wilson', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample applications
INSERT INTO applications (user_id, mobile, role, experience, skills, portfolio_link, status) VALUES
(2, '1234567890', 'PHP Developer', 'Beginner', 'PHP, MySQL, HTML, CSS, JavaScript', 'https://github.com/johndoe', 'pending'),
(2, '9876543210', 'Mobile App Developer', 'Intermediate', 'React Native, JavaScript, Firebase, Redux', 'https://github.com/johndoe/apps', 'reviewed'),
(3, '5551234567', 'Video Editor', 'Advanced', 'Premiere Pro, After Effects, Photoshop, Final Cut Pro', 'https://portfolio.janesmith.com', 'accepted'),
(3, '5559876543', 'PHP Developer', 'Intermediate', 'PHP, Laravel, MySQL, REST APIs, Git', 'https://github.com/janesmith', 'pending'),
(4, '4445556666', 'Video Editor', 'Beginner', 'Premiere Pro, Photoshop, Canva', NULL, 'rejected'),
(4, '7778889999', 'Mobile App Developer', 'Advanced', 'Flutter, Dart, Firebase, Android Studio, iOS Development', 'https://github.com/bobwilson', 'accepted');
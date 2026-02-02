-- ============================================
-- Smart Complaint Management System
-- MySQL Database Schema
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS complaint_management_system;
USE complaint_management_system;

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Complaints Table
-- ============================================
CREATE TABLE IF NOT EXISTS complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    complaint_text TEXT NOT NULL,
    priority ENUM('Other', 'Low', 'Medium', 'High') NOT NULL,
    status ENUM('Registered', 'Under Review', 'In Progress', 'Resolved') DEFAULT 'Registered',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    admin_notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_priority (priority),
    INDEX idx_status (status),
    INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Complaint Status History Table
-- ============================================
CREATE TABLE IF NOT EXISTS complaint_status_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    old_status ENUM('Registered', 'Under Review', 'In Progress', 'Resolved'),
    new_status ENUM('Registered', 'Under Review', 'In Progress', 'Resolved') NOT NULL,
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_complaint_id (complaint_id),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ML Model Predictions Log Table
-- ============================================
CREATE TABLE IF NOT EXISTS ml_predictions_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    predicted_priority ENUM('Other', 'Low', 'Medium', 'High') NOT NULL,
    confidence_score DECIMAL(5,4),
    model_version VARCHAR(50),
    predicted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    INDEX idx_complaint_id (complaint_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Admin Activity Log Table
-- ============================================
CREATE TABLE IF NOT EXISTS admin_activity_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    activity_type VARCHAR(100) NOT NULL,
    activity_description TEXT,
    related_complaint_id INT,
    activity_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (related_complaint_id) REFERENCES complaints(complaint_id) ON DELETE SET NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_activity_timestamp (activity_timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Sessions Table (for security)
-- ============================================
CREATE TABLE IF NOT EXISTS user_sessions (
    session_id VARCHAR(255) PRIMARY KEY,
    user_id INT NOT NULL,
    session_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Default Admin User
-- Password: Admin@123 (hashed using password_hash)
-- ============================================
INSERT INTO users (full_name, email, password_hash, role) VALUES
('System Administrator', 'admin@complaint.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ============================================
-- Views for Analytics
-- ============================================

-- View: Complaint Statistics by Priority
CREATE OR REPLACE VIEW complaint_stats_by_priority AS
SELECT 
    priority,
    COUNT(*) as total_complaints,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved_count,
    SUM(CASE WHEN status = 'Registered' THEN 1 ELSE 0 END) as pending_count,
    AVG(CASE WHEN resolved_at IS NOT NULL 
        THEN TIMESTAMPDIFF(HOUR, submitted_at, resolved_at) 
        ELSE NULL END) as avg_resolution_time_hours
FROM complaints
GROUP BY priority;

-- View: User Complaint Summary
CREATE OR REPLACE VIEW user_complaint_summary AS
SELECT 
    u.user_id,
    u.full_name,
    u.email,
    COUNT(c.complaint_id) as total_complaints,
    SUM(CASE WHEN c.status = 'Resolved' THEN 1 ELSE 0 END) as resolved_complaints,
    SUM(CASE WHEN c.priority = 'High' THEN 1 ELSE 0 END) as high_priority_complaints
FROM users u
LEFT JOIN complaints c ON u.user_id = c.user_id
WHERE u.role = 'user'
GROUP BY u.user_id, u.full_name, u.email;

-- View: Recent Activity Dashboard
CREATE OR REPLACE VIEW recent_activity AS
SELECT 
    c.complaint_id,
    c.complaint_text,
    c.priority,
    c.status,
    c.submitted_at,
    u.full_name as user_name,
    u.email as user_email
FROM complaints c
JOIN users u ON c.user_id = u.user_id
ORDER BY c.submitted_at DESC
LIMIT 50;

-- ============================================
-- Stored Procedures
-- ============================================

-- Procedure: Update Complaint Status
DELIMITER //
CREATE PROCEDURE UpdateComplaintStatus(
    IN p_complaint_id INT,
    IN p_new_status VARCHAR(50),
    IN p_admin_id INT,
    IN p_notes TEXT
)
BEGIN
    DECLARE v_old_status VARCHAR(50);
    
    -- Get current status
    SELECT status INTO v_old_status 
    FROM complaints 
    WHERE complaint_id = p_complaint_id;
    
    -- Update complaint status
    UPDATE complaints 
    SET status = p_new_status,
        admin_notes = COALESCE(p_notes, admin_notes),
        resolved_at = CASE WHEN p_new_status = 'Resolved' THEN CURRENT_TIMESTAMP ELSE resolved_at END
    WHERE complaint_id = p_complaint_id;
    
    -- Log status change
    INSERT INTO complaint_status_history 
        (complaint_id, old_status, new_status, changed_by, notes)
    VALUES 
        (p_complaint_id, v_old_status, p_new_status, p_admin_id, p_notes);
    
    -- Log admin activity
    INSERT INTO admin_activity_log 
        (admin_id, activity_type, activity_description, related_complaint_id)
    VALUES 
        (p_admin_id, 'STATUS_UPDATE', 
         CONCAT('Changed status from ', v_old_status, ' to ', p_new_status), 
         p_complaint_id);
END //
DELIMITER ;

-- Procedure: Get Complaint Lifecycle
DELIMITER //
CREATE PROCEDURE GetComplaintLifecycle(IN p_complaint_id INT)
BEGIN
    SELECT 
        c.complaint_id,
        c.complaint_text,
        c.priority,
        c.status,
        c.submitted_at,
        c.resolved_at,
        TIMESTAMPDIFF(HOUR, c.submitted_at, COALESCE(c.resolved_at, CURRENT_TIMESTAMP)) as hours_elapsed,
        u.full_name as user_name,
        u.email as user_email
    FROM complaints c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.complaint_id = p_complaint_id;
    
    SELECT 
        old_status,
        new_status,
        changed_at,
        notes,
        (SELECT full_name FROM users WHERE user_id = changed_by) as changed_by_name
    FROM complaint_status_history
    WHERE complaint_id = p_complaint_id
    ORDER BY changed_at ASC;
END //
DELIMITER ;

-- ============================================
-- Triggers
-- ============================================

-- Trigger: Auto-create status history on new complaint
DELIMITER //
CREATE TRIGGER after_complaint_insert
AFTER INSERT ON complaints
FOR EACH ROW
BEGIN
    INSERT INTO complaint_status_history 
        (complaint_id, old_status, new_status, notes)
    VALUES 
        (NEW.complaint_id, NULL, 'Registered', 'Initial complaint registration');
END //
DELIMITER ;

-- ============================================
-- Indexes for Performance Optimization
-- ============================================
CREATE INDEX idx_complaints_composite ON complaints(priority, status, submitted_at);
CREATE INDEX idx_history_composite ON complaint_status_history(complaint_id, changed_at);

-- ============================================
-- Grant Permissions (Example - Adjust as needed)
-- ============================================
-- CREATE USER 'complaint_app'@'localhost' IDENTIFIED BY 'secure_password_here';
-- GRANT SELECT, INSERT, UPDATE ON complaint_management_system.* TO 'complaint_app'@'localhost';
-- FLUSH PRIVILEGES;

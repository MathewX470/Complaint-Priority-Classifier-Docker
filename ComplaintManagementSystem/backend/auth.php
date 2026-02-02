<?php
/**
 * Authentication Handler
 * Smart Complaint Management System
 */

require_once 'config.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Register a new user
     */
    public function register($fullName, $email, $password, $phone = null) {
        try {
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already registered'];
            }
            
            // Validate password strength
            if (strlen($password) < 8) {
                return ['success' => false, 'message' => 'Password must be at least 8 characters'];
            }
            
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert user
            $stmt = $this->db->prepare(
                "INSERT INTO users (full_name, email, password_hash, phone, role) 
                 VALUES (?, ?, ?, ?, 'user')"
            );
            $stmt->execute([$fullName, $email, $passwordHash, $phone]);
            
            return [
                'success' => true, 
                'message' => 'Registration successful',
                'user_id' => $this->db->lastInsertId()
            ];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare(
                "SELECT user_id, full_name, email, password_hash, role, is_active 
                 FROM users WHERE email = ?"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            if (!$user['is_active']) {
                return ['success' => false, 'message' => 'Account is deactivated'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Update last login
            $stmt = $this->db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
            
            // Set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            
            // Create session record
            $this->createSession($user['user_id']);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'role' => $user['role']
            ];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Delete session from database
        if (isset($_SESSION['user_id'])) {
            $this->deleteSession($_SESSION['user_id']);
        }
        
        // Destroy session
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    /**
     * Create session record
     */
    private function createSession($userId) {
        try {
            $sessionId = session_id();
            $expiresAt = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = $this->db->prepare(
                "INSERT INTO user_sessions (session_id, user_id, expires_at, ip_address, user_agent) 
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE expires_at = ?, ip_address = ?, user_agent = ?"
            );
            $stmt->execute([
                $sessionId, $userId, $expiresAt, $ipAddress, $userAgent,
                $expiresAt, $ipAddress, $userAgent
            ]);
        } catch(PDOException $e) {
            error_log("Session creation failed: " . $e->getMessage());
        }
    }
    
    /**
     * Delete session record
     */
    private function deleteSession($userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch(PDOException $e) {
            error_log("Session deletion failed: " . $e->getMessage());
        }
    }
    
    /**
     * Clean expired sessions
     */
    public function cleanExpiredSessions() {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE expires_at < CURRENT_TIMESTAMP");
            $stmt->execute();
        } catch(PDOException $e) {
            error_log("Session cleanup failed: " . $e->getMessage());
        }
    }
}
?>

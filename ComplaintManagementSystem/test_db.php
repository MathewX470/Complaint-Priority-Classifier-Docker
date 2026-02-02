<?php
// Test database connection and check admin user
require_once 'backend/config.php';

echo "=== Database Connection Test ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    echo "✓ Database connection successful!\n\n";
    
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Users table exists\n\n";
        
        // Check admin user
        $stmt = $db->prepare("SELECT user_id, full_name, email, role FROM users WHERE email = ?");
        $stmt->execute(['admin@complaint.com']);
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "✓ Admin user found:\n";
            echo "  User ID: " . $admin['user_id'] . "\n";
            echo "  Name: " . $admin['full_name'] . "\n";
            echo "  Email: " . $admin['email'] . "\n";
            echo "  Role: " . $admin['role'] . "\n\n";
            
            // Test password hash
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE email = ?");
            $stmt->execute(['admin@complaint.com']);
            $result = $stmt->fetch();
            
            if (password_verify('Admin@123', $result['password_hash'])) {
                echo "✓ Password verification SUCCESSFUL!\n";
                echo "  You should be able to login with:\n";
                echo "  Email: admin@complaint.com\n";
                echo "  Password: Admin@123\n\n";
            } else {
                echo "✗ Password verification FAILED!\n";
                echo "  The password hash might be incorrect.\n\n";
                echo "  Fixing the password...\n";
                
                // Fix the password
                $newHash = password_hash('Admin@123', PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $stmt->execute([$newHash, 'admin@complaint.com']);
                
                echo "✓ Password has been reset. Try logging in now!\n\n";
            }
        } else {
            echo "✗ Admin user NOT found!\n";
            echo "  Creating admin user...\n\n";
            
            $passwordHash = password_hash('Admin@123', PASSWORD_BCRYPT);
            $stmt = $db->prepare(
                "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute(['System Administrator', 'admin@complaint.com', $passwordHash, 'admin']);
            
            echo "✓ Admin user created successfully!\n";
            echo "  Email: admin@complaint.com\n";
            echo "  Password: Admin@123\n\n";
        }
        
        // List all users
        echo "All users in database:\n";
        $stmt = $db->query("SELECT user_id, full_name, email, role FROM users");
        while ($user = $stmt->fetch()) {
            echo "  - {$user['full_name']} ({$user['email']}) - Role: {$user['role']}\n";
        }
        
    } else {
        echo "✗ Users table does NOT exist!\n";
        echo "  Please import database/schema.sql first.\n";
    }
    
} catch(Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>

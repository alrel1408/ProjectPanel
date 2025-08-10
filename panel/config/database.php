<?php
/**
 * Database Configuration for AlrelShop Panel
 * Created by: AlrelShop
 * Version: 1.0
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'alrelshop_panel';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Database Setup Script
function setupDatabase() {
    try {
        $pdo = new PDO("mysql:host=localhost", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS alrelshop_panel");
        $pdo->exec("USE alrelshop_panel");
        
        // Create servers table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS servers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                server_name VARCHAR(100) NOT NULL,
                server_ip VARCHAR(45) NOT NULL UNIQUE,
                server_location VARCHAR(100) DEFAULT 'Unknown',
                server_status ENUM('online', 'offline', 'maintenance') DEFAULT 'offline',
                max_users INT DEFAULT 100,
                current_users INT DEFAULT 0,
                cpu_usage DECIMAL(5,2) DEFAULT 0,
                memory_usage DECIMAL(5,2) DEFAULT 0,
                disk_usage DECIMAL(5,2) DEFAULT 0,
                load_average VARCHAR(20) DEFAULT '0',
                uptime VARCHAR(100) DEFAULT '',
                api_key VARCHAR(64) NOT NULL,
                services JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Create accounts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS accounts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                server_id INT NOT NULL,
                username VARCHAR(50) NOT NULL,
                password VARCHAR(100) NOT NULL,
                service_type ENUM('ssh', 'openvpn', 'xray_vmess', 'xray_vless', 'xray_trojan', 'shadowsocks') NOT NULL,
                max_connections INT DEFAULT 1,
                expired_date DATE NOT NULL,
                status ENUM('active', 'expired', 'suspended') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
                UNIQUE KEY unique_account (server_id, username, service_type)
            )
        ");
        
        // Create users table (panel users)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'reseller', 'user') DEFAULT 'user',
                balance DECIMAL(10,2) DEFAULT 0,
                status ENUM('active', 'suspended') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Create transactions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                server_id INT NOT NULL,
                account_id INT,
                transaction_type ENUM('purchase', 'renewal', 'trial') NOT NULL,
                service_type VARCHAR(50) NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                duration_days INT NOT NULL,
                status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL
            )
        ");
        
        // Create api_logs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS api_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                server_ip VARCHAR(45) NOT NULL,
                action VARCHAR(50) NOT NULL,
                request_data JSON,
                response_data JSON,
                status_code INT DEFAULT 200,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default admin user
        $admin_password = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->exec("
            INSERT IGNORE INTO users (username, email, password, role) 
            VALUES ('admin', 'admin@alrelshop.my.id', '$admin_password', 'admin')
        ");
        
        echo "Database setup completed successfully!\n";
        
    } catch(PDOException $e) {
        echo "Database setup failed: " . $e->getMessage() . "\n";
    }
}

// Run setup if called directly
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    setupDatabase();
}
?>

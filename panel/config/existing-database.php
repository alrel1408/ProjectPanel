<?php
/**
 * Existing Database Configuration for AlrelShop Panel
 * Menggunakan database yang sudah ada: dbpanelnew
 * Created by: AlrelShop
 * Version: 1.0
 */

class ExistingDatabase {
    private $host = 'localhost';
    private $db_name = 'dbpanelnew'; // Database yang sudah ada
    private $username = 'dbpanelnew'; // Username sesuai screenshot
    private $password = ''; // Password akan diisi sesuai konfigurasi
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

// Fungsi untuk menambahkan tabel baru tanpa mengubah yang sudah ada
function addNewTablesOnly() {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=dbpanelnew", "dbpanelnew", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Cek dan tambahkan tabel servers jika belum ada
        $check_servers = $pdo->query("SHOW TABLES LIKE 'vps_servers'");
        if ($check_servers->rowCount() == 0) {
            $pdo->exec("
                CREATE TABLE vps_servers (
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
                    services_status JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            echo "Table 'vps_servers' created successfully.\n";
        }
        
        // Cek dan tambahkan tabel vps_accounts jika belum ada
        $check_accounts = $pdo->query("SHOW TABLES LIKE 'vps_accounts'");
        if ($check_accounts->rowCount() == 0) {
            $pdo->exec("
                CREATE TABLE vps_accounts (
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
                    FOREIGN KEY (server_id) REFERENCES vps_servers(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_vps_account (server_id, username, service_type)
                )
            ");
            echo "Table 'vps_accounts' created successfully.\n";
        }
        
        // Cek dan tambahkan tabel vps_api_logs jika belum ada
        $check_logs = $pdo->query("SHOW TABLES LIKE 'vps_api_logs'");
        if ($check_logs->rowCount() == 0) {
            $pdo->exec("
                CREATE TABLE vps_api_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    server_ip VARCHAR(45) NOT NULL,
                    action VARCHAR(50) NOT NULL,
                    request_data JSON,
                    response_data JSON,
                    status_code INT DEFAULT 200,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            echo "Table 'vps_api_logs' created successfully.\n";
        }
        
        // Cek dan tambahkan tabel vps_commands jika belum ada
        $check_commands = $pdo->query("SHOW TABLES LIKE 'vps_commands'");
        if ($check_commands->rowCount() == 0) {
            $pdo->exec("
                CREATE TABLE vps_commands (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    server_ip VARCHAR(45) NOT NULL,
                    command TEXT NOT NULL,
                    status ENUM('pending', 'sent', 'executed', 'failed') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    executed_at TIMESTAMP NULL DEFAULT NULL
                )
            ");
            echo "Table 'vps_commands' created successfully.\n";
        }
        
        echo "Database integration completed successfully! Existing tables preserved.\n";
        
    } catch(PDOException $e) {
        echo "Database integration failed: " . $e->getMessage() . "\n";
    }
}

// Fungsi untuk mengecek koneksi ke database existing
function testExistingConnection($password = '') {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=dbpanelnew", "dbpanelnew", $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test query
        $result = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = 'dbpanelnew'");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'message' => 'Connection successful',
            'existing_tables' => $data['table_count']
        ];
        
    } catch(PDOException $e) {
        return [
            'success' => false,
            'message' => 'Connection failed: ' . $e->getMessage()
        ];
    }
}

// Fungsi untuk mendapatkan info tabel yang sudah ada
function getExistingTablesInfo() {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=dbpanelnew", "dbpanelnew", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $result = $pdo->query("SHOW TABLES");
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);
        
        $table_info = [];
        foreach ($tables as $table) {
            $count_result = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $count_result->fetch(PDO::FETCH_ASSOC)['count'];
            $table_info[$table] = $count;
        }
        
        return [
            'success' => true,
            'tables' => $table_info
        ];
        
    } catch(PDOException $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Run setup jika dipanggil langsung
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    echo "Testing connection to existing database 'dbpanelnew'...\n";
    $test = testExistingConnection();
    
    if ($test['success']) {
        echo "✓ Connection successful! Found " . $test['existing_tables'] . " existing tables.\n\n";
        echo "Adding VPS management tables (without modifying existing data)...\n";
        addNewTablesOnly();
    } else {
        echo "✗ " . $test['message'] . "\n";
    }
}
?>

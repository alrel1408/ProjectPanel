<?php
/**
 * Panel Installation Script for AlrelShop
 * Created by: AlrelShop
 * Version: 1.0
 */

// Check if already installed
if (file_exists('config/.installed')) {
    header('Location: dashboard.php');
    exit;
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

if ($_POST) {
    switch ($step) {
        case 1:
            // Database configuration step
            $host = $_POST['db_host'] ?? 'localhost';
            $username = $_POST['db_username'] ?? 'root';
            $password = $_POST['db_password'] ?? '';
            $database = $_POST['db_name'] ?? 'alrelshop_panel';
            
            try {
                // Test connection
                $pdo = new PDO("mysql:host=$host", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create database
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
                
                // Save config
                $config_content = "<?php\n";
                $config_content .= "// Database configuration\n";
                $config_content .= "define('DB_HOST', '$host');\n";
                $config_content .= "define('DB_USERNAME', '$username');\n";
                $config_content .= "define('DB_PASSWORD', '$password');\n";
                $config_content .= "define('DB_NAME', '$database');\n";
                
                file_put_contents('config/config.php', $config_content);
                header('Location: install.php?step=2');
                exit;
                
            } catch (Exception $e) {
                $error = 'Database connection failed: ' . $e->getMessage();
            }
            break;
            
        case 2:
            // Setup database tables
            try {
                require_once 'config/database.php';
                setupDatabase();
                header('Location: install.php?step=3');
                exit;
            } catch (Exception $e) {
                $error = 'Database setup failed: ' . $e->getMessage();
            }
            break;
            
        case 3:
            // Create admin account
            $admin_username = $_POST['admin_username'] ?? '';
            $admin_email = $_POST['admin_email'] ?? '';
            $admin_password = $_POST['admin_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if ($admin_password !== $confirm_password) {
                $error = 'Passwords do not match';
            } else {
                try {
                    require_once 'config/database.php';
                    $db = new Database();
                    $conn = $db->getConnection();
                    
                    $hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);
                    
                    $query = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'admin')";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':username', $admin_username);
                    $stmt->bindParam(':email', $admin_email);
                    $stmt->bindParam(':password', $hashed_password);
                    
                    if ($stmt->execute()) {
                        header('Location: install.php?step=4');
                        exit;
                    } else {
                        $error = 'Failed to create admin account';
                    }
                } catch (Exception $e) {
                    $error = 'Error: ' . $e->getMessage();
                }
            }
            break;
            
        case 4:
            // Final setup
            file_put_contents('config/.installed', date('Y-m-d H:i:s'));
            header('Location: dashboard.php');
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlrelShop Panel - Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .install-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step-indicator .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
            color: #6c757d;
        }
        .step-indicator .step.active {
            background: #007bff;
            color: white;
        }
        .step-indicator .step.completed {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-container">
            <div class="text-center text-white mb-4">
                <h1><i class="bi bi-lightning-fill"></i> AlrelShop Panel</h1>
                <p>VPS Management Panel Installation</p>
            </div>
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' ?>">1</div>
                <div class="step <?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : '' ?>">2</div>
                <div class="step <?= $step >= 3 ? ($step > 3 ? 'completed' : 'active') : '' ?>">3</div>
                <div class="step <?= $step >= 4 ? 'active' : '' ?>">4</div>
            </div>
            
            <div class="card">
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <?= $success ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php switch ($step): case 1: ?>
                        <h4 class="card-title">Database Configuration</h4>
                        <p class="text-muted">Please enter your database connection details.</p>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="db_host" class="form-label">Database Host</label>
                                <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                            </div>
                            <div class="mb-3">
                                <label for="db_username" class="form-label">Database Username</label>
                                <input type="text" class="form-control" id="db_username" name="db_username" value="root" required>
                            </div>
                            <div class="mb-3">
                                <label for="db_password" class="form-label">Database Password</label>
                                <input type="password" class="form-control" id="db_password" name="db_password">
                            </div>
                            <div class="mb-3">
                                <label for="db_name" class="form-label">Database Name</label>
                                <input type="text" class="form-control" id="db_name" name="db_name" value="alrelshop_panel" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Test Connection & Continue</button>
                        </form>
                        
                        <?php break; case 2: ?>
                        <h4 class="card-title">Database Setup</h4>
                        <p class="text-muted">Creating database tables and structure...</p>
                        
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Setting up database tables...</p>
                        </div>
                        
                        <form method="POST">
                            <button type="submit" class="btn btn-primary w-100">Setup Database</button>
                        </form>
                        
                        <?php break; case 3: ?>
                        <h4 class="card-title">Create Admin Account</h4>
                        <p class="text-muted">Create your admin account to access the panel.</p>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="admin_username" class="form-label">Admin Username</label>
                                <input type="text" class="form-control" id="admin_username" name="admin_username" required>
                            </div>
                            <div class="mb-3">
                                <label for="admin_email" class="form-label">Admin Email</label>
                                <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                            </div>
                            <div class="mb-3">
                                <label for="admin_password" class="form-label">Admin Password</label>
                                <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Admin Account</button>
                        </form>
                        
                        <?php break; case 4: ?>
                        <h4 class="card-title">Installation Complete!</h4>
                        <p class="text-muted">Your AlrelShop Panel has been successfully installed.</p>
                        
                        <div class="alert alert-success">
                            <h5><i class="bi bi-check-circle"></i> Installation Successful!</h5>
                            <p class="mb-0">Your panel is now ready to use. You can start adding servers and managing VPS accounts.</p>
                        </div>
                        
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6>VPS Installation Command:</h6>
                                <code>wget -O installer.sh https://raw.githubusercontent.com/alrel1408/ProjectPanel/main/installer.sh && chmod +x installer.sh && ./installer.sh</code>
                                <br><small class="text-muted">Run this command on your VPS to install and connect to this panel.</small>
                            </div>
                        </div>
                        
                        <form method="POST">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-box-arrow-right"></i> Go to Dashboard
                            </button>
                        </form>
                        
                        <?php break; endswitch; ?>
                </div>
            </div>
            
            <div class="text-center text-white mt-4">
                <small>&copy; 2024 AlrelShop. All rights reserved.</small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

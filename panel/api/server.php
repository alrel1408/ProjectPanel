<?php
/**
 * Server API Endpoints for AlrelShop Panel
 * Created by: AlrelShop
 * Version: 1.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

require_once '../config/database.php';

class ServerAPI {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    private function validateApiKey($api_key) {
        $query = "SELECT id FROM servers WHERE api_key = :api_key";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':api_key', $api_key);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function logApiCall($server_ip, $action, $request_data, $response_data, $status_code = 200) {
        $query = "INSERT INTO api_logs (server_ip, action, request_data, response_data, status_code) VALUES (:server_ip, :action, :request_data, :response_data, :status_code)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':server_ip', $server_ip);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':request_data', json_encode($request_data));
        $stmt->bindParam(':response_data', json_encode($response_data));
        $stmt->bindParam(':status_code', $status_code);
        $stmt->execute();
    }

    public function addServer($data) {
        try {
            // Generate API key
            $api_key = bin2hex(random_bytes(32));
            
            $query = "INSERT INTO servers (server_name, server_ip, server_location, server_status, max_users, api_key, services) VALUES (:server_name, :server_ip, :server_location, :server_status, :max_users, :api_key, :services)";
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':server_name', $data['server_name']);
            $stmt->bindParam(':server_ip', $data['server_ip']);
            $stmt->bindParam(':server_location', $data['server_location']);
            $stmt->bindParam(':server_status', $data['server_status']);
            $stmt->bindParam(':max_users', $data['max_users']);
            $stmt->bindParam(':api_key', $api_key);
            $stmt->bindParam(':services', json_encode($data['services']));
            
            if ($stmt->execute()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Server registered successfully',
                    'server_id' => $this->conn->lastInsertId(),
                    'api_key' => $api_key
                ];
                $this->logApiCall($data['server_ip'], 'add_server', $data, $response);
                return $response;
            }
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'message' => 'Failed to register server: ' . $e->getMessage()
            ];
            $this->logApiCall($data['server_ip'], 'add_server', $data, $response, 500);
            return $response;
        }
    }

    public function updateServerStatus($data) {
        try {
            $query = "UPDATE servers SET 
                        current_users = :current_users,
                        cpu_usage = :cpu_usage, 
                        memory_usage = :memory_usage, 
                        disk_usage = :disk_usage, 
                        load_average = :load_average,
                        uptime = :uptime,
                        server_status = 'online',
                        updated_at = CURRENT_TIMESTAMP
                      WHERE server_ip = :server_ip";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':server_ip', $data['server_ip']);
            $stmt->bindParam(':current_users', $data['active_users']);
            $stmt->bindParam(':cpu_usage', $data['cpu_usage']);
            $stmt->bindParam(':memory_usage', $data['memory_usage']);
            $stmt->bindParam(':disk_usage', $data['disk_usage']);
            $stmt->bindParam(':load_average', $data['load_average']);
            $stmt->bindParam(':uptime', $data['uptime']);
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Status updated'];
                $this->logApiCall($data['server_ip'], 'update_status', $data, $response);
                return $response;
            }
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'message' => 'Failed to update status: ' . $e->getMessage()
            ];
            $this->logApiCall($data['server_ip'], 'update_status', $data, $response, 500);
            return $response;
        }
    }

    public function getServerCommands($server_ip) {
        try {
            // Get pending commands for this server (you can implement a commands queue table)
            $query = "SELECT * FROM server_commands WHERE server_ip = :server_ip AND status = 'pending' ORDER BY created_at ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':server_ip', $server_ip);
            $stmt->execute();
            
            $commands = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Mark commands as sent
            if (!empty($commands)) {
                $command_ids = array_column($commands, 'id');
                $placeholders = str_repeat('?,', count($command_ids) - 1) . '?';
                $update_query = "UPDATE server_commands SET status = 'sent' WHERE id IN ($placeholders)";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->execute($command_ids);
            }
            
            $response = [
                'status' => 'success',
                'data' => array_column($commands, 'command')
            ];
            
            $this->logApiCall($server_ip, 'get_commands', [], $response);
            return $response;
            
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'message' => 'Failed to get commands: ' . $e->getMessage()
            ];
            $this->logApiCall($server_ip, 'get_commands', [], $response, 500);
            return $response;
        }
    }

    public function createAccount($data) {
        try {
            // Get server info
            $server_query = "SELECT id FROM servers WHERE server_ip = :server_ip";
            $server_stmt = $this->conn->prepare($server_query);
            $server_stmt->bindParam(':server_ip', $data['server_ip']);
            $server_stmt->execute();
            $server = $server_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$server) {
                throw new Exception('Server not found');
            }
            
            // Insert account
            $query = "INSERT INTO accounts (server_id, username, password, service_type, max_connections, expired_date, status) 
                      VALUES (:server_id, :username, :password, :service_type, :max_connections, :expired_date, 'active')";
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':server_id', $server['id']);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':password', $data['password']);
            $stmt->bindParam(':service_type', $data['service_type'] ?? 'ssh');
            $stmt->bindParam(':max_connections', $data['limit'] ?? 1);
            $stmt->bindParam(':expired_date', $data['expired_date']);
            
            if ($stmt->execute()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Account created successfully',
                    'account_id' => $this->conn->lastInsertId()
                ];
                $this->logApiCall($data['server_ip'], 'create_account', $data, $response);
                return $response;
            }
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'message' => 'Failed to create account: ' . $e->getMessage()
            ];
            $this->logApiCall($data['server_ip'], 'create_account', $data, $response, 500);
            return $response;
        }
    }

    public function getServers() {
        try {
            $query = "SELECT *, 
                        CASE 
                            WHEN updated_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 'online'
                            ELSE 'offline'
                        END as real_status
                      FROM servers ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($servers as &$server) {
                $server['services'] = json_decode($server['services'], true);
                
                // Get account count
                $count_query = "SELECT COUNT(*) as total_accounts FROM accounts WHERE server_id = :server_id AND status = 'active'";
                $count_stmt = $this->conn->prepare($count_query);
                $count_stmt->bindParam(':server_id', $server['id']);
                $count_stmt->execute();
                $count = $count_stmt->fetch(PDO::FETCH_ASSOC);
                $server['total_accounts'] = $count['total_accounts'];
            }
            
            return [
                'status' => 'success',
                'data' => $servers
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to get servers: ' . $e->getMessage()
            ];
        }
    }
}

// Handle API requests
$api = new ServerAPI();
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Parse the request
$path_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));
$endpoint = end($path_parts);

// Get request data
$input = file_get_contents('php://input');
$data = json_decode($input, true) ?? [];

// Validate API key for protected endpoints
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? '';
$protected_endpoints = ['add', 'status', 'commands'];

if (in_array($endpoint, $protected_endpoints) && !$api->validateApiKey($api_key)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API key']);
    exit;
}

switch ($method) {
    case 'POST':
        switch ($endpoint) {
            case 'add':
                echo json_encode($api->addServer($data));
                break;
            case 'status':
                echo json_encode($api->updateServerStatus($data));
                break;
            case 'create_account':
                echo json_encode($api->createAccount($data));
                break;
            default:
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
        }
        break;
        
    case 'GET':
        switch ($endpoint) {
            case 'commands':
                $server_ip = $_GET['ip'] ?? '';
                echo json_encode($api->getServerCommands($server_ip));
                break;
            case 'list':
                echo json_encode($api->getServers());
                break;
            default:
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>

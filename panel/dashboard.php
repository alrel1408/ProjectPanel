<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlrelShop Panel - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 0.375rem;
            margin: 0.125rem 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }
        .server-card {
            transition: transform 0.2s;
        }
        .server-card:hover {
            transform: translateY(-2px);
        }
        .status-online {
            color: #28a745;
        }
        .status-offline {
            color: #dc3545;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <h4><i class="bi bi-lightning-fill"></i> AlrelShop</h4>
                        <small>VPS Management Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#servers">
                                <i class="bi bi-server"></i> Servers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#accounts">
                                <i class="bi bi-people"></i> Accounts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#transactions">
                                <i class="bi bi-credit-card"></i> Transactions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#settings">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="text-white">
                    
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-2"></i>
                            <strong>Admin</strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="#profile">Profile</a></li>
                            <li><a class="dropdown-item" href="#logout">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" onclick="addServer()">
                            <i class="bi bi-plus"></i> Add Server
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Total Servers</h5>
                                        <h2 id="total-servers">0</h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-server fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Online Servers</h5>
                                        <h2 id="online-servers">0</h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-check-circle fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Total Accounts</h5>
                                        <h2 id="total-accounts">0</h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-people fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Active Users</h5>
                                        <h2 id="active-users">0</h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-person-check fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Servers List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Server Status</h5>
                                <button class="btn btn-outline-primary btn-sm" onclick="refreshServers()">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="servers-container" class="row">
                                    <!-- Server cards will be populated here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary" onclick="createTrialAccount()">
                                        <i class="bi bi-gift"></i> Create Trial Account
                                    </button>
                                    <button class="btn btn-outline-info" onclick="viewApiLogs()">
                                        <i class="bi bi-list-ul"></i> View API Logs
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="backupData()">
                                        <i class="bi bi-download"></i> Backup Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Installation Command</h5>
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="install-command" readonly 
                                           value="wget -O installer.sh https://raw.githubusercontent.com/alrel1408/ProjectPanel/main/installer.sh && chmod +x installer.sh && ./installer.sh">
                                    <button class="btn btn-outline-secondary" onclick="copyInstallCommand()">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Jalankan command ini di VPS baru untuk instalasi otomatis</small>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Server Modal -->
    <div class="modal fade" id="addServerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Server</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addServerForm">
                        <div class="mb-3">
                            <label for="serverName" class="form-label">Server Name</label>
                            <input type="text" class="form-control" id="serverName" required>
                        </div>
                        <div class="mb-3">
                            <label for="serverIP" class="form-label">Server IP</label>
                            <input type="text" class="form-control" id="serverIP" required>
                        </div>
                        <div class="mb-3">
                            <label for="serverLocation" class="form-label">Location</label>
                            <input type="text" class="form-control" id="serverLocation" value="Auto Detected">
                        </div>
                        <div class="mb-3">
                            <label for="maxUsers" class="form-label">Max Users</label>
                            <input type="number" class="form-control" id="maxUsers" value="100">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveServer()">Save Server</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dashboard Functions
        let servers = [];

        function loadDashboard() {
            refreshServers();
            updateStats();
            
            // Auto refresh every 30 seconds
            setInterval(() => {
                refreshServers();
                updateStats();
            }, 30000);
        }

        function refreshServers() {
            fetch('api/server.php/list')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        servers = data.data;
                        renderServers();
                        updateStats();
                    }
                })
                .catch(error => {
                    console.error('Error fetching servers:', error);
                });
        }

        function renderServers() {
            const container = document.getElementById('servers-container');
            container.innerHTML = '';

            servers.forEach(server => {
                const statusClass = server.real_status === 'online' ? 'status-online' : 'status-offline';
                const statusIcon = server.real_status === 'online' ? 'check-circle-fill' : 'x-circle-fill';
                
                const serverCard = `
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card server-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title">${server.server_name}</h6>
                                    <i class="bi bi-${statusIcon} ${statusClass}"></i>
                                </div>
                                <p class="card-text">
                                    <small class="text-muted">IP: ${server.server_ip}</small><br>
                                    <small class="text-muted">Location: ${server.server_location}</small>
                                </p>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <small class="text-muted">CPU</small>
                                        <div class="fw-bold">${server.cpu_usage || 0}%</div>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">RAM</small>
                                        <div class="fw-bold">${server.memory_usage || 0}%</div>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">Users</small>
                                        <div class="fw-bold">${server.current_users || 0}/${server.max_users}</div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Accounts: ${server.total_accounts || 0} | 
                                        Uptime: ${server.uptime || 'Unknown'}
                                    </small>
                                </div>
                                <div class="mt-2">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Services:</small>
                                            <div class="service-indicators">
                                                ${renderServiceIndicators(server.services_status || {})}
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Load: ${server.load_average || '0'}</small><br>
                                            <small class="text-muted">Disk: ${server.disk_usage || 0}%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="btn-group w-100" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="manageServer(${server.id})">
                                        <i class="bi bi-gear"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="createAccount(${server.id})">
                                        <i class="bi bi-person-plus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="restartServer(${server.id})">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += serverCard;
            });
        }

        function updateStats() {
            const totalServers = servers.length;
            const onlineServers = servers.filter(s => s.real_status === 'online').length;
            const totalAccounts = servers.reduce((sum, s) => sum + (s.total_accounts || 0), 0);
            const activeUsers = servers.reduce((sum, s) => sum + (s.current_users || 0), 0);

            document.getElementById('total-servers').textContent = totalServers;
            document.getElementById('online-servers').textContent = onlineServers;
            document.getElementById('total-accounts').textContent = totalAccounts;
            document.getElementById('active-users').textContent = activeUsers;
        }

        function addServer() {
            const modal = new bootstrap.Modal(document.getElementById('addServerModal'));
            modal.show();
        }

        function saveServer() {
            const serverData = {
                server_name: document.getElementById('serverName').value,
                server_ip: document.getElementById('serverIP').value,
                server_location: document.getElementById('serverLocation').value,
                server_status: 'online',
                max_users: parseInt(document.getElementById('maxUsers').value),
                services: {
                    ssh: true,
                    openvpn: true,
                    xray_vmess: true,
                    xray_vless: true,
                    xray_trojan: true,
                    shadowsocks: true
                }
            };

            fetch('api/server.php/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(serverData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Server added successfully! API Key: ' + data.api_key);
                    bootstrap.Modal.getInstance(document.getElementById('addServerModal')).hide();
                    refreshServers();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        function copyInstallCommand() {
            const command = document.getElementById('install-command');
            command.select();
            document.execCommand('copy');
            alert('Command copied to clipboard!');
        }

        function manageServer(serverId) {
            // Show detailed server information
            showServerDetails(serverId);
        }

        function createAccount(serverId) {
            // Implement account creation
            alert('Create account for server ID: ' + serverId);
        }

        function restartServer(serverId) {
            // Implement server restart
            if (confirm('Are you sure you want to restart this server?')) {
                alert('Restart command sent to server ID: ' + serverId);
            }
        }

        function createTrialAccount() {
            alert('Trial account creation feature');
        }

        function viewApiLogs() {
            alert('API logs viewer');
        }

        function backupData() {
            alert('Data backup feature');
        }

        // Function to render service indicators
        function renderServiceIndicators(services) {
            const serviceList = [
                { key: 'ssh', name: 'SSH', color: 'primary' },
                { key: 'dropbear', name: 'Dropbear', color: 'info' },
                { key: 'openvpn_ssl', name: 'OVPN', color: 'success' },
                { key: 'nginx', name: 'Nginx', color: 'warning' },
                { key: 'haproxy', name: 'HAProxy', color: 'secondary' },
                { key: 'xray', name: 'Xray', color: 'danger' },
                { key: 'badvpn_7100', name: 'BadVPN', color: 'dark' }
            ];
            
            let indicators = '';
            serviceList.forEach(service => {
                const status = services[service.key] || 'inactive';
                const badge_class = status === 'active' ? `bg-${service.color}` : 'bg-secondary';
                const opacity = status === 'active' ? '1' : '0.5';
                
                indicators += `<span class="badge ${badge_class} me-1" style="opacity: ${opacity}; font-size: 0.6em;">${service.name}</span>`;
            });
            
            return indicators;
        }

        // Function to show detailed server info
        function showServerDetails(serverId) {
            const server = servers.find(s => s.id === serverId);
            if (!server) return;
            
            const services = server.services_status || {};
            let serviceDetails = '';
            
            // Define all services from VPS
            const allServices = {
                'ssh': 'SSH Service (Port 22, 80, 443)',
                'dropbear': 'Dropbear Service (Port 109, 143, 443)',
                'openvpn_ssl': 'OpenVPN SSL (Port 443)',
                'openvpn_tcp': 'OpenVPN TCP (Port 443, 1194)',
                'openvpn_udp': 'OpenVPN UDP (Port 2200)',
                'nginx': 'Nginx Webserver (Port 80, 81, 443)',
                'haproxy': 'HAProxy Loadbalancer (Port 80, 443)',
                'xray': 'Xray Core Service',
                'badvpn_7100': 'BadVPN UDP Gateway (Port 7100)',
                'badvpn_7200': 'BadVPN UDP Gateway (Port 7200)',
                'badvpn_7300': 'BadVPN UDP Gateway (Port 7300)',
                'cron': 'Cron Service'
            };
            
            for (const [key, name] of Object.entries(allServices)) {
                const status = services[key] || 'unknown';
                const statusClass = status === 'active' ? 'text-success' : (status === 'inactive' ? 'text-danger' : 'text-warning');
                const statusIcon = status === 'active' ? 'check-circle-fill' : (status === 'inactive' ? 'x-circle-fill' : 'question-circle-fill');
                
                serviceDetails += `
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span>${name}</span>
                        <span class="${statusClass}">
                            <i class="bi bi-${statusIcon}"></i> ${status.toUpperCase()}
                        </span>
                    </div>
                `;
            }
            
            // Show modal with server details
            const modalHTML = `
                <div class="modal fade" id="serverDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Server Details - ${server.server_name}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Server Information</h6>
                                        <table class="table table-sm">
                                            <tr><td>IP Address:</td><td>${server.server_ip}</td></tr>
                                            <tr><td>Location:</td><td>${server.server_location}</td></tr>
                                            <tr><td>Status:</td><td><span class="badge bg-${server.real_status === 'online' ? 'success' : 'danger'}">${server.real_status}</span></td></tr>
                                            <tr><td>CPU Usage:</td><td>${server.cpu_usage || 0}%</td></tr>
                                            <tr><td>Memory Usage:</td><td>${server.memory_usage || 0}%</td></tr>
                                            <tr><td>Disk Usage:</td><td>${server.disk_usage || 0}%</td></tr>
                                            <tr><td>Load Average:</td><td>${server.load_average || '0'}</td></tr>
                                            <tr><td>Uptime:</td><td>${server.uptime || 'Unknown'}</td></tr>
                                            <tr><td>Active Users:</td><td>${server.current_users || 0}/${server.max_users}</td></tr>
                                            <tr><td>Total Accounts:</td><td>${server.total_accounts || 0}</td></tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Service Status</h6>
                                        <div class="service-list" style="max-height: 400px; overflow-y: auto;">
                                            ${serviceDetails}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-warning" onclick="restartAllServices(${server.id})">Restart All Services</button>
                                <button type="button" class="btn btn-primary" onclick="refreshServerStatus(${server.id})">Refresh Status</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('serverDetailsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('serverDetailsModal'));
            modal.show();
        }

        function restartAllServices(serverId) {
            if (confirm('Are you sure you want to restart all services on this server?')) {
                alert('Restart all services command sent to server ID: ' + serverId);
                // Implement API call to restart all services
            }
        }

        function refreshServerStatus(serverId) {
            alert('Refreshing server status for ID: ' + serverId);
            // Implement API call to refresh server status
            refreshServers();
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', loadDashboard);
    </script>
</body>
</html>

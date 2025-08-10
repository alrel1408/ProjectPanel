#!/bin/bash

# =================================================
# VPS Auto Installer Script for Panel Integration
# Created by: AlrelShop
# Version: 1.0
# =================================================

# Color definitions
Green="\e[92;1m"
RED="\033[31m"
YELLOW="\033[33m"
BLUE="\033[36m"
FONT="\033[0m"
GREENBG="\033[42;37m"
REDBG="\033[41;37m"
OK="${Green}✓${FONT}"
ERROR="${RED}[ERROR]${FONT}"
GRAY="\e[1;30m"
NC='\e[0m'
red='\e[1;31m'
green='\e[0;32m'

# Global variables
PANEL_URL=""
API_KEY=""
SERVER_NAME=""
VPS_IP=$(curl -sS icanhazip.com 2>/dev/null || curl -sS ifconfig.me 2>/dev/null)

clear

# Banner function
show_banner() {
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "           ${GREEN}VPS AUTO INSTALLER FOR PANEL${NC}"
    echo -e "  Developer » ALRELSHOP࿐${YELLOW} (${NC}${green} Panel Integration ${NC}${YELLOW})${NC}"
    echo -e "  » This Will Setup VPS Server For Panel Connection"
    echo -e "  Pembuat : ${green}AlrelShop࿐® ${NC}"
    echo -e "  ©Panel Integration Script ${YELLOW}(${NC} 2024 ${YELLOW})${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

# Check system compatibility
check_system() {
    echo -e "${BLUE}[INFO]${NC} Checking system compatibility..."
    
    # Check architecture
    if [[ $(uname -m) != "x86_64" ]]; then
        echo -e "${ERROR} Architecture not supported: $(uname -m)"
        exit 1
    fi
    echo -e "${OK} Architecture supported: $(uname -m)"
    
    # Check OS
    if [[ -f /etc/os-release ]]; then
        source /etc/os-release
        if [[ "$ID" == "ubuntu" ]]; then
            case "$VERSION_ID" in
                "18.04"|"20.04"|"22.04"|"24.04")
                    echo -e "${OK} Ubuntu $VERSION_ID is supported"
                    ;;
                *)
                    echo -e "${ERROR} Ubuntu $VERSION_ID is not supported"
                    exit 1
                    ;;
            esac
        elif [[ "$ID" == "debian" ]]; then
            case "$VERSION_ID" in
                "9"|"10"|"11"|"12")
                    echo -e "${OK} Debian $VERSION_ID is supported"
                    ;;
                *)
                    echo -e "${ERROR} Debian $VERSION_ID is not supported"
                    exit 1
                    ;;
            esac
        else
            echo -e "${ERROR} OS not supported: $ID"
            exit 1
        fi
    else
        echo -e "${ERROR} Cannot detect OS version"
        exit 1
    fi
}

# Get panel configuration
get_panel_config() {
    echo -e "${BLUE}[INFO]${NC} Konfigurasi Panel Connection"
    echo ""
    
    while [[ -z "$PANEL_URL" ]]; do
        read -p "Masukkan URL Panel Anda (contoh: https://panel.domain.com): " PANEL_URL
        if [[ ! "$PANEL_URL" =~ ^https?:// ]]; then
            echo -e "${ERROR} URL harus dimulai dengan http:// atau https://"
            PANEL_URL=""
        fi
    done
    
    while [[ -z "$API_KEY" ]]; do
        read -p "Masukkan API Key Panel: " API_KEY
    done
    
    while [[ -z "$SERVER_NAME" ]]; do
        read -p "Masukkan Nama Server (contoh: VPS-SG-01): " SERVER_NAME
    done
    
    echo ""
    echo -e "${OK} Panel URL: $PANEL_URL"
    echo -e "${OK} Server Name: $SERVER_NAME"
    echo -e "${OK} VPS IP: $VPS_IP"
    echo ""
}

# Install dependencies
install_dependencies() {
    echo -e "${BLUE}[INFO]${NC} Installing dependencies..."
    
    apt update -q >/dev/null 2>&1
    apt install -y wget curl unzip jq >/dev/null 2>&1
    
    if command -v wget >/dev/null 2>&1 && command -v curl >/dev/null 2>&1; then
        echo -e "${OK} Dependencies installed successfully"
    else
        echo -e "${ERROR} Failed to install dependencies"
        exit 1
    fi
}

# Download and install main script
install_main_script() {
    echo -e "${BLUE}[INFO]${NC} Downloading and installing main VPS script..."
    
    # Download main installer
    if wget -q --timeout=30 https://raw.githubusercontent.com/alrel1408/scriptaku/main/ubu20-deb10-stable.sh; then
        chmod +x ubu20-deb10-stable.sh
        echo -e "${OK} Main script downloaded successfully"
    else
        echo -e "${ERROR} Failed to download main script"
        exit 1
    fi
    
    # Run main installer
    echo -e "${BLUE}[INFO]${NC} Running main VPS installer..."
    echo -e "${YELLOW}Note: Proses ini akan memakan waktu beberapa menit...${NC}"
    
    # Run installer in background and show progress
    (
        ./ubu20-deb10-stable.sh
        echo "INSTALL_COMPLETE" > /tmp/install_status
    ) &
    
    # Show progress
    while [[ ! -f /tmp/install_status ]]; do
        echo -ne "${YELLOW}Installing VPS services... ⠋${NC}\r"
        sleep 0.5
        echo -ne "${YELLOW}Installing VPS services... ⠙${NC}\r"
        sleep 0.5
        echo -ne "${YELLOW}Installing VPS services... ⠹${NC}\r"
        sleep 0.5
        echo -ne "${YELLOW}Installing VPS services... ⠸${NC}\r"
        sleep 0.5
        echo -ne "${YELLOW}Installing VPS services... ⠼${NC}\r"
        sleep 0.5
        echo -ne "${YELLOW}Installing VPS services... ⠴${NC}\r"
        sleep 0.5
        echo -ne "${YELLOW}Installing VPS services... ⠦${NC}\r"
        sleep 0.5
        echo -ne "${YELLOW}Installing VPS services... ⠧${NC}\r"
        sleep 0.5
        echo -ne "${YELLOW}Installing VPS services... ⠇${NC}\r"
        sleep 0.5
        echo -ne "${YELLOW}Installing VPS services... ⠏${NC}\r"
        sleep 0.5
    done
    
    wait
    rm -f /tmp/install_status
    echo -e "\n${OK} Main VPS script installed successfully"
}

# Setup panel integration
setup_panel_integration() {
    echo -e "${BLUE}[INFO]${NC} Setting up panel integration..."
    
    # Create panel config directory
    mkdir -p /etc/panel-config
    
    # Create panel configuration file
    cat > /etc/panel-config/config.json << EOF
{
    "panel_url": "$PANEL_URL",
    "api_key": "$API_KEY",
    "server_name": "$SERVER_NAME",
    "server_ip": "$VPS_IP",
    "install_date": "$(date '+%Y-%m-%d %H:%M:%S')",
    "version": "1.0"
}
EOF
    
    # Download panel connector script
    cat > /usr/local/bin/panel-connector << 'SCRIPT_EOF'
#!/bin/bash

# Panel Connector Script
CONFIG_FILE="/etc/panel-config/config.json"

if [[ ! -f "$CONFIG_FILE" ]]; then
    echo "Panel config not found!"
    exit 1
fi

# Read config
PANEL_URL=$(jq -r '.panel_url' $CONFIG_FILE)
API_KEY=$(jq -r '.api_key' $CONFIG_FILE)
SERVER_NAME=$(jq -r '.server_name' $CONFIG_FILE)
SERVER_IP=$(jq -r '.server_ip' $CONFIG_FILE)

# Function to register server to panel
register_server() {
    # Register server dengan format untuk panel AlrelShop
    RESPONSE=$(curl -s -X POST "$PANEL_URL/api/server/add" \
        -H "Content-Type: application/json" \
        -H "X-API-Key: $API_KEY" \
        -d "{
            \"server_name\": \"$SERVER_NAME\",
            \"server_ip\": \"$SERVER_IP\",
            \"server_location\": \"Auto Detected\",
            \"server_status\": \"online\",
            \"max_users\": 100,
            \"services\": {
                \"ssh\": true,
                \"dropbear\": true,
                \"openvpn_ssl\": true,
                \"openvpn_tcp\": true,
                \"openvpn_udp\": true,
                \"nginx_webserver\": true,
                \"haproxy_loadbalancer\": true,
                \"xray_vmess_tls\": true,
                \"xray_vmess_grpc\": true,
                \"xray_vmess_none_tls\": true,
                \"xray_vless_tls\": true,
                \"xray_vless_grpc\": true,
                \"xray_vless_none_tls\": true,
                \"xray_trojan_grpc\": true,
                \"xray_trojan_ws\": true,
                \"xray_shadowsocks_ws\": true,
                \"xray_shadowsocks_grpc\": true,
                \"badVPN_7100\": true,
                \"badVPN_7200\": true,
                \"badVPN_7300\": true
            }
        }")
    
    if echo "$RESPONSE" | grep -q "success\|ok\|registered"; then
        echo "Server registered successfully"
        return 0
    else
        echo "Failed to register server: $RESPONSE"
        return 1
    fi
}

# Function to send server status
send_status() {
    # Get system info
    CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | sed 's/%us,//' | sed 's/%//')
    MEMORY_USAGE=$(free | grep Mem | awk '{printf("%.1f", $3/$2 * 100.0)}')
    DISK_USAGE=$(df -h / | awk 'NR==2{print $5}' | sed 's/%//')
    UPTIME=$(uptime -p)
    LOAD_AVG=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
    
    # Get active users count
    SSH_USERS=$(who | wc -l)
    TOTAL_USERS=$(cut -d: -f1 /etc/passwd | grep -v "^root$\|^daemon$\|^bin$\|^sys$\|^sync$\|^games$\|^man$\|^lp$\|^mail$\|^news$\|^uucp$\|^proxy$\|^www-data$\|^backup$\|^list$\|^irc$\|^gnats$\|^nobody$\|^systemd" | wc -l)
    
    curl -s -X POST "$PANEL_URL/api/server/status" \
        -H "Content-Type: application/json" \
        -H "X-API-Key: $API_KEY" \
        -d "{
            \"server_ip\": \"$SERVER_IP\",
            \"cpu_usage\": $CPU_USAGE,
            \"memory_usage\": $MEMORY_USAGE,
            \"disk_usage\": $DISK_USAGE,
            \"load_average\": \"$LOAD_AVG\",
            \"uptime\": \"$UPTIME\",
            \"active_users\": $SSH_USERS,
            \"total_accounts\": $TOTAL_USERS,
            \"last_update\": \"$(date '+%Y-%m-%d %H:%M:%S')\",
            \"services_status\": {
                \"ssh\": \"$(systemctl is-active ssh 2>/dev/null || echo 'inactive')\",
                \"dropbear\": \"$(systemctl is-active dropbear 2>/dev/null || echo 'inactive')\",
                \"openvpn_ssl\": \"$(systemctl is-active openvpn@server 2>/dev/null || echo 'inactive')\",
                \"openvpn_tcp\": \"$(systemctl is-active openvpn-server@server 2>/dev/null || echo 'inactive')\",
                \"openvpn_udp\": \"$(systemctl is-active openvpn-udp 2>/dev/null || echo 'inactive')\",
                \"nginx\": \"$(systemctl is-active nginx 2>/dev/null || echo 'inactive')\",
                \"haproxy\": \"$(systemctl is-active haproxy 2>/dev/null || echo 'inactive')\",
                \"xray\": \"$(systemctl is-active xray 2>/dev/null || echo 'inactive')\",
                \"badVPN_7100\": \"$(ps aux | grep -v grep | grep 'badvpn.*7100' >/dev/null && echo 'active' || echo 'inactive')\",
                \"badVPN_7200\": \"$(ps aux | grep -v grep | grep 'badvpn.*7200' >/dev/null && echo 'active' || echo 'inactive')\",
                \"badVPN_7300\": \"$(ps aux | grep -v grep | grep 'badvpn.*7300' >/dev/null && echo 'active' || echo 'inactive')\",
                \"crons\": \"$(systemctl is-active cron 2>/dev/null || echo 'inactive')\"
            }
        }" >/dev/null 2>&1

# Function to get panel commands
get_commands() {
    curl -s -X GET "$PANEL_URL/api/server/commands?ip=$SERVER_IP" \
        -H "X-API-Key: $API_KEY" | jq -r '.data[]' 2>/dev/null
}

# Function to create user account via API
create_user_account() {
    local username="$1"
    local password="$2"
    local expired_date="$3"
    local limit="$4"
    
    # Create user account on server
    useradd -M -N -s /bin/false "$username" 2>/dev/null
    echo "$username:$password" | chpasswd
    
    # Set expiry date
    chage -E "$expired_date" "$username"
    
    # Notify panel about new account
    curl -s -X POST "$PANEL_URL/api/account/created" \
        -H "Content-Type: application/json" \
        -H "X-API-Key: $API_KEY" \
        -d "{
            \"server_ip\": \"$SERVER_IP\",
            \"username\": \"$username\",
            \"password\": \"$password\",
            \"expired_date\": \"$expired_date\",
            \"limit\": $limit,
            \"created_at\": \"$(date '+%Y-%m-%d %H:%M:%S')\"
        }" >/dev/null 2>&1
}

case "$1" in
    "register")
        register_server
        ;;
    "status")
        send_status
        ;;
    "commands")
        get_commands
        ;;
    *)
        echo "Usage: $0 {register|status|commands}"
        exit 1
        ;;
esac
SCRIPT_EOF

    chmod +x /usr/local/bin/panel-connector
    
    # Create systemd service for panel monitoring
    cat > /etc/systemd/system/panel-monitor.service << 'SERVICE_EOF'
[Unit]
Description=Panel Monitor Service
After=network.target

[Service]
Type=forking
ExecStart=/usr/local/bin/panel-monitor.sh
Restart=always
RestartSec=30

[Install]
WantedBy=multi-user.target
SERVICE_EOF

    # Create monitoring script
    cat > /usr/local/bin/panel-monitor.sh << 'MONITOR_EOF'
#!/bin/bash

while true; do
    # Send status every 5 minutes
    /usr/local/bin/panel-connector status
    
    # Check for commands every minute
    commands=$(/usr/local/bin/panel-connector commands)
    if [[ -n "$commands" ]]; then
        echo "$commands" | while read -r cmd; do
            if [[ -n "$cmd" ]]; then
                eval "$cmd" 2>&1
            fi
        done
    fi
    
    sleep 60
done &
MONITOR_EOF

    chmod +x /usr/local/bin/panel-monitor.sh
    
    # Enable and start panel monitor service
    systemctl daemon-reload
    systemctl enable panel-monitor.service >/dev/null 2>&1
    systemctl start panel-monitor.service >/dev/null 2>&1
    
    echo -e "${OK} Panel integration setup completed"
}

# Register server to panel
register_to_panel() {
    echo -e "${BLUE}[INFO]${NC} Registering server to panel..."
    
    if /usr/local/bin/panel-connector register; then
        echo -e "${OK} Server registered to panel successfully"
    else
        echo -e "${YELLOW}[WARNING]${NC} Failed to register to panel (akan dicoba otomatis nanti)"
    fi
}

# Create management scripts
create_management_scripts() {
    echo -e "${BLUE}[INFO]${NC} Creating management scripts..."
    
    # Create panel management script
    cat > /usr/local/bin/panel-manage << 'MANAGE_EOF'
#!/bin/bash

Green="\e[92;1m"
RED="\033[31m"
YELLOW="\033[33m"
BLUE="\033[36m"
NC='\e[0m'

CONFIG_FILE="/etc/panel-config/config.json"

show_menu() {
    clear
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "           ${Green}PANEL MANAGEMENT MENU${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e ""
    echo -e "  ${BLUE}1.${NC} Show Panel Status"
    echo -e "  ${BLUE}2.${NC} Register Server to Panel"
    echo -e "  ${BLUE}3.${NC} Send Status to Panel"
    echo -e "  ${BLUE}4.${NC} View Panel Config"
    echo -e "  ${BLUE}5.${NC} Restart Panel Services"
    echo -e "  ${BLUE}6.${NC} View Service Logs"
    echo -e "  ${RED}0.${NC} Exit"
    echo -e ""
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

while true; do
    show_menu
    read -p "Select option [0-6]: " choice
    
    case $choice in
        1)
            echo -e "\n${BLUE}[INFO]${NC} Panel Service Status:"
            systemctl status panel-monitor.service --no-pager
            read -n 1 -s -r -p "Press any key to continue..."
            ;;
        2)
            echo -e "\n${BLUE}[INFO]${NC} Registering server to panel..."
            /usr/local/bin/panel-connector register
            read -n 1 -s -r -p "Press any key to continue..."
            ;;
        3)
            echo -e "\n${BLUE}[INFO]${NC} Sending status to panel..."
            /usr/local/bin/panel-connector status
            echo "Status sent!"
            read -n 1 -s -r -p "Press any key to continue..."
            ;;
        4)
            echo -e "\n${BLUE}[INFO]${NC} Panel Configuration:"
            if [[ -f "$CONFIG_FILE" ]]; then
                jq '.' "$CONFIG_FILE"
            else
                echo "Config file not found!"
            fi
            read -n 1 -s -r -p "Press any key to continue..."
            ;;
        5)
            echo -e "\n${BLUE}[INFO]${NC} Restarting panel services..."
            systemctl restart panel-monitor.service
            echo "Services restarted!"
            read -n 1 -s -r -p "Press any key to continue..."
            ;;
        6)
            echo -e "\n${BLUE}[INFO]${NC} Service Logs:"
            journalctl -u panel-monitor.service --no-pager -n 20
            read -n 1 -s -r -p "Press any key to continue..."
            ;;
        0)
            echo -e "\n${Green}Goodbye!${NC}"
            exit 0
            ;;
        *)
            echo -e "\n${RED}Invalid option!${NC}"
            sleep 2
            ;;
    esac
done
MANAGE_EOF

    chmod +x /usr/local/bin/panel-manage
    ln -sf /usr/local/bin/panel-manage /usr/bin/panel-manage
    
    echo -e "${OK} Management scripts created"
}

# Cleanup function
cleanup() {
    echo -e "${BLUE}[INFO]${NC} Cleaning up temporary files..."
    rm -f ubu20-deb10-stable.sh
    rm -f /tmp/install_status
    echo -e "${OK} Cleanup completed"
}

# Main installation function
main() {
    show_banner
    
    echo -e "${BLUE}[INFO]${NC} Starting VPS Panel Installer..."
    echo ""
    
    # Check if running as root
    if [[ $EUID -ne 0 ]]; then
        echo -e "${ERROR} This script must be run as root"
        exit 1
    fi
    
    # Check system
    check_system
    
    # Get panel configuration
    get_panel_config
    
    # Confirm installation
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "  ${BLUE}KONFIRMASI INSTALASI${NC}"
    echo -e "  Panel URL    : $PANEL_URL"
    echo -e "  Server Name  : $SERVER_NAME"
    echo -e "  Server IP    : $VPS_IP"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    read -p "Lanjutkan instalasi? (y/N): " confirm
    
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Instalasi dibatalkan.${NC}"
        exit 0
    fi
    
    echo ""
    echo -e "${BLUE}[INFO]${NC} Memulai proses instalasi..."
    
    # Install dependencies
    install_dependencies
    
    # Install main script
    install_main_script
    
    # Setup panel integration
    setup_panel_integration
    
    # Create management scripts
    create_management_scripts
    
    # Register to panel
    register_to_panel
    
    # Cleanup
    cleanup
    
    echo ""
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "           ${Green}INSTALASI SELESAI!${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e ""
    echo -e "  ${Green}✓${NC} VPS berhasil terinstall dan terhubung ke panel"
    echo -e "  ${Green}✓${NC} Panel URL: $PANEL_URL"
    echo -e "  ${Green}✓${NC} Server Name: $SERVER_NAME"
    echo -e "  ${Green}✓${NC} Server IP: $VPS_IP"
    echo -e ""
    echo -e "  ${BLUE}Command yang tersedia:${NC}"
    echo -e "  • ${YELLOW}menu${NC}         - Akses menu utama VPS"
    echo -e "  • ${YELLOW}panel-manage${NC} - Kelola koneksi panel"
    echo -e ""
    echo -e "  ${BLUE}Info Panel:${NC}"
    echo -e "  • Status monitoring otomatis aktif"
    echo -e "  • Server terdaftar di panel Anda"
    echo -e "  • Logs tersedia di: journalctl -u panel-monitor.service"
    echo -e ""
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    
    echo -e "  ${YELLOW}Note:${NC} Untuk mengakses menu VPS, ketik: ${GREEN}menu${NC}"
    echo -e "  ${YELLOW}Note:${NC} Untuk panel management, ketik: ${GREEN}panel-manage${NC}"
    echo ""
}

# Run main function
main "$@"

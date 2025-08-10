#!/bin/bash

# =================================================
# VPS Service Monitor Script for AlrelShop Panel
# Created by: AlrelShop
# Version: 1.0
# =================================================

# Color definitions
Green="\e[92;1m"
RED="\033[31m"
YELLOW="\033[33m"
BLUE="\033[36m"
NC='\e[0m'

CONFIG_FILE="/etc/panel-config/config.json"
LOG_FILE="/var/log/service-monitor.log"

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Function to check service status
check_service_status() {
    local service_name="$1"
    local check_command="$2"
    
    if eval "$check_command" >/dev/null 2>&1; then
        echo "active"
    else
        echo "inactive"
    fi
}

# Function to get all service status
get_all_services_status() {
    local services_json="{"
    
    # SSH Services
    ssh_status=$(check_service_status "SSH" "systemctl is-active ssh")
    services_json+='"ssh":"'$ssh_status'",'
    
    # Dropbear
    dropbear_status=$(check_service_status "Dropbear" "systemctl is-active dropbear")
    services_json+='"dropbear":"'$dropbear_status'",'
    
    # OpenVPN Services
    openvpn_ssl_status=$(check_service_status "OpenVPN SSL" "systemctl is-active openvpn@server")
    services_json+='"openvpn_ssl":"'$openvpn_ssl_status'",'
    
    openvpn_tcp_status=$(check_service_status "OpenVPN TCP" "systemctl is-active openvpn-server@server")
    services_json+='"openvpn_tcp":"'$openvpn_tcp_status'",'
    
    openvpn_udp_status=$(check_service_status "OpenVPN UDP" "systemctl is-active openvpn-udp")
    services_json+='"openvpn_udp":"'$openvpn_udp_status'",'
    
    # Web Services
    nginx_status=$(check_service_status "Nginx" "systemctl is-active nginx")
    services_json+='"nginx":"'$nginx_status'",'
    
    haproxy_status=$(check_service_status "HAProxy" "systemctl is-active haproxy")
    services_json+='"haproxy":"'$haproxy_status'",'
    
    # Xray Services
    xray_status=$(check_service_status "Xray" "systemctl is-active xray")
    services_json+='"xray":"'$xray_status'",'
    
    # BadVPN Services
    badvpn_7100_status=$(check_service_status "BadVPN 7100" "ps aux | grep -v grep | grep 'badvpn.*7100'")
    services_json+='"badvpn_7100":"'$badvpn_7100_status'",'
    
    badvpn_7200_status=$(check_service_status "BadVPN 7200" "ps aux | grep -v grep | grep 'badvpn.*7200'")
    services_json+='"badvpn_7200":"'$badvpn_7200_status'",'
    
    badvpn_7300_status=$(check_service_status "BadVPN 7300" "ps aux | grep -v grep | grep 'badvpn.*7300'")
    services_json+='"badvpn_7300":"'$badvpn_7300_status'",'
    
    # Cron Service
    cron_status=$(check_service_status "Cron" "systemctl is-active cron")
    services_json+='"cron":"'$cron_status'"'
    
    services_json+="}"
    echo "$services_json"
}

# Function to get port information
get_port_info() {
    cat << 'PORTINFO'
{
    "ssh": ["22", "80", "443"],
    "dropbear": ["109", "143", "443"],
    "dropbear_websocket": ["80", "443", "109"],
    "ssh_websocket_ssl": ["443"],
    "ssh_websocket": ["80"],
    "openvpn_ssl": ["443"],
    "openvpn_websocket_ssl": ["443"],
    "openvpn_tcp": ["443", "1194"],
    "openvpn_udp": ["2200"],
    "nginx_webserver": ["80", "81", "443"],
    "haproxy_loadbalancer": ["80", "443"],
    "openvpn_websocket_ssl": ["443"],
    "xray_vmess_tls": ["443"],
    "xray_vmess_grpc": ["443"],
    "xray_vmess_none_tls": ["80"],
    "xray_vless_tls": ["443"],
    "xray_vless_grpc": ["443"],
    "xray_vless_none_tls": ["80"],
    "xray_trojan_grpc": ["443"],
    "xray_trojan_ws": ["443"],
    "xray_shadowsocks_ws": ["443"],
    "xray_shadowsocks_grpc": ["443"],
    "badvpn_7100": ["7100"],
    "badvpn_7200": ["7200"],
    "badvpn_7300": ["7300"]
}
PORTINFO
}

# Function to restart service
restart_service() {
    local service="$1"
    local result=""
    
    case "$service" in
        "ssh")
            systemctl restart ssh && result="success" || result="failed"
            ;;
        "dropbear")
            systemctl restart dropbear && result="success" || result="failed"
            ;;
        "openvpn_ssl")
            systemctl restart openvpn@server && result="success" || result="failed"
            ;;
        "openvpn_tcp")
            systemctl restart openvpn-server@server && result="success" || result="failed"
            ;;
        "openvpn_udp")
            systemctl restart openvpn-udp && result="success" || result="failed"
            ;;
        "nginx")
            systemctl restart nginx && result="success" || result="failed"
            ;;
        "haproxy")
            systemctl restart haproxy && result="success" || result="failed"
            ;;
        "xray")
            systemctl restart xray && result="success" || result="failed"
            ;;
        "badvpn_7100")
            pkill -f "badvpn.*7100" 2>/dev/null
            sleep 2
            /usr/local/bin/badvpn-udpgw --listen-addr 127.0.0.1:7100 --max-clients 500 >/dev/null 2>&1 &
            result="success"
            ;;
        "badvpn_7200")
            pkill -f "badvpn.*7200" 2>/dev/null
            sleep 2
            /usr/local/bin/badvpn-udpgw --listen-addr 127.0.0.1:7200 --max-clients 500 >/dev/null 2>&1 &
            result="success"
            ;;
        "badvpn_7300")
            pkill -f "badvpn.*7300" 2>/dev/null
            sleep 2
            /usr/local/bin/badvpn-udpgw --listen-addr 127.0.0.1:7300 --max-clients 500 >/dev/null 2>&1 &
            result="success"
            ;;
        "cron")
            systemctl restart cron && result="success" || result="failed"
            ;;
        "all")
            systemctl restart ssh dropbear nginx haproxy xray cron
            systemctl restart openvpn@server openvpn-server@server openvpn-udp
            # Restart BadVPN services
            pkill -f "badvpn" 2>/dev/null
            sleep 3
            /usr/local/bin/badvpn-udpgw --listen-addr 127.0.0.1:7100 --max-clients 500 >/dev/null 2>&1 &
            /usr/local/bin/badvpn-udpgw --listen-addr 127.0.0.1:7200 --max-clients 500 >/dev/null 2>&1 &
            /usr/local/bin/badvpn-udpgw --listen-addr 127.0.0.1:7300 --max-clients 500 >/dev/null 2>&1 &
            result="success"
            ;;
        *)
            result="invalid_service"
            ;;
    esac
    
    log_message "Restart $service: $result"
    echo "$result"
}

# Function to check system resources
get_system_resources() {
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | sed 's/%us,//' | sed 's/%//')
    local memory_usage=$(free | grep Mem | awk '{printf("%.1f", $3/$2 * 100.0)}')
    local disk_usage=$(df -h / | awk 'NR==2{print $5}' | sed 's/%//')
    local load_avg=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
    local uptime_info=$(uptime -p)
    
    cat << EOF
{
    "cpu_usage": $cpu_usage,
    "memory_usage": $memory_usage,
    "disk_usage": $disk_usage,
    "load_average": "$load_avg",
    "uptime": "$uptime_info",
    "timestamp": "$(date '+%Y-%m-%d %H:%M:%S')"
}
EOF
}

# Function to get network statistics
get_network_stats() {
    local rx_bytes=$(cat /sys/class/net/*/statistics/rx_bytes | awk '{sum+=$1} END {print sum}')
    local tx_bytes=$(cat /sys/class/net/*/statistics/tx_bytes | awk '{sum+=$1} END {print sum}')
    local rx_mb=$((rx_bytes / 1024 / 1024))
    local tx_mb=$((tx_bytes / 1024 / 1024))
    
    cat << EOF
{
    "rx_bytes": $rx_bytes,
    "tx_bytes": $tx_bytes,
    "rx_mb": $rx_mb,
    "tx_mb": $tx_mb,
    "total_mb": $((rx_mb + tx_mb))
}
EOF
}

# Function to send comprehensive status to panel
send_comprehensive_status() {
    if [[ ! -f "$CONFIG_FILE" ]]; then
        echo "Panel config not found!"
        return 1
    fi
    
    local panel_url=$(jq -r '.panel_url' "$CONFIG_FILE")
    local api_key=$(jq -r '.api_key' "$CONFIG_FILE")
    local server_ip=$(jq -r '.server_ip' "$CONFIG_FILE")
    
    local services_status=$(get_all_services_status)
    local system_resources=$(get_system_resources)
    local network_stats=$(get_network_stats)
    local port_info=$(get_port_info)
    
    # Get active users
    local ssh_users=$(who | wc -l)
    local total_users=$(cut -d: -f1 /etc/passwd | grep -v "^root$\|^daemon$\|^bin$\|^sys$\|^sync$\|^games$\|^man$\|^lp$\|^mail$\|^news$\|^uucp$\|^proxy$\|^www-data$\|^backup$\|^list$\|^irc$\|^gnats$\|^nobody$\|^systemd" | wc -l)
    
    # Combine all data
    local combined_data=$(cat << EOF
{
    "server_ip": "$server_ip",
    "services": $services_status,
    "system": $system_resources,
    "network": $network_stats,
    "ports": $port_info,
    "users": {
        "active_ssh": $ssh_users,
        "total_accounts": $total_users
    },
    "last_update": "$(date '+%Y-%m-%d %H:%M:%S')"
}
EOF
)
    
    # Send to panel
    curl -s -X POST "$panel_url/api/server/comprehensive-status" \
        -H "Content-Type: application/json" \
        -H "X-API-Key: $api_key" \
        -d "$combined_data" >/dev/null 2>&1
}

# Main execution
case "$1" in
    "status")
        get_all_services_status
        ;;
    "resources")
        get_system_resources
        ;;
    "network")
        get_network_stats
        ;;
    "ports")
        get_port_info
        ;;
    "restart")
        restart_service "$2"
        ;;
    "send")
        send_comprehensive_status
        ;;
    "monitor")
        # Continuous monitoring mode
        while true; do
            send_comprehensive_status
            sleep 60
        done
        ;;
    *)
        echo "Usage: $0 {status|resources|network|ports|restart <service>|send|monitor}"
        echo ""
        echo "Available services for restart:"
        echo "  ssh, dropbear, openvpn_ssl, openvpn_tcp, openvpn_udp"
        echo "  nginx, haproxy, xray, badvpn_7100, badvpn_7200, badvpn_7300"
        echo "  cron, all"
        exit 1
        ;;
esac

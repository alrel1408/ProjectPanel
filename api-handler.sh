#!/bin/bash

# =================================================
# API Handler Script for Panel Commands
# Created by: AlrelShop
# Version: 1.0
# =================================================

CONFIG_FILE="/etc/panel-config/config.json"
LOG_FILE="/var/log/panel-api.log"

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Function to validate API key
validate_api_key() {
    local provided_key="$1"
    local stored_key=$(jq -r '.api_key' "$CONFIG_FILE")
    
    if [[ "$provided_key" == "$stored_key" ]]; then
        return 0
    else
        return 1
    fi
}

# Function to create SSH account
create_ssh_account() {
    local username="$1"
    local password="$2"
    local days="$3"
    local limit="${4:-1}"
    
    # Calculate expiry date
    local exp_date=$(date -d "+$days days" '+%Y-%m-%d')
    
    # Check if user already exists
    if id "$username" &>/dev/null; then
        echo "ERROR: User already exists"
        log_message "Failed to create user $username - already exists"
        return 1
    fi
    
    # Create user
    useradd -M -N -s /bin/false "$username" &>/dev/null
    if [[ $? -ne 0 ]]; then
        echo "ERROR: Failed to create user"
        log_message "Failed to create user $username"
        return 1
    fi
    
    # Set password
    echo "$username:$password" | chpasswd
    
    # Set expiry date
    chage -E "$exp_date" "$username"
    
    # Set up limit if needed
    if [[ "$limit" != "0" ]] && [[ -f /usr/local/sbin/user-limit ]]; then
        echo "$username $limit" >> /etc/kyt/limit/ssh/ip
    fi
    
    log_message "Created SSH account: $username (expires: $exp_date, limit: $limit)"
    echo "SUCCESS: Account $username created successfully"
    return 0
}

# Function to delete SSH account
delete_ssh_account() {
    local username="$1"
    
    # Check if user exists
    if ! id "$username" &>/dev/null; then
        echo "ERROR: User does not exist"
        return 1
    fi
    
    # Kill user processes
    pkill -u "$username" 2>/dev/null
    
    # Remove user
    userdel "$username" &>/dev/null
    
    # Remove from limit file
    sed -i "/^$username /d" /etc/kyt/limit/ssh/ip 2>/dev/null
    
    log_message "Deleted SSH account: $username"
    echo "SUCCESS: Account $username deleted successfully"
    return 0
}

# Function to extend account
extend_ssh_account() {
    local username="$1"
    local days="$2"
    
    # Check if user exists
    if ! id "$username" &>/dev/null; then
        echo "ERROR: User does not exist"
        return 1
    fi
    
    # Calculate new expiry date
    local exp_date=$(date -d "+$days days" '+%Y-%m-%d')
    
    # Set new expiry date
    chage -E "$exp_date" "$username"
    
    log_message "Extended SSH account: $username to $exp_date"
    echo "SUCCESS: Account $username extended to $exp_date"
    return 0
}

# Function to create trial account
create_trial_account() {
    local username="trial$(date +%s | tail -c 5)"
    local password=$(openssl rand -base64 8)
    local days="1"
    
    create_ssh_account "$username" "$password" "$days" "1"
    if [[ $? -eq 0 ]]; then
        echo "TRIAL_ACCOUNT:$username:$password"
    fi
}

# Function to get active users
get_active_users() {
    echo "ACTIVE_USERS:"
    who | awk '{print $1}' | sort | uniq -c | sort -nr
}

# Function to restart services
restart_service() {
    local service="$1"
    
    case "$service" in
        "ssh")
            systemctl restart ssh
            echo "SUCCESS: SSH service restarted"
            ;;
        "nginx")
            systemctl restart nginx
            echo "SUCCESS: Nginx service restarted"
            ;;
        "xray")
            systemctl restart xray
            echo "SUCCESS: Xray service restarted"
            ;;
        "openvpn")
            systemctl restart openvpn@server
            echo "SUCCESS: OpenVPN service restarted"
            ;;
        "all")
            systemctl restart ssh nginx xray openvpn@server
            echo "SUCCESS: All services restarted"
            ;;
        *)
            echo "ERROR: Invalid service name"
            return 1
            ;;
    esac
    
    log_message "Restarted service: $service"
}

# Function to get server info
get_server_info() {
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | sed 's/%us,//' | sed 's/%//')
    local memory_usage=$(free | grep Mem | awk '{printf("%.1f", $3/$2 * 100.0)}')
    local disk_usage=$(df -h / | awk 'NR==2{print $5}' | sed 's/%//')
    local uptime=$(uptime -p)
    local load_avg=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
    local ssh_users=$(who | wc -l)
    local total_users=$(cut -d: -f1 /etc/passwd | grep -v "^root$\|^daemon$\|^bin$\|^sys$\|^sync$\|^games$\|^man$\|^lp$\|^mail$\|^news$\|^uucp$\|^proxy$\|^www-data$\|^backup$\|^list$\|^irc$\|^gnats$\|^nobody$\|^systemd" | wc -l)
    
    cat << EOF
SERVER_INFO:
CPU_Usage: ${cpu_usage}%
Memory_Usage: ${memory_usage}%
Disk_Usage: ${disk_usage}%
Load_Average: ${load_avg}
Uptime: ${uptime}
Active_SSH: ${ssh_users}
Total_Accounts: ${total_users}
Timestamp: $(date '+%Y-%m-%d %H:%M:%S')
EOF
}

# Function to handle webhook from panel
handle_webhook() {
    local action="$1"
    local data="$2"
    
    case "$action" in
        "create_account")
            local username=$(echo "$data" | jq -r '.username')
            local password=$(echo "$data" | jq -r '.password')
            local days=$(echo "$data" | jq -r '.days')
            local limit=$(echo "$data" | jq -r '.limit // 1')
            
            create_ssh_account "$username" "$password" "$days" "$limit"
            ;;
        "delete_account")
            local username=$(echo "$data" | jq -r '.username')
            delete_ssh_account "$username"
            ;;
        "extend_account")
            local username=$(echo "$data" | jq -r '.username')
            local days=$(echo "$data" | jq -r '.days')
            extend_ssh_account "$username" "$days"
            ;;
        "create_trial")
            create_trial_account
            ;;
        "restart_service")
            local service=$(echo "$data" | jq -r '.service')
            restart_service "$service"
            ;;
        "get_info")
            get_server_info
            ;;
        "get_users")
            get_active_users
            ;;
        *)
            echo "ERROR: Invalid action"
            return 1
            ;;
    esac
}

# Main execution
if [[ $# -eq 0 ]]; then
    echo "Usage: $0 <action> [data]"
    echo "Actions: create_account, delete_account, extend_account, create_trial, restart_service, get_info, get_users"
    exit 1
fi

# Check if config file exists
if [[ ! -f "$CONFIG_FILE" ]]; then
    echo "ERROR: Panel config not found"
    exit 1
fi

# Create log file if it doesn't exist
touch "$LOG_FILE"

# Handle the action
ACTION="$1"
DATA="$2"

handle_webhook "$ACTION" "$DATA"

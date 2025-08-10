#!/bin/bash

# =================================================
# Quick Deploy Script for AlrelShop Panel
# Repository: alrel1408/ProjectPanel
# Created by: AlrelShop
# Version: 1.0
# =================================================

Green="\e[92;1m"
RED="\033[31m"
YELLOW="\033[33m"
BLUE="\033[36m"
NC='\e[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}  AlrelShop Panel Quick Deploy Script${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Function to show step
show_step() {
    echo -e "${YELLOW}[STEP]${NC} $1"
}

# Function to show success
show_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# Function to show error
show_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [[ ! -f "installer.sh" ]]; then
    show_error "installer.sh not found! Make sure you're in the vps-installer directory."
    exit 1
fi

show_step "Preparing files for deployment..."

# Make scripts executable
chmod +x installer.sh
chmod +x api-handler.sh
chmod +x service-monitor.sh
chmod +x quick-deploy.sh

show_success "Scripts made executable"

# Check if git is initialized
if [[ ! -d ".git" ]]; then
    show_step "Initializing Git repository..."
    git init
    show_success "Git repository initialized"
fi

# Add all files
show_step "Adding files to Git..."
git add .

# Show status
echo ""
echo -e "${BLUE}Files to be committed:${NC}"
git status --short

echo ""
read -p "Continue with commit? (y/N): " confirm
if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
    echo "Deployment cancelled."
    exit 0
fi

# Commit changes
show_step "Committing changes..."
commit_message="Deploy AlrelShop VPS Panel - $(date '+%Y-%m-%d %H:%M:%S')"
git commit -m "$commit_message"
show_success "Changes committed"

# Check if remote exists
if ! git remote get-url origin >/dev/null 2>&1; then
    show_step "Adding GitHub remote..."
    git remote add origin https://github.com/alrel1408/ProjectPanel.git
    show_success "Remote added"
fi

# Set main branch
git branch -M main

# Push to GitHub
show_step "Pushing to GitHub repository alrel1408/ProjectPanel..."
echo ""
echo -e "${YELLOW}Note: You may need to enter your GitHub credentials${NC}"
echo ""

if git push -u origin main; then
    show_success "Successfully deployed to GitHub!"
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}         DEPLOYMENT SUCCESSFUL!${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo -e "${BLUE}Repository URL:${NC} https://github.com/alrel1408/ProjectPanel"
    echo -e "${BLUE}Raw Installer:${NC} https://raw.githubusercontent.com/alrel1408/ProjectPanel/main/installer.sh"
    echo ""
    echo -e "${YELLOW}Installation Command for Customers:${NC}"
    echo -e "${GREEN}wget -O installer.sh https://raw.githubusercontent.com/alrel1408/ProjectPanel/main/installer.sh && chmod +x installer.sh && ./installer.sh${NC}"
    echo ""
    echo -e "${BLUE}Next Steps:${NC}"
    echo "1. Deploy panel files to panel.alrelshop.my.id"
    echo "2. Run database setup: php config/existing-database.php"
    echo "3. Test the installation command on a VPS"
    echo "4. Update panel dashboard with new API endpoints"
    echo ""
else
    show_error "Failed to push to GitHub!"
    echo ""
    echo -e "${YELLOW}Possible solutions:${NC}"
    echo "1. Check your GitHub credentials"
    echo "2. Make sure repository alrel1408/ProjectPanel exists"
    echo "3. Check internet connection"
    echo "4. Try: git push origin main --force (if needed)"
    exit 1
fi

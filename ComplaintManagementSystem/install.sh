#!/bin/bash

# ============================================
# Smart Complaint Management System
# Installation Script for Linux/Ubuntu
# ============================================

echo "=========================================="
echo "Smart Complaint Management System"
echo "Installation Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    echo -e "${RED}Please do not run as root${NC}"
    exit 1
fi

# Function to check command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check prerequisites
echo "Checking prerequisites..."

# Check PHP
if command_exists php; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -f1-2 -d".")
    echo -e "${GREEN}✓${NC} PHP $PHP_VERSION found"
else
    echo -e "${RED}✗${NC} PHP not found. Please install PHP 7.4+"
    exit 1
fi

# Check MySQL
if command_exists mysql; then
    echo -e "${GREEN}✓${NC} MySQL found"
else
    echo -e "${RED}✗${NC} MySQL not found. Please install MySQL 5.7+"
    exit 1
fi

# Check Python
if command_exists python3; then
    PYTHON_VERSION=$(python3 --version | cut -d " " -f 2)
    echo -e "${GREEN}✓${NC} Python $PYTHON_VERSION found"
else
    echo -e "${RED}✗${NC} Python not found. Please install Python 3.8+"
    exit 1
fi

# Check pip
if command_exists pip3; then
    echo -e "${GREEN}✓${NC} pip found"
else
    echo -e "${RED}✗${NC} pip not found. Please install pip"
    exit 1
fi

echo ""
echo "All prerequisites met!"
echo ""

# Database setup
echo "=========================================="
echo "Database Setup"
echo "=========================================="
echo ""

read -p "MySQL root password: " -s MYSQL_ROOT_PASSWORD
echo ""

read -p "Create new database user? (y/n): " CREATE_USER

if [ "$CREATE_USER" = "y" ]; then
    read -p "Enter database username [complaint_user]: " DB_USER
    DB_USER=${DB_USER:-complaint_user}
    
    read -p "Enter database password: " -s DB_PASSWORD
    echo ""
else
    DB_USER="root"
    DB_PASSWORD=$MYSQL_ROOT_PASSWORD
fi

echo "Creating database..."

# Import schema
mysql -u root -p"$MYSQL_ROOT_PASSWORD" < database/schema.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Database created successfully"
else
    echo -e "${RED}✗${NC} Database creation failed"
    exit 1
fi

# Create user if needed
if [ "$CREATE_USER" = "y" ]; then
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" << EOF
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON complaint_management_system.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
    echo -e "${GREEN}✓${NC} Database user created"
fi

echo ""

# Configure backend
echo "=========================================="
echo "Backend Configuration"
echo "=========================================="
echo ""

echo "Updating configuration..."

# Backup original config
cp backend/config.php backend/config.php.bak

# Update database credentials
sed -i "s/define('DB_USER', 'root');/define('DB_USER', '$DB_USER');/" backend/config.php
sed -i "s/define('DB_PASS', '');/define('DB_PASS', '$DB_PASSWORD');/" backend/config.php

echo -e "${GREEN}✓${NC} Backend configured"
echo ""

# Install Python dependencies
echo "=========================================="
echo "ML Model Setup"
echo "=========================================="
echo ""

echo "Installing Python dependencies..."

cd ml_model
pip3 install -r requirements.txt

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Python dependencies installed"
else
    echo -e "${RED}✗${NC} Failed to install Python dependencies"
    exit 1
fi

cd ..
echo ""

# Set permissions
echo "=========================================="
echo "Setting Permissions"
echo "=========================================="
echo ""

chmod -R 755 backend
chmod -R 755 frontend
chmod 644 database/schema.sql

echo -e "${GREEN}✓${NC} Permissions set"
echo ""

# Create uploads directory
mkdir -p uploads
chmod 755 uploads
echo -e "${GREEN}✓${NC} Uploads directory created"
echo ""

# Installation complete
echo "=========================================="
echo "Installation Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo ""
echo "1. Start the ML API:"
echo "   cd ml_model"
echo "   python3 ml_api.py"
echo ""
echo "2. In another terminal, start the web server:"
echo "   cd frontend"
echo "   php -S localhost:8000"
echo ""
echo "3. Access the application:"
echo "   http://localhost:8000/login.php"
echo ""
echo "4. Default admin credentials:"
echo "   Email: admin@complaint.com"
echo "   Password: Admin@123"
echo ""
echo -e "${YELLOW}Important:${NC} Change the admin password after first login!"
echo ""
echo "For AWS deployment, see: config/aws_setup.md"
echo "For troubleshooting, see: QUICKSTART.md"
echo ""
echo "=========================================="

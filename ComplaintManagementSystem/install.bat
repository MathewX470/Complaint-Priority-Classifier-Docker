@echo off
REM ============================================
REM Smart Complaint Management System
REM Installation Script for Windows
REM ============================================

echo ==========================================
echo Smart Complaint Management System
echo Installation Script for Windows
echo ==========================================
echo.

REM Check prerequisites
echo Checking prerequisites...

where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [X] PHP not found. Please install PHP 7.4+
    pause
    exit /b 1
) else (
    echo [OK] PHP found
)

where mysql >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [X] MySQL not found. Please install MySQL 5.7+
    pause
    exit /b 1
) else (
    echo [OK] MySQL found
)

where python >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [X] Python not found. Please install Python 3.8+
    pause
    exit /b 1
) else (
    echo [OK] Python found
)

where pip >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [X] pip not found. Please install pip
    pause
    exit /b 1
) else (
    echo [OK] pip found
)

echo.
echo All prerequisites met!
echo.

REM Database setup
echo ==========================================
echo Database Setup
echo ==========================================
echo.

set /p MYSQL_PASSWORD="Enter MySQL root password: "
echo.

echo Importing database schema...
mysql -u root -p%MYSQL_PASSWORD% < database\schema.sql

if %ERRORLEVEL% EQU 0 (
    echo [OK] Database created successfully
) else (
    echo [X] Database creation failed
    pause
    exit /b 1
)

echo.

REM Install Python dependencies
echo ==========================================
echo Installing Python Dependencies
echo ==========================================
echo.

cd ml_model
pip install -r requirements.txt

if %ERRORLEVEL% EQU 0 (
    echo [OK] Python dependencies installed
) else (
    echo [X] Failed to install Python dependencies
    pause
    exit /b 1
)

cd ..
echo.

REM Create directories
echo Creating upload directory...
if not exist "uploads" mkdir uploads
echo [OK] Uploads directory created
echo.

REM Installation complete
echo ==========================================
echo Installation Complete!
echo ==========================================
echo.
echo Next steps:
echo.
echo 1. Update backend\config.php with your database credentials
echo.
echo 2. Start the ML API in one command prompt:
echo    cd ml_model
echo    python ml_api.py
echo.
echo 3. Start the web server in another command prompt:
echo    cd frontend
echo    php -S localhost:8000
echo.
echo 4. Access the application:
echo    http://localhost:8000/login.php
echo.
echo 5. Default admin credentials:
echo    Email: admin@complaint.com
echo    Password: Admin@123
echo.
echo IMPORTANT: Change the admin password after first login!
echo.
echo For detailed instructions, see: README.md
echo ==========================================
echo.
pause

# Quick Start Guide
## Smart Complaint Management System

Get started in 5 minutes!

## Prerequisites Check

```bash
# Check PHP version (need 7.4+)
php -v

# Check MySQL (need 5.7+)
mysql --version

# Check Python (need 3.8+)
python3 --version

# Check pip
pip3 --version
```

## Quick Installation

### Step 1: Database Setup (2 minutes)

```bash
# Login to MySQL
mysql -u root -p

# Run the schema file
mysql -u root -p < database/schema.sql

# Verify
mysql -u root -p -e "USE complaint_management_system; SHOW TABLES;"
```

### Step 2: Backend Configuration (1 minute)

```bash
# Edit config file
nano backend/config.php

# Update these lines:
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### Step 3: Start ML API (1 minute)

```bash
cd ml_model

# Install dependencies
pip3 install -r requirements.txt

# Start API (will auto-train model on first run)
python3 ml_api.py
```

### Step 4: Start Web Server (1 minute)

```bash
cd frontend

# Using PHP built-in server
php -S localhost:8000
```

## Access the Application

1. Open browser: **http://localhost:8000/login.php**

2. **Default Admin Credentials:**
   - Email: `admin@complaint.com`
   - Password: `Admin@123`

3. **Or Register** a new user account

## Test the System

### As a User:

1. Register/Login
2. Submit a complaint (e.g., "Server is down")
3. Watch it auto-classify as "High" priority
4. View your complaint dashboard

### As an Admin:

1. Login with admin credentials
2. View all complaints
3. Filter by priority
4. Update complaint status
5. View statistics

## API Endpoints

### Test ML API:

```bash
# Health check
curl http://localhost:5000/health

# Predict priority
curl -X POST http://localhost:5000/predict \
  -H "Content-Type: application/json" \
  -d '{"complaint_text":"Server crash detected"}'
```

### Test Backend API:

```bash
# Register user
curl -X POST http://localhost:8000/backend/register_handler.php \
  -H "Content-Type: application/json" \
  -d '{
    "fullName":"Test User",
    "email":"test@example.com",
    "password":"Test@123"
  }'
```

## Troubleshooting

### Issue: Database connection failed

```bash
# Check MySQL is running
sudo systemctl status mysql

# Check credentials in backend/config.php
```

### Issue: ML API not starting

```bash
# Check Python version
python3 --version

# Install missing packages
pip3 install flask pandas scikit-learn

# Check if port 5000 is free
lsof -i :5000
```

### Issue: Can't access web interface

```bash
# Check PHP is running
php -v

# Try different port
php -S localhost:8080
```

## Next Steps

1. **Customize**: Edit the UI colors and branding
2. **Deploy**: Follow `config/aws_setup.md` for AWS deployment
3. **Secure**: Change default admin password
4. **Scale**: Add more training data to improve ML accuracy

## Production Checklist

Before going live:

- [ ] Change admin password
- [ ] Update database credentials
- [ ] Enable HTTPS/SSL
- [ ] Set `display_errors = Off` in PHP
- [ ] Configure firewall rules
- [ ] Set up automated backups
- [ ] Enable logging and monitoring
- [ ] Test disaster recovery

## Common Commands

```bash
# Restart ML API
pkill -f ml_api.py
python3 ml_api.py &

# Restart web server
# (If using Apache)
sudo systemctl restart apache2

# Check logs
tail -f /var/log/apache2/error.log
tail -f /var/log/mysql/error.log

# Backup database
mysqldump -u root -p complaint_management_system > backup.sql

# Restore database
mysql -u root -p complaint_management_system < backup.sql
```

## Support

- Check `README.md` for detailed documentation
- Check `config/aws_setup.md` for AWS deployment
- Review logs for error messages
- Test individual components separately

## Sample Data

Test with these complaint examples:

**High Priority:**
- "Critical server failure affecting all users"
- "Security breach detected in system"
- "Database completely down"

**Medium Priority:**
- "Application response time is slow"
- "Email notifications delayed"
- "Dashboard loading slowly"

**Low Priority:**
- "Can you add dark mode?"
- "Suggestion to improve UI"
- "Request to change theme colors"

**Other:**
- "Hi, how are you?"
- "Thanks for your help"
- "Good morning"

---

**Congratulations! Your system is now running!** 🎉

Visit: http://localhost:8000/login.php

# AWS Deployment Guide
## Smart Complaint Management System

This guide provides step-by-step instructions to deploy the Smart Complaint Management System on AWS.

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                         Internet                             │
└─────────────────────┬───────────────────────────────────────┘
                      │
              ┌───────▼────────┐
              │  Route 53 DNS  │
              └───────┬────────┘
                      │
              ┌───────▼────────┐
              │  Load Balancer │
              │   (Optional)   │
              └───────┬────────┘
                      │
          ┌───────────┴──────────┐
          │                      │
    ┌─────▼──────┐        ┌─────▼──────┐
    │  EC2 Web   │        │  EC2 ML    │
    │   Server   │◄───────┤  API Server│
    │ (PHP/Apache)│        │  (Flask)   │
    └─────┬──────┘        └────────────┘
          │
    ┌─────▼──────┐
    │  RDS MySQL │
    │  Database  │
    └────────────┘
```

## Prerequisites

- AWS Account
- AWS CLI installed and configured
- SSH key pair for EC2 access
- Domain name (optional, for Route 53)

## Step 1: Set Up RDS MySQL Database

### 1.1 Create RDS Instance

```bash
# Via AWS Console:
# 1. Go to RDS Dashboard
# 2. Click "Create database"
# 3. Choose MySQL 8.0
# 4. Select template: "Free tier" or "Production"
# 5. Settings:
#    - DB instance identifier: complaint-management-db
#    - Master username: admin
#    - Master password: [Your Secure Password]
# 6. Instance configuration:
#    - DB instance class: db.t3.micro (Free tier)
#    - Storage: 20 GB SSD
# 7. Connectivity:
#    - VPC: Default VPC
#    - Public access: Yes (for initial setup)
#    - Security group: Create new (complaint-db-sg)
# 8. Create database
```

### 1.2 Configure Security Group

```bash
# Allow MySQL access from EC2 instances
# Security Group Rules:
# - Type: MySQL/Aurora (3306)
# - Source: EC2 security group ID
```

### 1.3 Import Database Schema

```bash
# Connect to RDS
mysql -h [RDS-ENDPOINT] -u admin -p

# Import schema
mysql -h [RDS-ENDPOINT] -u admin -p < database/schema.sql
```

## Step 2: Launch EC2 Instances

### 2.1 Web Server Instance (PHP/Apache)

```bash
# Launch Ubuntu 22.04 LTS
# Instance type: t2.micro (Free tier eligible)
# Configure Security Group:
# - Port 80 (HTTP): 0.0.0.0/0
# - Port 443 (HTTPS): 0.0.0.0/0
# - Port 22 (SSH): Your IP
```

**Connect and Install Dependencies:**

```bash
# SSH into instance
ssh -i your-key.pem ubuntu@[EC2-PUBLIC-IP]

# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache, PHP, and MySQL client
sudo apt install -y apache2 php8.1 php8.1-mysql php8.1-curl php8.1-json php8.1-mbstring mysql-client

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2

# Install Composer (optional)
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```

### 2.2 ML API Server Instance (Python/Flask)

```bash
# Launch Ubuntu 22.04 LTS
# Instance type: t2.small (recommended for ML)
# Configure Security Group:
# - Port 5000 (Flask API): Web Server SG
# - Port 22 (SSH): Your IP
```

**Connect and Install Dependencies:**

```bash
# SSH into instance
ssh -i your-key.pem ubuntu@[EC2-PUBLIC-IP]

# Update system
sudo apt update && sudo apt upgrade -y

# Install Python and pip
sudo apt install -y python3 python3-pip python3-venv

# Create virtual environment
python3 -m venv ml_env
source ml_env/bin/activate

# Install Python packages
pip install flask flask-cors pandas scikit-learn numpy gunicorn
```

## Step 3: Deploy Application

### 3.1 Deploy Web Application

```bash
# On Web Server EC2
cd /var/www/html

# Remove default files
sudo rm -rf *

# Upload application files
# Option 1: Using SCP
scp -i your-key.pem -r ComplaintManagementSystem/* ubuntu@[EC2-IP]:/tmp/
sudo mv /tmp/* /var/www/html/

# Option 2: Using Git
sudo git clone [YOUR-REPO-URL] .

# Set permissions
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

# Configure Apache
sudo nano /etc/apache2/sites-available/000-default.conf
```

**Apache Configuration:**

```apache
<VirtualHost *:80>
    ServerAdmin admin@complaint.com
    DocumentRoot /var/www/html/frontend
    
    <Directory /var/www/html/frontend>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

```bash
# Restart Apache
sudo systemctl restart apache2
```

### 3.2 Configure Backend

```bash
# Edit configuration
sudo nano /var/www/html/backend/config.php

# Update database credentials:
define('DB_HOST', '[RDS-ENDPOINT]');
define('DB_USER', 'admin');
define('DB_PASS', '[YOUR-PASSWORD]');
define('DB_NAME', 'complaint_management_system');

# Update ML API URL
define('ML_API_URL', 'http://[ML-EC2-PRIVATE-IP]:5000/predict');
```

### 3.3 Deploy ML API

```bash
# On ML API EC2
cd /home/ubuntu

# Upload ML files
scp -i your-key.pem -r ComplaintManagementSystem/ml_model/* ubuntu@[ML-EC2-IP]:/home/ubuntu/

# Upload dataset
scp -i your-key.pem data.csv ubuntu@[ML-EC2-IP]:/home/ubuntu/

# Activate virtual environment
source ml_env/bin/activate

# Install dependencies
pip install -r requirements.txt

# Test ML API
python ml_api.py
```

**Create systemd service for Flask API:**

```bash
sudo nano /etc/systemd/system/ml-api.service
```

```ini
[Unit]
Description=ML API for Complaint Management
After=network.target

[Service]
Type=simple
User=ubuntu
WorkingDirectory=/home/ubuntu
Environment="PATH=/home/ubuntu/ml_env/bin"
ExecStart=/home/ubuntu/ml_env/bin/gunicorn -w 4 -b 0.0.0.0:5000 ml_api:app

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start service
sudo systemctl daemon-reload
sudo systemctl enable ml-api
sudo systemctl start ml-api
sudo systemctl status ml-api
```

## Step 4: Configure Security Groups

### Web Server Security Group

```
Inbound Rules:
- Type: HTTP (80), Source: 0.0.0.0/0
- Type: HTTPS (443), Source: 0.0.0.0/0
- Type: SSH (22), Source: Your IP

Outbound Rules:
- Type: All traffic, Destination: 0.0.0.0/0
```

### ML API Security Group

```
Inbound Rules:
- Type: Custom TCP (5000), Source: Web Server SG
- Type: SSH (22), Source: Your IP

Outbound Rules:
- Type: All traffic, Destination: 0.0.0.0/0
```

### Database Security Group

```
Inbound Rules:
- Type: MySQL/Aurora (3306), Source: Web Server SG
- Type: MySQL/Aurora (3306), Source: Your IP (temporary)

Outbound Rules:
- Type: All traffic, Destination: 0.0.0.0/0
```

## Step 5: Set Up Load Balancer (Optional)

```bash
# Via AWS Console:
# 1. Go to EC2 > Load Balancers
# 2. Create Application Load Balancer
# 3. Configure listeners (HTTP:80, HTTPS:443)
# 4. Add target group with Web Server EC2
# 5. Configure health checks
# 6. Update DNS to point to ALB
```

## Step 6: Set Up Auto Scaling (Optional)

```bash
# Create Launch Template from Web Server instance
# Create Auto Scaling Group
# Configure scaling policies based on CPU/Memory
```

## Step 7: Configure Route 53 (Optional)

```bash
# 1. Create hosted zone for your domain
# 2. Create A record pointing to:
#    - EC2 Elastic IP, or
#    - Load Balancer DNS name
# 3. Update nameservers at domain registrar
```

## Step 8: SSL/TLS Configuration

### Using Let's Encrypt (Free SSL)

```bash
# On Web Server
sudo apt install -y certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

## Step 9: Monitoring and Logging

### CloudWatch Setup

```bash
# Install CloudWatch agent
wget https://s3.amazonaws.com/amazoncloudwatch-agent/ubuntu/amd64/latest/amazon-cloudwatch-agent.deb
sudo dpkg -i amazon-cloudwatch-agent.deb

# Configure agent
sudo /opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-config-wizard

# Start agent
sudo systemctl start amazon-cloudwatch-agent
```

### Application Logs

```bash
# Apache logs
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/apache2/access.log

# ML API logs
sudo journalctl -u ml-api -f

# PHP error logs
sudo tail -f /var/log/php_errors.log
```

## Step 10: Backup Strategy

### Database Backups

```bash
# Enable automated backups in RDS
# Backup retention: 7-35 days
# Backup window: Choose off-peak hours
```

### Application Backups

```bash
# Create AMI snapshot of EC2 instances
# Regular: Weekly
# Before updates: Always

# Automated backup script
#!/bin/bash
aws ec2 create-image \
  --instance-id i-1234567890abcdef0 \
  --name "web-server-backup-$(date +%Y%m%d)" \
  --description "Automated backup"
```

## Testing Deployment

### 1. Test Web Application

```bash
# Access application
curl http://[EC2-PUBLIC-IP]/login.php
# or
curl https://yourdomain.com/login.php
```

### 2. Test ML API

```bash
curl -X POST http://[ML-EC2-PRIVATE-IP]:5000/predict \
  -H "Content-Type: application/json" \
  -d '{"complaint_text":"Server is down"}'
```

### 3. Test Database Connection

```bash
mysql -h [RDS-ENDPOINT] -u admin -p -e "SHOW DATABASES;"
```

### 4. End-to-End Test

1. Register a new user
2. Login
3. Submit a complaint
4. Verify ML classification
5. Login as admin
6. Update complaint status
7. Verify status history

## Cost Estimation

### Free Tier Eligible (First 12 months)

- EC2 t2.micro: 750 hours/month
- RDS db.t2.micro: 750 hours/month
- EBS: 30 GB
- Data Transfer: 15 GB/month

### Beyond Free Tier (Approximate Monthly)

- EC2 t2.micro (Web): ~$10
- EC2 t2.small (ML): ~$20
- RDS db.t3.micro: ~$15
- EBS: ~$2
- Data Transfer: ~$1/GB
- **Total**: ~$50-70/month

## Optimization Tips

1. **Use Reserved Instances**: Save up to 75% for long-term deployment
2. **Enable Auto Scaling**: Scale based on demand
3. **Use CloudFront CDN**: Cache static assets
4. **Optimize Database**: Regular indexing and query optimization
5. **Use ElastiCache**: Cache frequent queries
6. **Compress Assets**: Reduce bandwidth costs
7. **Monitor Costs**: Set up billing alerts

## Troubleshooting

### Issue: Can't connect to database

```bash
# Check security group
# Verify RDS endpoint
# Test connection
mysql -h [RDS-ENDPOINT] -u admin -p
```

### Issue: ML API not responding

```bash
# Check service status
sudo systemctl status ml-api

# Check logs
sudo journalctl -u ml-api -n 50

# Restart service
sudo systemctl restart ml-api
```

### Issue: PHP errors

```bash
# Check Apache logs
sudo tail -f /var/log/apache2/error.log

# Check PHP configuration
php -i | grep error

# Enable error display (development only)
sudo nano /etc/php/8.1/apache2/php.ini
# display_errors = On
```

## Security Best Practices

1. **Never commit credentials**: Use AWS Secrets Manager or Parameter Store
2. **Restrict SSH access**: Use bastion host or VPN
3. **Enable WAF**: Protect against common attacks
4. **Regular updates**: Keep system and packages updated
5. **Use IAM roles**: Don't use access keys on EC2
6. **Enable MFA**: For AWS console access
7. **Audit logs**: Regular review of CloudTrail logs
8. **Encryption**: Enable encryption at rest and in transit

## Maintenance Schedule

### Daily
- Monitor CloudWatch metrics
- Check application logs
- Verify backup completion

### Weekly
- Review security alerts
- Check disk usage
- Test disaster recovery

### Monthly
- Update packages and patches
- Review and optimize costs
- Test backup restoration
- Security audit

## Support and Resources

- AWS Documentation: https://docs.aws.amazon.com
- AWS Free Tier: https://aws.amazon.com/free
- AWS Well-Architected Framework: https://aws.amazon.com/architecture/well-architected

## Rollback Plan

In case of deployment issues:

1. Restore previous AMI snapshot
2. Restore database from RDS snapshot
3. Revert DNS changes
4. Roll back code from Git

---

**Deployment Checklist:**

- [ ] RDS instance created and schema imported
- [ ] EC2 instances launched and configured
- [ ] Security groups properly configured
- [ ] Application deployed on web server
- [ ] ML API deployed and running
- [ ] Database connection verified
- [ ] ML API integration tested
- [ ] SSL certificate installed
- [ ] Domain DNS configured
- [ ] Monitoring and logging enabled
- [ ] Backup strategy implemented
- [ ] End-to-end testing completed
- [ ] Documentation updated

**Congratulations! Your Smart Complaint Management System is now deployed on AWS!**

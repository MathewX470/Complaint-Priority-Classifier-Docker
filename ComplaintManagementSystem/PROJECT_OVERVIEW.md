# Smart Complaint Management System
## Project Overview & Documentation

---

## 📋 Table of Contents

1. [System Architecture](#system-architecture)
2. [Features](#features)
3. [Technology Stack](#technology-stack)
4. [File Structure](#file-structure)
5. [Database Schema](#database-schema)
6. [API Documentation](#api-documentation)
7. [ML Model Details](#ml-model-details)
8. [Installation Guide](#installation-guide)
9. [Usage Guide](#usage-guide)
10. [Deployment](#deployment)
11. [Security Features](#security-features)
12. [Troubleshooting](#troubleshooting)

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────┐
│                      User Interface                      │
│              (HTML5 + Bootstrap 5 + JS)                 │
└──────────────────────┬──────────────────────────────────┘
                       │
                       │ HTTP/HTTPS
                       │
┌──────────────────────▼──────────────────────────────────┐
│                    PHP Backend                           │
│  ┌────────────────────────────────────────────────┐    │
│  │  • Authentication (auth.php)                    │    │
│  │  • Complaint API (complaint_api.php)            │    │
│  │  • Configuration (config.php)                   │    │
│  │  • Request Handlers (login, register, etc.)     │    │
│  └────────────────────────────────────────────────┘    │
└──────────────┬────────────────────┬─────────────────────┘
               │                    │
               │                    │ REST API
               │                    │
      ┌────────▼─────────┐   ┌─────▼──────────┐
      │  MySQL Database  │   │  Python Flask   │
      │                  │   │    ML API       │
      │  • Users         │   │                 │
      │  • Complaints    │   │  • TF-IDF      │
      │  • History       │   │  • Naive Bayes │
      │  • Logs          │   │  • Prediction  │
      └──────────────────┘   └────────────────┘
```

---

## ✨ Features

### User Features
- ✅ User registration and authentication
- ✅ Submit complaints with automatic ML classification
- ✅ Real-time complaint status tracking
- ✅ View complaint history
- ✅ Track resolution timeline
- ✅ Responsive mobile-friendly interface

### Admin Features
- ✅ Comprehensive dashboard with statistics
- ✅ View all complaints with advanced filtering
- ✅ Filter by priority (High, Medium, Low, Other)
- ✅ Filter by status (Registered, Under Review, In Progress, Resolved)
- ✅ Search functionality
- ✅ Update complaint status
- ✅ View complete complaint lifecycle
- ✅ Activity logging
- ✅ Analytics and insights

### ML Features
- ✅ Automatic priority classification using NLP
- ✅ 4-class classification (High, Medium, Low, Other)
- ✅ Confidence score reporting
- ✅ Real-time prediction API
- ✅ Model retraining capability
- ✅ Performance metrics tracking

### System Features
- ✅ Secure authentication with password hashing
- ✅ Session management with timeout
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ Complete audit trail
- ✅ Status change history
- ✅ Timestamp tracking for all events
- ✅ RESTful API architecture
- ✅ Cloud-ready (AWS compatible)

---

## 💻 Technology Stack

### Frontend
- **HTML5**: Structure and markup
- **Bootstrap 5.3**: Responsive UI framework
- **JavaScript (ES6+)**: Client-side logic
- **Bootstrap Icons**: Icon library
- **Fetch API**: Asynchronous requests

### Backend
- **PHP 8.x**: Server-side language
- **PDO**: Database abstraction layer
- **Session Management**: User authentication
- **cURL**: ML API integration

### Database
- **MySQL 8.0**: Relational database
- **Stored Procedures**: Business logic
- **Triggers**: Automated actions
- **Views**: Data aggregation
- **Indexes**: Performance optimization

### Machine Learning
- **Python 3.8+**: ML development
- **Flask**: Web framework for API
- **Flask-CORS**: Cross-origin support
- **Scikit-learn**: ML library
  - TF-IDF Vectorizer: Text feature extraction
  - Multinomial Naive Bayes: Classification
- **Pandas**: Data manipulation
- **NumPy**: Numerical operations
- **Pickle**: Model serialization

### DevOps & Cloud
- **AWS EC2**: Application hosting
- **AWS RDS**: Database hosting
- **Apache/Nginx**: Web server
- **Gunicorn**: WSGI server for Python
- **Git**: Version control
- **systemd**: Service management

---

## 📁 File Structure

```
ComplaintManagementSystem/
│
├── backend/                          # PHP Backend
│   ├── config.php                    # Database & app configuration
│   ├── auth.php                      # Authentication logic
│   ├── complaint_api.php             # Complaint management API
│   ├── login_handler.php             # Login endpoint
│   ├── register_handler.php          # Registration endpoint
│   ├── submit_complaint.php          # Submit complaint endpoint
│   ├── update_status.php             # Update status endpoint
│   └── get_complaint_details.php     # Get details endpoint
│
├── frontend/                         # User Interface
│   ├── login.php                     # Login page
│   ├── register.php                  # Registration page
│   ├── dashboard.php                 # User dashboard
│   ├── admin_dashboard.php           # Admin dashboard
│   └── logout.php                    # Logout handler
│
├── ml_model/                         # Machine Learning
│   ├── ml_api.py                     # Flask ML API server
│   ├── requirements.txt              # Python dependencies
│   ├── test_api.py                   # API testing script
│   └── complaint_model.pkl           # Trained model (generated)
│
├── database/                         # Database
│   └── schema.sql                    # Complete database schema
│
├── config/                           # Configuration
│   └── aws_setup.md                  # AWS deployment guide
│
├── .htaccess                         # Apache configuration
├── .env.example                      # Environment variables template
├── .gitignore                        # Git ignore rules
├── README.md                         # Main documentation
├── QUICKSTART.md                     # Quick start guide
├── PROJECT_OVERVIEW.md               # This file
├── install.sh                        # Linux installation script
└── install.bat                       # Windows installation script
```

---

## 🗄️ Database Schema

### Tables

#### 1. users
Stores user account information.

| Column | Type | Description |
|--------|------|-------------|
| user_id | INT (PK) | Unique user identifier |
| full_name | VARCHAR(100) | User's full name |
| email | VARCHAR(100) | Unique email address |
| password_hash | VARCHAR(255) | Bcrypt hashed password |
| phone | VARCHAR(20) | Phone number (optional) |
| role | ENUM | 'user' or 'admin' |
| created_at | TIMESTAMP | Account creation time |
| last_login | TIMESTAMP | Last login time |
| is_active | BOOLEAN | Account status |

#### 2. complaints
Stores all complaint records.

| Column | Type | Description |
|--------|------|-------------|
| complaint_id | INT (PK) | Unique complaint ID |
| user_id | INT (FK) | User who submitted |
| complaint_text | TEXT | Complaint description |
| priority | ENUM | High/Medium/Low/Other |
| status | ENUM | Registered/Under Review/In Progress/Resolved |
| submitted_at | TIMESTAMP | Submission time |
| updated_at | TIMESTAMP | Last update time |
| resolved_at | TIMESTAMP | Resolution time |
| admin_notes | TEXT | Admin comments |

#### 3. complaint_status_history
Tracks all status changes.

| Column | Type | Description |
|--------|------|-------------|
| history_id | INT (PK) | Unique history record |
| complaint_id | INT (FK) | Related complaint |
| old_status | ENUM | Previous status |
| new_status | ENUM | New status |
| changed_by | INT (FK) | Admin who made change |
| changed_at | TIMESTAMP | Change timestamp |
| notes | TEXT | Change notes |

#### 4. ml_predictions_log
Logs all ML predictions.

| Column | Type | Description |
|--------|------|-------------|
| log_id | INT (PK) | Unique log ID |
| complaint_id | INT (FK) | Related complaint |
| predicted_priority | ENUM | ML prediction |
| confidence_score | DECIMAL | Prediction confidence |
| model_version | VARCHAR | Model version used |
| predicted_at | TIMESTAMP | Prediction time |

#### 5. admin_activity_log
Tracks admin actions.

| Column | Type | Description |
|--------|------|-------------|
| log_id | INT (PK) | Unique log ID |
| admin_id | INT (FK) | Admin user |
| activity_type | VARCHAR | Type of activity |
| activity_description | TEXT | Activity details |
| related_complaint_id | INT (FK) | Related complaint |
| activity_timestamp | TIMESTAMP | Activity time |
| ip_address | VARCHAR | Admin IP address |

#### 6. user_sessions
Manages active sessions.

| Column | Type | Description |
|--------|------|-------------|
| session_id | VARCHAR (PK) | Session identifier |
| user_id | INT (FK) | User ID |
| created_at | TIMESTAMP | Session creation |
| expires_at | TIMESTAMP | Expiration time |
| ip_address | VARCHAR | Client IP |
| user_agent | TEXT | Browser info |

### Views

1. **complaint_stats_by_priority**: Aggregated statistics by priority
2. **user_complaint_summary**: Per-user complaint summary
3. **recent_activity**: Recent complaint activity

### Stored Procedures

1. **UpdateComplaintStatus**: Updates status with logging
2. **GetComplaintLifecycle**: Retrieves complete complaint history

### Triggers

1. **after_complaint_insert**: Auto-creates initial status history

---

## 🔌 API Documentation

### ML API Endpoints (Flask - Port 5000)

#### POST /predict
Predicts complaint priority.

**Request:**
```json
{
  "complaint_text": "Server is down"
}
```

**Response:**
```json
{
  "priority": "High",
  "confidence": 0.95,
  "all_scores": {
    "High": 0.95,
    "Medium": 0.03,
    "Low": 0.01,
    "Other": 0.01
  },
  "model_version": "v1.0"
}
```

#### GET /health
Health check endpoint.

**Response:**
```json
{
  "status": "healthy",
  "model_loaded": true,
  "version": "v1.0"
}
```

#### GET /stats
Model statistics.

**Response:**
```json
{
  "total_samples": 2598,
  "priority_distribution": {
    "High": 650,
    "Medium": 650,
    "Low": 650,
    "Other": 648
  },
  "classes": ["High", "Low", "Medium", "Other"],
  "model_type": "Naive Bayes with TF-IDF"
}
```

#### POST /train
Retrain the model.

**Response:**
```json
{
  "message": "Model retrained successfully",
  "model_version": "v1.0"
}
```

### Backend API Endpoints (PHP)

#### POST /backend/login_handler.php
User login.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "role": "user"
  }
}
```

#### POST /backend/register_handler.php
User registration.

**Request:**
```json
{
  "fullName": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "password": "password123"
}
```

#### POST /backend/submit_complaint.php
Submit new complaint.

**Request:**
```json
{
  "complaint_text": "Application is not responding"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Complaint submitted successfully",
  "data": {
    "complaint_id": 123,
    "priority": "Medium"
  }
}
```

#### POST /backend/update_status.php
Update complaint status (Admin only).

**Request:**
```json
{
  "complaint_id": 123,
  "new_status": "In Progress",
  "notes": "Working on resolution"
}
```

#### GET /backend/get_complaint_details.php?id=123
Get complaint details.

**Response:**
```json
{
  "success": true,
  "data": {
    "details": { /* complaint info */ },
    "history": [ /* status changes */ ]
  }
}
```

---

## 🤖 ML Model Details

### Algorithm: Multinomial Naive Bayes with TF-IDF

#### Why This Approach?

1. **Naive Bayes**: 
   - Fast training and prediction
   - Works well with text classification
   - Handles multi-class problems naturally
   - Good baseline performance

2. **TF-IDF (Term Frequency-Inverse Document Frequency)**:
   - Converts text to numerical features
   - Emphasizes important words
   - Reduces impact of common words
   - Creates sparse feature matrix

### Model Pipeline

```
Input Text
    ↓
[TF-IDF Vectorizer]
    ↓
Feature Vector (5000 dimensions)
    ↓
[Multinomial Naive Bayes]
    ↓
Priority Prediction + Confidence
```

### Training Process

1. **Data Loading**: Load CSV with complaint text and labels
2. **Preprocessing**: Clean and tokenize text
3. **Feature Extraction**: TF-IDF with:
   - Max 5000 features
   - 1-2 word n-grams
   - English stop words removed
   - Min document frequency: 2
4. **Model Training**: Multinomial NB with alpha=0.1
5. **Evaluation**: Classification report and accuracy
6. **Serialization**: Save model as pickle file

### Performance Metrics

The model is evaluated on:
- **Accuracy**: Overall correct predictions
- **Precision**: Correct positive predictions per class
- **Recall**: Actual positives found per class
- **F1-Score**: Harmonic mean of precision and recall

Expected performance: ~85-95% accuracy depending on data quality.

### Model Retraining

To retrain with new data:
1. Update `data.csv` with new examples
2. Call `/train` endpoint, or
3. Delete `complaint_model.pkl` and restart API

---

## 🚀 Installation Guide

### Prerequisites

- PHP 8.x with PDO MySQL
- MySQL 8.0+
- Python 3.8+
- pip3
- Web server (Apache/Nginx or PHP built-in)

### Quick Install

#### Using Installation Scripts

**Linux/Mac:**
```bash
chmod +x install.sh
./install.sh
```

**Windows:**
```cmd
install.bat
```

#### Manual Installation

1. **Setup Database:**
```bash
mysql -u root -p < database/schema.sql
```

2. **Configure Backend:**
Edit `backend/config.php` with your database credentials.

3. **Install Python Dependencies:**
```bash
cd ml_model
pip3 install -r requirements.txt
```

4. **Start ML API:**
```bash
python3 ml_api.py
```

5. **Start Web Server:**
```bash
cd frontend
php -S localhost:8000
```

6. **Access Application:**
Visit http://localhost:8000/login.php

### Default Credentials

- **Admin**
  - Email: admin@complaint.com
  - Password: Admin@123

**⚠️ Change these credentials after first login!**

---

## 📖 Usage Guide

### For End Users

1. **Register Account**
   - Click "Register" on login page
   - Fill in details
   - Submit

2. **Submit Complaint**
   - Login to dashboard
   - Click "New Complaint"
   - Describe your issue
   - Submit (ML automatically classifies priority)

3. **Track Status**
   - View all your complaints
   - See current status
   - Check status history
   - View timestamps

### For Administrators

1. **Access Admin Dashboard**
   - Login with admin credentials
   - View statistics overview

2. **Manage Complaints**
   - View all complaints
   - Filter by priority/status
   - Search for specific complaints
   - View user details

3. **Update Status**
   - Click "Edit" on any complaint
   - Select new status
   - Add notes
   - Submit update

4. **Monitor System**
   - Check statistics
   - Review recent activity
   - Analyze priorities
   - Track resolution times

---

## ☁️ Deployment

### AWS Deployment

Detailed guide: `config/aws_setup.md`

**Architecture:**
- EC2 for web and ML servers
- RDS for MySQL database
- Load Balancer (optional)
- Route 53 for DNS
- CloudWatch for monitoring

**Estimated Cost:** $50-70/month

### Alternative Platforms

- **Heroku**: Easy deployment with buildpacks
- **DigitalOcean**: Simple droplet setup
- **Google Cloud**: App Engine or Compute Engine
- **Azure**: App Service or VMs

---

## 🔒 Security Features

1. **Authentication**
   - Password hashing (bcrypt)
   - Session management
   - Session timeout
   - Login attempt limiting

2. **Data Protection**
   - SQL injection prevention (prepared statements)
   - XSS protection (input sanitization)
   - CSRF protection ready
   - Secure session cookies

3. **Access Control**
   - Role-based permissions
   - Admin-only endpoints
   - User data isolation

4. **Logging**
   - Admin activity tracking
   - Status change history
   - ML prediction logging
   - Complete audit trail

5. **Network Security**
   - HTTPS support
   - CORS configuration
   - Security headers
   - IP logging

---

## 🔧 Troubleshooting

### Common Issues

#### Database Connection Failed
```bash
# Check MySQL is running
sudo systemctl status mysql

# Verify credentials in config.php
# Test connection
mysql -u your_user -p
```

#### ML API Not Responding
```bash
# Check if running
ps aux | grep ml_api

# Check logs
tail -f ml_api.log

# Restart
pkill -f ml_api.py
python3 ml_api.py &
```

#### PHP Errors
```bash
# Check Apache logs
tail -f /var/log/apache2/error.log

# Enable error display (dev only)
# In php.ini: display_errors = On
```

#### Port Already in Use
```bash
# Find process using port
lsof -i :5000

# Kill process
kill -9 PID
```

### Debug Mode

Enable in `backend/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 📊 Performance Optimization

1. **Database**
   - Regular indexing
   - Query optimization
   - Connection pooling

2. **Caching**
   - Redis for sessions
   - Memcached for queries
   - CDN for static assets

3. **ML API**
   - Model caching in memory
   - Batch predictions
   - Async processing

4. **Frontend**
   - Minify CSS/JS
   - Image optimization
   - Lazy loading

---

## 🤝 Contributing

Contributions welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

---

## 📄 License

MIT License - See LICENSE file for details

---

## 📞 Support

- **Documentation**: README.md, QUICKSTART.md
- **Issues**: GitHub Issues
- **Email**: admin@complaint.com

---

**System Version:** 1.0.0
**Last Updated:** January 2026
**Status:** Production Ready ✅

# Smart Complaint Management System

A cloud-based AI-powered complaint management system that automatically classifies complaint priorities using Machine Learning.

## Features

- **User Management**: Registration, login, and secure authentication
- **Complaint Submission**: Users can submit complaints with automatic ML-based priority classification
- **Real-time Tracking**: Track complaint status from submission to resolution
- **Admin Dashboard**: 
  - View all complaints with filtering options
  - Prioritize high-priority complaints
  - Update complaint status
  - View statistics and analytics
- **ML Integration**: Automatic priority classification (Other, Low, Medium, High)
- **Complete Lifecycle Tracking**: Timestamps for all status changes

## Technology Stack

- **Frontend**: HTML5, Bootstrap 5, JavaScript
- **Backend**: PHP 8.x
- **Database**: MySQL 8.x
- **ML Model**: Python 3.x with Flask API
  - Scikit-learn (Naive Bayes + TF-IDF)
  - Flask-CORS for API integration
- **Cloud**: AWS (EC2, RDS)

## Project Structure

```
ComplaintManagementSystem/
├── backend/
│   ├── config.php              # Configuration and database connection
│   ├── auth.php                # Authentication logic
│   ├── complaint_api.php       # Complaint management API
│   ├── login_handler.php       # Login endpoint
│   ├── register_handler.php    # Registration endpoint
│   ├── submit_complaint.php    # Submit complaint endpoint
│   ├── update_status.php       # Update status endpoint
│   └── get_complaint_details.php # Get details endpoint
├── frontend/
│   ├── login.php               # Login page
│   ├── register.php            # Registration page
│   ├── dashboard.php           # User dashboard
│   ├── admin_dashboard.php     # Admin dashboard
│   └── logout.php              # Logout handler
├── ml_model/
│   ├── ml_api.py               # Flask ML API
│   ├── requirements.txt        # Python dependencies
│   └── complaint_model.pkl     # Trained model (generated)
├── database/
│   └── schema.sql              # Database schema
├── config/
│   └── aws_setup.md            # AWS deployment guide
└── README.md
```

## Installation

### Prerequisites

- PHP 8.x with PDO MySQL extension
- MySQL 8.x
- Python 3.8+
- Composer (optional)
- Web server (Apache/Nginx)

### Local Setup

1. **Clone the repository**
   ```bash
   cd ComplaintManagementSystem
   ```

2. **Set up the database**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Configure the backend**
   - Edit `backend/config.php`
   - Update database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'complaint_management_system');
     ```

4. **Install Python dependencies**
   ```bash
   cd ml_model
   pip install -r requirements.txt
   ```

5. **Start the ML API**
   ```bash
   python ml_api.py
   ```
   The API will run on `http://localhost:5000`

6. **Start the web server**
   - For Apache: Place the project in `htdocs` or `www` folder
   - For PHP built-in server:
     ```bash
     cd frontend
     php -S localhost:8000
     ```

7. **Access the application**
   - Open browser: `http://localhost:8000/login.php`
   - Default admin credentials:
     - Email: `admin@complaint.com`
     - Password: `Admin@123`

## ML Model Training

The ML model is automatically trained on first run using the provided dataset. To retrain:

1. **Update the dataset**: Modify `data.csv` with new training data
2. **Retrain via API**:
   ```bash
   curl -X POST http://localhost:5000/train
   ```
3. **Or restart the ML API** (it will detect missing model and train automatically)

## API Endpoints

### ML API (Python Flask - Port 5000)

- `POST /predict` - Predict complaint priority
  ```json
  {
    "complaint_text": "Server is down"
  }
  ```
  Response:
  ```json
  {
    "priority": "High",
    "confidence": 0.95,
    "model_version": "v1.0"
  }
  ```

- `GET /health` - Health check
- `GET /stats` - Model statistics
- `POST /train` - Retrain model

### Backend API (PHP)

- `POST /backend/login_handler.php` - User login
- `POST /backend/register_handler.php` - User registration
- `POST /backend/submit_complaint.php` - Submit complaint
- `POST /backend/update_status.php` - Update complaint status (Admin)
- `GET /backend/get_complaint_details.php?id=X` - Get complaint details

## Database Schema

The system uses 6 main tables:

1. **users** - User accounts (admin and regular users)
2. **complaints** - Complaint records
3. **complaint_status_history** - Status change tracking
4. **ml_predictions_log** - ML prediction logging
5. **admin_activity_log** - Admin action tracking
6. **user_sessions** - Secure session management

## Security Features

- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- Session timeout management
- CSRF protection ready
- Role-based access control

## AWS Deployment

See `config/aws_setup.md` for detailed AWS deployment instructions.

### Quick AWS Setup

1. **EC2 Instance**: Launch Ubuntu 22.04 instance
2. **RDS Database**: Create MySQL 8.0 database
3. **Security Groups**: Configure ports 80, 443, 5000
4. **Install Dependencies**: PHP, MySQL client, Python
5. **Deploy Application**: Upload files and configure
6. **Start Services**: Apache, MySQL, Flask API

## Usage

### For Users

1. Register an account
2. Login to dashboard
3. Submit a complaint
4. Track complaint status in real-time
5. View complaint history

### For Administrators

1. Login with admin credentials
2. View all complaints with filters
3. Prioritize complaints by priority level
4. Update complaint status (Registered → Under Review → In Progress → Resolved)
5. View analytics and statistics

## Priority Classification

The ML model classifies complaints into 4 categories:

- **High**: Critical issues, system failures, security breaches
- **Medium**: Performance issues, delays, moderate problems
- **Low**: Feature requests, minor issues, improvements
- **Other**: General inquiries, greetings, non-complaints

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License.

## Support

For issues or questions:
- Create an issue in the repository
- Contact: admin@complaint.com

## Acknowledgments

- Scikit-learn for ML framework
- Bootstrap for UI components
- Flask for Python API

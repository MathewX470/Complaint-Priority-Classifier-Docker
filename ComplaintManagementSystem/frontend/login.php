<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Complaint Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border-radius: 15px;
        }
        .logo-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 0 0 15px;
            padding: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card login-card">
                    <div class="row g-0">
                        <div class="col-md-5 logo-section d-none d-md-flex flex-column justify-content-center align-items-center">
                            <i class="bi bi-headset" style="font-size: 80px;"></i>
                            <h3 class="mt-3 text-center">Smart Complaint Management</h3>
                            <p class="text-center mt-2">AI-Powered Priority Classification</p>
                        </div>
                        <div class="col-md-7">
                            <div class="card-body p-5">
                                <h2 class="mb-4">Login</h2>
                                
                                <div id="alertMessage"></div>
                                
                                <form id="loginForm">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 mb-3">
                                        <i class="bi bi-box-arrow-in-right"></i> Login
                                    </button>
                                </form>
                                
                                <div class="text-center">
                                    <p class="mb-0">Don't have an account? <a href="register.php">Register</a></p>
                                </div>
                                
                                <div class="mt-4 p-3 bg-light rounded">
                                    <small class="text-muted">
                                        <strong>Demo Credentials:</strong><br>
                                        Admin: admin@complaint.com / Admin@123<br>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            try {
                const response = await fetch('../backend/login_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.role === 'admin') {
                        window.location.href = 'admin_dashboard.php';
                    } else {
                        window.location.href = 'dashboard.php';
                    }
                } else {
                    showAlert('danger', data.message);
                }
            } catch (error) {
                showAlert('danger', 'Login failed. Please try again.');
            }
        });
        
        function showAlert(type, message) {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        }
        
        // Check for timeout parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('timeout') === '1') {
            showAlert('warning', 'Your session has expired. Please login again.');
        }
    </script>
</body>
</html>

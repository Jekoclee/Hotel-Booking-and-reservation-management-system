<?php
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - LCR Website</title>
    <?php require('inc/links.php'); ?>
    <style>
        .forgot-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        .forgot-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        .forgot-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .forgot-header h2 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        .forgot-header p {
            color: #666;
            font-size: 0.9rem;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            margin-bottom: 1rem;
        }
        .btn-forgot {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s;
        }
        .btn-forgot:hover {
            transform: translateY(-2px);
            color: white;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-header">
                <h2><i class="bi bi-key"></i> Forgot Password</h2>
                <p>Enter your email address and we'll send you a reset link</p>
            </div>

            <div id="forgot-alert"></div>

            <form id="forgot-form">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input name="email" type="email" class="form-control" placeholder="Enter your email" required>
                </div>
                
                <button type="submit" class="btn btn-forgot">
                    <i class="bi bi-envelope"></i> Send Reset Link
                </button>
            </form>

            <div class="back-link">
                <a href="index.php">← Back to Login</a>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>
    
    <script>
        let forgot_form = document.getElementById('forgot-form');
        
        forgot_form.addEventListener('submit', (e) => {
            e.preventDefault();
            let data = new FormData();
            data.append('email', forgot_form.elements['email'].value);
            data.append('forgot_password', '');
            
            var myModal = document.getElementById('forgot-alert');
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/login_register.php", true);
            
            xhr.onload = function() {
                if (this.responseText == 1) {
                    myModal.innerHTML = `<div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Password reset link sent to your email!
                    </div>`;
                    forgot_form.reset();
                } else if (this.responseText == 'inv_email') {
                    myModal.innerHTML = `<div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Email address not found!
                    </div>`;
                } else if (this.responseText == 'mail_failed') {
                    myModal.innerHTML = `<div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Failed to send email. Please try again!
                    </div>`;
                } else {
                    myModal.innerHTML = `<div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Something went wrong. Please try again!
                    </div>`;
                }
            }
            
            xhr.send(data);
        });
    </script>
</body>
</html>
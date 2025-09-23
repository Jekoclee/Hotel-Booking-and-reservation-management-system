<?php
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

$token_valid = false;
$user_data = null;

if(isset($_GET['email']) && isset($_GET['token'])) {
    $data = filteration($_GET);
    
    // Check if the token is valid and not expired
    $verify_query = select("SELECT * FROM `user_cred` WHERE `email`=? AND `token`=? AND `t_expire` > NOW() LIMIT 1", 
                          [$data['email'], $data['token']], "ss");
    
    if(mysqli_num_rows($verify_query) == 1) {
        $token_valid = true;
        $user_data = mysqli_fetch_assoc($verify_query);
    }
}

if(isset($_POST['reset_password'])) {
    $data = filteration($_POST);
    
    if($data['new_password'] != $data['confirm_password']) {
        $error = "Passwords do not match!";
    } else {
        // Verify token again
        $verify_query = select("SELECT * FROM `user_cred` WHERE `email`=? AND `token`=? AND `t_expire` > NOW() LIMIT 1", 
                              [$data['email'], $data['token']], "ss");
        
        if(mysqli_num_rows($verify_query) == 1) {
            $user_fetch = mysqli_fetch_assoc($verify_query);
            
            // Hash new password
            $new_password = password_hash($data['new_password'], PASSWORD_BCRYPT);
            
            // Update password and clear token
            $update_query = "UPDATE `user_cred` SET `password`=?, `token`=?, `t_expire`=? WHERE `id`=?";
            $update_values = [$new_password, null, null, $user_fetch['id']];
            
            if(update($update_query, $update_values, 'sssi')) {
                $success = "Password reset successful! You can now login with your new password.";
            } else {
                $error = "Failed to reset password. Please try again.";
            }
        } else {
            $error = "Invalid or expired reset link!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - LCR Website</title>
    <?php require('inc/links.php'); ?>
    <style>
        .reset-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .reset-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .reset-header h2 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        .reset-header p {
            color: #666;
            font-size: 0.9rem;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            margin-bottom: 1rem;
        }
        .btn-reset {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s;
        }
        .btn-reset:hover {
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
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <h2><i class="bi bi-shield-lock"></i> Reset Password</h2>
                <p>Enter your new password below</p>
            </div>

            <?php if(isset($success)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= $success ?>
                </div>
                <div class="back-link">
                    <a href="index.php">← Back to Login</a>
                </div>
            <?php elseif(!$token_valid): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> Invalid or expired reset link!
                </div>
                <div class="back-link">
                    <a href="index.php">← Back to Login</a>
                </div>
            <?php else: ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email']) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input name="new_password" type="password" class="form-control" placeholder="Enter new password" required minlength="6">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input name="confirm_password" type="password" class="form-control" placeholder="Confirm new password" required minlength="6">
                    </div>
                    
                    <button type="submit" name="reset_password" class="btn btn-reset">
                        <i class="bi bi-shield-check"></i> Reset Password
                    </button>
                </form>

                <div class="back-link">
                    <a href="index.php">← Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>
</body>
</html>
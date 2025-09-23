<?php

require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');
require("../inc/sendgrid/sendgrid-php.php");

function send_mail($uemail, $name, $token)
{

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("villamorjerichoivan@gmail.com", "Jeko");
    $email->setSubject("Account Verification Link");

    $email->addTo($uemail, $name);


    $email->addContent(
        "text/html",
        "click the link to confirm it: <br>
        <a href='" . SITE_URL . "email_confirm.php?email=$uemail&token=$token" . "'>Click me</a>
        "
    );
    $sendgrid = new \SendGrid(SENDGRID_API_KEY);
    try {
        $sendgrid->send($email);
        return 1;
    } catch (Exception $e) {
        return 0;
    }
}

if (isset($_POST['register'])) {
    $data = filteration($_POST);

    //match pass and cpass

    if ($data['pass'] != $data['cpass']) {
        echo 'pass_mismatch';
        exit;
    }

    // check user exist

    $u_exist = select("SELECT * FROM `user_cred` WHERE `email` = ? AND `phonenum` = ? LIMIT 1", [$data['email'], $data['phonenum']], "ss");

    if (mysqli_num_rows($u_exist) != 0) {
        $u_exist_fetch = mysqli_fetch_assoc($u_exist);
        echo ($u_exist_fetch['email'] == $data['email']) ? 'email_already' : 'phone_already';
        exit;
    }
    ///upload
    $img = uploadUserImage($_FILES['profile']);

    if ($img == 'inv_img') {
        echo 'inv_img';
        exit;
    } else if ($img == 'upd_failed') {
        echo 'upd_failed';
        exit;
    }

    // send confirmation link

    $token = bin2hex(random_bytes(16));

    if (!send_mail($data['email'], $data['name'], $token)) {
        echo 'mail_failed';
        exit;
    }
    $enc_pass = password_hash($data['pass'], PASSWORD_BCRYPT);
    $query = "INSERT INTO `user_cred`(`name`, `email`, `address`, `phonenum`, `pincode`, `dob`, `profile`, 
    `password`,`token`) VALUES (?,?,?,?,?,?,?,?,?)";

    $values = [$data['name'], $data['email'], $data['address'], $data['phonenum'], $data['pincode'], $data['dob'], $img, $enc_pass, $token];

    if (insert($query, $values, 'sssssssss')) {
        echo 1;
    } else {
        echo 'ins_failed';
    }
}

if (isset($_POST['login'])) {
    $data = filteration($_POST);

    $u_exist = select("SELECT * FROM `user_cred` WHERE `email` = ? OR `phonenum` = ? LIMIT 1", [$data['email'], $data['email']], "ss");

    if (mysqli_num_rows($u_exist) == 0) {
        echo 'inv_email_mob';
    } else {
        $u_fetch = mysqli_fetch_assoc($u_exist);
        if ($u_fetch['is_verified'] == 0) {
            echo 'not_verified';
        } else if ($u_fetch['status'] == 0) {
            echo 'acc_inactive';
        } else if ($u_fetch['banned'] == 1) {
            echo 'acc_banned';
        } else {
            if (!password_verify($data['pass'], $u_fetch['password'])) {
                echo 'invalid_pass';
            } else {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['login'] = true;
                $_SESSION['uId'] = $u_fetch['id'];
                $_SESSION['uName'] = $u_fetch['name'];
                $_SESSION['uEmail'] = $u_fetch['email'];
                $_SESSION['uPic'] = $u_fetch['profile'];
                $_SESSION['uPhone'] = $u_fetch['phonenum'];
                
                // Debug: Log session data
                error_log("Login successful for user: " . $u_fetch['name']);
                error_log("Session data set: " . print_r($_SESSION, true));
                
                echo 1;
            }
        }
    }
}

if (isset($_POST['forgot_password'])) {
    $data = filteration($_POST);
    
    // Check if user exists
    $u_exist = select("SELECT * FROM `user_cred` WHERE `email` = ? LIMIT 1", [$data['email']], "s");
    
    if (mysqli_num_rows($u_exist) == 0) {
        echo 'inv_email';
    } else {
        $u_fetch = mysqli_fetch_assoc($u_exist);
        
        // Generate reset token
        $reset_token = bin2hex(random_bytes(16));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
        
        // Update user with reset token
        $update_query = "UPDATE `user_cred` SET `token`=?, `t_expire`=? WHERE `id`=?";
        $update_values = [$reset_token, $token_expiry, $u_fetch['id']];
        
        if (update($update_query, $update_values, 'ssi')) {
            // Send password reset email
            if (send_reset_email($data['email'], $u_fetch['name'], $reset_token)) {
                echo 1;
            } else {
                echo 'mail_failed';
            }
        } else {
            echo 'upd_failed';
        }
    }
}

// New: Update logged-in user's profile
if (isset($_POST['update_profile'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        echo 'not_logged_in';
        return;
    }

    $uid = (int)($_SESSION['uId'] ?? 0);
    if ($uid <= 0) {
        echo 'not_logged_in';
        return;
    }

    $data = filteration($_POST);

    $name = $data['name'] ?? '';
    $phone = $data['phonenum'] ?? '';
    $address = $data['address'] ?? '';
    $pincode = $data['pincode'] ?? '';
    $dob = $data['dob'] ?? '';

    // Basic validation
    if ($name === '' || $phone === '') {
        echo 'missing_fields';
        return;
    }

    // Ensure phone uniqueness for other users
    $check = select("SELECT id FROM user_cred WHERE phonenum=? AND id!=? LIMIT 1", [$phone, $uid], 'si');
    if ($check && mysqli_num_rows($check) > 0) {
        echo 'phone_already';
        return;
    }

    // Fetch current profile image
    $cur = select("SELECT profile FROM user_cred WHERE id=? LIMIT 1", [$uid], 'i');
    $cur_img = '';
    if ($cur && mysqli_num_rows($cur) === 1) {
        $cur_img = mysqli_fetch_assoc($cur)['profile'] ?? '';
    }

    $img = '';
    if (isset($_FILES['profile']) && isset($_FILES['profile']['name']) && $_FILES['profile']['name'] !== '') {
        $img = uploadUserImage($_FILES['profile']);
        if ($img === 'inv_img') {
            echo 'inv_img';
            return;
        } else if ($img === 'upd_failed') {
            echo 'upd_failed';
            return;
        }
    }

    // Build update query
    if ($img) {
        $q = "UPDATE `user_cred` SET `name`=?, `phonenum`=?, `address`=?, `pincode`=?, `dob`=?, `profile`=? WHERE `id`=?";
        $values = [$name, $phone, $address, $pincode, $dob, $img, $uid];
        $types = 'ssssssi';
    } else {
        $q = "UPDATE `user_cred` SET `name`=?, `phonenum`=?, `address`=?, `pincode`=?, `dob`=? WHERE `id`=?";
        $values = [$name, $phone, $address, $pincode, $dob, $uid];
        $types = 'sssssi';
    }

    $res = update($q, $values, $types);

    if ($res) {
        // Delete old image if a new one uploaded
        if ($img && !empty($cur_img)) {
            $old_path = UPLOAD_IMAGE_PATH . USERS_FOLDER . $cur_img;
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }

        // Refresh session data
        $_SESSION['uName'] = $name;
        $_SESSION['uPhone'] = $phone;
        if ($img) {
            $_SESSION['uPic'] = $img;
        }

        echo 1;
    } else {
        echo 'upd_failed';
    }
}

function send_reset_email($uemail, $name, $token)
{
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("villamorjerichoivan@gmail.com", "LCR Website");
    $email->setSubject("Password Reset Request");

    $email->addTo($uemail, $name);

    $email->addContent(
        "text/html",
        "Hello $name,<br><br>
        You have requested to reset your password. Click the link below to reset your password:<br><br>
        <a href='" . SITE_URL . "reset_password.php?email=$uemail&token=$token" . "' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a><br><br>
        This link will expire in 1 hour.<br><br>
        If you did not request this password reset, please ignore this email.<br><br>
        Best regards,<br>
        LCR Website Team"
    );
    
    $sendgrid = new \SendGrid(SENDGRID_API_KEY);
    try {
        $sendgrid->send($email);
        return 1;
    } catch (Exception $e) {
        return 0;
    }
}

<?php

require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

if(isset($_GET['email']) && isset($_GET['token'])) {
    $data = filteration($_GET);
    
    // Check if the token and email match in database
    $verify_query = select("SELECT * FROM `user_cred` WHERE `email`=? AND `token`=? AND `is_verified`=0 LIMIT 1", 
                          [$data['email'], $data['token']], "ss");
    
    if(mysqli_num_rows($verify_query) == 1) {
        $verify_fetch = mysqli_fetch_assoc($verify_query);
        
        // Check if token has expired (optional - you can set expiration logic)
        // For now, we'll just verify without expiration check
        
        // Update user as verified
        $update_query = "UPDATE `user_cred` SET `is_verified`=?, `token`=? WHERE `id`=?";
        $update_values = [1, null, $verify_fetch['id']];
        
        if(update($update_query, $update_values, 'isi')) {
            echo "<script>alert('Email verification successful! You can now login.'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Verification failed! Please try again.'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid verification link or email already verified!'); window.location.href='index.php';</script>";
    }
} else {
    echo "<script>alert('Invalid verification link!'); window.location.href='index.php';</script>";
}

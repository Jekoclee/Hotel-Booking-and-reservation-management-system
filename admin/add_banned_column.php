<?php
require('inc/db_config.php');

// Add banned column to user_cred table
$query = "ALTER TABLE user_cred ADD COLUMN banned TINYINT(1) DEFAULT 0 AFTER status";

if (mysqli_query($con, $query)) {
    echo "Column 'banned' added successfully to user_cred table.";
} else {
    // Check if column already exists
    if (mysqli_errno($con) == 1060) {
        echo "Column 'banned' already exists in user_cred table.";
    } else {
        echo "Error adding column: " . mysqli_error($con);
    }
}

mysqli_close($con);
?>
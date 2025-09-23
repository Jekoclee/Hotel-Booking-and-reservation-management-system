<?php
require('admin/inc/db_config.php');

// Insert a test booking
$query = "INSERT INTO bookings (room_id, user_id, check_in, check_out, booking_status, total_amount, created_at) 
          VALUES (1, 1, '2025-09-19', '2025-09-21', 'confirmed', 200.00, NOW())";

if ($con->query($query)) {
    echo "Test booking created successfully\n";
    echo "Room ID: 1\n";
    echo "Check-in: 2025-09-19\n";
    echo "Check-out: 2025-09-21\n";
    echo "Status: confirmed\n";
} else {
    echo "Error creating test booking: " . $con->error . "\n";
}

$con->close();

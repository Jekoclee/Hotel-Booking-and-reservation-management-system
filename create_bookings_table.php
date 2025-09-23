<?php
require('admin/inc/db_config.php');

// Create bookings table to prevent double booking
$create_bookings_table = "
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `booking_id` varchar(50) NOT NULL UNIQUE,
    `room_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `guest_name` varchar(100) NOT NULL,
    `guest_email` varchar(100) NOT NULL,
    `guest_phone` varchar(20) NOT NULL,
    `check_in` date NOT NULL,
    `check_out` date NOT NULL,
    `adults` int(11) NOT NULL DEFAULT 1,
    `children` int(11) NOT NULL DEFAULT 0,
    `total_amount` decimal(10,2) NOT NULL,
    `special_requests` text DEFAULT NULL,
    `booking_status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
    `payment_status` enum('pending','paid','refunded') NOT NULL DEFAULT 'pending',
    `booking_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `room_id` (`room_id`),
    KEY `check_in` (`check_in`),
    KEY `check_out` (`check_out`),
    KEY `booking_status` (`booking_status`),
    CONSTRAINT `fk_bookings_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if (mysqli_query($con, $create_bookings_table)) {
    echo "Bookings table created successfully!<br>";
} else {
    echo "Error creating bookings table: " . mysqli_error($con) . "<br>";
}

// Create room_availability table for more detailed availability tracking
$create_availability_table = "
CREATE TABLE IF NOT EXISTS `room_availability` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `room_id` int(11) NOT NULL,
    `date` date NOT NULL,
    `available_quantity` int(11) NOT NULL DEFAULT 0,
    `total_quantity` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `room_date` (`room_id`, `date`),
    KEY `date` (`date`),
    CONSTRAINT `fk_availability_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if (mysqli_query($con, $create_availability_table)) {
    echo "Room availability table created successfully!<br>";
} else {
    echo "Error creating room availability table: " . mysqli_error($con) . "<br>";
}

// Insert sample booking data for testing
$sample_bookings = [
    [
        'booking_id' => 'LCR20250119001',
        'room_id' => 1,
        'guest_name' => 'John Doe',
        'guest_email' => 'john@example.com',
        'guest_phone' => '+63 912 345 6789',
        'check_in' => '2025-01-25',
        'check_out' => '2025-01-27',
        'adults' => 2,
        'children' => 0,
        'total_amount' => 5000.00,
        'booking_status' => 'confirmed',
        'payment_status' => 'paid'
    ],
    [
        'booking_id' => 'LCR20250119002',
        'room_id' => 1,
        'guest_name' => 'Jane Smith',
        'guest_email' => 'jane@example.com',
        'guest_phone' => '+63 912 345 6790',
        'check_in' => '2025-01-28',
        'check_out' => '2025-01-30',
        'adults' => 1,
        'children' => 1,
        'total_amount' => 4000.00,
        'booking_status' => 'confirmed',
        'payment_status' => 'paid'
    ]
];

foreach ($sample_bookings as $booking) {
    $insert_booking = "INSERT INTO `bookings` 
        (`booking_id`, `room_id`, `guest_name`, `guest_email`, `guest_phone`, 
         `check_in`, `check_out`, `adults`, `children`, `total_amount`, 
         `booking_status`, `payment_status`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($con, $insert_booking);
    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            'sissssiiidss',
            $booking['booking_id'],
            $booking['room_id'],
            $booking['guest_name'],
            $booking['guest_email'],
            $booking['guest_phone'],
            $booking['check_in'],
            $booking['check_out'],
            $booking['adults'],
            $booking['children'],
            $booking['total_amount'],
            $booking['booking_status'],
            $booking['payment_status']
        );

        if (mysqli_stmt_execute($stmt)) {
            echo "Sample booking {$booking['booking_id']} inserted successfully!<br>";
        } else {
            echo "Error inserting booking {$booking['booking_id']}: " . mysqli_stmt_error($stmt) . "<br>";
        }
        mysqli_stmt_close($stmt);
    }
}

echo "<br><strong>Database setup completed!</strong><br>";
echo "<a href='booking_calendar.php'>Go to Booking Calendar</a>";

mysqli_close($con);

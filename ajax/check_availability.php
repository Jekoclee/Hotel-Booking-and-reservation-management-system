<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

header('Content-Type: application/json');
// Prevent caching to ensure calendar reflects latest availability after admin actions
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Allow both GET and POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Get room_id from either GET or POST
$room_id = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : null;
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    $room_id = isset($input['room_id']) ? (int)$input['room_id'] : null;
}

if (!$room_id) {
    echo json_encode(['error' => 'Room ID is required']);
    exit;
}

try {
    // Get booked dates from bookings table (exclude deleted bookings)
    $query = "SELECT DISTINCT DATE(check_in) as booked_date
              FROM bookings 
              WHERE room_id = ? 
              AND booking_status IN ('confirmed', 'pending')
              AND removed = 0
              AND check_in >= CURDATE()
              UNION
              SELECT DISTINCT check_out as booked_date
              FROM bookings 
              WHERE room_id = ? 
              AND booking_status IN ('confirmed', 'pending')
              AND removed = 0
              AND check_out >= CURDATE()
              UNION
              SELECT DISTINCT date as booked_date
              FROM room_availability 
              WHERE room_id = ? 
              AND available_quantity = 0 
              AND date >= CURDATE()
              ORDER BY booked_date";

    $stmt = $con->prepare($query);
    $stmt->bind_param('iii', $room_id, $room_id, $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $booked_dates = [];
    while ($row = $result->fetch_assoc()) {
        $booked_dates[] = $row['booked_date'];
    }

    // Also get date ranges that are booked (between check_in and check_out), exclude deleted bookings
    $range_query = "SELECT check_in, check_out
                    FROM bookings 
                    WHERE room_id = ? 
                    AND booking_status IN ('confirmed', 'pending')
                    AND removed = 0
                    AND check_out >= CURDATE()";

    $range_stmt = $con->prepare($range_query);
    $range_stmt->bind_param('i', $room_id);
    $range_stmt->execute();
    $range_result = $range_stmt->get_result();

    while ($row = $range_result->fetch_assoc()) {
        $start = new DateTime($row['check_in']);
        $end = new DateTime($row['check_out']);

        // Add all dates in the range
        while ($start < $end) {
            $date_str = $start->format('Y-m-d');
            if (!in_array($date_str, $booked_dates)) {
                $booked_dates[] = $date_str;
            }
            $start->add(new DateInterval('P1D'));
        }
    }

    // Sort the dates
    sort($booked_dates);

    echo json_encode([
        'success' => true,
        'room_id' => $room_id,
        'booked_dates' => $booked_dates,
        'total_booked_dates' => count($booked_dates)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

$con->close();

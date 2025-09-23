<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Validate required fields
$required_fields = ['room_id', 'checkin_date', 'checkout_date'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$room_id = (int)$input['room_id'];
$checkin_date = $input['checkin_date'];
$checkout_date = $input['checkout_date'];

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $checkin_date) || !DateTime::createFromFormat('Y-m-d', $checkout_date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

// Validate date logic
$checkin = new DateTime($checkin_date);
$checkout = new DateTime($checkout_date);
$today = new DateTime();

if ($checkin < $today) {
    echo json_encode(['success' => false, 'message' => 'Check-in date cannot be in the past']);
    exit;
}

if ($checkout <= $checkin) {
    echo json_encode(['success' => false, 'message' => 'Check-out date must be after check-in date']);
    exit;
}

try {
    // Check for existing bookings that overlap with the requested dates
    $query = "SELECT booking_id, check_in, check_out 
              FROM bookings 
              WHERE room_id = ? 
              AND booking_status IN ('confirmed', 'pending')
              AND (
                  (check_in <= ? AND check_out > ?) OR
                  (check_in < ? AND check_out >= ?) OR
                  (check_in >= ? AND check_in < ?)
              )";

    $stmt = $con->prepare($query);
    $stmt->bind_param(
        'issssss',
        $room_id,
        $checkin_date,
        $checkin_date,  // Check if existing booking starts before or on our checkin and ends after our checkin
        $checkout_date,
        $checkout_date, // Check if existing booking starts before our checkout and ends on or after our checkout
        $checkin_date,
        $checkout_date   // Check if existing booking is completely within our date range
    );

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $conflicting_bookings = [];
        while ($row = $result->fetch_assoc()) {
            $conflicting_bookings[] = [
                'booking_id' => $row['booking_id'],
                'check_in' => $row['check_in'],
                'check_out' => $row['check_out']
            ];
        }

        echo json_encode([
            'success' => false,
            'message' => 'Selected dates are not available. There are conflicting bookings.',
            'conflicting_bookings' => $conflicting_bookings
        ]);
        exit;
    }

    // Check room availability in room_availability table
    $availability_query = "SELECT date FROM room_availability 
                          WHERE room_id = ? 
                          AND date >= ? 
                          AND date < ? 
                          AND available_quantity = 0";

    $availability_stmt = $con->prepare($availability_query);
    $availability_stmt->bind_param('iss', $room_id, $checkin_date, $checkout_date);
    $availability_stmt->execute();
    $availability_result = $availability_stmt->get_result();

    if ($availability_result->num_rows > 0) {
        $unavailable_dates = [];
        while ($row = $availability_result->fetch_assoc()) {
            $unavailable_dates[] = $row['date'];
        }

        echo json_encode([
            'success' => false,
            'message' => 'Some dates in the selected range are not available.',
            'unavailable_dates' => $unavailable_dates
        ]);
        exit;
    }

    // If we reach here, the booking is valid
    echo json_encode([
        'success' => true,
        'message' => 'Dates are available for booking',
        'room_id' => $room_id,
        'checkin_date' => $checkin_date,
        'checkout_date' => $checkout_date,
        'nights' => $checkin->diff($checkout)->days
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$con->close();

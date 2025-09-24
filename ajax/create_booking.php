<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

// Ensure clean JSON output
if (function_exists('ini_set')) { ini_set('display_errors', '0'); }
error_reporting(0);
while (ob_get_level()) { ob_end_clean(); }
header('Content-Type: application/json; charset=UTF-8');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get request body and attempt JSON decode, with fallbacks to form data
$raw = file_get_contents('php://input');
$input = null;
if (is_string($raw) && $raw !== '') {
    $input = json_decode($raw, true);
}
if (!$input || !is_array($input)) {
    if (!empty($_POST)) {
        $input = $_POST;
    } else {
        $tmp = [];
        if (is_string($raw)) {
            parse_str($raw, $tmp);
        }
        if (!empty($tmp)) { $input = $tmp; }
    }
}
if (!$input || !is_array($input)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data',
        'debug' => [
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
            'raw_len' => is_string($raw) ? strlen($raw) : 0,
            'raw_preview' => is_string($raw) ? substr($raw, 0, 200) : ''
        ]
    ]);
    exit;
}

// Validate required fields
$required_fields = ['room_id', 'check_in', 'check_out', 'adults', 'children', 'guest_name', 'guest_email', 'guest_phone', 'total_amount', 'booking_token'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || (is_string($input[$field]) && trim($input[$field]) === '')) {
        echo json_encode(['success' => false, 'message' => "Missing or empty required field: $field"]);
        exit;
    }
}

$room_id = (int)$input['room_id'];
$check_in = $input['check_in'];
$check_out = $input['check_out'];
$adults = (int)$input['adults'];
$children = (int)$input['children'];
$guest_name = filteration($input['guest_name']);
$guest_email = filteration($input['guest_email']);
$guest_phone = filteration($input['guest_phone']);
$total_amount = (float)$input['total_amount'];
$special_requests = isset($input['special_requests']) ? filteration($input['special_requests']) : '';
$booking_token = filteration($input['booking_token']);

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $check_in) || !DateTime::createFromFormat('Y-m-d', $check_out)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

// Validate date logic
$checkin = new DateTime($check_in);
$checkout = new DateTime($check_out);
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
    // Check for existing booking with same token (idempotency)
    $token_check = "SELECT id, booking_id FROM bookings WHERE booking_token = ?";
    $stmt_token = $con->prepare($token_check);
    $stmt_token->bind_param('s', $booking_token);
    $stmt_token->execute();
    $token_result = $stmt_token->get_result();
    
    if ($token_result->num_rows > 0) {
        $existing_booking = $token_result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'message' => 'Booking already exists',
            'booking_id' => $existing_booking['booking_id'],
            'is_duplicate' => true
        ]);
        exit;
    }

    // Check for room availability conflicts

    // Begin transaction and lock overlapping bookings to prevent race conditions
    mysqli_begin_transaction($con);

    $lock_q = "SELECT id, check_in, check_out FROM bookings 
               WHERE room_id = ? 
               AND booking_status IN ('confirmed', 'pending') 
               AND removed = 0 
               AND (check_in < ? AND check_out > ?) 
               FOR UPDATE";
    $lock_stmt = $con->prepare($lock_q);
    $lock_stmt->bind_param('iss', $room_id, $check_out, $check_in);
    $lock_stmt->execute();
    $lock_res = $lock_stmt->get_result();
    $conflicting_bookings = [];
    if ($lock_res && $lock_res->num_rows > 0) {
        while ($row = $lock_res->fetch_assoc()) {
            $conflicting_bookings[] = [
                'booking_id' => $row['id'],
                'check_in' => $row['check_in'],
                'check_out' => $row['check_out']
            ];
        }
        mysqli_rollback($con);
        echo json_encode([
            'success' => false,
            'message' => 'Selected dates are not available. There are conflicting bookings.',
            'conflicting_bookings' => $conflicting_bookings
        ]);
        exit;
    }
 
    // Check and lock room_availability rows
    $availability_query = "SELECT date FROM room_availability 
                          WHERE room_id = ? 
                          AND date >= ? 
                          AND date < ? 
                          AND (available_quantity <= 0 OR total_quantity = 0)
                          FOR UPDATE";

    $availability_stmt = $con->prepare($availability_query);
    $availability_stmt->bind_param('iss', $room_id, $check_in, $check_out);
    $availability_stmt->execute();
    $availability_result = $availability_stmt->get_result();

    if ($availability_result && $availability_result->num_rows > 0) {
        $unavailable_dates = [];
        while ($row = $availability_result->fetch_assoc()) {
            $unavailable_dates[] = $row['date'];
        }
        mysqli_rollback($con);
        echo json_encode([
            'success' => false,
            'message' => 'Some dates in the selected range are not available.',
            'unavailable_dates' => $unavailable_dates
        ]);
        exit;
    }

    // Generate unique booking ID
    $booking_id = 'LCR' . date('Ymd') . rand(1000, 9999);
    
    // Ensure booking ID is unique
    $check_id_query = "SELECT COUNT(*) as count FROM bookings WHERE booking_id = ?";
    $check_stmt = $con->prepare($check_id_query);
    do {
        $check_stmt->bind_param('s', $booking_id);
        $check_stmt->execute();
        $id_result = $check_stmt->get_result();
        $row = $id_result->fetch_assoc();
        if ($row['count'] > 0) {
            $booking_id = 'LCR' . date('Ymd') . rand(1000, 9999);
        }
    } while ($row['count'] > 0);

    // Insert booking into database
    $insert_query = "INSERT INTO bookings 
                    (booking_id, room_id, user_id, guest_name, guest_email, guest_phone, 
                     check_in, check_out, adults, children, total_amount, special_requests, 
                     booking_status, payment_status, booking_date, booking_token) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', 'pending', NOW(), ?)";

    $user_id = isset($_SESSION['uId']) ? (int)$_SESSION['uId'] : 0;
    
    $insert_stmt = $con->prepare($insert_query);
    $insert_stmt->bind_param(
        'siisssssiidss',
        $booking_id,
        $room_id,
        $user_id,
        $guest_name,
        $guest_email,
        $guest_phone,
        $check_in,
        $check_out,
        $adults,
        $children,
        $total_amount,
        $special_requests,
        $booking_token
    );

    if ($insert_stmt->execute()) {
        mysqli_commit($con);
        echo json_encode([
            'success' => true,
            'message' => 'Booking created successfully!',
            'booking_id' => $booking_id,
            'nights' => $checkin->diff($checkout)->days
        ]);
    } else {
        mysqli_rollback($con);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create booking: ' . $insert_stmt->error
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$con->close();
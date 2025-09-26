<?php
require('../inc/essentials.php');
require('../inc/db_config.php');
require_once('../inc/bookings_schema.php');
require_once('../inc/booking_helpers.php');
adminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST requests allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$action = $input['action'];

// Ensure bookings table has required columns for soft delete, payments, and refunds
// Moved: ensureBookingsSchema($con) is defined in admin/inc/bookings_schema.php
ensureBookingsSchema($con);

switch ($action) {
    case 'get_bookings':
        getBookings($con, $input);
        break;
    case 'create_booking':
        createBooking($con, $input);
        break;
    case 'update_booking':
        updateBooking($con, $input);
        break;
    case 'delete_booking':
        deleteBooking($con, $input);
        break;
    case 'validate_dates':
        validateBookingDates($con, $input);
        break;
    case 'update_payment_status':
        bm_updatePaymentStatus($con, $input);
        break;
    case 'approve_refund':
        bm_approveRefund($con, $input);
        break;
    case 'reject_refund':
        bm_rejectRefund($con, $input);
        break;
    case 'get_dashboard_metrics':
        getDashboardMetrics($con, $input);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getBookings($con, $input)
{
    $where_conditions = ["b.removed = 0"];
    $params = [];
    $types = "";

    // Apply filters
    if (!empty($input['status_filter'])) {
        $where_conditions[] = "b.booking_status = ?";
        $params[] = $input['status_filter'];
        $types .= "s";
    }

    if (!empty($input['room_filter'])) {
        $where_conditions[] = "b.room_id = ?";
        $params[] = $input['room_filter'];
        $types .= "i";
    }

    if (!empty($input['date_filter'])) {
        $where_conditions[] = "(b.check_in <= ? AND b.check_out >= ?)";
        $params[] = $input['date_filter'];
        $params[] = $input['date_filter'];
        $types .= "ss";
    }

    if (!empty($input['source_filter'])) {
        $where_conditions[] = "b.booking_source = ?";
        $params[] = $input['source_filter'];
        $types .= "s";
    }

    $where_clause = implode(" AND ", $where_conditions);

    $query = "SELECT b.*, r.name as room_name, r.price as room_price 
              FROM bookings b 
              LEFT JOIN rooms r ON b.room_id = r.id 
              WHERE $where_clause 
              ORDER BY b.created_at DESC";

    $stmt = mysqli_prepare($con, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }

    echo json_encode(['success' => true, 'bookings' => $bookings]);
}

function createBooking($con, $input)
{
    // Validate required fields
    $required_fields = ['guest_name', 'guest_email', 'room_id', 'checkin_date', 'checkout_date'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }

    // Validate dates
    $checkin = $input['checkin_date'];
    $checkout = $input['checkout_date'];

    if (strtotime($checkin) >= strtotime($checkout)) {
        echo json_encode(['success' => false, 'message' => 'Check-out date must be after check-in date']);
        return;
    }

    if (strtotime($checkin) < strtotime('today')) {
        echo json_encode(['success' => false, 'message' => 'Check-in date cannot be in the past']);
        return;
    }

    $booking_token = isset($input['booking_token']) ? trim($input['booking_token']) : null;

    // Idempotency: if a booking already exists with this token, return it instead of creating a new one
    if ($booking_token) {
        $token_q = "SELECT id, check_in, check_out, total_amount FROM bookings WHERE booking_token = ? AND removed = 0";
        $token_stmt = mysqli_prepare($con, $token_q);
        mysqli_stmt_bind_param($token_stmt, "s", $booking_token);
        mysqli_stmt_execute($token_stmt);
        $token_res = mysqli_stmt_get_result($token_stmt);
        if ($existing = mysqli_fetch_assoc($token_res)) {
            $existing_nights = (strtotime($existing['check_out']) - strtotime($existing['check_in'])) / (60 * 60 * 24);
            echo json_encode([
                'success' => true,
                'message' => 'Booking already exists for this token',
                'booking_id' => $existing['id'],
                'nights' => $existing_nights,
                'total_amount' => (float)$existing['total_amount'],
                'idempotent' => true
            ]);
            return;
        }
    }

    // Check for double booking
    $conflict_check = checkBookingConflicts($con, $input['room_id'], $checkin, $checkout);
    if (!$conflict_check['available']) {
        echo json_encode([
            'success' => false,
            'message' => 'Selected dates are not available. There are conflicting bookings.',
            'conflicting_bookings' => $conflict_check['conflicts']
        ]);
        return;
    }

    // Calculate nights and total amount if not provided
    $nights = (strtotime($checkout) - strtotime($checkin)) / (60 * 60 * 24);

    $total_amount = $input['total_amount'] ?? 0;
    if ($total_amount == 0) {
        // Get room price and calculate
        $room_query = "SELECT price FROM rooms WHERE id = ? AND status = 1 AND removed = 0";
        $room_stmt = mysqli_prepare($con, $room_query);
        mysqli_stmt_bind_param($room_stmt, "i", $input['room_id']);
        mysqli_stmt_execute($room_stmt);
        $room_result = mysqli_stmt_get_result($room_stmt);

        if ($room_row = mysqli_fetch_assoc($room_result)) {
            $total_amount = $room_row['price'] * $nights;
        }
    }

    // Begin transaction to ensure atomicity and avoid race conditions
    mysqli_begin_transaction($con);
    
    // Lock any overlapping bookings for this room to prevent concurrent inserts
    $lock_q = "SELECT id, check_in, check_out FROM bookings 
               WHERE room_id = ? 
               AND booking_status IN ('confirmed', 'pending') 
               AND removed = 0 
               AND (check_in < ? AND check_out > ?) 
               FOR UPDATE";
    
    $lock_stmt = mysqli_prepare($con, $lock_q);
    mysqli_stmt_bind_param($lock_stmt, "iss", $input['room_id'], $checkout, $checkin);
    mysqli_stmt_execute($lock_stmt);
    $lock_res = mysqli_stmt_get_result($lock_stmt);
    
    $conflicts = [];
    if ($lock_res) {
        while ($row = mysqli_fetch_assoc($lock_res)) {
            $conflicts[] = [
                'booking_id' => $row['id'],
                'check_in' => $row['check_in'],
                'check_out' => $row['check_out']
            ];
        }
    }
    
    if (!empty($conflicts)) {
        mysqli_rollback($con);
        echo json_encode([
            'success' => false,
            'message' => 'Selected dates are not available. There are conflicting bookings.',
            'conflicting_bookings' => $conflicts
        ]);
        return;
    }
    
    // Check and lock unavailability in room_availability table
    $avail_q = "SELECT date FROM room_availability 
                WHERE room_id = ? 
                  AND date >= ? AND date < ? 
                  AND (available_quantity <= 0 OR total_quantity = 0)
                FOR UPDATE";
    $avail_stmt = mysqli_prepare($con, $avail_q);
    mysqli_stmt_bind_param($avail_stmt, "iss", $input['room_id'], $checkin, $checkout);
    mysqli_stmt_execute($avail_stmt);
    $avail_res = mysqli_stmt_get_result($avail_stmt);
    $unavailable_dates = [];
    if ($avail_res) {
        while ($row = mysqli_fetch_assoc($avail_res)) {
            $unavailable_dates[] = $row['date'];
        }
    }
    
    if (!empty($unavailable_dates)) {
        mysqli_rollback($con);
        echo json_encode([
            'success' => false,
            'message' => 'Room has unavailable dates in the selected range.',
            'unavailable_dates' => $unavailable_dates
        ]);
        return;
    }
    
    // Insert booking (idempotent by booking_token if provided)
    $status = $input['status'] ?? 'confirmed';
    $phone = $input['guest_phone'] ?? '';
    $special_requests = $input['special_requests'] ?? '';
    
    $booking_source = isset($input['booking_source']) ? trim($input['booking_source']) : null;

    if ($booking_token) {
        $insert_query = "INSERT INTO bookings (room_id, guest_name, guest_email, guest_phone, check_in, check_out, 
                         total_amount, booking_status, special_requests, booking_token, booking_source, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($con, $insert_query);
        mysqli_stmt_bind_param(
            $stmt,
            "isssssdssss",
            $input['room_id'],
            $input['guest_name'],
            $input['guest_email'],
            $phone,
            $checkin,
            $checkout,
            $total_amount,
            $status,
            $special_requests,
            $booking_token,
            $booking_source
        );
    } else {
        $insert_query = "INSERT INTO bookings (room_id, guest_name, guest_email, guest_phone, check_in, check_out, 
                         total_amount, booking_status, special_requests, booking_source, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($con, $insert_query);
        mysqli_stmt_bind_param(
            $stmt,
            "isssssdsss",
            $input['room_id'],
            $input['guest_name'],
            $input['guest_email'],
            $phone,
            $checkin,
            $checkout,
            $total_amount,
            $status,
            $special_requests,
            $booking_source
        );
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $booking_id = mysqli_insert_id($con);
        mysqli_commit($con);
        echo json_encode([
            'success' => true,
            'message' => 'Booking created successfully',
            'booking_id' => $booking_id,
            'nights' => $nights,
            'total_amount' => $total_amount
        ]);
    } else {
        // Handle duplicate booking_token gracefully
        if ($booking_token && mysqli_errno($con) === 1062) {
            // Fetch existing booking by token and return success (idempotent)
            $ex_q = "SELECT id, check_in, check_out, total_amount FROM bookings WHERE booking_token = ? AND removed = 0";
            $ex_stmt = mysqli_prepare($con, $ex_q);
            mysqli_stmt_bind_param($ex_stmt, "s", $booking_token);
            mysqli_stmt_execute($ex_stmt);
            $ex_res = mysqli_stmt_get_result($ex_stmt);
            if ($existing = mysqli_fetch_assoc($ex_res)) {
                mysqli_commit($con);
                $existing_nights = (strtotime($existing['check_out']) - strtotime($existing['check_in'])) / (60 * 60 * 24);
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking already exists for this token',
                    'booking_id' => $existing['id'],
                    'nights' => $existing_nights,
                    'total_amount' => (float)$existing['total_amount'],
                    'idempotent' => true
                ]);
                return;
            }
        }
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'message' => 'Failed to create booking: ' . mysqli_error($con)]);
    }
}

function updateBooking($con, $input)
{
    if (empty($input['booking_id'])) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }

    // Build update query dynamically based on provided fields
    $update_fields = [];
    $params = [];
    $types = "";

    $allowed_fields = ['guest_name', 'guest_email', 'guest_phone', 'booking_status', 'total_amount', 'special_requests', 'booking_source'];

    foreach ($allowed_fields as $field) {
        if (isset($input[$field])) {
            $db_field = ($field === 'booking_status') ? 'booking_status' : $field;
            $update_fields[] = "$db_field = ?";
            $params[] = $input[$field];
            // type inference: amount numeric, others string
            $types .= ($field === 'total_amount') ? 'd' : 's';
        }
    }

    if (empty($update_fields)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        return;
    }

    $params[] = $input['booking_id'];
    $types .= "i";

    $update_query = "UPDATE bookings SET " . implode(", ", $update_fields) . " WHERE id = ? AND removed = 0";

    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Booking not found or no changes made']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update booking: ' . mysqli_error($con)]);
    }
}

function deleteBooking($con, $input)
{
    if (empty($input['booking_id'])) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }

    // Soft delete - mark as removed
    $delete_query = "UPDATE bookings SET removed = 1 WHERE id = ?";
    $stmt = mysqli_prepare($con, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $input['booking_id']);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete booking: ' . mysqli_error($con)]);
    }
}

// Approve refund request: set payment_status=refunded, refund_status=approved, cancel booking
function approveRefund($con, $input) {
    if (empty($input['booking_id'])) {
        echo json_encode(['success' => false, 'message' => 'booking_id is required']);
        return;
    }
    $booking_id = (int)$input['booking_id'];

    $q = "UPDATE bookings SET payment_status='refunded', refund_status='approved', booking_status='cancelled', refund_processed_at=NOW(), updated_at=NOW() WHERE id=? AND removed=0";
    $stmt = mysqli_prepare($con, $q);
    mysqli_stmt_bind_param($stmt, 'i', $booking_id);
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => false, 'message' => 'Failed to approve refund: ' . mysqli_error($con)]);
        return;
    }
    echo json_encode(['success' => true, 'message' => 'Refund approved and booking marked refunded', 'booking_id' => $booking_id]);
}

// Reject refund request: set refund_status=rejected
function rejectRefund($con, $input) {
    if (empty($input['booking_id'])) {
        echo json_encode(['success' => false, 'message' => 'booking_id is required']);
        return;
    }
    $booking_id = (int)$input['booking_id'];

    $q = "UPDATE bookings SET refund_status='rejected', refund_processed_at=NOW(), updated_at=NOW() WHERE id=? AND removed=0";
    $stmt = mysqli_prepare($con, $q);
    mysqli_stmt_bind_param($stmt, 'i', $booking_id);
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => false, 'message' => 'Failed to reject refund: ' . mysqli_error($con)]);
        return;
    }
    echo json_encode(['success' => true, 'message' => 'Refund request rejected', 'booking_id' => $booking_id]);
}

function validateBookingDates($con, $input)
{
    if (empty($input['room_id']) || empty($input['checkin_date']) || empty($input['checkout_date'])) {
        echo json_encode(['success' => false, 'message' => 'Room ID, check-in date, and check-out date are required']);
        return;
    }

    $conflict_check = checkBookingConflicts($con, $input['room_id'], $input['checkin_date'], $input['checkout_date'], $input['exclude_booking_id'] ?? null);

    if ($conflict_check['available']) {
        echo json_encode([
            'success' => true,
            'message' => 'Dates are available for booking',
            'room_id' => $input['room_id'],
            'checkin_date' => $input['checkin_date'],
            'checkout_date' => $input['checkout_date']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Selected dates are not available. There are conflicting bookings.',
            'conflicting_bookings' => $conflict_check['conflicts']
        ]);
    }
}

function checkBookingConflicts($con, $room_id, $checkin, $checkout, $exclude_booking_id = null)
{
    $query = "SELECT id, check_in, check_out FROM bookings 
              WHERE room_id = ? 
              AND booking_status IN ('confirmed', 'pending') 
              AND removed = 0
              AND (check_in < ? AND check_out > ?)";

    $params = [$room_id, $checkout, $checkin];
    $types = "iss";

    if ($exclude_booking_id) {
        $query .= " AND id != ?";
        $params[] = $exclude_booking_id;
        $types .= "i";
    }

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $conflicts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $conflicts[] = [
            'booking_id' => $row['id'],
            'check_in' => $row['check_in'],
            'check_out' => $row['check_out']
        ];
    }

    return [
        'available' => empty($conflicts),
        'conflicts' => $conflicts
    ];
}

function getDashboardMetrics($con, $input) {
    // Arrivals and departures today
    $arrivals_sql = "SELECT COUNT(*) AS cnt FROM bookings WHERE removed=0 AND booking_status IN ('confirmed','pending') AND DATE(check_in)=CURDATE()";
    $departures_sql = "SELECT COUNT(*) AS cnt FROM bookings WHERE removed=0 AND booking_status IN ('confirmed','pending') AND DATE(check_out)=CURDATE()";
    $arrivals = mysqli_fetch_assoc(mysqli_query($con, $arrivals_sql))['cnt'] ?? 0;
    $departures = mysqli_fetch_assoc(mysqli_query($con, $departures_sql))['cnt'] ?? 0;

    // Occupancy today
    $total_rooms_sql = "SELECT COUNT(*) AS cnt FROM rooms WHERE status=1 AND removed=0";
    $total_rooms = (int)(mysqli_fetch_assoc(mysqli_query($con, $total_rooms_sql))['cnt'] ?? 0);
    $occupied_sql = "SELECT COUNT(DISTINCT room_id) AS cnt FROM bookings WHERE removed=0 AND booking_status IN ('confirmed','pending') AND check_in <= CURDATE() AND check_out > CURDATE()";
    $occupied = (int)(mysqli_fetch_assoc(mysqli_query($con, $occupied_sql))['cnt'] ?? 0);
    $occupancy_pct = ($total_rooms > 0) ? round(($occupied / $total_rooms) * 100, 2) : 0.0;

    // ADR approximation: total revenue per night across active stays
    $rev_nights_sql = "SELECT COALESCE(SUM(total_amount),0) AS total_rev, COALESCE(SUM(DATEDIFF(check_out, check_in)),0) AS total_nights FROM bookings WHERE removed=0 AND booking_status IN ('confirmed','pending') AND check_in <= CURDATE() AND check_out > CURDATE()";
    $rn = mysqli_fetch_assoc(mysqli_query($con, $rev_nights_sql));
    $total_rev = (float)($rn['total_rev'] ?? 0);
    $total_nights = (int)($rn['total_nights'] ?? 0);
    $adr = ($total_nights > 0) ? round($total_rev / $total_nights, 2) : 0.0;

    // RevPAR basic approximation
    $revpar = ($total_rooms > 0) ? round(($adr * ($occupancy_pct / 100)), 2) : 0.0;

    // Top rooms (last 30 days by check-in)
    $top_rooms = [];
    $top_sql = "SELECT r.id AS room_id, r.name AS room_name, COUNT(*) AS bookings_count
                FROM bookings b
                JOIN rooms r ON r.id = b.room_id
                WHERE b.removed=0 AND b.check_in >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY r.id, r.name
                ORDER BY bookings_count DESC
                LIMIT 5";
    $res_top = mysqli_query($con, $top_sql);
    if ($res_top) {
        while ($row = mysqli_fetch_assoc($res_top)) { $top_rooms[] = $row; }
    }

    // Booking source distribution (last 30 days by created_at)
    $sources = [];
    $src_sql = "SELECT COALESCE(NULLIF(TRIM(booking_source), ''), 'unknown') AS source, COUNT(*) AS cnt
                FROM bookings
                WHERE removed=0 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY source
                ORDER BY cnt DESC";
    $res_src = mysqli_query($con, $src_sql);
    if ($res_src) {
        while ($row = mysqli_fetch_assoc($res_src)) { $sources[] = $row; }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'arrivals_today' => (int)$arrivals,
            'departures_today' => (int)$departures,
            'occupancy_pct' => (float)$occupancy_pct,
            'adr' => (float)$adr,
            'revpar' => (float)$revpar,
            'top_rooms' => $top_rooms,
            'source_distribution' => $sources,
        ]
    ]);
}

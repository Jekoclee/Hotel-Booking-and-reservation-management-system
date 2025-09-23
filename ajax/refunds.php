<?php
// User-side refunds endpoint
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');
require_once('../admin/inc/bookings_schema.php');

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

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = (int)($_SESSION['uId'] ?? 0);
if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user session']);
    exit;
}

$action = $input['action'];

// ensureBookingsSchema is now loaded from shared include
ensureBookingsSchema($con);

switch ($action) {
    case 'request_refund':
        request_refund($con, $user_id, $input);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function request_refund($con, $user_id, $input) {
    $booking_id = isset($input['booking_id']) ? (int)$input['booking_id'] : 0;
    $reason = trim($input['reason'] ?? '');

    if ($booking_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'booking_id is required']);
        return;
    }

    if ($reason === '' || mb_strlen($reason) < 5) {
        echo json_encode(['success' => false, 'message' => 'Please provide a brief reason (at least 5 characters).']);
        return;
    }

    // Verify booking ownership and current statuses
    $q = "SELECT id, user_id, payment_status, booking_status, refund_status, check_in FROM bookings WHERE id=? AND user_id=? AND removed=0 LIMIT 1";
    $stmt = mysqli_prepare($con, $q);
    mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (!$res || mysqli_num_rows($res) === 0) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        return;
    }

    $bk = mysqli_fetch_assoc($res);

    // Eligibility checks
    if ($bk['payment_status'] !== 'paid') {
        echo json_encode(['success' => false, 'message' => 'Only paid bookings can be refunded.']);
        return;
    }

    if (!in_array($bk['booking_status'], ['confirmed','pending'], true)) {
        echo json_encode(['success' => false, 'message' => 'Only active bookings can be refunded.']);
        return;
    }

    if (in_array($bk['refund_status'], ['requested','approved'], true)) {
        echo json_encode(['success' => false, 'message' => 'Refund already in process or completed.']);
        return;
    }

    // Must be before check-in date
    $today = new DateTime('today');
    $checkin = new DateTime($bk['check_in']);
    if ($checkin <= $today) {
        echo json_encode(['success' => false, 'message' => 'Refunds can only be requested before the check-in date.']);
        return;
    }

    // Update refund status
    $now = (new DateTime())->format('Y-m-d H:i:s');
    $q2 = "UPDATE bookings SET refund_status='requested', refund_reason=?, refund_requested_at=? WHERE id=? AND user_id=? LIMIT 1";
    $stmt2 = mysqli_prepare($con, $q2);
    mysqli_stmt_bind_param($stmt2, 'ssii', $reason, $now, $booking_id, $user_id);
    $ok = mysqli_stmt_execute($stmt2);

    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Refund request submitted', 'booking_id' => $booking_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit refund request']);
    }
}
<?php
// Admin reminder sender script
// Scans bookings and sends/logs scheduled reminders with dedupe protection.

// Usage: php send_reminders.php

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain');
}

require_once __DIR__ . '/../inc/db_config.php';
require_once __DIR__ . '/../inc/essentials.php';

$now = new DateTime('now');
$today = $now->format('Y-m-d');

echo "Starting reminder run at " . $now->format('Y-m-d H:i:s') . "\n";

// Helper to insert notification log with unique dedupe_key
function logNotification($con, $booking_id, $user_id, $type, $channel, $dedupe_key) {
    $sql = "INSERT INTO notification_log (booking_id, user_id, type, channel, sent_at, dedupe_key) VALUES (?, ?, ?, ?, NOW(), ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'iisss', $booking_id, $user_id, $type, $channel, $dedupe_key);
    $ok = @mysqli_stmt_execute($stmt);
    if (!$ok) {
        $err = mysqli_stmt_error($stmt);
        // Duplicate dedupe_key is expected sometimes; ignore silently
        if (stripos($err, 'Duplicate') === false) {
            echo "  [logNotification] Error: $err\n";
        }
    }
    mysqli_stmt_close($stmt);
    return $ok;
}

// Helper to attempt sending email (best-effort; logging is primary)
function trySendEmail($to, $subject, $message) {
    // Basic headers
    $headers = "From: noreply@localhost\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";
    $sent = @mail($to, $subject, $message, $headers);
    return $sent ? 'sent' : 'failed';
}

// Fetch candidate bookings for reminders
// Pre-arrival T3 (3 days before check-in)
$preT3_sql = "SELECT id, user_id, guest_email, guest_name, check_in, check_out, booking_status
              FROM bookings
              WHERE removed = 0
                AND booking_status IN ('confirmed','pending')
                AND DATEDIFF(check_in, CURDATE()) = 3";
$preT3_res = mysqli_query($con, $preT3_sql);

// Pre-arrival T1 (1 day before check-in)
$preT1_sql = "SELECT id, user_id, guest_email, guest_name, check_in, check_out, booking_status
              FROM bookings
              WHERE removed = 0
                AND booking_status IN ('confirmed','pending')
                AND DATEDIFF(check_in, CURDATE()) = 1";
$preT1_res = mysqli_query($con, $preT1_sql);

// Post-stay T1 (1 day after check-out)
$postT1_sql = "SELECT id, user_id, guest_email, guest_name, check_in, check_out, booking_status
               FROM bookings
               WHERE removed = 0
                 AND booking_status IN ('confirmed','completed')
                 AND DATEDIFF(CURDATE(), check_out) = 1";
$postT1_res = mysqli_query($con, $postT1_sql);

$counts = [ 'pre_arrival_T3' => 0, 'pre_arrival_T1' => 0, 'post_stay_T1' => 0 ];

function processReminderSet($con, $res, $type, &$count) {
    if (!$res) {
        echo "  [$type] Query error: " . mysqli_error($con) . "\n";
        return;
    }
    while ($b = mysqli_fetch_assoc($res)) {
        $bookingId = (int)$b['id'];
        $userId = isset($b['user_id']) ? (int)$b['user_id'] : null;
        $email = trim($b['guest_email'] ?? '');
        $guestName = trim($b['guest_name'] ?? 'Guest');

        // Build dedupe key stable for the booking and type per-day
        $dedupe_key = $type . ':' . $bookingId . ':' . (new DateTime('today'))->format('Y-m-d');

        // Try sending email (best-effort)
        $subject = '';
        $message = '';
        if ($type === 'pre_arrival_T3') {
            $subject = 'Your upcoming stay in 3 days - Leisure Coast Resort';
            $message = "Hi $guestName,\n\nWe're excited to welcome you in 3 days! If you need to adjust your booking or have special requests, please reply to this email.\n\nSee you soon!";
        } elseif ($type === 'pre_arrival_T1') {
            $subject = 'Your stay is tomorrow - Final check-in details';
            $message = "Hi $guestName,\n\nYour stay is tomorrow. Please bring a valid ID. Check-in starts at 2:00 PM.\n\nSafe travels!";
        } else { // post_stay_T1
            $subject = 'Thank you for staying with us - We value your feedback';
            $message = "Hi $guestName,\n\nThank you for staying with us! We'd love to hear your feedback.\n\nWe hope to see you again.";
        }

        $channel = 'email';
        $status = $email ? trySendEmail($email, $subject, $message) : 'skipped';

        // Always log, even if email failed/skipped (dedupe unique index prevents duplicates)
        $logged = logNotification($con, $bookingId, $userId ?: null, $type, $channel, $dedupe_key);

        echo "  [$type] Booking #$bookingId -> email=$status, log=" . ($logged ? 'ok' : 'dup/err') . "\n";
        $count++;
    }
}

processReminderSet($con, $preT3_res, 'pre_arrival_T3', $counts['pre_arrival_T3']);
processReminderSet($con, $preT1_res, 'pre_arrival_T1', $counts['pre_arrival_T1']);
processReminderSet($con, $postT1_res, 'post_stay_T1', $counts['post_stay_T1']);

echo "\nSummary:\n";
echo "  pre_arrival_T3: {$counts['pre_arrival_T3']} processed\n";
echo "  pre_arrival_T1: {$counts['pre_arrival_T1']} processed\n";
echo "  post_stay_T1:   {$counts['post_stay_T1']} processed\n";

echo "\nReminder run finished.\n";
<?php
/**
 * Booking action helpers used by admin/ajax/booking_management.php
 * Keep action logic centralized to reduce duplication and errors.
 */

if (!function_exists('bm_updatePaymentStatus')) {
    function bm_updatePaymentStatus($con, $input)
    {
        $allowed = ['pending', 'paid', 'refunded'];
        if (empty($input['booking_id']) || empty($input['new_status']) || !in_array($input['new_status'], $allowed, true)) {
            echo json_encode(['success' => false, 'message' => 'booking_id and valid new_status are required']);
            return;
        }

        $booking_id = (int)$input['booking_id'];
        $new_status = $input['new_status'];

        $update_query = "UPDATE bookings SET payment_status = ?, updated_at = NOW() WHERE id = ? AND removed = 0";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $new_status, $booking_id);

        if (!mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => false, 'message' => 'Failed to update payment status: ' . mysqli_error($con)]);
            return;
        }

        if ($new_status === 'refunded') {
            $cancel_q = "UPDATE bookings SET booking_status = 'cancelled', refund_status = IF(refund_status='requested','approved',refund_status), refund_processed_at = NOW(), updated_at = NOW() WHERE id = ? AND booking_status <> 'cancelled'";
            $stmt2 = mysqli_prepare($con, $cancel_q);
            mysqli_stmt_bind_param($stmt2, 'i', $booking_id);
            mysqli_stmt_execute($stmt2);
        }

        echo json_encode(['success' => true, 'message' => 'Payment status updated', 'booking_id' => $booking_id, 'payment_status' => $new_status]);
    }
}

if (!function_exists('bm_approveRefund')) {
    function bm_approveRefund($con, $input) {
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
}

if (!function_exists('bm_rejectRefund')) {
    function bm_rejectRefund($con, $input) {
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
}
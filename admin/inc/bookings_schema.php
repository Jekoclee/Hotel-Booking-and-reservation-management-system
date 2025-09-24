<?php
/**
 * Shared bookings schema ensure function.
 * Centralizes column checks/DDL for bookings table so endpoints/pages stay clean.
 */

if (!function_exists('ensureBookingsSchema')) {
    function ensureBookingsSchema($con) {
        // Helper: check if a column exists in bookings
        $hasCol = function($column) use ($con) {
            $column_safe = mysqli_real_escape_string($con, $column);
            $sql = "SHOW COLUMNS FROM bookings LIKE '" . $column_safe . "'";
            $res = mysqli_query($con, $sql);
            return $res && mysqli_num_rows($res) > 0;
        };

        // Soft delete flag
        if (!$hasCol('removed')) {
            @mysqli_query($con, "ALTER TABLE bookings ADD COLUMN removed TINYINT(1) NOT NULL DEFAULT 0 AFTER updated_at");
        }

        // Payment status enum
        if (!$hasCol('payment_status')) {
            @mysqli_query($con, "ALTER TABLE bookings ADD COLUMN payment_status ENUM('pending','paid','refunded') NOT NULL DEFAULT 'pending' AFTER booking_status");
        } else {
            @mysqli_query($con, "ALTER TABLE bookings MODIFY COLUMN payment_status ENUM('pending','paid','refunded') NOT NULL DEFAULT 'pending'");
        }

        // Refund related columns
        if (!$hasCol('refund_status')) {
            @mysqli_query($con, "ALTER TABLE bookings ADD COLUMN refund_status ENUM('none','requested','approved','rejected') NOT NULL DEFAULT 'none' AFTER payment_status");
        }
        if (!$hasCol('refund_reason')) {
            @mysqli_query($con, "ALTER TABLE bookings ADD COLUMN refund_reason TEXT NULL AFTER refund_status");
        }
        if (!$hasCol('refund_requested_at')) {
            @mysqli_query($con, "ALTER TABLE bookings ADD COLUMN refund_requested_at DATETIME NULL AFTER refund_reason");
        }
        if (!$hasCol('refund_processed_at')) {
            @mysqli_query($con, "ALTER TABLE bookings ADD COLUMN refund_processed_at DATETIME NULL AFTER refund_requested_at");
        }

        // Idempotency token for client-side booking creation
        if (!$hasCol('booking_token')) {
            @mysqli_query($con, "ALTER TABLE bookings ADD COLUMN booking_token VARCHAR(64) NULL AFTER booking_id");
            // Add unique index to enforce idempotency (safe to ignore error if already exists)
            @mysqli_query($con, "ALTER TABLE bookings ADD UNIQUE KEY booking_token_unique (booking_token)");
        }
    }
}

<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

header('Content-Type: application/json');

// Ensure reviews table exists (idempotent)
$create_reviews_table = "
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `room_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `rating` TINYINT(1) NOT NULL,
    `comment` TEXT DEFAULT NULL,
    `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `removed` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_room_id` (`room_id`),
    KEY `idx_status` (`status`),
    KEY `idx_removed` (`removed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
mysqli_query($con, $create_reviews_table);

// Defensive: auto-migrate legacy tables missing expected columns
function ensure_review_column($con, $column, $definition) {
  $check = mysqli_query($con, "SHOW COLUMNS FROM `reviews` LIKE '" . mysqli_real_escape_string($con, $column) . "'");
  if ($check && mysqli_num_rows($check) === 0) {
    @mysqli_query($con, "ALTER TABLE `reviews` ADD `{$column}` {$definition}");
  }
}
ensure_review_column($con, 'status', "ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
ensure_review_column($con, 'removed', "TINYINT(1) NOT NULL DEFAULT 0");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['submit_review'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || empty($_SESSION['uId'])) {
        echo json_encode(['success' => false, 'message' => 'not_logged_in']);
        exit;
    }

    $data = filteration($_POST);

    $room_id = (int)($data['room_id'] ?? 0);
    $rating = (int)($data['rating'] ?? 0);
    $comment = trim($data['comment'] ?? '');

    if ($room_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'invalid_room']);
        exit;
    }
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'invalid_rating']);
        exit;
    }
    if ($comment === '') {
        echo json_encode(['success' => false, 'message' => 'empty_comment']);
        exit;
    }

    // Verify room exists and is active
    $room_check = select("SELECT id FROM rooms WHERE id=? AND removed=0 LIMIT 1", [$room_id], 'i');
    if (!$room_check || mysqli_num_rows($room_check) === 0) {
        echo json_encode(['success' => false, 'message' => 'room_not_found']);
        exit;
    }

    $user_id = (int)($_SESSION['uId']);

    $q = "INSERT INTO reviews (room_id, user_id, rating, comment, status, removed, created_at) VALUES (?, ?, ?, ?, 'pending', 0, NOW())";
    $values = [$room_id, $user_id, $rating, $comment];

    $res = insert($q, $values, 'iiis');
    if ($res) {
        echo json_encode(['success' => true, 'message' => 'review_submitted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'submit_failed']);
    }
    exit;
}

if ($method === 'GET' && isset($_GET['room_id'])) {
    $room_id = (int)($_GET['room_id'] ?? 0);
    if ($room_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'invalid_room']);
        exit;
    }

    // Ensure table exists (again for safety on environments with delayed schema creation)
    mysqli_query($con, $create_reviews_table);
    ensure_review_column($con, 'status', "ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
    ensure_review_column($con, 'removed', "TINYINT(1) NOT NULL DEFAULT 0");

    // Fetch approved reviews
    $reviews = [];
    $sql = "SELECT r.id, r.rating, r.comment, r.created_at, u.name AS user_name
            FROM reviews r
            LEFT JOIN user_cred u ON r.user_id = u.id
            WHERE r.room_id = ? AND r.status = 'approved' AND r.removed = 0
            ORDER BY r.created_at DESC
            LIMIT 50";
    $res = select($sql, [$room_id], 'i');
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $reviews[] = $row;
        }
    }

    // Compute average rating
    $avg = 0; $count = 0;
    $agg = select("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE room_id=? AND status='approved' AND removed=0", [$room_id], 'i');
    if ($agg && mysqli_num_rows($agg) > 0) {
        $r = mysqli_fetch_assoc($agg);
        $avg = round((float)($r['avg_rating'] ?? 0), 2);
        $count = (int)($r['cnt'] ?? 0);
    }

    echo json_encode(['success' => true, 'reviews' => $reviews, 'avg_rating' => $avg, 'count' => $count]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'invalid_request']);
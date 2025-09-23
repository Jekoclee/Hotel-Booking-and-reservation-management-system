<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

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

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'list') {
    // List reviews for admin with room and user names
    $sql = "SELECT r.id, r.room_id, r.user_id, r.rating, r.comment, r.status, r.created_at,
                   rooms.name AS room_name, users.name AS user_name
            FROM reviews r
            LEFT JOIN rooms ON rooms.id = r.room_id
            LEFT JOIN user_cred AS users ON users.id = r.user_id
            WHERE r.removed = 0
            ORDER BY r.created_at DESC
            LIMIT 300";

    $res = mysqli_query($con, $sql);
    $reviews = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $row['id'] = (int)$row['id'];
            $row['room_id'] = (int)$row['room_id'];
            $row['user_id'] = (int)$row['user_id'];
            $row['rating'] = (int)$row['rating'];
            $reviews[] = $row;
        }
    }

    echo json_encode(['success' => true, 'reviews' => $reviews]);
    exit;
}

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (!is_array($payload)) { $payload = $_POST; }

    $action = $payload['action'] ?? '';

    if ($action === 'update_status') {
        $id = isset($payload['id']) ? (int)$payload['id'] : 0;
        $status = $payload['status'] ?? '';
        $allowed = ['pending','approved','rejected'];
        if ($id <= 0 || !in_array($status, $allowed, true)) {
            echo json_encode(['success' => false, 'message' => 'invalid_params']);
            exit;
        }

        $q = "UPDATE reviews SET status=? WHERE id=?";
        $ok = update($q, [$status, $id], 'si');
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'unknown_action']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'invalid_request']);
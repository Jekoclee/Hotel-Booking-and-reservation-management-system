<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

// Debug session information
echo "<!-- DEBUG BOOKING_CONFIRMATION: Session login: " . (isset($_SESSION['login']) ? ($_SESSION['login'] ? 'TRUE' : 'FALSE') : 'NOT SET') . " -->";
echo "<!-- DEBUG BOOKING_CONFIRMATION: Session data: " . print_r($_SESSION, true) . " -->";

// Check if user is logged in
if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
    echo "<!-- DEBUG: User not logged in, redirecting to index.php -->";
    redirect('index.php');
}

// Get room ID from URL parameter
$room_id = isset($_GET['room_id']) ? filteration($_GET['room_id']) : null;

// Fetch room data if room_id is provided
if ($room_id) {
    $room_q = "SELECT * FROM `rooms` WHERE `id`=? AND `status`=? AND `removed`=?";
    $room_res = select($room_q, [$room_id, 1, 0], 'iii');

    if (mysqli_num_rows($room_res) == 0) {
        redirect('rooms.php');
    }

    $room_data = mysqli_fetch_assoc($room_res);

    // Get room features
    $features_q = "SELECT f.name FROM `features` f 
                   INNER JOIN `room_features` rf ON f.id = rf.features_id 
                   WHERE rf.room_id = ?";
    $features_res = select($features_q, [$room_id], 'i');

    $features_data = [];
    if (mysqli_num_rows($features_res) > 0) {
        while ($features_row = mysqli_fetch_assoc($features_res)) {
            $features_data[] = $features_row['name'];
        }
    }

    // Get room facilities
    $facilities_q = "SELECT f.name FROM `facilities` f 
                     INNER JOIN `room_facilities` rf ON f.id = rf.facilities_id 
                     WHERE rf.room_id = ?";
    $facilities_res = select($facilities_q, [$room_id], 'i');

    $facilities_data = [];
    if (mysqli_num_rows($facilities_res) > 0) {
        while ($facilities_row = mysqli_fetch_assoc($facilities_res)) {
            $facilities_data[] = $facilities_row['name'];
        }
    }

    // Create booking data with actual room information
    $availability_error = '';

    // Read and validate GET parameters if provided
    $check_in_param = isset($_GET['check_in']) ? trim($_GET['check_in']) : null;
    $check_out_param = isset($_GET['check_out']) ? trim($_GET['check_out']) : null;
    $adults_param = isset($_GET['adults']) ? (int)$_GET['adults'] : 2;
    $children_param = isset($_GET['children']) ? (int)$_GET['children'] : 0;

    $valid_dates = false;
    if ($check_in_param && $check_out_param) {
        $ci_dt = DateTime::createFromFormat('Y-m-d', $check_in_param);
        $co_dt = DateTime::createFromFormat('Y-m-d', $check_out_param);
        $valid_dates = $ci_dt && $co_dt && $ci_dt->format('Y-m-d') === $check_in_param && $co_dt->format('Y-m-d') === $check_out_param && $ci_dt < $co_dt;
        if (!$valid_dates) {
            $availability_error = 'Invalid date selection. Please choose valid check-in and check-out dates.';
        }
    }

    // Default to +1 and +3 days if no valid GET params
    $final_check_in = $valid_dates ? $check_in_param : date('Y-m-d', strtotime('+1 day'));
    $final_check_out = $valid_dates ? $check_out_param : date('Y-m-d', strtotime('+3 days'));

    // If dates are valid, check availability against bookings and room_availability
    if ($valid_dates) {
        // Check conflicts in bookings table
        $conflict_query = "SELECT id FROM bookings 
                           WHERE room_id = ? 
                           AND booking_status IN ('confirmed','pending') 
                           AND (
                               (check_in < ? AND check_out > ?) OR
                               (check_in < ? AND check_out > ?) OR
                               (check_in >= ? AND check_out <= ?)
                           )";
        $stmt = mysqli_prepare($con, $conflict_query);
        mysqli_stmt_bind_param($stmt, 'issssss', $room_id, $final_check_out, $final_check_in, $final_check_out, $final_check_in, $final_check_in, $final_check_out);
        mysqli_stmt_execute($stmt);
        $conf_res = mysqli_stmt_get_result($stmt);
        if ($conf_res && mysqli_num_rows($conf_res) > 0) {
            $availability_error = 'Selected dates are not available for this room due to existing bookings.';
        }
        mysqli_stmt_close($stmt);

        // Additionally check room_availability table for fully booked dates
        if ($availability_error === '') {
            $avail_q = "SELECT COUNT(*) AS cnt FROM room_availability WHERE room_id = ? AND date >= ? AND date < ? AND available_quantity = 0";
            $stmt2 = mysqli_prepare($con, $avail_q);
            mysqli_stmt_bind_param($stmt2, 'iss', $room_id, $final_check_in, $final_check_out);
            mysqli_stmt_execute($stmt2);
            $avail_res = mysqli_stmt_get_result($stmt2);
            if ($avail_res) {
                $row = mysqli_fetch_assoc($avail_res);
                if (!empty($row['cnt']) && (int)$row['cnt'] > 0) {
                    $availability_error = 'Selected dates are fully booked. Please choose different dates.';
                }
            }
            mysqli_stmt_close($stmt2);
        }
    }

    // Calculate nights based on final dates
    $nights_tmp = (new DateTime($final_check_in))->diff(new DateTime($final_check_out))->days;
    if ($nights_tmp <= 0) {
        $nights_tmp = 1;
    }

    // Build booking data
    $booking_data = [
        'booking_id' => 'LCR' . date('Ymd') . rand(1000, 9999),
        'room_id' => $room_data['id'],
        'room_name' => $room_data['name'],
        'room_price' => $room_data['price'],
        'guest_name' => $_SESSION['uName'] ?? 'Guest User',
        'guest_email' => $_SESSION['uEmail'] ?? 'guest@example.com',
        'guest_phone' => $_SESSION['uPhone'] ?? '+63 912 345 6789',
        'check_in' => $final_check_in,
        'check_out' => $final_check_out,
        'adults' => max(1, $adults_param),
        'children' => max(0, $children_param),
        'special_requests' => '',
        'booking_date' => date('Y-m-d H:i:s'),
        'booking_status' => 'confirmed',
        'payment_status' => 'pending',
        'total_amount' => $room_data['price'] * $nights_tmp
    ];
} else {
    // Create sample booking data for demonstration if no room_id
    $booking_data = [
        'booking_id' => 'LCR' . date('Ymd') . rand(1000, 9999),
        'room_name' => 'Deluxe Ocean View Suite',
        'room_price' => 2500,
        'guest_name' => $_SESSION['uName'] ?? 'Guest User',
        'guest_email' => $_SESSION['uEmail'] ?? 'guest@example.com',
        'guest_phone' => $_SESSION['uPhone'] ?? '+63 912 345 6789',
        'check_in' => date('Y-m-d', strtotime('+1 day')),
        'check_out' => date('Y-m-d', strtotime('+3 days')),
        'adults' => 2,
        'children' => 0,
        'special_requests' => 'Ocean view preferred',
        'booking_date' => date('Y-m-d H:i:s'),
        'booking_status' => 'confirmed',
        'payment_status' => 'pending',
        'total_amount' => 5000
    ];
}

// Calculate nights
$checkin = new DateTime($booking_data['check_in']);
$checkout = new DateTime($booking_data['check_out']);
$nights = $checkin->diff($checkout)->days;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Leisure Coast Resort</title>


    <?php require('inc/links.php'); ?>
    <style>
        body {
            font-family: var(--font-body);
            background-color: var(--background);
            color: var(--text-dark);
        }

        /* Enhanced Modern Styling - Consistent with room_details.php */
        .transparent-navbar {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6)) !important;
            backdrop-filter: blur(10px);
            border: none;
            transition: all 0.3s ease;
        }

        .transparent-navbar.scrolled {
            /* background: rgba(255, 255, 255, 0.95) !important; */
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .transparent-navbar.scrolled .nav-link {
            color: white !important;
        }

        .transparent-navbar.scrolled .brand-Cinzel {
            color: white !important;
        }

        /* Hero Section - Matching room_details.php */
        .booking-hero {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9)),
                url('images/rooms/room1.jpg') center/cover;
            min-height: 40vh;
            display: flex;
            align-items: center;
            position: relative;
            margin-top: 76px;
        }

        .booking-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8));
        }

        .booking-hero .container {
            position: relative;
            z-index: 2;
        }

        .confirmation-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }

        .booking-summary-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 100px;
        }

        .status-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 20px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            font-weight: 600;
        }

        .booking-details {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .detail-row {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            transition: all 0.3s ease;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row:hover {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            padding: 15px 10px;
        }

        .pay-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 25px;
            padding: 15px 40px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            color: white;
        }

        .payment-section {
            margin-top: 2rem;
        }

        /* Room Gallery - Matching room_details.php */
        .room-gallery {
            margin-bottom: 30px;
        }

        .room-gallery img {
            border-radius: 15px;
            height: 300px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .room-gallery img:hover {
            transform: scale(1.02);
        }

        /* Room Info Card - Matching room_details.php */
        .room-info-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .price-badge {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            display: inline-block;
        }

        .amenity-item {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .amenity-item:hover {
            transform: translateY(-5px);
        }

        @media (max-width: 768px) {
            .booking-hero {
                min-height: 30vh;
                padding: 60px 0 20px;
            }
        }

        .payment-summary {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            background: linear-gradient(135deg, #fff, #f8f9fa);
        }

        #pay-now-btn {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        #pay-now-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
        }

        .success-icon {
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #28a745, #20c997);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body class="bg-light">
    <!-- Transparent Navbar -->
    <?php require('inc/header.php'); ?>

    <!-- Hero Section with Booking Title -->
    <div class="booking-hero">
        <div class="container text-center text-white">
            <h1 class="display-4 fw-bold mb-3">
                Booking Confirmation
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item">
                        <a href="index.php" class="text-white-50 text-decoration-none">HOME</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="rooms.php" class="text-white-50 text-decoration-none">ROOMS</a>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page">
                        BOOKING CONFIRMATION
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (!empty($availability_error)): ?>
        <div class="container mt-4">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($availability_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Success Message -->
                <div class="text-center mb-4">
                    <div class="success-icon mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h2 class="fw-bold text-success">Booking Confirmed!</h2>
                    <p class="text-muted fs-5">Your reservation has been successfully confirmed. Please save this confirmation for your records.</p>
                </div>

                <!-- Room Gallery -->
                <div class="room-gallery mb-4">
                    <div id="roomCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php
                            // Fallback room images array
                            $fallback_images = [
                                "IMG_12341.jpg",
                                "IMG_12633.jpg",
                                "IMG_19430.jpg",
                                "IMG_25323.jpg",
                                "IMG_29030.jpg",
                                "IMG_41616.jpg",
                                "IMG_49697.jpg",
                                "IMG_57663.jpg",
                                "IMG_63903.jpg",
                                "IMG_67520.jpg",
                                "IMG_72980.jpg",
                                "IMG_76227.jpg",
                                "IMG_76731.jpg",
                                "IMG_99925.jpg",
                                "room1.jpg"
                            ];

                            if ($room_id) {
                                $img_q = mysqli_query($con, "SELECT * FROM `room_images` WHERE `room_id`='{$room_data['id']}'");

                                if (mysqli_num_rows($img_q) > 0) {
                                    // Display images from database
                                    $active_class = 'active';
                                    while ($img_res = mysqli_fetch_assoc($img_q)) {
                                        echo "
                                    <div class='carousel-item $active_class'>
                                        <img src='" . ROOMS_IMG_PATH . $img_res['image'] . "' class='d-block w-100' alt='Room Image' style='height: 300px; object-fit: cover;'>
                                    </div>";
                                        $active_class = '';
                                    }
                                } else {
                                    // Display fallback images when no database images exist
                                    $active_class = 'active';
                                    // Use room ID to select consistent images for each room
                                    $start_index = ($room_data['id'] - 1) % count($fallback_images);

                                    // Show 5 different images for variety
                                    for ($i = 0; $i < 5; $i++) {
                                        $image_index = ($start_index + $i) % count($fallback_images);
                                        $room_img = ROOMS_IMG_PATH . $fallback_images[$image_index];
                                        echo "
                                    <div class='carousel-item $active_class'>
                                        <img src='$room_img' class='d-block w-100' alt='Room Image' style='height: 300px; object-fit: cover;'>
                                    </div>";
                                        $active_class = '';
                                    }
                                }
                            } else {
                                // Default image when no room_id
                                echo "
                                <div class='carousel-item active'>
                                    <img src='" . ROOMS_IMG_PATH . "room1.jpg' class='d-block w-100' alt='Room Image' style='height: 300px; object-fit: cover;'>
                                </div>";
                            }
                            ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Booking Details</h4>
                    <span class="badge bg-success fs-6">Confirmed</span>
                </div>

                <div class="row">
                    <!-- Left Column - Room Info -->
                    <div class="col-md-8">
                        <div class="room-info-card p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h3 class="fw-bold mb-2"><?= $booking_data['room_name'] ?></h3>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rating me-3">
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <i class="bi bi-star text-warning"></i>
                                            <span class="ms-2 text-muted">(4.2)</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="price-badge">
                                    ₱<?= number_format($booking_data['room_price'], 0) ?>
                                    <small class="text-muted">/night</small>
                                </div>
                            </div>

                            <!-- Room Features -->
                            <div class="room-features mb-4">
                                <h5 class="fw-bold mb-3">Room Features</h5>
                                <div class="row">
                                    <?php if ($room_id && isset($features_data) && !empty($features_data)): ?>
                                        <?php foreach ($features_data as $feature): ?>
                                            <div class="col-md-6 mb-2">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <?= $feature ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <i class="bi bi-info-circle text-muted me-2"></i>
                                            No specific features listed
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Room Facilities - REMOVED FROM UI DISPLAY -->
                            <!--
                            <!-- Room Facilities - REMOVED FROM UI DISPLAY -->
                            <!--
                            <div class="room-facilities mb-4">
                                <h5 class="fw-bold mb-3">Facilities</h5>
                                <div class="row">
                                    <?php if ($room_id && isset($facilities_data) && !empty($facilities_data)): ?>
                                        <?php foreach ($facilities_data as $facility): ?>
                                            <div class="col-md-6 mb-2">
                                                <i class="bi bi-wifi text-primary me-2"></i>
                                                <?= $facility ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <i class="bi bi-info-circle text-muted me-2"></i>
                                            Standard hotel facilities available
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            -->
                            -->
                            <div class="room-facilities mb-4">
                                <h5 class="fw-bold mb-3">Facilities</h5>
                                <div class="row">
                                    <?php if ($room_id && isset($facilities_data) && !empty($facilities_data)): ?>
                                        <?php foreach ($facilities_data as $facility): ?>
                                            <div class="col-md-6 mb-2">
                                                <i class="bi bi-wifi text-primary me-2"></i>
                                                <?= $facility ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <i class="bi bi-info-circle text-muted me-2"></i>
                                            Standard hotel facilities available
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Room Capacity -->
                            <div class="room-capacity">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="capacity-item">
                                            <i class="bi bi-people-fill text-info me-2"></i>
                                            <strong>Capacity:</strong> <?= $booking_data['adults'] ?> Adults, <?= $booking_data['children'] ?> Children
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="capacity-item">
                                            <i class="bi bi-arrows-fullscreen text-info me-2"></i>
                                            <strong>Area:</strong> 450 sq ft
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Booking Summary -->
                    <div class="col-md-4">
                        <div class="booking-summary-card p-4">
                            <h5 class="fw-bold mb-3">Booking Summary</h5>

                            <div class="booking-info mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Check-in:</span>
                                    <span class="fw-bold"><?= date('M j, Y', strtotime($booking_data['check_in'])) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Check-out:</span>
                                    <span class="fw-bold"><?= date('M j, Y', strtotime($booking_data['check_out'])) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Guests:</span>
                                    <span class="fw-bold"><?= $booking_data['adults'] ?> Adults, <?= $booking_data['children'] ?> Children</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Nights:</span>
                                    <span class="fw-bold"><?= $nights ?></span>
                                </div>
                            </div>

                            <hr>

                            <div class="price-breakdown mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Room Rate (<?= $nights ?> nights):</span>
                                    <span>₱<?= number_format($booking_data['room_price'] * $nights, 0) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Taxes & Fees:</span>
                                    <span>₱0</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total Amount:</span>
                                    <span>₱<?= number_format($booking_data['total_amount'], 0) ?></span>
                                </div>
                            </div>

                            <!-- Guest Information -->
                            <div class="guest-info mb-3">
                                <h6 class="fw-bold mb-2">Guest Information</h6>
                                <div class="small text-muted">
                                    <div class="mb-1"><strong>Name:</strong> <?= $booking_data['guest_name'] ?></div>
                                    <div class="mb-1"><strong>Email:</strong> <?= $booking_data['guest_email'] ?></div>
                                    <div class="mb-1"><strong>Phone:</strong> <?= $booking_data['guest_phone'] ?></div>
                                    <div class="mb-1"><strong>Booking ID:</strong> <?= $booking_data['booking_id'] ?></div>
                                </div>
                            </div>

                            <!-- Payment Status -->
                            <div class="payment-status mb-3">
                                <small class="text-muted">Payment Status: <span class="text-warning fw-bold"><?= ucfirst($booking_data['payment_status']) ?></span></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Room Details Section -->
            <div class="confirmation-card p-4 mb-4">
                <h4 class="fw-bold mb-4">
                    <i class="bi bi-house-door me-2"></i>Room Details
                </h4>

                <!-- Room Information Card -->
                <div class="room-info-card p-4">
                    <!-- Price Badge -->
                    <div class="price-badge mb-3">
                        ₱<?= $booking_data['room_price'] ?> per night
                    </div>

                    <!-- Rating -->
                    <div class="mb-3">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-half text-warning"></i>
                        <span class="ms-2 text-muted">4.5 (128 reviews)</span>
                    </div>

                    <!-- Features -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-star me-2"></i>Features</h6>
                        <span class='badge bg-primary me-2 mb-2'>Ocean View</span>
                        <span class='badge bg-primary me-2 mb-2'>King Size Bed</span>
                        <span class='badge bg-primary me-2 mb-2'>Private Balcony</span>
                        <span class='badge bg-primary me-2 mb-2'>Mini Bar</span>
                    </div>

                    <!-- Facilities - REMOVED FROM UI DISPLAY -->
                    <!--
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-gear me-2"></i>Facilities</h6>
                        <span class='badge bg-success me-2 mb-2'>WiFi</span>
                        <span class='badge bg-success me-2 mb-2'>Air Conditioning</span>
                        <span class='badge bg-success me-2 mb-2'>Room Service</span>
                        <span class='badge bg-success me-2 mb-2'>Flat Screen TV</span>
                    </div>
                    -->

                    <!-- Guest and Area Info -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="guest-info text-center p-3 bg-light rounded">
                                <i class="bi bi-people-fill fs-4 mb-2 text-primary"></i>
                                <h6 class="mb-0">Capacity</h6>
                                <p class="mb-0"><?= $booking_data['adults'] ?> Adults • <?= $booking_data['children'] ?> Children</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="area-info text-center p-3 bg-light rounded">
                                <i class="bi bi-house-fill fs-4 mb-2 text-primary"></i>
                                <h6 class="mb-0">Room Area</h6>
                                <p class="mb-0">450 sq. ft.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <?php if ($booking_data['payment_status'] == 'pending'): ?>
                <div class="payment-section mb-4">
                    <div class="confirmation-card p-4">
                        <h5 class="fw-bold mb-4 text-center">
                            <i class="bi bi-credit-card me-2"></i>Complete Your Payment
                        </h5>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Your booking is confirmed but payment is still pending. Please complete your payment to secure your reservation.
                        </div>

                        <form id="payment-form" class="row g-3">
                            <input type="hidden" name="booking_id" value="<?= $booking_data['booking_id'] ?>">
                            <input type="hidden" name="total_amount" value="<?= $booking_data['total_amount'] ?>">

                            <!-- Payment Method -->
                            <div class="col-12">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="debit_card">Debit Card</option>
                                    <option value="gcash">GCash</option>
                                    <option value="paymaya">PayMaya</option>
                                </select>
                            </div>

                            <!-- Card Details (shown when card payment is selected) -->
                            <div id="card-details" style="display: none;">
                                <div class="col-md-8">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                                </div>
                                <div class="col-md-4">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" maxlength="4">
                                </div>
                                <div class="col-md-6">
                                    <label for="expiry_month" class="form-label">Expiry Month</label>
                                    <select class="form-select" id="expiry_month" name="expiry_month">
                                        <option value="">Month</option>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= sprintf('%02d', $i) ?>"><?= sprintf('%02d', $i) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="expiry_year" class="form-label">Expiry Year</label>
                                    <select class="form-select" id="expiry_year" name="expiry_year">
                                        <option value="">Year</option>
                                        <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="cardholder_name" class="form-label">Cardholder Name</label>
                                    <input type="text" class="form-control" id="cardholder_name" name="cardholder_name" placeholder="Name as on card">
                                </div>
                            </div>

                            <!-- E-wallet Details (shown when e-wallet is selected) -->
                            <div id="ewallet-details" style="display: none;">
                                <div class="col-12">
                                    <label for="mobile_number" class="form-label">Mobile Number</label>
                                    <input type="tel" class="form-control" id="mobile_number" name="mobile_number" placeholder="+63 912 345 6789">
                                </div>
                            </div>

                            <!-- Payment Summary -->
                            <div class="col-12">
                                <div class="payment-summary p-3 bg-light rounded">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Booking Amount:</span>
                                        <span>₱<?= number_format($booking_data['total_amount'], 2) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Processing Fee:</span>
                                        <span>₱0.00</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total to Pay:</span>
                                        <span class="text-primary">₱<?= number_format($booking_data['total_amount'], 2) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Pay Now Button -->
                            <div class="col-12 text-center">
                                <?php if (isset($_SESSION['login']) && $_SESSION['login'] == true): ?>
                                    <button type="submit" class="pay-btn" id="pay-now-btn">
                                        <i class="bi bi-credit-card me-2"></i>Pay Now - ₱<?= number_format($booking_data['total_amount'], 2) ?>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-warning w-100 py-3" onclick="alert('Please log in to proceed with payment.')">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Pay Now (Login Required)
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="text-center no-print">
                <button onclick="window.print()" class="btn print-btn me-3">
                    <i class="bi bi-printer me-2"></i>Print Confirmation
                </button>
                <a href="rooms.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Rooms
                </a>
            </div>

            <!-- Important Information -->
            <div class="mt-4 p-4 bg-info bg-opacity-10 rounded">
                <h6 class="fw-bold text-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>Important Information
                </h6>
                <ul class="mb-0 text-muted">
                    <li>Please arrive at the resort with a valid ID and this confirmation.</li>
                    <li>Check-in time is 3:00 PM and check-out time is 12:00 PM.</li>
                    <li>For any changes or cancellations, please contact us at least 24 hours in advance.</li>
                    <li>Payment can be made at the resort upon check-in.</li>
                </ul>
            </div>
            <!-- Room Description Section -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="description-card p-4">
                        <h4 class="fw-bold mb-4">
                            <i class="bi bi-info-circle me-2"></i>About This Room
                        </h4>
                        <p class="lead text-muted">
                            Experience luxury and comfort in our beautifully appointed <?= $booking_data['room_name'] ?>.
                            This spacious room offers modern amenities and elegant furnishings to ensure a memorable stay.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="reviews-card p-4">
                        <h4 class="fw-bold mb-4">
                            <i class="bi bi-chat-dots me-2"></i>Guest Reviews
                        </h4>
                        <div class="text-center text-muted">
                            <i class="bi bi-star fs-1 mb-3"></i>
                            <p>Reviews coming soon...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Footer -->
    <?php require('inc/footer.php'); ?>

    <!-- JavaScript for Payment Form -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethod = document.getElementById('payment_method');
            const cardDetails = document.getElementById('card-details');
            const ewalletDetails = document.getElementById('ewallet-details');
            const paymentForm = document.getElementById('payment-form');

            // Show/hide payment details based on selected method
            if (paymentMethod) {
                paymentMethod.addEventListener('change', function() {
                    const selectedMethod = this.value;

                    // Hide all detail sections
                    cardDetails.style.display = 'none';
                    ewalletDetails.style.display = 'none';

                    // Show relevant section
                    if (selectedMethod === 'credit_card' || selectedMethod === 'debit_card') {
                        cardDetails.style.display = 'block';
                        cardDetails.classList.add('row', 'g-3');
                    } else if (selectedMethod === 'gcash' || selectedMethod === 'paymaya') {
                        ewalletDetails.style.display = 'block';
                        ewalletDetails.classList.add('row', 'g-3');
                    }
                });
            }

            // Format card number input
            const cardNumberInput = document.getElementById('card_number');
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function() {
                    let value = this.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
                    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                    this.value = formattedValue;
                });
            }

            // Handle form submission
            if (paymentForm) {
                paymentForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Show loading state
                    const payButton = document.getElementById('pay-now-btn');
                    const originalText = payButton.innerHTML;
                    payButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing Payment...';
                    payButton.disabled = true;

                    // Simulate payment processing
                    setTimeout(function() {
                        alert('Payment processed successfully! Your booking is now confirmed.');
                        // In a real application, you would send the data to your payment processor
                        // and then redirect to a success page or update the booking status
                        location.reload();
                    }, 3000);
                });
            }
        });

        // Navbar scroll effect - consistent with index.php
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector(".transparent-navbar");
            if (window.scrollY > 50) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });
    </script>

    REGISTER
    </button>
    </div>
    </div>
    </form>
    </div>
    </div>
    </div>

</body>

</html>
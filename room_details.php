<?php
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leisure Coast Resort - ROOM-DETAILS</title>
    <?php require('inc/links.php'); ?>


    <style>
        body {
            font-family: var(--font-body);
            background-color: var(--background);
            color: var(--text-dark);
        }

        /* Navbar Styles */
        .nav-link-resort {
            color: white !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link-resort:hover {
            color: #b2ebf2 !important;
        }

        .resort-brand .brand-text {
            color: white;
        }

        /* Navbar styles are now handled globally in styles.css */

        .resort-logo {
            width: 60px;
            height: 60px;
        }

        @media screen and (max-width: 575px) {
            .transparent-navbar {
                position: fixed;
                right: 10px;
            }
        }

        /* Modern Room Details Styling */
        .room-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 120px 0 60px;
            margin-top: 0;
            position: relative;
            overflow: hidden;
        }

        .room-gallery {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .room-gallery img {
            height: 400px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .room-gallery:hover img {
            transform: scale(1.05);
        }

        .room-info-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }

        .price-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }

        .feature-badge {
            background: linear-gradient(45deg, #74b9ff, #0984e3);
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            margin: 3px;
            font-size: 0.85rem;
            display: inline-block;
        }

        .facility-badge {
            background: linear-gradient(45deg, #00b894, #00a085);
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            margin: 3px;
            font-size: 0.85rem;
            display: inline-block;
        }

        .guest-info {
            background: linear-gradient(45deg, #fdcb6e, #e17055);
            color: white;
            padding: 15px;
            border-radius: 15px;
            text-align: center;
            margin: 15px 0;
        }

        .area-info {
            background: linear-gradient(45deg, #a29bfe, #6c5ce7);
            color: white;
            padding: 15px;
            border-radius: 15px;
            text-align: center;
            margin: 15px 0;
        }

        .book-btn {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .book-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 184, 148, 0.3);
            color: white;
        }

        .availability-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            border: none;
        }

        .form-control,
        .form-select {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .check-btn {
            background: white;
            color: #667eea;
            border: none;
            padding: 15px;
            border-radius: 15px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .check-btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            color: #007bff;
        }

        .description-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .room-amenities {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
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

        .amenity-icon {
            font-size: 2rem;
            color: #007bff;
            margin-bottom: 10px;
        }

        /* Booking Card Styles */
        .booking-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: none;
            position: sticky;
            top: 100px;
        }

        .price-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin: -10px -10px 20px -10px;
        }

        .total-amount {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef) !important;
            border: 2px solid #dee2e6;
            border-radius: 15px;
        }

        /* Description and Reviews Cards */
        .description-card,
        .reviews-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
        }

        /* Enhanced Mobile Responsiveness */
        @media (max-width: 768px) {
            .room-gallery img {
                height: 250px;
            }

            .room-hero {
                padding: 60px 0 20px;
            }

            .booking-card {
                position: static;
                margin-top: 30px;
            }

            .price-badge {
                font-size: 1rem;
                padding: 12px 20px;
            }

            .guest-info,
            .area-info {
                margin: 10px 0;
                padding: 12px;
            }

            .room-amenities {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .amenity-item {
                padding: 15px;
            }
        }

        @media (max-width: 576px) {
            .room-hero {
                padding: 40px 0 15px;
            }

            .room-hero h1 {
                font-size: 2rem;
            }

            .room-info-card,
            .booking-card,
            .description-card,
            .reviews-card {
                margin: 15px 0;
                padding: 20px;
            }

            .feature-badge,
            .facility-badge {
                font-size: 0.75rem;
                padding: 6px 12px;
            }

            .book-btn {
                padding: 12px 25px;
                font-size: 1rem;
            }
        }
    </style>
</head>

<body class="bg-light">

    <!-- Transparent Navbar -->
    <?php require('inc/header.php'); ?>

    <?php
    if (!isset($_GET['id'])) {
        redirect('rooms.php');
    }

    $data = filteration($_GET);
    $room_res = select("SELECT * FROM `rooms` WHERE `id`=? AND `status`=? AND `removed`=?", [$data['id'], 1, 0], 'iii');

    if (mysqli_num_rows($room_res) == 0) {
        redirect('rooms.php');
    }

    $room_data = mysqli_fetch_assoc($room_res);
    ?>

    <!-- Hero Section with Room Title -->
    <div class="room-hero">
        <div class="container text-center text-white">
            <h1 class="display-4 fw-bold mb-3">
                <?= htmlspecialchars($room_data['name']) ?>
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
                        <?= htmlspecialchars($room_data['name']) ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row g-5">
            <!-- Left Side - Room Gallery and Info -->
            <div class="col-lg-8">
                <!-- Room Gallery -->
                <div class="room-gallery mb-5">
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

                            $img_q = mysqli_query($con, "SELECT * FROM `room_images` WHERE `room_id`='{$room_data['id']}'");

                            if (mysqli_num_rows($img_q) > 0) {
                                // Display images from database
                                $active_class = 'active';
                                while ($img_res = mysqli_fetch_assoc($img_q)) {
                                    echo "
                                <div class='carousel-item $active_class'>
                                    <img src='" . ROOMS_IMG_PATH . $img_res['image'] . "' class='d-block w-100' alt='Room Image' style='height: 400px; object-fit: cover;'>
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
                                    <img src='$room_img' class='d-block w-100' alt='Room Image' style='height: 400px; object-fit: cover;'>
                                </div>";
                                    $active_class = '';
                                }
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

                <!-- Room Information Card -->
                <div class="room-info-card p-5">
                    <!-- Price Badge -->
                    <div class="price-badge mb-4">
                        ₱<?= $room_data['price'] ?> per night
                    </div>

                    <!-- Rating -->
                    <div class="mb-4">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-half text-warning"></i>
                        <span class="ms-2 text-muted fs-6">4.5 (128 reviews)</span>
                    </div>

                    <!-- Features -->
                    <div class="mb-5">
                        <h5 class="fw-bold mb-3 text-dark">
                            <i class="bi bi-star me-2 text-primary"></i>Features
                        </h5>
                        <div class="d-flex flex-wrap">
                            <?php
                            $fea_q = mysqli_query($con, "SELECT f.name FROM `features` f INNER JOIN `room_features` rfea ON f.id = rfea.features_id WHERE rfea.room_id = '$room_data[id]'");
                            while ($fea_row = mysqli_fetch_assoc($fea_q)) {
                                echo "<span class='feature-badge me-2 mb-2'>$fea_row[name]</span>";
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Facilities -->
                    <div class="mb-5">
                        <h5 class="fw-bold mb-3 text-dark">
                            <i class="bi bi-gear me-2 text-primary"></i>Facilities
                        </h5>
                        <div class="d-flex flex-wrap">
                            <?php
                            $fac_q = mysqli_query($con, "SELECT f.name FROM `facilities` f INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id WHERE rfac.room_id = '$room_data[id]'");
                            while ($fac_row = mysqli_fetch_assoc($fac_q)) {
                                echo "<span class='facility-badge me-2 mb-2'>$fac_row[name]</span>";
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Guest and Area Info -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="guest-info">
                                <i class="bi bi-people-fill fs-4 mb-2"></i>
                                <h6 class="mb-1 fw-bold">Capacity</h6>
                                <p class="mb-0"><?= $room_data['adult'] ?> Adults • <?= $room_data['children'] ?> Children</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="area-info">
                                <i class="bi bi-house-fill fs-4 mb-2"></i>
                                <h6 class="mb-1 fw-bold">Room Area</h6>
                                <p class="mb-0"><?= $room_data['area'] ?> sq. ft.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Book Now Button -->
                    <?php if (isset($_SESSION['login']) && $_SESSION['login'] == true): ?>
                        <button class="book-btn mt-3" id="book-now-scroll">
                            <i class="bi bi-calendar-check me-2"></i>Book Now
                        </button>
                    <?php else: ?>
                        <button class="book-btn mt-3" onclick="showLoginAlert()">
                            <i class="bi bi-exclamation-triangle me-2"></i>Book Now (Login Required)
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Side - Booking Form -->
            <div class="col-lg-4">
                <div class="booking-card p-4">
                    <h5 class="fw-bold mb-4 text-center">
                        <i class="bi bi-calendar-check me-2"></i>Book This Room
                    </h5>

                    <!-- Price Display -->
                    <div class="price-display text-center mb-4">
                        <h3 class="fw-bold mb-1">₱<?= $room_data['price'] ?></h3>
                        <small>per night</small>
                    </div>

                    <form id="booking-form">
                        <input type="hidden" name="room_id" value="<?= $room_data['id'] ?>">
                        <input type="hidden" name="room_price" value="<?= $room_data['price'] ?>">

                        <!-- Date Selection -->
                        <div class="mb-3">
                            <label for="checkin" class="form-label">Check-in Date</label>
                            <input type="date" class="form-control" id="checkin" name="checkin" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="checkout" class="form-label">Check-out Date</label>
                            <input type="date" class="form-control" id="checkout" name="checkout" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>

                        <!-- Guest Selection -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="adults" class="form-label">Adults</label>
                                <select class="form-select" id="adults" name="adults" required>
                                    <?php
                                    for ($i = 1; $i <= $room_data['adult']; $i++) {
                                        echo "<option value='$i'>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="children" class="form-label">Children</label>
                                <select class="form-select" id="children" name="children">
                                    <?php
                                    for ($i = 0; $i <= $room_data['children']; $i++) {
                                        echo "<option value='$i'>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Guest Information -->
                        <div class="alert alert-secondary mb-4">
                            <i class="bi bi-person-lines-fill me-2"></i>
                            Guest details and payment method will be collected on the next step.
                        </div>

                        <!-- Total Amount Display -->
                        <div class="total-amount mb-4 p-3 rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Room Rate:</span>
                                <span id="room-rate">₱<?= $room_data['price'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Number of Nights:</span>
                                <span id="nights-count">0</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total Amount:</span>
                                <span id="total-amount" class="text-primary">₱0</span>
                            </div>
                        </div>

                        <!-- Login check with proper alert -->
                        <?php if (isset($_SESSION['login']) && $_SESSION['login'] == true): ?>
                            <button type="button" class="btn btn-success w-100 py-3" id="book-now-btn">
                                <i class="bi bi-arrow-right-circle me-2"></i>Continue to Guest Info & Payment
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-warning w-100 py-3" id="login-required-btn" onclick="showLoginAlert()">
                                <i class="bi bi-exclamation-triangle me-2"></i>Continue (Login Required)
                            </button>
                            <div class="alert alert-info text-center mt-2">
                                <i class="bi bi-info-circle me-2"></i>
                                Please <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="text-decoration-none">login</a> to continue
                            </div>
                        <?php endif; ?>

                        <!-- Debug info - visible on page -->
                        <div class="alert alert-info mt-3">
                            <strong>Debug Info:</strong><br>
                            Login Status: <?= isset($_SESSION['login']) ? ($_SESSION['login'] ? 'TRUE' : 'FALSE') : 'NOT SET' ?><br>
                            User Name: <?= isset($_SESSION['uName']) ? $_SESSION['uName'] : 'NOT SET' ?><br>
                            User Email: <?= isset($_SESSION['uEmail']) ? $_SESSION['uEmail'] : 'NOT SET' ?>
                        </div>

                        <!-- Debug info -->
                        <?php
                        echo "<!-- DEBUG: Session login: " . (isset($_SESSION['login']) ? $_SESSION['login'] : 'not set') . " -->";
                        echo "<!-- DEBUG: Session data: " . print_r($_SESSION, true) . " -->";
                        ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Room Description Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="description-card p-4">
                    <h4 class="fw-bold mb-4">
                        <i class="bi bi-info-circle me-2"></i>About This Room
                    </h4>
                    <p class="lead text-muted">
                        <?= $room_data['description'] ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="reviews-card p-4">
                    <h4 class="fw-bold mb-4 d-flex align-items-center justify-content-between">
                        <span>
                            <i class="bi bi-chat-dots me-2"></i>Guest Reviews
                        </span>
                        <span class="text-muted fs-6" id="avg-rating-display" style="display:none"></span>
                    </h4>

                    <div id="reviews-list" class="mb-4">
                        <div class="text-center text-muted">
                            <i class="bi bi-star fs-1 mb-3"></i>
                            <p>No reviews yet. Be the first to review this room.</p>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['login']) && $_SESSION['login'] == true): ?>
                        <div class="review-form">
                            <h5 class="fw-bold mb-3">Write a Review</h5>
                            <form id="review-form">
                                <input type="hidden" name="room_id" id="review-room-id" value="<?= $room_data['id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <select class="form-select w-auto" name="rating" id="review-rating" required>
                                            <option value="">Select</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                        <span class="text-muted">Stars</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="review-comment" class="form-label">Your Review</label>
                                    <textarea class="form-control" id="review-comment" name="comment" rows="3" placeholder="Share your experience..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                                <div class="form-text">Your review will be visible after admin approval.</div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Please <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">login</a> to write a review.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    </div>


    <!-- Optional Room List Section -->
    <!-- You can move your room card loop here if needed -->

    </div>
</body>

<?php require('inc/footer.php'); ?>

<!-- Scroll Navbar Effect -->
<script>
    // Login alert function
    function showLoginAlert() {
        alert('Please log in to book this room.');
        var loginModal = document.querySelector('#loginModal');
        if (loginModal) {
            var modal = new bootstrap.Modal(loginModal);
            modal.show();
        }
    }

    window.addEventListener("scroll", function() {
        const navbar = document.querySelector(".transparent-navbar");
        if (window.scrollY > 50) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }
    });

    // Availability + totals logic for booking form
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('booking-form');
        if (!form) return;

        const roomId = form.querySelector('input[name="room_id"]').value;
        const roomPrice = parseFloat(form.querySelector('input[name="room_price"]').value || '0');
        const checkinEl = document.getElementById('checkin');
        const checkoutEl = document.getElementById('checkout');
        const nightsEl = document.getElementById('nights-count');
        const totalEl = document.getElementById('total-amount');
        const statusEl = document.getElementById('availability-status');
        const adultsEl = document.getElementById('adults');
        const childrenEl = document.getElementById('children');
        const nameEl = document.getElementById('guest_name');
        const emailEl = document.getElementById('guest_email');
        const phoneEl = document.getElementById('guest_phone');
        const reqEl = document.getElementById('special_requests');

        // NEW: Pre-fill from URL params if present
        const params = new URLSearchParams(window.location.search);
        const urlCheckin = params.get('checkin') || params.get('check_in');
        const urlCheckout = params.get('checkout') || params.get('check_out');
        const urlAdults = params.get('adults');
        const urlChildren = params.get('children');

        if (urlCheckin) {
            checkinEl.value = urlCheckin;
            // set checkout min to next day
            const ci = new Date(urlCheckin);
            const nextDay = new Date(ci);
            nextDay.setDate(nextDay.getDate() + 1);
            const y = nextDay.getFullYear();
            const m = String(nextDay.getMonth() + 1).padStart(2, '0');
            const d = String(nextDay.getDate()).padStart(2, '0');
            checkoutEl.min = `${y}-${m}-${d}`;
        }
        if (urlCheckout) {
            checkoutEl.value = urlCheckout;
        }
        if (urlAdults && adultsEl) adultsEl.value = urlAdults;
        if (urlChildren && childrenEl) childrenEl.value = urlChildren;

        const scrollBtn = document.getElementById('book-now-scroll');
        if (scrollBtn && form) {
            scrollBtn.addEventListener('click', function() {
                form.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        }

        function formatCurrency(num) {
            try {
                return '₱' + Number(num).toLocaleString('en-PH', {
                    maximumFractionDigits: 0
                });
            } catch (e) {
                return '₱' + num;
            }
        }

        function computeNights() {
            if (!checkinEl.value || !checkoutEl.value) return 0;
            const ci = new Date(checkinEl.value);
            const co = new Date(checkoutEl.value);
            const diffMs = co - ci;
            const nights = Math.round(diffMs / (1000 * 60 * 60 * 24));
            return Math.max(0, nights);
        }

        function addDays(dateStr, days) {
            const d = new Date(dateStr);
            d.setDate(d.getDate() + days);
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function updateCheckoutMin() {
            if (checkinEl.value) {
                checkoutEl.min = addDays(checkinEl.value, 1);
                if (checkoutEl.value && checkoutEl.value <= checkinEl.value) {
                    checkoutEl.value = '';
                }
            }
        }

        function updateTotals() {
            updateCheckoutMin();
            const nights = computeNights();
            if (nightsEl) nightsEl.textContent = nights;
            if (totalEl) totalEl.textContent = formatCurrency(nights * roomPrice);
        }

        if (checkinEl) checkinEl.addEventListener('change', updateTotals);
        if (checkoutEl) checkoutEl.addEventListener('change', updateTotals);
        // NEW: compute totals after potential URL prefill
        updateTotals();

        function showStatus(type, message, extraHtml = '') {
            if (!statusEl) return;
            statusEl.style.display = 'block';
            statusEl.innerHTML = `<div class="alert alert-${type}">${message}${extraHtml}</div>`;
        }

        async function validateAndProceed() {
            const checkin = checkinEl.value;
            const checkout = checkoutEl.value;
            if (!checkin || !checkout) {
                showStatus('warning', 'Please select both check-in and check-out dates.');
                return;
            }
            if (new Date(checkout) <= new Date(checkin)) {
                showStatus('warning', 'Check-out date must be after check-in date.');
                return;
            }

            showStatus('info', 'Checking availability, please wait...');

            try {
                const res = await fetch('ajax/validate_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        room_id: parseInt(roomId),
                        checkin_date: checkin,
                        checkout_date: checkout
                    })
                });
                const contentType = res.headers.get('content-type') || '';
                const text = await res.text();
                console.debug('[validate_booking] status=', res.status, 'content-type=', contentType, 'body=', text.slice(0, 500));
                if (!contentType.includes('application/json')) {
                    throw new Error(`Unexpected response (status ${res.status}): ${text.slice(0, 200)}`);
                }
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Failed to parse server response as JSON: ' + text.slice(0, 300));
                }

                if (data && data.success) {
                    showStatus('success', `Dates are available for ${data.nights} night(s). Redirecting to guest information...`);

                    const params = new URLSearchParams({
                        room_id: roomId,
                        check_in: checkin,
                        check_out: checkout,
                        adults: adultsEl?.value || '1',
                        children: childrenEl?.value || '0'
                    });

                    // Slight delay to let the user see the success message
                    setTimeout(() => {
                        window.location.href = 'guest_information.php?' + params.toString();
                    }, 600);
                } else {
                    let extra = '';
                    if (data?.conflicting_bookings?.length) {
                        const items = data.conflicting_bookings.map(b => `<li><strong>${b.booking_id}</strong>: ${b.check_in} to ${b.check_out}</li>`).join('');
                        extra = `<ul class="mt-2 mb-0">${items}</ul>`;
                    }
                    if (data?.unavailable_dates?.length) {
                        const items = data.unavailable_dates.map(d => `<span class="badge bg-secondary me-1 mb-1">${d}</span>`).join('');
                        extra += `<div class="mt-2">Unavailable dates: ${items}</div>`;
                    }
                    showStatus('danger', data?.message || 'Selected dates are not available.', extra);
                }
            } catch (err) {
                console.error(err);
                showStatus('danger', 'An error occurred while checking availability. ' + (err?.message || 'Please try again.'));
            }
        }

        const bookBtn = document.getElementById('book-now-btn');
        if (bookBtn) {
            bookBtn.addEventListener('click', validateAndProceed);
        }
    });
</script>

<!-- Reviews logic -->
<script>
    (function() {
        const roomId = document.getElementById('review-room-id') ? document.getElementById('review-room-id').value : '<?= $room_data['id'] ?>';
        const listEl = document.getElementById('reviews-list');
        const avgEl = document.getElementById('avg-rating-display');

        function renderReviews(data) {
            if (!data || !data.success) {
                return;
            }
            const {
                reviews,
                avg_rating,
                count
            } = data;
            if (typeof avg_rating !== 'undefined') {
                if (count > 0) {
                    avgEl.style.display = '';
                    avgEl.textContent = `${avg_rating} / 5 · ${count} review${count>1?'s':''}`;
                } else {
                    avgEl.style.display = 'none';
                }
            }
            if (!reviews || reviews.length === 0) {
                listEl.innerHTML = `<div class="text-center text-muted"><i class=\"bi bi-star fs-1 mb-3\"></i><p>No reviews yet. Be the first to review this room.</p></div>`;
                return;
            }
            listEl.innerHTML = reviews.map(r => {
                const stars = '★★★★★'.slice(0, r.rating) + '☆☆☆☆☆'.slice(0, 5 - r.rating);
                const user = r.user_name ? r.user_name : 'Guest';
                const date = r.created_at ? new Date(r.created_at).toLocaleDateString() : '';
                return `
        <div class="border rounded p-3 mb-3">
          <div class="d-flex justify-content-between">
            <strong>${user}</strong>
            <small class="text-muted">${date}</small>
          </div>
          <div class="text-warning" style="letter-spacing:2px">${stars}</div>
          <p class="mb-0">${r.comment ? r.comment.replace(/</g,'&lt;') : ''}</p>
        </div>`;
            }).join('');
        }

        function fetchReviews() {
            fetch(`ajax/reviews.php?room_id=${encodeURIComponent(roomId)}`)
                .then(r => r.json())
                .then(renderReviews)
                .catch(() => {});
        }

        fetchReviews();

        const form = document.getElementById('review-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const fd = new FormData(form);
                fd.append('submit_review', '1');
                fetch('ajax/reviews.php', {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.success) {
                            alert('success', 'Review submitted! Waiting for admin approval.');
                            form.reset();
                            fetchReviews();
                        } else {
                            const raw = (data && data.message) ? data.message : 'Submission failed';
                            let friendly = raw;
                            if (raw === 'not_logged_in') friendly = 'Please log in to submit a review.';
                            if (raw === 'invalid_room') friendly = 'Invalid room. Please refresh the page and try again.';
                            if (raw === 'invalid_rating') friendly = 'Please select a rating between 1 and 5.';
                            if (raw === 'empty_comment') friendly = 'Please enter your review comment.';
                            alert('danger', friendly);
                        }
                    })
                    .catch(() => alert('danger', 'Network error'));
            });
        }
    })();
</script>

</body>

</html>
<?php
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leisure Coast Resort - HOME</title>
    <?php require('inc/links.php'); ?>
</head>
<?php $isLoggedIn = (isset($_SESSION['login']) && $_SESSION['login'] === true) ? '1' : '0'; ?>

<body class="bg-light home-page" data-logged-in="<?php echo $isLoggedIn; ?>">
    <?php require('inc/header.php'); ?>

    <!-- Hero Section with Enhanced Blue Theme -->
    <div class="hero-section">
        <div class="hero-content text-center">
            <h1 class="hero-text">LEISURE COAST RESORT</h1>
            <p class="hero-subtitle text-white mb-4" style="font-size: 1.3rem; opacity: 0.9; animation: fadeInUp 1s ease-out 0.5s both;">
                Your Gateway to Unforgettable Memories & Luxury
            </p>
            <div class="hero-buttons" style="animation: fadeInUp 1s ease-out 1s both;">
                <a href="#rooms" class="btn btn-primary me-3">Explore Rooms</a>
                <a href="#facilities" class="btn btn-outline-light">Our Facilities</a>
            </div>
        </div>
    </div>


    <?php
    // Build rooms array for home slideshow
    $rooms_array = [];
    $room_res = select("SELECT * FROM `rooms` WHERE `status`=? AND `removed`=? ORDER BY `id` DESC", [1, 0], 'ii');
    if ($room_res && mysqli_num_rows($room_res) > 0) {
        while ($room_data = mysqli_fetch_assoc($room_res)) {
            $features_data = '';
            $fea_q = mysqli_query($con, "SELECT f.name FROM `features` f INNER JOIN `room_features` rf ON f.id = rf.features_id WHERE rf.room_id = '{$room_data['id']}'");
            while ($fea_row = mysqli_fetch_assoc($fea_q)) {
                $features_data .= "<span class='badge me-2 mb-2'>" . htmlspecialchars($fea_row['name']) . "</span>";
            }
            $fac_q = mysqli_query($con, "SELECT f.name FROM `facilities` f INNER JOIN `room_facilities` rf ON f.id = rf.facilities_id WHERE rf.room_id = '{$room_data['id']}'");
            while ($fac_row = mysqli_fetch_assoc($fac_q)) {
                $features_data .= "<span class='badge me-2 mb-2'>" . htmlspecialchars($fac_row['name']) . "</span>";
            }
            $room_thumb = ROOMS_IMG_PATH . "room1.jpg";
            $thumb_q = mysqli_query($con, "SELECT image FROM `room_images` WHERE `room_id`='{$room_data['id']}' AND `thumb`='1' LIMIT 1");
            if ($thumb_q && mysqli_num_rows($thumb_q) > 0) {
                $thumb_res = mysqli_fetch_assoc($thumb_q);
                $room_thumb = ROOMS_IMG_PATH . $thumb_res['image'];
            }
            $rooms_array[] = [
                'id' => (int)$room_data['id'],
                'name' => $room_data['name'],
                'price' => (int)$room_data['price'],
                'features' => $features_data,
                'adult' => (int)$room_data['adult'],
                'children' => (int)$room_data['children'],
                'description' => $room_data['description'] ?? '',
                'image' => $room_thumb
            ];
        }
    }
    ?>
    <script type="application/json" id="roomsData">
        <?php echo json_encode($rooms_array); ?>
    </script>

    <!-- Home Rooms Slideshow Section -->
    <div class="room-slideshow-section spacing-section" id="rooms">
        <div class="container">
            <h2 class="section-title text-center mb-5 reveal">
                <span style="color: var(--primary-color); font-weight: 700;">OUR PREMIUM</span>
                <span style="color: var(--accent-color);">ROOMS</span>
            </h2>
            <div class="room-slideshow-card reveal" id="roomSlideshow">
                <div class="room-slide-content">
                    <div class="room-image-container">
                        <img src="" alt="" class="room-slide-image" id="roomImage">
                        <div class="room-image-overlay"></div>
                        <button class="room-slide-nav room-slide-prev" id="prevSlide"><i class="bi bi-chevron-left"></i></button>
                        <button class="room-slide-nav room-slide-next" id="nextSlide"><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <div class="room-slide-info">
                        <h3 class="room-slide-title" id="roomTitle"></h3>
                        <div class="room-slide-price">
                            <h4 id="roomPrice"></h4>
                        </div>
                        <div id="roomFeatures" class="mb-3"></div>
                        <div id="roomGuests" class="mb-3"></div>
                        <p id="roomDescription" class="mb-4"></p>
                        <div class="room-slide-buttons d-flex gap-2">
                            <a href="#" class="btn btn-outline-primary" id="moreDetailsBtn"><i class="bi bi-info-circle me-2"></i>More Details</a>
                            <button class="btn btn-primary" id="bookNowBtn"><i class="bi bi-calendar-check me-2"></i>Check Availability</button>
                        </div>
                    </div>
                </div>
                <div class="room-slide-indicators" id="roomIndicators"></div>
            </div>
        </div>
    </div>

    <!-- Facilities slider removed: Facilities are shown only on facilities.php -->
    <!-- Modern Facilities Slider Section -->
    <div class="facilities-slider-section py-5" id="facilities">
        <div class="container">
            <h2 class="section-title text-center mb-5 reveal">
                <span style="color: var(--primary-color); font-weight: 700;">WORLD-CLASS</span>
                <span style="color: var(--accent-color);">FACILITIES</span>
            </h2>

            <?php
            $facilities_res = select("SELECT * FROM `facilities` ORDER BY `id` DESC", [], "");
            $facilities_array = [];
            while ($facilities_data = mysqli_fetch_assoc($facilities_res)) {
                $facilities_array[] = $facilities_data;
            }
            ?>

            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="facility-slider-card" id="facilitySlider">
                        <div class="facility-slider-content">
                            <div class="facility-image-container">
                                <img src="" alt="" class="facility-slider-image" id="facilityImage">
                                <div class="facility-overlay"></div>
                            </div>
                            <div class="facility-info">
                                <h3 class="facility-slider-title" id="facilityTitle"></h3>
                                <p class="facility-slider-description" id="facilityDescription"></p>
                                <div class="facility-slider-controls">
                                    <div class="slider-dots" id="sliderDots"></div>
                                    <a href="facilities.php" class="btn btn-facility-modern">
                                        <i class="bi bi-arrow-right me-2"></i>Explore All Facilities
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Facilities slider data for script.js -->
    <script type="application/json" id="facilitiesData">
        <?php echo json_encode($facilities_array ?? []); ?>
    </script>
    <script type="application/json" id="facilitiesImgPath">
        <?php echo json_encode(FACILITIES_IMG_PATH ?? ''); ?>
    </script>

    <!-- Testimonials Section -->
    <div class="testimonials py-5" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
        <div class="container">
            <h2 class="section-title text-center mb-5 reveal">
                <span style="color: var(--primary-color); font-weight: 700;">GUEST</span>
                <span style="color: var(--accent-color);">TESTIMONIALS</span>
            </h2>

            <div class="row g-4">
                <!-- Testimonial 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face"
                                class="testimonial-avatar me-3" alt="John Smith">
                            <div>
                                <h6 class="testimonial-author">John Smith</h6>
                                <small class="text-muted">Business Traveler</small>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Exceptional service and beautiful location! The staff went above and beyond to make our stay memorable. The facilities are top-notch and the rooms are incredibly comfortable."
                        </p>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face"
                                class="testimonial-avatar me-3" alt="Sarah Johnson">
                            <div>
                                <h6 class="testimonial-author">Sarah Johnson</h6>
                                <small class="text-muted">Family Vacation</small>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-half"></i>
                        </div>
                        <p class="testimonial-text">
                            "Perfect for families! The kids loved the facilities and we enjoyed the peaceful atmosphere. Great value for money and excellent location near all the attractions."
                        </p>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face"
                                class="testimonial-avatar me-3" alt="Michael Chen">
                            <div>
                                <h6 class="testimonial-author">Michael Chen</h6>
                                <small class="text-muted">Couple's Getaway</small>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Romantic and serene! The perfect place for a couple's retreat. Beautiful views, excellent dining, and the spa services were absolutely divine. Highly recommended!"
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Reach Us -->

    <?php
    $contact_q = "SELECT * FROM `contact_details` WHERE `sr_no` =?";
    $values = [1];
    $contact_r = mysqli_fetch_assoc(select($contact_q, $values, 'i'));

    ?>
    <!-- Contact Section -->
    <div class="contact">
        <div class="container">
            <h2 class="section-title text-center mb-5 text-white">GET IN TOUCH</h2>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="map-container">
                        <iframe class="w-100" height="450" src="<?php echo $contact_r['iframe'] ?>"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="contact-card mb-4">
                        <div class="contact-icon">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <h5 class="text-white mb-3">Call Us</h5>
                        <a href="tel: +<?php echo $contact_r['pn1'] ?>" class="d-block mb-2 text-white text-decoration-none">
                            <i class="bi bi-telephone me-2"></i>+<?php echo $contact_r['pn1'] ?>
                        </a>
                        <?php
                        if ($contact_r['pn2'] != '') {
                            echo <<<data
                            <a href="tel: +$contact_r[pn2]" class="d-block text-white text-decoration-none">
                                <i class="bi bi-telephone me-2"></i>+$contact_r[pn2]
                            </a>
data;
                        }
                        ?>
                    </div>

                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="bi bi-share-fill"></i>
                        </div>
                        <h5 class="text-white mb-3">Follow Us</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php
                            if ($contact_r['fb'] != '') {
                                echo <<<data
                                <a href="$contact_r[fb]" class="social">
                                    <i class="bi bi-facebook"></i>
                                </a>
data;
                            }
                            ?>
                            <a href="<?php echo $contact_r['insta'] ?>" class="social">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" class="social">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" class="social">
                                <i class="bi bi-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>






    <br><br><br>
    <br><br><br>


    <?php require('inc/footer.php'); ?>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>



</body>

</html>
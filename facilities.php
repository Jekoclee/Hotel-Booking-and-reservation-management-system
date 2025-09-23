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
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Facilities - LCR</title>
    <?php require('inc/links.php'); ?>
    <style>
        body {
            font-family: var(--font-body);
            background-color: var(--background);
            color: var(--text-dark);
            padding-top: 0;
            /* Remove any top padding that might interfere with navbar */
        }

        /* Ensure navbar transparency works properly */
        .transparent-navbar {
            background: rgba(0, 0, 0, 0.1) !important;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .transparent-navbar.scrolled {
            /* background: rgba(255, 255, 255, 0.95) !important; */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .transparent-navbar.scrolled .nav-link,
        .transparent-navbar.scrolled .brand-Cinzel {
            color: white !important;
        }

        /* Modern Hero Section */
        .facilities-hero {
            background: transparent;
            color: #333;
            padding: 120px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
            min-height: 60vh;
            display: block;
        }

        .facilities-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('<?= ABOUT_IMG_PATH ?>masaya.jpg') center/cover;
            opacity: 0.8;
            z-index: 1;
        }

        .facilities-hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(to top, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.6) 50%, transparent 100%);
            z-index: 2;
        }

        .facilities-hero .container {
            position: relative;
            z-index: 2;
        }

        .facilities-hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: slideInDown 1s ease-out;
            color: white;
        }

        .facilities-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            animation: slideInUp 1s ease-out 0.3s both;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        /* Hero Image Grid Styles */
        .hero-image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 50px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            z-index: 2;
        }

        .hero-image-item {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            height: 250px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .hero-image-item:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .hero-facility-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .hero-image-item:hover .hero-facility-img {
            transform: scale(1.1);
        }

        .hero-image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: white;
            padding: 25px;
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .facilities-hero {
                text-align: center;
                padding: 80px 0 60px;
            }

            .facilities-hero h1 {
                font-size: 2.5rem;
            }

            .hero-facility-img {
                height: 120px;
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced Facility Cards */
        .facility-card-modern {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .facility-card-modern:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }

        .facility-icon-enhanced {
            width: 70px;
            height: 70px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .facility-icon-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent);
            border-radius: 50%;
            transform: scale(0);
            transition: transform 0.3s ease;
        }

        .facility-card-modern:hover .facility-icon-enhanced::before {
            transform: scale(1);
        }

        .facility-card-modern:hover .facility-icon-enhanced {
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .facility-icon-enhanced img {
            width: 40px;
            height: 40px;
            filter: brightness(0) invert(1);
            transition: all 0.3s ease;
        }

        .facility-card-modern:hover .facility-icon-enhanced img {
            transform: scale(1.1);
        }

        .facility-content-enhanced {
            padding: 2rem;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .facility-content-enhanced h5 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .facility-card-modern:hover .facility-content-enhanced h5 {
            color: #667eea;
            transform: translateY(-5px);
        }

        .facility-content-enhanced p {
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 0;
            transition: color 0.3s ease;
        }

        .facility-card-modern:hover .facility-content-enhanced p {
            color: #5a6c7d;
        }

        /* Floating Animation for Cards */
        .facility-card-modern {
            animation: floatFacility 8s ease-in-out infinite;
        }

        .facility-card-modern:nth-child(2n) {
            animation-delay: -2s;
        }

        .facility-card-modern:nth-child(3n) {
            animation-delay: -4s;
        }

        @keyframes floatFacility {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        .facility-card-modern:hover {
            animation: none;
        }

        /* Section Dividers */
        .section-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #667eea, #764ba2, transparent);
            margin: 4rem 0;
            position: relative;
        }

        .section-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
        }

        /* Enhanced Grid Layout */
        .facilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        /* Staggered Animation */
        .facility-card-modern {
            opacity: 0;
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .facility-card-modern:nth-child(1) {
            animation-delay: 0.1s;
        }

        .facility-card-modern:nth-child(2) {
            animation-delay: 0.2s;
        }

        .facility-card-modern:nth-child(3) {
            animation-delay: 0.3s;
        }

        .facility-card-modern:nth-child(4) {
            animation-delay: 0.4s;
        }

        .facility-card-modern:nth-child(5) {
            animation-delay: 0.5s;
        }

        .facility-card-modern:nth-child(6) {
            animation-delay: 0.6s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Interactive Hover Effects */
        .facility-card-modern {
            cursor: pointer;
            perspective: 1000px;
        }

        .facility-card-modern:hover {
            transform-style: preserve-3d;
        }

        /* Glowing Border Effect */
        .facility-card-modern::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 25px;
            padding: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2, #667eea);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .facility-card-modern:hover::after {
            opacity: 1;
        }

        /* Loading Shimmer Effect */
        .loading-shimmer-facilities {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmerFacilities 2s infinite;
        }

        @keyframes shimmerFacilities {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .facilities-hero h1 {
                font-size: 2.5rem;
            }

            .facilities-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .facility-card-modern:hover {
                transform: translateY(-10px) scale(1.02);
            }

            .facility-content-enhanced {
                padding: 1.5rem;
            }
        }

        /* Scroll Animations */
        @media (prefers-reduced-motion: no-preference) {
            .facility-card-modern {
                transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            .facility-card-modern {
                background: #2c3e50;
                color: white;
            }

            .facility-content-enhanced h5 {
                color: #ecf0f1;
            }

            .facility-content-enhanced p {
                color: #bdc3c7;
            }
        }

        /* Modern Slideshow Section */
        .slideshow-section {
            padding: 4rem 0;
            background: white;
            position: relative;
        }

        /* First slideshow section with white background */
        .slideshow-section:first-of-type {
            background: white;
        }

        /* Second slideshow section with white background */
        .slideshow-section:nth-of-type(2) {
            background: white;
        }

        /* Compact Slideshow Styles */
        .slideshow-container.side-by-side {
            position: relative;
            width: 100%;
            height: 450px;
            /* Increased height for better presentation */
            overflow: hidden;
            border-radius: 25px;
            background: white;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
        }

        .slideshow-container.side-by-side:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .slideshow-container .slide {
            display: none;
            width: 100%;
            height: 100%;
            position: relative;
        }

        .slideshow-container .slide.active {
            display: block;
        }

        .slideshow-container .slide-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1.3);
            transition: transform 0.8s ease, opacity 0.8s ease;
            opacity: 0.7;
        }

        /* Fade-in animation for scroll effect */
        .slideshow-container.fade-in {
            opacity: 0;
            transform: translateY(50px) scale(0.9);
            transition: all 0.8s ease;
        }

        .slideshow-container.fade-in.visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .slideshow-container.fade-in.visible .slide-image {
            transform: scale(1);
            opacity: 1;
        }

        .slideshow-container .slide-content {
            position: relative;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 249, 250, 0.95) 100%);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
        }

        /* For absolute positioning when needed */
        .slideshow-container .slide-content.absolute-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
        }

        .slideshow-container .slide-content.absolute-left {
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
        }

        .slideshow-container .slide-info {
            text-align: center;
            max-width: 100%;
        }

        .slideshow-container .slide-info h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-family: var(--font-heading);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .slideshow-container .slide-info p {
            font-size: 1.1rem;
            color: #5a6c7d;
            line-height: 1.6;
            margin: 0;
            text-align: center !important;
        }

        /* Navigation Arrows */
        .slideshow-container .prev,
        .slideshow-container .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 12px 16px;
            cursor: pointer;
            font-size: 18px;
            border-radius: 8px;
            /* Square shape instead of circular */
            transition: all 0.3s ease;
            z-index: 10;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Position arrows side by side at the bottom center */
        .slideshow-container .prev {
            bottom: 20px;
            left: calc(50% - 60px);
            /* Position to the left of center */
            top: auto;
            transform: none;
        }

        .slideshow-container .next {
            bottom: 20px;
            right: calc(50% - 60px);
            /* Position to the right of center */
            top: auto;
            transform: none;
        }

        .slideshow-container .prev:hover,
        .slideshow-container .next:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: scale(1.1);
        }

        /* Dots Indicators */
        .slideshow-container .dots-container {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .slideshow-container .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slideshow-container .dot.active,
        .slideshow-container .dot:hover {
            background: white;
            transform: scale(1.2);
        }

        /* Responsive Design for Compact Cards */
        @media (max-width: 768px) {
            .slideshow-container.side-by-side {
                height: 350px;
                margin: 0 10px;
            }

            .slideshow-container .slide-content {
                position: static;
                width: 100%;
                height: auto;
                background: rgba(255, 255, 255, 0.95);
                padding: 1.5rem;
                border-radius: 0 0 15px 15px;
            }

            .slideshow-container .slide-info h2 {
                font-size: 1.4rem;
                margin-bottom: 0.8rem;
            }

            .slideshow-container .slide-info p {
                font-size: 0.9rem;
                line-height: 1.5;
            }

            .slideshow-container .slide .row {
                flex-direction: column;
            }

            .slideshow-container .slide-image {
                height: 200px;
                border-radius: 15px 15px 0 0;
            }

            /* Adjust navigation for mobile */
            .slideshow-container .prev,
            .slideshow-container .next {
                width: 35px;
                height: 35px;
                font-size: 14px;
                bottom: 15px;
            }

            .slideshow-container .prev {
                left: calc(50% - 50px);
            }

            .slideshow-container .next {
                right: calc(50% - 50px);
            }

            .slideshow-container .dot {
                width: 10px;
                height: 10px;
            }
        }

        /* Additional mobile optimization */
        @media (max-width: 480px) {
            .slideshow-container.side-by-side {
                height: 320px;
                margin: 0 5px;
            }

            .slideshow-container .slide-content {
                padding: 1rem;
            }

            .slideshow-container .slide-info h2 {
                font-size: 1.2rem;
            }

            .slideshow-container .slide-info p {
                font-size: 0.85rem;
            }

            .slideshow-container .slide-image {
                height: 180px;
            }
        }
    </style>
</head>

<body class="bg-white" data-logged-in="<?php echo isset($_SESSION['login']) && $_SESSION['login'] ? '1' : '0'; ?>">

    <?php require('inc/header.php'); ?>

    <!-- Modern Hero Section -->
    <section class="facilities-hero">
        <div class="container">
            <!-- Text removed as requested -->
        </div>
    </section>

    <!-- Modern Slideshow Section -->
    <section class="slideshow-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3" style="color: #333; font-weight: 600;">Explore Our Facilities</h2>
                <p class="lead text-muted" style="max-width: 800px; margin: 0 auto;">Take a virtual tour through our premium facilities. Each space is thoughtfully designed to enhance your stay and create memorable experiences for you and your loved ones.</p>
            </div>
            <div class="d-flex justify-content-center">
                <div class="row g-4 w-100" style="max-width: 1200px;">
                    <!-- Main Hero Pool -->
                    <div class="col-12 mb-4 d-flex justify-content-center">
                        <div class="card facility-card-modern h-100 w-100">
                            <div class="slideshow-container side-by-side">
                                <div class="slide active">
                                    <div class="row g-0 h-100">
                                        <div class="col-md-6">
                                            <img src="<?= ROOMS_IMG_PATH ?>IMG_29030.jpg" alt="Pool Area" class="slide-image">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="slide-content">
                                                <div class="slide-info">
                                                    <h2>Pool</h2>
                                                    <p>Swim in a 4 ft. pool with a view of the city or simply sit back, relax and order refreshing beverages and sumptuous snacks at the Poolside Bar for ultimate relaxation.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="slide">
                                    <div class="row g-0 h-100">
                                        <div class="col-md-6">
                                            <img src="<?= ROOMS_IMG_PATH ?>IMG_67520.jpg" alt="Pool Area" class="slide-image">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="slide-content">
                                                <div class="slide-info">
                                                    <h2>Pool</h2>
                                                    <p>Swim in a 4 ft. pool with a view of the city or simply sit back, relax and order refreshing beverages and sumptuous snacks at the Poolside Bar for ultimate relaxation.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="slide">
                                    <div class="row g-0 h-100">
                                        <div class="col-md-6">
                                            <img src="<?= ROOMS_IMG_PATH ?>IMG_12341.jpg" alt="Pool Area" class="slide-image">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="slide-content">
                                                <div class="slide-info">
                                                    <h2>Pool</h2>
                                                    <p>Swim in a 4 ft. pool with a view of the city or simply sit back, relax and order refreshing beverages and sumptuous snacks at the Poolside Bar for ultimate relaxation.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="slide">
                                    <div class="row g-0 h-100">
                                        <div class="col-md-6">
                                            <img src="<?= ROOMS_IMG_PATH ?>IMG_19430.jpg" alt="Pool Area" class="slide-image">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="slide-content">
                                                <div class="slide-info">
                                                    <h2>Pool</h2>
                                                    <p>Swim in a 4 ft. pool with a view of the city or simply sit back, relax and order refreshing beverages and sumptuous snacks at the Poolside Bar for ultimate relaxation.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Navigation arrows -->
                                <a class="prev" onclick="changeSlide(-1, this)">&#10094;</a>
                                <a class="next" onclick="changeSlide(1, this)">&#10095;</a>
                                <!-- Dots indicators -->
                                <div class="dots-container">
                                    <span class="dot active" onclick="currentSlide(1, this)"></span>
                                    <span class="dot" onclick="currentSlide(2, this)"></span>
                                    <span class="dot" onclick="currentSlide(3, this)"></span>
                                    <span class="dot" onclick="currentSlide(4, this)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modern Slideshow Section -->
    <section class="slideshow-section">
        <div class="container d-flex justify-content-center">
            <div class="row g-4 w-100" style="max-width: 1200px;">
                <?php
                // Get facilities from database
                $facilities_q = mysqli_query($con, "SELECT * FROM `facilities` ORDER BY `id` ASC");
                $path = EVENTS_IMG_PATH;

                // Sample room images for slideshow
                $room_images = [
                    "IMG_29030.jpg",
                    "IMG_67520.jpg",
                    "IMG_12341.jpg",
                    "IMG_19430.jpg",
                    "IMG_25323.jpg",
                    "IMG_41616.jpg",
                    "IMG_49697.jpg",
                    "IMG_57663.jpg"
                ];

                // Use a PHP variable for image base path so it can be interpolated inside heredoc
                $rooms_img_path = ROOMS_IMG_PATH;

                $card_counter = 0; // Counter to track card position for alternating layout

                while ($facility = mysqli_fetch_assoc($facilities_q)) {
                    // Special handling for Pool facility to include the requested description
                    $description = $facility['description'];
                    if (strtolower($facility['name']) == 'pool' || strpos(strtolower($facility['name']), 'pool') !== false) {
                        $description = "Swim in a 4 ft. pool with a view of the city or simply sit back, relax and order refreshing beverages and sumptuous snacks at the Poolside Bar for ultimate relaxation.";
                    }

                    // Determine if this is an even or odd card for alternating layout
                    $is_even_card = ($card_counter % 2 == 0);

                    echo <<<facility
                    <!-- {$facility['name']} -->
                    <div class="col-12 mb-4 d-flex justify-content-center">
                        <div class="card facility-card-modern h-100 w-100">
                            <div class="slideshow-container side-by-side">
                    facility;

                    // Generate 4 slides for each facility
                    for ($i = 0; $i < 4; $i++) {
                        $active_class = ($i == 0) ? 'active' : '';
                        $image = $room_images[($facility['id'] + $i - 1) % count($room_images)];

                        // Alternate layout: even cards (0,2,4...) = image left, text right
                        // odd cards (1,3,5...) = image right, text left
                        if ($is_even_card) {
                            // Image left, text right (default layout)
                            echo <<<slide
                                <div class="slide $active_class">
                                    <div class="row g-0 h-100">
                                        <div class="col-md-6">
                                            <img src="{$rooms_img_path}{$image}" alt="{$facility['name']} Area" class="slide-image">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="slide-content">
                                                <div class="slide-info">
                                                    <h2>{$facility['name']}</h2>
                                                    <p>$description</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            slide;
                        } else {
                            // Image right, text left (reversed layout)
                            echo <<<slide
                                <div class="slide $active_class">
                                    <div class="row g-0 h-100">
                                        <div class="col-md-6">
                                            <div class="slide-content">
                                                <div class="slide-info">
                                                    <h2>{$facility['name']}</h2>
                                                    <p>$description</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <img src="{$rooms_img_path}{$image}" alt="{$facility['name']} Area" class="slide-image">
                                        </div>
                                    </div>
                                </div>
                            slide;
                        }
                    }

                    echo <<<navigation
                                <!-- Navigation arrows -->
                                <a class="prev" onclick="changeSlide(-1, this)">&#10094;</a>
                                <a class="next" onclick="changeSlide(1, this)">&#10095;</a>
                                <!-- Dots indicators -->
                                <div class="dots-container">
                                    <span class="dot active" onclick="currentSlide(1, this)"></span>
                                    <span class="dot" onclick="currentSlide(2, this)"></span>
                                    <span class="dot" onclick="currentSlide(3, this)"></span>
                                    <span class="dot" onclick="currentSlide(4, this)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    navigation;

                    $card_counter++; // Increment counter for next card
                }
                ?>
            </div>
        </div>
        </div>
        </div>
    </section>

    <?php require('inc/footer.php'); ?>

    <script>
        window.addEventListener("scroll", function() {
            let navbar = document.querySelector(".transparent-navbar");
            if (window.scrollY > 10) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });

        // Add scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all facility cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.facility-card-modern');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
        });

        // Main Hero Slideshow Functionality
        let currentSlideIndex = 0;
        const slides = document.querySelectorAll('.slideshow-section:first-child .slide');
        const indicators = document.querySelectorAll('.slideshow-section:first-child .indicator');
        const totalSlides = slides.length;

        function showSlide(index) {
            // Remove active class from all slides and indicators
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));

            // Add active class to current slide and indicator
            if (slides[index]) {
                slides[index].classList.add('active');
            }
            if (indicators[index]) {
                indicators[index].classList.add('active');
            }
        }

        function nextSlide() {
            currentSlideIndex = (currentSlideIndex + 1) % totalSlides;
            showSlide(currentSlideIndex);
        }

        function prevSlide() {
            currentSlideIndex = (currentSlideIndex - 1 + totalSlides) % totalSlides;
            showSlide(currentSlideIndex);
        }

        function changeSlide(direction, element = null) {
            if (element) {
                // Handle individual facility slideshow
                const container = element.closest('.slideshow-container');
                const slides = container.querySelectorAll('.slide');
                const dots = container.querySelectorAll('.dot');
                let activeIndex = Array.from(slides).findIndex(slide => slide.classList.contains('active'));

                // Calculate new index
                if (direction === 1) {
                    activeIndex = (activeIndex + 1) % slides.length;
                } else {
                    activeIndex = (activeIndex - 1 + slides.length) % slides.length;
                }

                // Update slides and dots
                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));

                slides[activeIndex].classList.add('active');
                dots[activeIndex].classList.add('active');
            } else {
                // Handle main hero slideshow
                if (direction === 1) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }
        }

        function currentSlide(index, element = null) {
            if (element) {
                // Handle individual facility slideshow
                const container = element.closest('.slideshow-container');
                const slides = container.querySelectorAll('.slide');
                const dots = container.querySelectorAll('.dot');

                // Update slides and dots
                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));

                slides[index - 1].classList.add('active');
                dots[index - 1].classList.add('active');
            } else {
                // Handle main hero slideshow
                currentSlideIndex = index - 1;
                showSlide(currentSlideIndex);
            }
        }

        // Auto-advance main slideshow every 7 seconds
        let slideInterval = setInterval(nextSlide, 7000);

        // Pause auto-advance on hover for main slideshow
        const mainSlideshow = document.querySelector('.slideshow-section:first-child .slideshow-container');
        if (mainSlideshow) {
            mainSlideshow.addEventListener('mouseenter', () => {
                clearInterval(slideInterval);
            });

            // Resume auto-advance when mouse leaves
            mainSlideshow.addEventListener('mouseleave', () => {
                slideInterval = setInterval(nextSlide, 7000);
            });
        }

        // Auto-advance facility slideshows every 5 seconds
        function autoAdvanceFacilitySlides() {
            const facilityContainers = document.querySelectorAll('.slideshow-container.side-by-side');

            facilityContainers.forEach(container => {
                const slides = container.querySelectorAll('.slide');
                const dots = container.querySelectorAll('.dot');

                if (slides.length > 0) {
                    let activeIndex = Array.from(slides).findIndex(slide => slide.classList.contains('active'));
                    let nextIndex = (activeIndex + 1) % slides.length;

                    // Remove active class from current slide and dot
                    slides.forEach(slide => slide.classList.remove('active'));
                    dots.forEach(dot => dot.classList.remove('active'));

                    // Add active class to next slide and dot
                    slides[nextIndex].classList.add('active');
                    if (dots[nextIndex]) {
                        dots[nextIndex].classList.add('active');
                    }
                }
            });
        }

        // Initialize all slideshows
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize main slideshow
            if (slides.length > 0) {
                showSlide(0);
            }

            // Initialize all facility slideshows
            const facilityContainers = document.querySelectorAll('.slideshow-container.side-by-side');
            facilityContainers.forEach(container => {
                const slides = container.querySelectorAll('.slide');
                const dots = container.querySelectorAll('.dot');

                // Set first slide as active
                if (slides.length > 0) {
                    slides[0].classList.add('active');
                }
                if (dots.length > 0) {
                    dots[0].classList.add('active');
                }
            });

            // Start auto-advance for facility slideshows every 5 seconds
            setInterval(autoAdvanceFacilitySlides, 5000);

            // Scroll-triggered animation for facility containers
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);

            // Add fade-in class to facility containers and observe them
            facilityContainers.forEach(container => {
                container.classList.add('fade-in');
                observer.observe(container);
            });
        });
    </script>




</body>

</html>
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
    <title>Leisure Coast Resort - EVENTS</title>
    <?php require('inc/links.php'); ?>

    <style>
        body {
            font-family: var(--font-body);
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--text-dark);
            padding-top: 0;
        }

        /* Transparent Navbar Styling */
        .transparent-navbar {
            background: linear-gradient(135deg, rgba(108, 117, 125, 0.9), rgba(73, 80, 87, 0.8)) !important;
            backdrop-filter: blur(10px);
            transition: all var(--transition-normal);
        }

        .transparent-navbar.scrolled {
            box-shadow: var(--shadow-md);
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--white);
            padding: 120px 0 80px;
            text-align: center;
            margin-top: 80px;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,0 1000,100 1000,0"/></svg>');
            background-size: cover;
        }

        .page-header h1 {
            font-family: var(--font-heading);
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: var(--spacing-md);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }

        .page-header p {
            font-size: 1.4rem;
            opacity: 0.95;
            position: relative;
            z-index: 2;
            font-weight: 300;
        }

        .events-container {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 50%, #f8f9fa 100%);
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-family: var(--font-heading);
            font-size: 3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 20px;
            position: relative;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        .section-title p {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        .event-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            border: none;
            height: 100%;
            position: relative;
        }

        .event-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: 1;
        }

        .event-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }

        .event-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .event-card:hover img {
            transform: scale(1.1);
        }

        .event-card-body {
            padding: 30px;
            position: relative;
        }

        .event-card h5 {
            color: #667eea;
            font-family: var(--font-heading);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .event-card p {
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .event-date {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--white);
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }

        .btn-outline-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .btn-outline-primary:hover::before {
            left: 0;
        }

        .btn-outline-primary:hover {
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .venue-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 0;
            margin-top: 60px;
        }

        .venue-section .section-title h2 {
            color: white;
        }

        .venue-section .section-title p {
            color: rgba(255, 255, 255, 0.9);
        }

        .venue-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            height: 100%;
        }

        .venue-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body class="bg-light">

    <!-- Transparent Navbar -->
    <?php
    // Include header without session_start since it's already called at the top
    require('admin/inc/db_config.php');
    require('admin/inc/essentials.php');
    ?>
    <?php require('inc/header.php'); ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>Our Events</h1>
            <p>Discover the perfect venue for your special occasions and create unforgettable memories</p>
        </div>
    </div>

    <!-- Events Section -->
    <div class="events-container">
        <div class="container">
            <div class="section-title">
                <h2>Event Categories</h2>
                <p>From intimate gatherings to grand celebrations, we have the perfect setting for every occasion</p>
            </div>

            <div class="row">
                <!-- Wedding Events -->
                <div class="col-lg-4 col-md-6 mb-5">
                    <div class="event-card">
                        <img src="<?= EVENTS_IMG_PATH ?>wedding.jpg" alt="Wedding Event" class="card-img-top">
                        <div class="event-card-body">
                            <div class="event-date">Year Round</div>
                            <h5>Wedding Celebrations</h5>
                            <p>Create magical moments with our elegant wedding packages. From intimate ceremonies to grand receptions, we make your special day unforgettable with personalized service and attention to detail.</p>
                            <a href="#" class="btn btn-outline-primary">Explore Packages</a>
                        </div>
                    </div>
                </div>

                <!-- Corporate Events -->
                <div class="col-lg-4 col-md-6 mb-5">
                    <div class="event-card">
                        <img src="<?= EVENTS_IMG_PATH ?>corporate.jpg" alt="Corporate Event" class="card-img-top">
                        <div class="event-card-body">
                            <div class="event-date">All Year</div>
                            <h5>Corporate Events</h5>
                            <p>Professional venues for conferences, seminars, and corporate gatherings. Modern facilities with state-of-the-art technology and comprehensive business amenities.</p>
                            <a href="#" class="btn btn-outline-primary">View Facilities</a>
                        </div>
                    </div>
                </div>

                <!-- Birthday Parties -->
                <div class="col-lg-4 col-md-6 mb-5">
                    <div class="event-card">
                        <img src="<?= EVENTS_IMG_PATH ?>birthday.jpg" alt="Birthday Party" class="card-img-top">
                        <div class="event-card-body">
                            <div class="event-date">Any Date</div>
                            <h5>Birthday Celebrations</h5>
                            <p>Make birthdays memorable with our customizable party packages. Perfect for all ages with flexible arrangements and themed decorations to suit your style.</p>
                            <a href="#" class="btn btn-outline-primary">Plan Your Party</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Venues Section -->
    <div class="venue-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Event Venues</h2>
                <p>Choose from our variety of stunning venues, each designed to create the perfect atmosphere for your special event</p>
            </div>

            <div class="row">
                <!-- Grand Ballroom -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="venue-card">
                        <img src="<?= EVENTS_IMG_PATH ?>ballroom.jpg" alt="Grand Ballroom" class="card-img-top" style="height: 300px; object-fit: cover;">
                        <div class="card-body p-4">
                            <h4 class="card-title text-primary mb-3">Grand Ballroom</h4>
                            <p class="card-text">Our flagship venue featuring elegant chandeliers, spacious dance floor, and capacity for up to 300 guests. Perfect for weddings, galas, and large celebrations.</p>
                            <div class="venue-features mb-3">
                                <span class="badge bg-primary me-2">300 Capacity</span>
                                <span class="badge bg-secondary me-2">Dance Floor</span>
                                <span class="badge bg-success">Full Service</span>
                            </div>
                            <a href="#" class="btn btn-outline-light">View Details</a>
                        </div>
                    </div>
                </div>

                <!-- Garden Pavilion -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="venue-card">
                        <img src="<?= EVENTS_IMG_PATH ?>garden.jpg" alt="Garden Pavilion" class="card-img-top" style="height: 300px; object-fit: cover;">
                        <div class="card-body p-4">
                            <h4 class="card-title text-primary mb-3">Garden Pavilion</h4>
                            <p class="card-text">An enchanting outdoor venue surrounded by lush gardens and natural beauty. Ideal for intimate ceremonies, cocktail parties, and outdoor celebrations.</p>
                            <div class="venue-features mb-3">
                                <span class="badge bg-primary me-2">150 Capacity</span>
                                <span class="badge bg-secondary me-2">Outdoor</span>
                                <span class="badge bg-success">Garden View</span>
                            </div>
                            <a href="#" class="btn btn-outline-light">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.transparent-navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards
        document.querySelectorAll('.event-card, .venue-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>

</body>

</html>
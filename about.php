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
    <title>Leisure Coast Resort - ABOUT</title>
    <?php require('inc/links.php'); ?>
    <style>
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

        body {
            font-family: var(--font-body);
            background-color: var(--background);
            color: var(--text-dark);
            padding-top: 0;
            /* Remove any top padding that might interfere with navbar */
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: var(--white);
            padding: var(--spacing-xxl) 0;
            text-align: center;
            margin-top: 80px;
        }

        .page-header h1 {
            font-family: var(--font-heading);
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: var(--spacing-md);
        }

        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .facility-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-md);
            transition: all var(--transition-normal);
            border: 1px solid var(--gray-light);
        }

        .facility-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .facility-card h3 {
            color: var(--primary);
            font-family: var(--font-heading);
            font-size: 1.5rem;
            margin-bottom: var(--spacing-md);
        }

        .facility-card p {
            color: var(--text-muted);
            line-height: 1.6;
        }

        .stats-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-xl);
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: all var(--transition-normal);
            border: 1px solid var(--gray-light);
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            font-family: var(--font-heading);
        }

        .stats-label {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-top: var(--spacing-sm);
        }

        .facility-icon {
            font-size: 2rem;
            color: #0d6efd;
        }

        .facility-title {
            font-weight: 600;
            font-size: 1.2rem;
        }

        /* Add some spacing so transparent navbar doesn’t overlap title */
        .page-top-space {
            padding-top: 120px;
        }


        .box {
            border-top-color: var(--teal) !important;
        }
    </style>
</head>

<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <!-- Consistent Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>About Us</h1>
            <p>Experience comfort and leisure with our world-class amenities</p>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-between align-items-center">
            <div class="col-lg-6 col-md-5 mb-4 order-lg-1 order-md-1 order-2">

                <h3 class="mb-3 bg-light">Lorem ipsum dolor sit amet consectetur , nostrum asperiores, quidem facilis. Dignissimos non nostrum quia reprehenderit!</h3>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. repellat eum, nostrum asperiores, quidem facilis. Dignissimos non nostrum quia reprehenderit!</p>
            </div>
            <div class="col-lg-5 col-md-5 mb-4 order-lg-2 order-md-2 order-1">
                <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" class="w-100">

            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4 px-4">
                <div class="bg-white rounded shadow p-4 border-top border-4 text-center box">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" width="70px">
                    <h4 class="mt-3">100+ ROOMS</h4>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 px-4">
                <div class="bg-white rounded shadow p-4 border-top border-4 text-center box">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" width="70px">
                    <h4 class="mt-3">100+ ROOMS</h4>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 px-4">
                <div class="bg-white rounded shadow p-4 border-top border-4 text-center box">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" width="70px">
                    <h4 class="mt-3">100+ ROOMS</h4>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 px-4">
                <div class="bg-white rounded shadow p-4 border-top border-4 text-center box">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" width="70px">
                    <h4 class="mt-3">100+ ROOMS</h4>
                </div>
            </div>
        </div>
    </div>

    <h3 class="my-5 fw-bold h-font text-center bg-light">MANAGEMENT TEAM</h3>

    <div class="container px-4">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper mb-5">

                <?php
                $about_r = selectAll('team_details');
                $path = ABOUT_IMG_PATH;
                while ($row = mysqli_fetch_assoc($about_r)) {
                    echo <<<data
                        <div class="swiper-slide bg-white text-center overflow-hidden rounded">
                       <img src="$path$row[picture]" class="w-100">
                       <h5 class="mt-2">$row[name]</h5>
                        </div>
                    data;
                }
                ?>
                <div class="swiper-slide bg-white text-center overflow-hidden rounded">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" class="w-100">
                    <h5 class="mt-2">Random name</h5>
                </div>
                <div class="swiper-slide bg-white text-center overflow-hidden rounded">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" class="w-100">
                    <h5 class="mt-2">Random name</h5>
                </div>
                <div class="swiper-slide bg-white text-center overflow-hidden rounded">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" class="w-100">
                    <h5 class="mt-2">Random name</h5>
                </div>
                <div class="swiper-slide bg-white text-center overflow-hidden rounded">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" class="w-100">
                    <h5 class="mt-2">Random name</h5>
                </div>
                <div class="swiper-slide bg-white text-center overflow-hidden rounded">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" class="w-100">
                    <h5 class="mt-2">Random name</h5>
                </div>
                <div class="swiper-slide bg-white text-center overflow-hidden rounded">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" class="w-100">
                    <h5 class="mt-2">Random name</h5>
                </div>
                <div class="swiper-slide bg-white text-center overflow-hidden rounded">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" class="w-100">
                    <h5 class="mt-2">Random name</h5>
                </div>
                <div class="swiper-slide bg-white text-center overflow-hidden rounded">
                    <img src="<?= ABOUT_IMG_PATH ?>about1.jpg" class="w-100">
                    <h5 class="mt-2">Random name</h5>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
        window.addEventListener("scroll", function() {
            let navbar = document.querySelector(".navbar");
            if (window.scrollY > 10) { // when scrolled 50px down
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });
    </script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 1, // default for mobile
            spaceBetween: 20,
            loop: true, // makes it continuous
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                640: { // tablets
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                768: { // small laptops
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
                1024: { // desktops
                    slidesPerView: 4,
                    spaceBetween: 40,
                }
            }
        });
    </script>



</body>

</html>
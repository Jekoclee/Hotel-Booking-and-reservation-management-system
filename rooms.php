<?php
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize filter variables to prevent undefined variable warnings
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 1;
$children = isset($_GET['children']) ? (int)$_GET['children'] : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leisure Coast Resort - Luxury Rooms & Suites</title>
    <?php require('inc/links.php'); ?>
</head>

<body class="bg-light rooms-page" data-logged-in="<?php echo isset($_SESSION['login']) && $_SESSION['login'] ? '1' : '0'; ?>">

    <!-- Transparent Navbar -->
    <?php require('inc/header.php'); ?>

    <!-- Luxury Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Luxury Rooms & Suites</h1>
            <p>Indulge in unparalleled comfort and elegance in our exquisitely designed accommodations</p>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="hero-section" style="height: 60vh; background: linear-gradient(135deg, rgba(30, 64, 175, 0.8), rgba(245, 158, 11, 0.6)), url('images/LCR.jpg') center center / cover no-repeat;">
        <div class="hero-content text-center">
            <h1 class="hero-text floating-element" style="font-size: 3.5rem;">Our Premium Rooms</h1>
            <p class="hero-subtitle text-white mb-4" style="font-size: 1.2rem; opacity: 0.9; animation: fadeInUp 1s ease-out 0.5s both;">
                Discover Comfort & Luxury in Every Stay
            </p>
        </div>
    </div>

    <!-- Rooms Section -->
    <div class="rooms-section py-5">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h2 class="section-title reveal">
                        <span style="color: var(--primary-color); font-weight: 700;">CHOOSE YOUR</span>
                        <span style="color: var(--accent-color);">PERFECT ROOM</span>
                    </h2>
                    <p class="section-subtitle reveal" style="color: var(--secondary-light); font-size: 1.1rem; margin-top: 15px;">
                        Each room is thoughtfully designed with modern amenities and elegant furnishings to ensure your comfort and satisfaction.
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <?php
                $room_res = select("SELECT * FROM `rooms` WHERE `status`=? AND `removed`=?", [1, 0], "ii");

                if (mysqli_num_rows($room_res) > 0) {
                    while ($room_data = mysqli_fetch_assoc($room_res)) {
                        // Get room features
                        $fea_q = mysqli_query($con, "SELECT f.name FROM `features` f 
                            INNER JOIN `room_features` rf ON f.id = rf.features_id 
                            WHERE rf.room_id = '$room_data[id]'");

                        $features_data = "";
                        while ($fea_row = mysqli_fetch_assoc($fea_q)) {
                            $features_data .= "<span class='amenity-item'>$fea_row[name]</span>";
                        }

                        // Get room facilities (keep query active, but do not display in Rooms page)
                        $facilities_q = mysqli_query($con, "SELECT f.name FROM `facilities` f 
                            INNER JOIN `room_facilities` rf ON f.id = rf.facilities_id 
                            WHERE rf.room_id = '$room_data[id]'");
                        // while ($fac_row = mysqli_fetch_assoc($facilities_q)) { /* no UI display here */ }

                        // Get room thumbnail
                        $room_thumb = ROOMS_IMG_PATH . "room1.jpg";
                        $thumb_q = mysqli_query($con, "SELECT * FROM `room_images` 
                            WHERE `room_id`='$room_data[id]' AND `thumb`='1'");

                        if (mysqli_num_rows($thumb_q) > 0) {
                            $thumb_res = mysqli_fetch_assoc($thumb_q);
                            $room_thumb = ROOMS_IMG_PATH . $thumb_res['image'];
                        }

                        echo "
                            <div class='col-lg-4 col-md-6 mb-4'>
                                <div class='room-card reveal'>
                                    <div class='room-image'>
                                        <img src='$room_thumb' alt='$room_data[name]' loading='lazy'>
                                        <div class='price-badge'>₱" . number_format($room_data['price'], 0) . "/night</div>
                                    </div>
                                    <div class='room-content'>
                                        <h3 class='room-title'>$room_data[name]</h3>
                                        
                                        <div class='room-info'>
                                            <div class='info-item'>
                                                <i class='bi bi-people-fill' style='color: var(--primary-color);'></i>
                                                <span>$room_data[adult] Adults</span>
                                            </div>";

                        if ($room_data['children'] > 0) {
                            echo "<div class='info-item'>
                                    <i class='bi bi-person-fill' style='color: var(--primary-color);'></i>
                                    <span>$room_data[children] Children</span>
                                  </div>";
                        }

                        echo "<div class='info-item'>
                                <i class='bi bi-house-door-fill' style='color: var(--primary-color);'></i>
                                <span>$room_data[area] m²</span>
                              </div>
                          </div>";

                        if (!empty($features_data)) {
                            echo "<div class='amenities'>
                                    <h6 style='color: var(--primary-color); font-weight: 600;'><i class='bi bi-star-fill'></i> Premium Amenities</h6>
                                    <div class='amenity-list'>
                                        $features_data
                                    </div>
                                  </div>";
                        }

                        echo "<div class='room-actions'>
                                <a href='room_details.php?id=$room_data[id]' class='btn btn-outline-primary btn-details'>
                                    <i class='bi bi-info-circle me-2'></i>More Details
                                </a>
                                <a href='" . (!empty($checkin) && !empty($checkout)
                                    ? ("room_details.php?id={$room_data['id']}&checkin=" . urlencode($checkin) . "&checkout=" . urlencode($checkout)
                                        . (!empty($adults) ? ("&adults=" . urlencode($adults)) : "")
                                        . (isset($children) ? ("&children=" . urlencode($children)) : ""))
                                    : ("booking_calendar.php?room_id={$room_data['id']}")
                                ) . "' class='btn btn-primary btn-book'>
                                    <i class='bi bi-calendar-check me-2'></i>Book Now
                                </a>
                              </div>
                          </div>
                      </div>
                  </div>";
                    }
                } else {
                    echo '<div class="col-12"><div class="alert alert-info text-center" style="border-radius: 15px; border: none; background: linear-gradient(135deg, #e3f2fd, #bbdefb);">
                        <i class="bi bi-info-circle" style="font-size: 2rem; color: var(--primary-color);"></i>
                        <h5 style="color: var(--primary-color); margin-top: 15px;">No rooms available at the moment.</h5>
                        <p style="color: var(--secondary-light);">Please check back later or contact us for more information.</p>
                    </div></div>';
                }
                ?>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>

</body>

</html>
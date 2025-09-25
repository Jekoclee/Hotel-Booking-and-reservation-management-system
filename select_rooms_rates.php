<?php
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 1;
$children = isset($_GET['children']) ? (int)$_GET['children'] : 0;
$promo = isset($_GET['promo']) ? trim($_GET['promo']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Select Rooms & Rates</title>
  <?php require('inc/links.php'); ?>
  <style>
    body { padding-top: 90px; background: #f7f7f7; }
    .booking-container { width: 100%; max-width: 1200px; margin: 2rem auto; padding: 0 1rem }
    .rooms-card { background: #fff; border-radius: 15px; box-shadow: 0 10px 25px rgba(0, 0, 0, .08); padding: 1.5rem; }
    .section-title { font-weight: 700; margin: 0 0 1rem 0; color: #333; display: flex; align-items: center }
    .section-title .badge-icon { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 6px; background: #e7f1ff; color: #0d6efd; margin-right: 8px }
    .section-title .badge-icon svg { width: 16px; height: 16px }
    .room-card { border: 1px solid #e9ecef; border-radius: 10px; background: #fff; overflow: hidden; transition: transform .2s ease, box-shadow .2s ease; display: flex; flex-direction: column; height: 100%; cursor: pointer }
    .room-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 0, 0, .08) }
    .room-image { position: relative }
    .room-image img { width: 100%; height: 180px; object-fit: cover }
    .room-content { padding: 12px 12px 14px; display: flex; flex-direction: column; gap: 8px; flex: 1 }
    .price-badge { position: absolute; right: 10px; bottom: 10px; background: rgba(220, 53, 69, .95); color: #fff; border-radius: 8px; padding: 6px 10px; font-size: 12px; font-weight: 600; box-shadow: 0 4px 10px rgba(220, 53, 69, .25) }
    .amenity-list { display: flex; flex-wrap: wrap; gap: 6px }
    .amenity-item { background: #f8f9fa; border: 1px solid #eef2f4; border-radius: 12px; padding: 6px 10px; font-size: 11px; color: #666 }
    .booking-summary { background: linear-gradient(135deg, #0d6efd, #2563eb); color: #fff; border-radius: 15px; padding: 16px 18px; box-shadow: 0 10px 25px rgba(0, 0, 0, .08); min-height: 380px }
    .booking-summary h6 { font-weight: 700; margin-bottom: 12px }
    .summary-line { background: rgba(255, 255, 255, .15); border-radius: 10px; padding: 10px 12px; font-size: 13px; margin-bottom: 10px; display: flex; justify-content: space-between }
    .summary-edit { display: inline-block; margin-top: 8px; color: #fff; text-decoration: underline; font-size: 12px }

    .booking-steps { background: #fff; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); width: 100%; margin: 0 0 1rem; padding: 1.25rem }
    .booking-steps .steps-container { display: flex; gap: .75rem; align-items: center; overflow-x: auto; padding-bottom: .25rem }
    .booking-steps .step { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .4rem; padding: .5rem; border-radius: 12px; min-width: 140px; text-align: center }
    .booking-steps .step-icon { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; background: #e9ecef; color: #6c757d; transition: all .3s ease }
    .booking-steps .step-title { font-size: 13px; font-weight: 600; text-align: center; line-height: 1.2; color: #6c757d }
    .booking-steps .step.active .step-icon { background: #0d6efd; color: #fff }
    .booking-steps .step.active .step-title { color: #0d6efd }
    .booking-steps .connector { display: flex; align-items: center; gap: .4rem; min-width: 80px; flex: 1 }
    .booking-steps .connector .dot { width: 10px; height: 10px; border-radius: 50%; background: #e0e6ef; flex-shrink: 0 }
    .booking-steps .connector .line { height: 3px; background: #e0e6ef; border-radius: 3px; flex: 1 }
    .booking-steps .connector.active .dot, .booking-steps .connector.active .line { background: #0d6efd }

    .select-btn { display: block; width: 100%; margin-top: 10px }

    @media (max-width: 991.98px) { .booking-summary { margin-top: 16px } }
    @media (min-width: 768px) { .booking-summary { position: sticky; top: 90px; } }
  </style>
</head>
<body>
  <?php require('inc/header.php'); ?>

  <div class="booking-steps">
    <div class="steps-container">
      <div class="step">
        <div class="step-icon"><i class="bi bi-calendar-check-fill"></i></div>
        <div class="step-title">Check-in &<br>Check-out Date</div>
      </div>
      <div class="connector active"><span class="dot"></span><span class="line"></span></div>
      <div class="step active">
        <div class="step-icon"><i class="bi bi-house-door-fill"></i></div>
        <div class="step-title">Select<br>Rooms & Rates</div>
      </div>
      <div class="connector"><span class="dot"></span><span class="line"></span></div>
      <div class="step">
        <div class="step-icon"><i class="bi bi-person-fill"></i></div>
        <div class="step-title">Guest<br>Information</div>
      </div>
      <div class="connector"><span class="dot"></span><span class="line"></span></div>
      <div class="step">
        <div class="step-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="step-title">Booking<br>Confirmation</div>
      </div>
    </div>
  </div>

  <div class="booking-container">
    <div class="row g-4">
      <div class="col-md-8 col-lg-9 order-md-1 order-lg-1">
        <div class="rooms-card">
          <h5 class="section-title">
            <span class="badge-icon">
              <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M3 7a2 2 0 0 1 2-2h7.586a2 2 0 0 1 1.414.586l5.414 5.414a2 2 0 0 1 0 2.828l-5.172 5.172a2 2 0 0 1-2.828 0L3.586 12.414A2 2 0 0 1 3 11V7z"></path>
              </svg>
            </span>
            <span>Available Rooms</span>
          </h5>
          <div class="row g-3">
            <?php
            $rooms_query = "SELECT * FROM rooms WHERE status = 1 AND removed = 0";
            $rooms_result = mysqli_query($con, $rooms_query);
            if ($rooms_result && mysqli_num_rows($rooms_result) > 0) {
              while ($room = mysqli_fetch_assoc($rooms_result)) {
                $thumb_q = mysqli_query($con, "SELECT image FROM room_images WHERE room_id='{$room['id']}' AND thumb='1' LIMIT 1");
                $thumb = 'images/rooms/room1.jpg';
                if (mysqli_num_rows($thumb_q) > 0) {
                  $tr = mysqli_fetch_assoc($thumb_q);
                  $thumb = ROOMS_IMG_PATH . $tr['image'];
                }

                $fea_q = mysqli_query($con, "SELECT f.name FROM `features` f INNER JOIN `room_features` rf ON f.id=rf.features_id WHERE rf.room_id='{$room['id']}'");
                $features = '';
                while ($fr = mysqli_fetch_assoc($fea_q)) {
                  $features .= "<span class='amenity-item'>{$fr['name']}</span>";
                }
                $features_html = !empty($features) ? "<div class='amenity-list mt-2'>$features</div>" : "";
                $q = http_build_query(array_filter([
                  'id' => $room['id'],
                  'checkin' => $checkin,
                  'checkout' => $checkout,
                  'adults' => $adults,
                  'children' => $children,
                  'promo' => $promo
                ], function ($v) { return $v !== '' && $v !== null; }));

                echo "<div class='col-6 col-md-6'>
                <div class='room-card h-100' data-room-id='" . $room['id'] . "'>
                  <div class='room-image'>
                    <img src='$thumb' alt='" . htmlspecialchars($room['name']) . "'>
                    <div class='price-badge'>₱" . number_format($room['price'], 0) . "/night</div>
                  </div>
                  <div class='room-content'>
                    <h6 class='room-title'>" . htmlspecialchars($room['name']) . "</h6>
                    <div style='display:flex; gap:10px; color:#666; font-size:12px;'>
                      <span><i class='bi bi-people-fill'></i> {$room['adult']} Adults</span>" .
                  ($room['children'] > 0 ? "<span><i class='bi bi-person-fill'></i> {$room['children']} Children</span>" : "") .
                  "<span><i class='bi bi-house-door-fill'></i> {$room['area']} m²</span>
                    </div>
                    "
                  . $features_html .
                  "
                  </div>
                </div>
              </div>";
              }
            } else {
              echo '<div class="col-12"><div class="alert alert-info">No rooms available right now.</div></div>';
            }
            ?>
          </div>
        </div>
      </div>
      <div class="col-md-4 col-lg-3 order-md-2 order-lg-2">
        <div class="booking-summary">
          <h6>Booking Summary</h6>
          <div class="summary-line"><span>Date</span><span><?php echo $checkin ? date('M d, Y', strtotime($checkin)) : '—'; ?> - <?php echo $checkout ? date('M d, Y', strtotime($checkout)) : '—'; ?></span></div>
          <div class="summary-line"><span>Special Code</span><span><?php echo $promo !== '' ? htmlspecialchars($promo) : '(No input)'; ?></span></div>
          <div class="summary-line"><span>Selected Room</span><span id="summaryRoom">—</span></div>
          <div class="summary-line"><span>Price</span><span id="summaryPrice">—</span></div>
          <a class="summary-edit" href="booking_calendar.php?<?php echo http_build_query(array_filter(['checkin' => $checkin, 'checkout' => $checkout, 'adults' => $adults, 'children' => $children, 'promo' => $promo], function ($v) { return $v !== '' && $v !== null; })); ?>">Edit</a>
          <button id="continueBtn" class="btn btn-light w-100 mt-3" disabled>Continue</button>
        </div>
      </div>
    </div>
  </div>

  <?php require('inc/footer.php'); ?>
</body>
</html>

<script>
  (function() {
    const roomNameSpan = document.getElementById('summaryRoom');
    const priceSpan = document.getElementById('summaryPrice');
    const continueBtn = document.getElementById('continueBtn');
    let selectedRoomId = null;

    const filters = <?php echo json_encode([
                      'checkin' => $checkin,
                      'checkout' => $checkout,
                      'adults' => $adults,
                      'children' => $children,
                      'promo' => $promo,
                    ]); ?>;

    document.querySelectorAll('.room-card').forEach(card => {
      card.addEventListener('click', function() {
        const roomName = this.querySelector('.room-title')?.textContent?.trim() || '—';
        const priceText = this.querySelector('.price-badge')?.textContent?.trim() || '—';
        selectedRoomId = this.dataset.roomId || null;

        if (roomNameSpan) roomNameSpan.textContent = roomName;
        if (priceSpan) priceSpan.textContent = (priceText || '—').replace(/\s+/g, ' ');
        const summary = document.querySelector('.booking-summary');
        if (summary) {
          summary.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        if (continueBtn) { continueBtn.disabled = !selectedRoomId; }
      });
    });

    if (continueBtn) {
      continueBtn.addEventListener('click', function() {
        if (!selectedRoomId) { alert('Please select a room first.'); return; }
        const params = new URLSearchParams();
        params.set('room_id', String(selectedRoomId));
        if (filters.checkin) params.set('check_in', String(filters.checkin));
        if (filters.checkout) params.set('check_out', String(filters.checkout));
        if (filters.adults) params.set('adults', String(filters.adults));
        if (filters.children) params.set('children', String(filters.children));
        if (filters.promo) params.set('promo', String(filters.promo));
        window.location.href = 'guest_information.php?' + params.toString();
      });
    }
  })();
</script>
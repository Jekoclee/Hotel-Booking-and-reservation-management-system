<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

// Require login to proceed
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    redirect('index.php');
}

// Read GET params
$room_id = isset($_GET['room_id']) ? (int)filteration($_GET['room_id']) : 0;
$check_in = isset($_GET['check_in']) ? trim($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? trim($_GET['check_out']) : '';
$adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 1;
$children = isset($_GET['children']) ? (int)$_GET['children'] : 0;

if ($room_id <= 0) {
    redirect('rooms.php');
}

// Load room data
$room_stmt = $con->prepare("SELECT id, name, price FROM rooms WHERE id = ? AND status = 1 AND removed = 0 LIMIT 1");
$room_stmt->bind_param('i', $room_id);
$room_stmt->execute();
$room_res = $room_stmt->get_result();
if (!$room_res || $room_res->num_rows === 0) {
    $room_stmt->close();
    redirect('rooms.php');
}
$room = $room_res->fetch_assoc();
$room_stmt->close();

// Validate dates
$availability_error = '';
$ci_dt = DateTime::createFromFormat('Y-m-d', $check_in);
$co_dt = DateTime::createFromFormat('Y-m-d', $check_out);
$valid_dates = $ci_dt && $co_dt && $ci_dt->format('Y-m-d') === $check_in && $co_dt->format('Y-m-d') === $check_out && $ci_dt < $co_dt;
if (!$valid_dates) {
    // Default future dates if invalid
    $check_in = date('Y-m-d', strtotime('+1 day'));
    $check_out = date('Y-m-d', strtotime('+3 days'));
}

$nights = (new DateTime($check_in))->diff(new DateTime($check_out))->days;
if ($nights <= 0) { $nights = 1; }
$total_amount = (float)$room['price'] * $nights;

// Prefill guest info from session
$guest_name = $_SESSION['uName'] ?? '';
$guest_email = $_SESSION['uEmail'] ?? '';
$guest_phone = $_SESSION['uPhone'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Guest Information & Payment - Leisure Coast Resort</title>
    <?php require('inc/links.php'); ?>
    <style>
        .hero { background: linear-gradient(135deg, rgba(102,126,234,0.9), rgba(118,75,162,0.9)), url('images/rooms/room1.jpg') center/cover; min-height: 35vh; display:flex; align-items:center; margin-top: 76px; }
        .card-ghost { background: #fff; border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); border: none; }
        .summary { background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 15px; border: 1px solid rgba(0,0,0,0.05); }
        .status-pill { padding: 6px 10px; border-radius: 16px; font-size: 12px; text-transform: capitalize; }
        .btn-confirm { background: linear-gradient(135deg, #28a745, #20c997); border: none; border-radius: 25px; padding: 12px 30px; color: white; font-weight: 600; }
    </style>
</head>
<body class="bg-light">
    <?php require('inc/header.php'); ?>

    <section class="hero text-white">
        <div class="container">
            <h1 class="fw-bold">Guest Information</h1>
            <p class="text-white-50 mb-0">Step 2 of 3: Provide guest details to finalize your booking</p>
        </div>
    </section>

    <div class="container my-5">
        <div class="row g-4">
            <!-- Guest Form -->
            <div class="col-lg-8">
                <div class="card-ghost p-4">
                    <h4 class="fw-bold mb-3">Guest Details</h4>
                    <div class="alert alert-secondary">
                        <i class="bi bi-info-circle me-2"></i>
                        You will select your payment method on the confirmation page.
                    </div>
                    <form id="guest-form" class="row g-3">
                        <input type="hidden" id="room_id" value="<?= $room['id'] ?>" />
                        <input type="hidden" id="check_in" value="<?= htmlspecialchars($check_in) ?>" />
                        <input type="hidden" id="check_out" value="<?= htmlspecialchars($check_out) ?>" />
                        <input type="hidden" id="adults" value="<?= (int)$adults ?>" />
                        <input type="hidden" id="children" value="<?= (int)$children ?>" />
                        <input type="hidden" id="room_price" value="<?= (float)$room['price'] ?>" />
                        <input type="hidden" id="nights" value="<?= (int)$nights ?>" />
                        <input type="hidden" id="total_amount" value="<?= number_format($total_amount, 2, '.', '') ?>" />

                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="guest_name" value="<?= htmlspecialchars($guest_name) ?>" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" id="guest_email" value="<?= htmlspecialchars($guest_email) ?>" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone *</label>
                            <input type="tel" class="form-control" id="guest_phone" value="<?= htmlspecialchars($guest_phone) ?>" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Special Requests</label>
                            <input type="text" class="form-control" id="special_requests" placeholder="Optional" />
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn-confirm" id="confirm-booking-btn">
                                <i class="bi bi-check-circle me-2"></i>Confirm Booking
                            </button>
                        </div>
                    </form>

                    <div id="feedback" class="mt-3"></div>
                </div>
            </div>

            <!-- Summary -->
            <div class="col-lg-4">
                <div class="summary p-4">
                    <h5 class="fw-bold mb-3">Booking Summary</h5>
                    <div class="mb-2 d-flex justify-content-between"><span class="text-muted">Room:</span><span class="fw-bold"><?= htmlspecialchars($room['name']) ?></span></div>
                    <div class="mb-2 d-flex justify-content-between"><span class="text-muted">Check-in:</span><span class="fw-bold"><?= date('M j, Y', strtotime($check_in)) ?></span></div>
                    <div class="mb-2 d-flex justify-content-between"><span class="text-muted">Check-out:</span><span class="fw-bold"><?= date('M j, Y', strtotime($check_out)) ?></span></div>
                    <div class="mb-2 d-flex justify-content-between"><span class="text-muted">Guests:</span><span class="fw-bold"><?= (int)$adults ?> Adults, <?= (int)$children ?> Children</span></div>
                    <div class="mb-2 d-flex justify-content-between"><span class="text-muted">Nights:</span><span class="fw-bold"><?= (int)$nights ?></span></div>
                    <hr>
                    <div class="mb-2 d-flex justify-content-between"><span>Room Rate (<?= (int)$nights ?> nights):</span><span>₱<?= number_format((float)$room['price'] * (int)$nights, 2) ?></span></div>
                    <div class="mb-2 d-flex justify-content-between"><span>Taxes & Fees:</span><span>₱0.00</span></div>
                    <div class="d-flex justify-content-between fw-bold"><span>Total:</span><span class="text-primary">₱<?= number_format($total_amount, 2) ?></span></div>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>

    <script>
        const feedbackEl = document.getElementById('feedback');
        const formEl = document.getElementById('guest-form');

        function showFeedback(type, msg) {
            feedbackEl.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
        }

        function generateToken() {
            return 'bk_' + Math.random().toString(36).slice(2) + Date.now().toString(36);
        }

        async function validateAvailability(room_id, check_in, check_out) {
            const payload = { room_id, checkin_date: check_in, checkout_date: check_out };
            const res = await fetch('ajax/validate_booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const contentType = res.headers.get('content-type') || '';
            const text = await res.text();
            console.debug('[validate_booking] status=', res.status, 'content-type=', contentType, 'body=', text.slice(0, 500));
            if (!contentType.includes('application/json')) {
                throw new Error(`Unexpected response (status ${res.status}): ${text.slice(0, 200)}`);
            }
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Failed to parse server response as JSON: ' + text.slice(0, 300));
            }
        }

        async function createBooking(data) {
            const formBody = new URLSearchParams();
            Object.entries(data).forEach(([k, v]) => formBody.append(k, v != null ? String(v) : ''));

            const res = await fetch('ajax/create_booking.php', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formBody
            });
            const contentType = res.headers.get('content-type') || '';
            const text = await res.text();
            console.debug('[create_booking] status=', res.status, 'content-type=', contentType, 'body=', text.slice(0, 500));
            if (!contentType.includes('application/json')) {
                throw new Error(`Unexpected response (status ${res.status}): ${text.slice(0, 200)}`);
            }
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Failed to parse server response as JSON: ' + text.slice(0, 300));
            }
        }

        formEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            feedbackEl.innerHTML = '';

            const room_id = parseInt(document.getElementById('room_id').value, 10);
            const check_in = document.getElementById('check_in').value;
            const check_out = document.getElementById('check_out').value;
            const adults = parseInt(document.getElementById('adults').value, 10);
            const children = parseInt(document.getElementById('children').value, 10);
            const guest_name = document.getElementById('guest_name').value.trim();
            const guest_email = document.getElementById('guest_email').value.trim();
            const guest_phone = document.getElementById('guest_phone').value.trim();
            const special_requests = document.getElementById('special_requests').value.trim();
            const total_amount = parseFloat(document.getElementById('total_amount').value);

            if (!guest_name || !guest_email || !guest_phone) {
                showFeedback('warning', 'Please fill in all required guest details.');
                return;
            }

            try {
                // Step 1: Validate availability
                showFeedback('info', '<i class="bi bi-hourglass-split me-2"></i> Validating availability...');
                const v = await validateAvailability(room_id, check_in, check_out);
                if (!v.success) {
                    showFeedback('danger', v.message || 'Selected dates are not available.');
                    return;
                }

                // Step 2: Create booking
                showFeedback('info', '<i class="bi bi-hourglass-split me-2"></i> Creating your booking...');
                const booking_token = generateToken();
                const payload = {
                    room_id, check_in, check_out, adults, children,
                    guest_name, guest_email, guest_phone,
                    total_amount, booking_token,
                    special_requests
                };
                const result = await createBooking(payload);
                if (result.success) {
                    const booking_id = result.booking_id;
                    window.location.href = `booking_confirmation.php?booking_id=${encodeURIComponent(booking_id)}`;
                } else {
                    showFeedback('danger', result.message || 'Failed to create booking.');
                }
            } catch (err) {
                showFeedback('danger', 'Unexpected error: ' + (err?.message || err));
            }
        });
    </script>
</body>
</html>
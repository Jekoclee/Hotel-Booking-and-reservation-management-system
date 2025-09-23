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
    <title>Book Your Stay - LCR Hotel</title>
    <?php require('inc/links.php'); ?>

    <style>
        body {
            font-family: var(--font-body);
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            padding-top: 90px; /* Offset for fixed navbar so calendar isn't covered */
        }

        .booking-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .booking-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        /* Steps: horizontal with connectors (blue theme to match Select Rooms & Rates) */
        .booking-steps{background:#fff;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.08);max-width:1200px;margin:1.5rem auto;padding:1.25rem}
        .booking-steps .steps-container{display:flex;gap:.75rem;align-items:center;overflow-x:auto;padding-bottom:.25rem}
        .booking-steps .step{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.4rem;padding:.5rem;border-radius:12px;min-width:140px;text-align:center}
        .booking-steps .step-icon{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;background:#e9ecef;color:#6c757d;transition:all .3s ease}
        .booking-steps .step-title{font-size:13px;font-weight:600;text-align:center;line-height:1.2;color:#6c757d}
        .booking-steps .step.active .step-icon{background:#0d6efd;color:#fff}
        .booking-steps .step.active .step-title{color:#0d6efd}
        .booking-steps .connector{display:flex;align-items:center;gap:.4rem;min-width:80px;flex:1}
        .booking-steps .connector .dot{width:10px;height:10px;border-radius:50%;background:#e0e6ef;flex-shrink:0}
        .booking-steps .connector .line{height:3px;background:#e0e6ef;border-radius:3px;flex:1}
        /* If you want to show progress connector after current step, add .active to .connector */
        .booking-steps .connector.active .dot, .booking-steps .connector.active .line{background:#0d6efd}

        /* NEW: page grid & right summary (align with Select Rooms & Rates) */
        .calendar-page-grid{display:grid;grid-template-columns:1fr;gap:1.5rem}
        .calendar-main{min-width:0}
        .booking-summary{background:linear-gradient(135deg,#0d6efd,#2563eb);color:#fff;border-radius:15px;padding:16px 18px;box-shadow:0 10px 25px rgba(0,0,0,.08)}
        .booking-summary h6{font-weight:700;margin-bottom:12px}
        .summary-line{background:rgba(255,255,255,.15);border-radius:10px;padding:10px 12px;font-size:13px;margin-bottom:10px;display:flex;justify-content:space-between}
        .summary-edit{display:inline-block;margin-top:8px;color:#fff;text-decoration:underline;font-size:12px}
        @media(min-width:768px){ .calendar-page-grid{grid-template-columns:2.5fr 1fr} .booking-summary{ position:sticky; top:90px; } }
        @media (max-width:991.98px){ .booking-summary{ margin-top:16px } }

        .booking-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .form-control {
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .calendar-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }

        .calendar {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .calendar-header {
            background: #6c757d;
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0;
        }

        .day-header, .calendar-day {
            padding: 0.75rem;
            text-align: center;
            border: 1px solid #e9ecef;
        }

        .calendar-day {
            cursor: pointer;
        }

        .calendar-day.disabled {
            background: #f4f5f7;
            color: #c1c7d0;
            pointer-events: none;
        }

        .calendar-day.available {
            background: white;
        }

        .calendar-day.invalid {
            background: #fde7ea; /* softer red */
            color: #b02a37;
            cursor: not-allowed;
            pointer-events: none;
        }

        .calendar-day.checkin, .calendar-day.checkout {
            background: #0d6efd; /* solid blue */
            color: #ffffff;
            font-weight: 700;
        }

        /* Added: days between check-in and check-out */
        .calendar-day.period {
            background: #e7f1ff;
            color: #0d6efd;
        }

        /* Added: booked / not available indicator */
        .calendar-day.booked, .calendar-day.not-available {
            background: #fde7ea;
            color: #b02a37;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Added: past dates (tapos na) */
        .calendar-day.past {
            background: #f1f3f5;
            color: #adb5bd;
            cursor: not-allowed;
            pointer-events: none;
        }

        .legend-badge {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            display: inline-block;
        }

        .badge-available { background: #ffffff; border:1px solid #e9ecef; }
        .badge-checkin { background: #0d6efd; }
        .badge-checkout { background: #0d6efd; }
        .badge-invalid { background: #fde7ea; }

        .continue-btn {
            background: #ff6f00;
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin: 2rem auto 0;
        }

        .continue-btn:hover {
            background: #ffb300;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .calendar-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            /* keep steps horizontal scroll on mobile */
            .booking-steps .steps-container { flex-wrap: nowrap; }
            .booking-steps .step { min-width: 140px; }

            .legend-items {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php require('inc/header.php'); ?>

    <div class="booking-container">
        <!-- Booking Steps -->
        <div class="booking-steps">
            <div class="steps-container">
                <div class="step active">
                    <div class="step-icon">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="step-title">Check-in &<br>Check-out Date</div>
                </div>
                <div class="connector active"><span class="dot"></span><span class="line"></span></div>
                <div class="step">
                    <div class="step-icon">
                        <i class="bi bi-house-door-fill"></i>
                    </div>
                    <div class="step-title">Select<br>Rooms & Rates</div>
                </div>
                <div class="connector"><span class="dot"></span><span class="line"></span></div>
                <div class="step">
                    <div class="step-icon">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="step-title">Guest<br>Information</div>
                </div>
                <div class="connector"><span class="dot"></span><span class="line"></span></div>
                <div class="step">
                    <div class="step-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="step-title">Booking<br>Confirmation</div>
                </div>
            </div>
        </div>

        <div class="calendar-page-grid">
            <div class="calendar-main">
                <!-- Booking Form -->
                <div class="booking-form">
                    <form id="bookingForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="checkin">Check-in</label>
                                <input type="date" id="checkin" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="checkout">Check-out</label>
                                <input type="date" id="checkout" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="nights">Number of night(s)</label>
                                <input type="text" id="nights" class="form-control" value="1 night(s)" readonly>
                            </div>
                            <div class="form-group">
                                <label for="specialCode">Special Code</label>
                                <input type="text" id="specialCode" class="form-control" placeholder="Optional">
                            </div>
                        </div>

                        <!-- Calendar Container -->
                        <div class="calendar-container">
                            <!-- September 2025 Calendar -->
                            <div class="calendar">
                                <div class="calendar-header">
                                    <div class="calendar-nav">
                                        <button type="button" class="nav-btn" onclick="previousMonth()">&lt;</button>
                                        <span id="currentMonth">September 2025</span>
                                        <button type="button" class="nav-btn" onclick="nextMonth()">&gt;</button>
                                    </div>
                                </div>
                                <div class="calendar-grid">
                                    <div class="day-header">SUN</div>
                                    <div class="day-header">MON</div>
                                    <div class="day-header">TUE</div>
                                    <div class="day-header">WED</div>
                                    <div class="day-header">THU</div>
                                    <div class="day-header">FRI</div>
                                    <div class="day-header">SAT</div>
                                    <!-- September 2025 days -->
                                    <div class="calendar-day disabled"></div>
                                    <div class="calendar-day available" data-date="2025-09-01">1</div>
                                    <div class="calendar-day available" data-date="2025-09-02">2</div>
                                    <div class="calendar-day available" data-date="2025-09-03">3</div>
                                    <div class="calendar-day available" data-date="2025-09-04">4</div>
                                    <div class="calendar-day available" data-date="2025-09-05">5</div>
                                    <div class="calendar-day available" data-date="2025-09-06">6</div>
                                    <div class="calendar-day available" data-date="2025-09-07">7</div>
                                    <div class="calendar-day available" data-date="2025-09-08">8</div>
                                    <div class="calendar-day available" data-date="2025-09-09">9</div>
                                    <div class="calendar-day available" data-date="2025-09-10">10</div>
                                    <div class="calendar-day available" data-date="2025-09-11">11</div>
                                    <div class="calendar-day available" data-date="2025-09-12">12</div>
                                    <div class="calendar-day available" data-date="2025-09-13">13</div>
                                    <div class="calendar-day available" data-date="2025-09-14">14</div>
                                    <div class="calendar-day available" data-date="2025-09-15">15</div>
                                    <div class="calendar-day available" data-date="2025-09-16">16</div>
                                    <div class="calendar-day available" data-date="2025-09-17">17</div>
                                    <div class="calendar-day invalid" data-date="2025-09-18">18</div>
                                    <div class="calendar-day checkin" data-date="2025-09-19">19</div>
                                    <div class="calendar-day checkout" data-date="2025-09-20">20</div>
                                    <div class="calendar-day available" data-date="2025-09-21">21</div>
                                    <div class="calendar-day available" data-date="2025-09-22">22</div>
                                    <div class="calendar-day available" data-date="2025-09-23">23</div>
                                    <div class="calendar-day available" data-date="2025-09-24">24</div>
                                    <div class="calendar-day available" data-date="2025-09-25">25</div>
                                    <div class="calendar-day available" data-date="2025-09-26">26</div>
                                    <div class="calendar-day available" data-date="2025-09-27">27</div>
                                    <div class="calendar-day available" data-date="2025-09-28">28</div>
                                    <div class="calendar-day available" data-date="2025-09-29">29</div>
                                    <div class="calendar-day available" data-date="2025-09-30">30</div>
                                    <div class="calendar-day disabled"></div>
                                    <div class="calendar-day disabled"></div>
                                    <div class="calendar-day disabled"></div>
                                    <div class="calendar-day disabled"></div>
                                </div>
                                <div class="calendar-footer">
                                    Check-in: 03:00 PM | Check-out: 12:00 PM
                                </div>
                            </div>

                            <!-- October 2025 Calendar -->
                            <div class="calendar">
                                <div class="calendar-header">
                                    <div class="calendar-nav">
                                        <span>October 2025</span>
                                        <button type="button" class="nav-btn" onclick="nextMonth()">&gt;</button>
                                    </div>
                                </div>
                                <div class="calendar-grid">
                                    <div class="day-header">SUN</div>
                                    <div class="day-header">MON</div>
                                    <div class="day-header">TUE</div>
                                    <div class="day-header">WED</div>
                                    <div class="day-header">THU</div>
                                    <div class="day-header">FRI</div>
                                    <div class="day-header">SAT</div>
                                    <!-- October 2025 days -->
                                    <div class="calendar-day disabled"></div>
                                    <div class="calendar-day disabled"></div>
                                    <div class="calendar-day disabled"></div>
                                    <div class="calendar-day available" data-date="2025-10-01">1</div>
                                    <div class="calendar-day available" data-date="2025-10-02">2</div>
                                    <div class="calendar-day available" data-date="2025-10-03">3</div>
                                    <div class="calendar-day available" data-date="2025-10-04">4</div>
                                    <div class="calendar-day available" data-date="2025-10-05">5</div>
                                    <div class="calendar-day available" data-date="2025-10-06">6</div>
                                    <div class="calendar-day available" data-date="2025-10-07">7</div>
                                    <div class="calendar-day available" data-date="2025-10-08">8</div>
                                    <div class="calendar-day available" data-date="2025-10-09">9</div>
                                    <div class="calendar-day available" data-date="2025-10-10">10</div>
                                    <div class="calendar-day available" data-date="2025-10-11">11</div>
                                    <div class="calendar-day available" data-date="2025-10-12">12</div>
                                    <div class="calendar-day available" data-date="2025-10-13">13</div>
                                    <div class="calendar-day available" data-date="2025-10-14">14</div>
                                    <div class="calendar-day available" data-date="2025-10-15">15</div>
                                    <div class="calendar-day available" data-date="2025-10-16">16</div>
                                    <div class="calendar-day available" data-date="2025-10-17">17</div>
                                    <div class="calendar-day available" data-date="2025-10-18">18</div>
                                    <div class="calendar-day available" data-date="2025-10-19">19</div>
                                    <div class="calendar-day available" data-date="2025-10-20">20</div>
                                    <div class="calendar-day available" data-date="2025-10-21">21</div>
                                    <div class="calendar-day available" data-date="2025-10-22">22</div>
                                    <div class="calendar-day available" data-date="2025-10-23">23</div>
                                    <div class="calendar-day available" data-date="2025-10-24">24</div>
                                    <div class="calendar-day available" data-date="2025-10-25">25</div>
                                    <div class="calendar-day available" data-date="2025-10-26">26</div>
                                    <div class="calendar-day available" data-date="2025-10-27">27</div>
                                    <div class="calendar-day available" data-date="2025-10-28">28</div>
                                    <div class="calendar-day available" data-date="2025-10-29">29</div>
                                    <div class="calendar-day available" data-date="2025-10-30">30</div>
                                    <div class="calendar-day available" data-date="2025-10-31">31</div>
                                </div>
                                <div class="calendar-footer">
                                    Check-in: 03:00 PM | Check-out: 12:00 PM
                                </div>
                            </div>
                        </div>

                        <button type="button" class="continue-btn" onclick="continueBooking()">Continue</button>
                    </form>
                </div>

                <!-- Legend -->
                <div class="legend">
                    <div>
                        <h4 style="margin-top:0">Legend</h4>
                        <div class="legend-items">
                            <div class="legend-item"><span class="legend-badge badge-available"></span> Available</div>
                            <div class="legend-item"><span class="legend-badge badge-checkin"></span> Check-in</div>
                            <div class="legend-item"><span class="legend-badge badge-checkout"></span> Check-out</div>
                            <div class="legend-item"><span class="legend-badge badge-invalid"></span> Not available</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right-side Booking Summary (sticky on md+) -->
            <aside class="booking-summary">
                <h6>Booking Summary</h6>
                <div class="summary-line"><span>Date</span><span id="summaryDates"><?php echo $checkin?date('M d, Y', strtotime($checkin)):'—'; ?> - <?php echo $checkout?date('M d, Y', strtotime($checkout)):'—'; ?></span></div>
                <div class="summary-line"><span>Special Code</span><span id="summaryPromo"><?php echo $promo!==''?$promo:'(No input)'; ?></span></div>
                <a class="summary-edit" href="#bookingForm">Edit</a>
            </aside>
        </div>
    </div>

    <script>
        let checkinDate = null;
        let checkoutDate = null;
        let currentMonthIndex = 8; // September (0-based)
        let currentYear = 2025;
        let bookedDates = []; // Store booked dates from server
        let selectedRoomId = 1; // Default room ID, can be changed based on selection

        const months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Parse URL params and initialize inputs/dates
        function initializeFromParams() {
            const params = new URLSearchParams(window.location.search);

            // room_id
            const rid = parseInt(params.get('room_id'), 10);
            if (!isNaN(rid)) {
                selectedRoomId = rid;
            }

            const today = new Date();
            today.setHours(0,0,0,0);
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);

            const paramCheckin = params.get('checkin') || params.get('check_in') || params.get('checkin_date');
            const paramCheckout = params.get('checkout') || params.get('check_out') || params.get('checkout_date');

            const checkinInput = document.getElementById('checkin');
            const checkoutInput = document.getElementById('checkout');

            // Set min attributes to prevent past dates
            const minStr = formatDateForInput(today);
            checkinInput.min = minStr;
            checkoutInput.min = minStr;

            // Set values from params or defaults
            const checkinStr = paramCheckin || formatDateForInput(today);
            const checkoutStr = paramCheckout || formatDateForInput(tomorrow);

            checkinInput.value = checkinStr;
            checkoutInput.value = checkoutStr;

            checkinDate = new Date(checkinStr);
            checkoutDate = new Date(checkoutStr);
        }

        // Initialize calendar
        document.addEventListener('DOMContentLoaded', function() {
            initializeFromParams();
            setupDateInputs();
            setupCalendarClicks();
            loadBookedDates(); // Load booked dates from server
            updateCalendarDisplay();
            updateNights();
            updateSummaryFromInputs();
        });

        function setupDateInputs() {
            const checkinInput = document.getElementById('checkin');
            const checkoutInput = document.getElementById('checkout');
            const specialCodeInput = document.getElementById('specialCode');

            checkinInput.addEventListener('change', function() {
                checkinDate = new Date(this.value);
                // Ensure checkout is after checkin
                if (checkoutDate && checkoutDate <= checkinDate) {
                    const nextDay = new Date(checkinDate);
                    nextDay.setDate(nextDay.getDate() + 1);
                    checkoutDate = nextDay;
                    checkoutInput.value = formatDateForInput(nextDay);
                }
                updateCalendarDisplay();
                updateNights();
                updateSummaryFromInputs();
            });

            checkoutInput.addEventListener('change', function() {
                checkoutDate = new Date(this.value);
                // Ensure checkout after checkin
                if (checkinDate && checkoutDate <= checkinDate) {
                    const nextDay = new Date(checkinDate);
                    nextDay.setDate(nextDay.getDate() + 1);
                    checkoutDate = nextDay;
                    checkoutInput.value = formatDateForInput(nextDay);
                }
                updateCalendarDisplay();
                updateNights();
                updateSummaryFromInputs();
            });

            if (specialCodeInput) {
                specialCodeInput.addEventListener('input', updateSummaryFromInputs);
            }
        }

        // NEW: Enable clicking on calendar cells to pick dates
        function setupCalendarClicks() {
            const checkinInput = document.getElementById('checkin');
            const checkoutInput = document.getElementById('checkout');

            const minStr = checkinInput?.min || formatDateForInput(new Date());

            function isSelectable(dayEl) {
                if (!dayEl || !dayEl.dataset.date) return false;
                if (dayEl.classList.contains('disabled') || dayEl.classList.contains('invalid') ||
                    dayEl.classList.contains('not-available') || dayEl.classList.contains('booked') ||
                    dayEl.classList.contains('past')) {
                    return false;
                }
                // Prevent selecting past dates
                if (dayEl.dataset.date < minStr) return false;
                return true;
            }

            document.querySelectorAll('.calendar-day').forEach(day => {
                day.addEventListener('click', () => {
                    if (!isSelectable(day)) return;
                    const dateStr = day.dataset.date;
                    const dateObj = new Date(dateStr);

                    if (!checkinDate || (checkinDate && checkoutDate)) {
                        // Start a new selection
                        checkinDate = dateObj;
                        checkoutDate = null;
                        if (checkinInput) checkinInput.value = dateStr;
                        if (checkoutInput) checkoutInput.value = '';
                    } else {
                        // Set checkout if after checkin, otherwise reset checkin
                        if (dateObj <= checkinDate) {
                            checkinDate = dateObj;
                            if (checkinInput) checkinInput.value = dateStr;
                        } else {
                            checkoutDate = dateObj;
                            if (checkoutInput) checkoutInput.value = dateStr;
                        }
                    }

                    updateCalendarDisplay();
                    updateNights();
                    updateSummaryFromInputs();
                });
            });
        }

        function updateCalendarDisplay() {
            // Reset all calendar days
            document.querySelectorAll('.calendar-day').forEach(day => {
                day.classList.remove('checkin', 'checkout', 'period');
                if (day.dataset.date && !day.classList.contains('disabled') && !day.classList.contains('invalid') && !day.classList.contains('not-available') && !day.classList.contains('booked')) {
                    day.classList.add('available');
                }
            });

            // Mark past dates (tapos na)
            const today = new Date();
            today.setHours(0,0,0,0);
            const todayStr = formatDateForInput(today);
            document.querySelectorAll('.calendar-day').forEach(day => {
                if (day.dataset.date && day.dataset.date < todayStr) {
                    day.classList.remove('available');
                    day.classList.add('past');
                    day.title = 'Past date';
                }
            });

            // Mark booked dates
            markBookedDates();

            if (checkinDate) {
                const checkinStr = formatDateForInput(checkinDate);
                const checkinDay = document.querySelector(`[data-date="${checkinStr}"]`);
                if (checkinDay) {
                    checkinDay.classList.remove('available');
                    checkinDay.classList.add('checkin');
                }
            }

            if (checkoutDate) {
                const checkoutStr = formatDateForInput(checkoutDate);
                const checkoutDay = document.querySelector(`[data-date="${checkoutStr}"]`);
                if (checkoutDay) {
                    checkoutDay.classList.remove('available');
                    checkoutDay.classList.add('checkout');
                }

                // Mark period days
                if (checkinDate && checkoutDate) {
                    const currentDate = new Date(checkinDate);
                    currentDate.setDate(currentDate.getDate() + 1);

                    while (currentDate < checkoutDate) {
                        const dateStr = formatDateForInput(currentDate);
                        const periodDay = document.querySelector(`[data-date="${dateStr}"]`);
                        if (periodDay) {
                            periodDay.classList.remove('available');
                            periodDay.classList.add('period');
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                }
            }
        }

        function updateNights() {
            const nightsInput = document.getElementById('nights');

            if (checkinDate && checkoutDate) {
                const timeDiff = checkoutDate.getTime() - checkinDate.getTime();
                const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
                nightsInput.value = `${nights} night(s)`;
            } else {
                nightsInput.value = '1 night(s)';
            }
        }

        // NEW: Summary formatter and updater
        function formatDisplayDate(str) {
            const d = new Date(str);
            return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
        }
        function updateSummaryFromInputs() {
            const ci = document.getElementById('checkin')?.value || '';
            const co = document.getElementById('checkout')?.value || '';
            const promo = document.getElementById('specialCode')?.value || '';
            const datesEl = document.getElementById('summaryDates');
            const promoEl = document.getElementById('summaryPromo');
            if (datesEl) {
                const left = ci ? formatDisplayDate(ci) : '—';
                const right = co ? formatDisplayDate(co) : '—';
                datesEl.textContent = `${left} - ${right}`;
            }
            if (promoEl) {
                promoEl.textContent = promo.trim() !== '' ? promo.trim() : '(No input)';
            }
        }

        function formatDateForInput(date) {
            return date.toISOString().split('T')[0];
        }

        function previousMonth() {
            // Implementation for previous month navigation
            console.log('Previous month clicked');
        }

        function nextMonth() {
            // Implementation for next month navigation
            console.log('Next month clicked');
        }

        function continueBooking() {
            if (!checkinDate || !checkoutDate) {
                alert('Please select both check-in and check-out dates.');
                return;
            }

            // Final validation before proceeding
            if (!isRangeAvailable(checkinDate, checkoutDate)) {
                alert('Selected dates are not available. Please choose different dates.');
                return;
            }

            // Server-side validation
            const bookingData = {
                room_id: selectedRoomId,
                checkin_date: formatDateForInput(checkinDate),
                checkout_date: formatDateForInput(checkoutDate)
            };

            fetch('ajax/validate_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(bookingData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to details with preserved params when available
                        const checkinStr = formatDateForInput(checkinDate);
                        const checkoutStr = formatDateForInput(checkoutDate);
                        const params = new URLSearchParams(window.location.search);
                        const adults = params.get('adults') || '';
                        const children = params.get('children') || '';

                        let target = 'select_rooms_rates.php' +
                                     '?checkin=' + encodeURIComponent(checkinStr) +
                                     '&checkout=' + encodeURIComponent(checkoutStr);
                        if (adults) target += '&adults=' + encodeURIComponent(adults);
                        if (children) target += '&children=' + encodeURIComponent(children);
                        // Read promo from input instead of URL params
                        const specialCodeInput = document.getElementById('specialCode');
                        const promo = specialCodeInput ? specialCodeInput.value.trim() : '';
                        if (promo) target += '&promo=' + encodeURIComponent(promo);

                        window.location.href = target;
                    } else {
                        alert('Booking validation failed: ' + data.message);
                        // Reload booked dates to refresh the calendar
                        loadBookedDates();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while validating your booking. Please try again.');
                });
        }

        // Load booked dates from server
        function loadBookedDates() {
            fetch('ajax/check_availability.php?room_id=' + selectedRoomId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bookedDates = data.booked_dates;
                        updateCalendarDisplay();
                    } else {
                        console.error('Error loading booked dates:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Check if a specific date is booked
        function isDateBooked(date) {
            const dateStr = formatDateForInput(date);
            return bookedDates.includes(dateStr);
        }

        // Check if a date range is available (no booked dates in between)
        function isRangeAvailable(startDate, endDate) {
            const currentDate = new Date(startDate);

            while (currentDate < endDate) {
                if (isDateBooked(currentDate)) {
                    return false;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }
            return true;
        }

        // Mark booked dates in the calendar
        function markBookedDates() {
            bookedDates.forEach(dateStr => {
                const dayElement = document.querySelector(`[data-date="${dateStr}"]`);
                if (dayElement) {
                    dayElement.classList.remove('available');
                    dayElement.classList.add('booked');
                    dayElement.title = 'Not available';
                }
            });
        }
    </script>

    <?php require('inc/footer.php'); ?>
</body>

</html>
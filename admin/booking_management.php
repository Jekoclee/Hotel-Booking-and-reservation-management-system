<?php
require('inc/essentials.php');
require('inc/db_config.php');
adminLogin();

// Get rooms for dropdown
$rooms_query = "SELECT id, name FROM rooms WHERE status = 1 AND removed = 0";
$rooms_result = mysqli_query($con, $rooms_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Booking Management</title>
    <?php require('inc/links.php'); ?>
    
</head>

<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <!-- Main Content -->
    <div class="col-lg-10 ms-auto p-4 overflow-hidden">
        <h3 class="mb-4">Booking Management</h3>

        <!-- Create New Booking Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBookingModal">
                    <i class="bi bi-plus-circle"></i> Create New Booking
                </button>
            </div>

            <!-- Filters -->
            <div class="d-flex gap-2">
                <select class="form-select" id="statusFilter" style="width: auto;">
                    <option value="">All Status</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="pending">Pending</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                </select>
                <select class="form-select" id="roomFilter" style="width: auto;">
                    <option value="">All Rooms</option>
                    <?php while ($room = mysqli_fetch_assoc($rooms_result)): ?>
                        <option value="<?= $room['id'] ?>"><?= $room['name'] ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="date" class="form-control" id="dateFilter" style="width: auto;">
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover border" id="bookingsTable">
                        <thead>
                            <tr class="bg-dark text-light">
                                <th>Booking ID</th>
                                <th>Guest Name</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Nights</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Refund</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="bookingsTableBody">
                            <!-- Bookings will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Booking Modal -->
    <div class="modal fade" id="createBookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createBookingForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guest Name *</label>
                                <input type="text" class="form-control" name="guest_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guest Email *</label>
                                <input type="email" class="form-control" name="guest_email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guest Phone</label>
                                <input type="tel" class="form-control" name="guest_phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room *</label>
                                <select class="form-select" name="room_id" required>
                                    <option value="">Select Room</option>
                                    <?php
                                    mysqli_data_seek($rooms_result, 0);
                                    while ($room = mysqli_fetch_assoc($rooms_result)):
                                    ?>
                                        <option value="<?= $room['id'] ?>"><?= $room['name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Check-in Date *</label>
                                <input type="date" class="form-control" name="checkin_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Check-out Date *</label>
                                <input type="date" class="form-control" name="checkout_date" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Amount</label>
                                <input type="number" class="form-control" name="total_amount" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="confirmed">Confirmed</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Special Requests</label>
                            <textarea class="form-control" name="special_requests" rows="3"></textarea>
                        </div>
                        <div id="bookingValidationMessage" class="alert d-none"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="createBookingBtn">Create Booking</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Booking Modal -->
    <div class="modal fade" id="editBookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editBookingForm">
                        <input type="hidden" name="booking_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guest Name *</label>
                                <input type="text" class="form-control" name="guest_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guest Email *</label>
                                <input type="email" class="form-control" name="guest_email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guest Phone</label>
                                <input type="tel" class="form-control" name="guest_phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="confirmed">Confirmed</option>
                                    <option value="pending">Pending</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Amount</label>
                                <input type="number" class="form-control" name="total_amount" step="0.01">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Special Requests</label>
                            <textarea class="form-control" name="special_requests" rows="3"></textarea>
                        </div>
                        <div id="editBookingValidationMessage" class="alert d-none"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateBookingBtn">Update Booking</button>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/scripts.php'); ?>
    <script src="scripts/booking_management.js"></script>
</body>

</html>
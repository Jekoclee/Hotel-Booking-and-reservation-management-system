<?php
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-light transparent-navbar px-lg-3 py-lg-2 shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand resort-brand d-flex flex-column align-items-center" href="index.php">
            <img src="<?= ABOUT_IMG_PATH ?>LCR 6.png" alt="Resort Logo" class="resort-logo" style="height: 70px; width: auto;">
            <span class="resort-text" style="font-size: 12px; font-weight: 600; color: white; margin-top: 2px;">Leisure Coast Resort</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link me-2" href="index.php" style="color: white; font-weight: 500;">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-2" href="rooms.php" style="color: white; font-weight: 500;">Rooms</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-2" href="facilities.php" style="color: white; font-weight: 500;">Facilities</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-2" href="contact.php" style="color: white; font-weight: 500;">Contact Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-2" href="about.php" style="color: white; font-weight: 500;">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link me-2" href="events.php" style="color: white; font-weight: 500;">Events</a>
                </li>
            </ul>
            <div class="d-flex ms-auto">
                <?php
                // Debug: Log session status
                error_log("Session status in header: " . print_r($_SESSION, true));
                error_log("Login check: " . (isset($_SESSION['login']) ? 'true' : 'false'));

                if (isset($_SESSION['login']) && $_SESSION['login'] == true): ?>
                    <!-- User is logged in -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-light shadow-none dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" style="font-weight: 500;">
                            <img src="<?= USERS_IMG_PATH ?><?= $_SESSION['uPic'] ?>" style="width: 25px; height: 25px;" class="me-1 rounded-circle">
                            <?= $_SESSION['uName'] ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-lg-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="bookings.php"><i class="bi bi-journal-bookmark"></i> My Bookings</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- User is not logged in -->
                    <button type="button" class="btn btn-login shadow-none me-lg-3 me-2 rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="bi bi-person-circle me-1"></i>Login
                    </button>
                    <button type="button" class="btn btn-register shadow-none rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#registerModal">
                        <i class="bi bi-person-plus me-1"></i>Register
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Login Modal (restyled to match Register) -->
<div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="loginLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="login-form">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title d-flex align-items-center" id="loginLabel">
                        <i class="bi bi-person-circle fs-3 me-2"></i> User Login
                    </h5>
                    <button type="reset" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input name="email" type="email" class="form-control rounded-3 shadow-none" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input id="login-pass" name="pass" type="password" class="form-control rounded-3 shadow-none has-password-toggle" required>
                            <button type="button" class="password-toggle toggle-password" data-target="#login-pass" aria-label="Show/Hide password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <button type="submit" class="btn btn-success px-5 py-2 rounded-pill shadow-sm">Login</button>
                        <a href="forgot_password.php" class="text-secondary text-decoration-none">Forgot Password?</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="registerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="register-form">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title d-flex align-items-center" id="registerLabel">
                        <i class="bi bi-person-lines-fill fs-3 me-2"></i> User Registration
                    </h5>
                    <button type="reset" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="alert alert-secondary text-dark text-wrap mb-4 rounded-pill text-center small">
                        <strong>Note:</strong> Please fill in all the required fields (that will be required during check-in.)
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Name</label>
                            <input name="name" type="text" class="form-control rounded-3" id="name" placeholder="Enter your full name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input name="email" type="email" class="form-control rounded-3" id="email" placeholder="Enter email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input name="phonenum" type="number" class="form-control rounded-3" id="phone" placeholder="Enter phone number" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Picture</label>
                            <input name="profile" type="file" accept=".jpg, .jpeg, .png, .webp" class="form-control rounded-3 shadow-none" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control rounded-3 shadow-none" rows="2" placeholder="Enter your address" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pincode</label>
                            <input name="pincode" type="number" class="form-control rounded-3" placeholder="Enter pincode" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input name="dob" type="date" class="form-control rounded-3 shadow-none" placeholder="Select your date of birth" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="password-wrapper">
                                <input id="reg-pass" name="pass" type="password" class="form-control rounded-3 has-password-toggle" placeholder="Create a password" required>
                                <button type="button" class="password-toggle toggle-password" data-target="#reg-pass" aria-label="Show/Hide password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm Password</label>
                            <div class="password-wrapper">
                                <input id="reg-cpass" name="cpass" type="password" class="form-control rounded-3 shadow-none has-password-toggle" placeholder="Re-enter your password" required>
                                <button type="button" class="password-toggle toggle-password" data-target="#reg-cpass" aria-label="Show/Hide password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button name="register" type="submit" class="btn btn-success px-5 py-2 rounded-pill shadow-sm">Register</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
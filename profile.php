<?php
// Profile page for logged-in users
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    redirect('index.php');
}

$user_id = (int)($_SESSION['uId'] ?? 0);
$user = null;
if ($user_id > 0) {
    $stmt = $con->prepare("SELECT id, name, email, phonenum, address, dob, profile, pincode, is_verified, status, banned, created_at FROM user_cred WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();
}

if (!$user) {
    // Fallback to session values if DB not found (should not normally happen)
    $user = [
        'name' => $_SESSION['uName'] ?? 'Guest',
        'email' => $_SESSION['uEmail'] ?? '',
        'phonenum' => $_SESSION['uPhone'] ?? '',
        'address' => '',
        'dob' => '',
        'profile' => $_SESSION['uPic'] ?? 'default.png',
        'is_verified' => 0,
        'status' => 1,
        'banned' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Profile - Leisure Coast Resort</title>
    <?php require('inc/links.php'); ?>
    <style>
        .profile-hero {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9));
            min-height: 30vh;
            display: flex;
            align-items: center;
            margin-top: 76px;
        }

        .profile-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }

        .badge-soft {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
            border-radius: 20px;
            padding: 6px 12px;
        }

        .info-row {
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding: 12px 0;
        }

        .info-row:last-child {
            border-bottom: 0;
        }
    </style>
</head>

<body class="bg-light">
    <?php require('inc/header.php'); ?>

    <section class="profile-hero text-white">
        <div class="container">
            <h1 class="fw-bold">My Profile</h1>
            <p class="mb-0 text-white-50">Manage your personal information and view your bookings</p>
        </div>
    </section>

    <div class="container my-5">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="profile-card p-4 text-center">
                    <img src="<?= USERS_IMG_PATH ?><?= htmlspecialchars($user['profile'] ?? 'default.png') ?>" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;" alt="Profile" />
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['name'] ?? '') ?></h4>
                    <div class="small text-muted mb-2">Member since <?= isset($user['created_at']) ? date('M Y', strtotime($user['created_at'])) : '—' ?></div>
                    <div class="d-flex justify-content-center gap-2">
                        <?php if (($user['is_verified'] ?? 0) == 1): ?>
                            <span class="badge bg-success">Verified</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Not Verified</span>
                        <?php endif; ?>
                        <?php if (($user['status'] ?? 1) == 1 && ($user['banned'] ?? 0) == 0): ?>
                            <span class="badge bg-primary">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Restricted</span>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <a href="bookings.php" class="btn btn-outline-primary w-100"><i class="bi bi-journal-bookmark me-1"></i> View My Bookings</a>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="profile-card p-4">
                    <h5 class="fw-bold mb-3">Personal Information</h5>
                    <div class="row">
                        <div class="col-md-6 info-row"><strong>Name:</strong> <span class="text-muted ms-2"><?= htmlspecialchars($user['name'] ?? '') ?></span></div>
                        <div class="col-md-6 info-row"><strong>Email:</strong> <span class="text-muted ms-2"><?= htmlspecialchars($user['email'] ?? '') ?></span></div>
                        <div class="col-md-6 info-row"><strong>Phone:</strong> <span class="text-muted ms-2"><?= htmlspecialchars($user['phonenum'] ?? '') ?></span></div>
                        <div class="col-md-6 info-row"><strong>Date of Birth:</strong> <span class="text-muted ms-2"><?= htmlspecialchars($user['dob'] ?? '') ?></span></div>
                        <div class="col-12 info-row"><strong>Address:</strong> <span class="text-muted ms-2"><?= htmlspecialchars($user['address'] ?? '') ?></span></div>
                        <div class="col-md-6 info-row"><strong>Pincode:</strong> <span class="text-muted ms-2"><?= htmlspecialchars($user['pincode'] ?? '') ?></span></div>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><i class="bi bi-info-circle me-2"></i>To update your personal information, please contact support or use the upcoming settings page.</div>
                </div>

                <!-- Profile Update Form -->
                <div class="profile-card p-4 mt-4">
                    <h5 class="fw-bold mb-3">Update Profile</h5>
                    <form id="profile-update-form" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phonenum" class="form-control" value="<?= htmlspecialchars($user['phonenum'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" class="form-control" value="<?= htmlspecialchars($user['pincode'] ?? '') ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($user['dob'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" name="profile" accept="image/*" class="form-control">
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/footer.php'); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('profile-update-form');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const data = new FormData(form);
                data.append('update_profile', '1');

                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'ajax/login_register.php', true);
                xhr.onload = function() {
                    const res = this.responseText.trim();
                    if (res == '1') {
                        alert('success', 'Profile updated successfully!');
                        setTimeout(() => window.location.reload(), 800);
                    } else if (res === 'not_logged_in') {
                        alert('error', 'Please login to update your profile.');
                    } else if (res === 'missing_fields') {
                        alert('error', 'Name and phone number are required.');
                    } else if (res === 'phone_already') {
                        alert('error', 'This phone number is already used by another account.');
                    } else if (res === 'inv_img') {
                        alert('error', 'Invalid image format. Please upload JPG, PNG, or WEBP.');
                    } else if (res === 'upd_failed') {
                        alert('error', 'Update failed. Please try again later.');
                    } else {
                        alert('error', 'Unexpected response: ' + res);
                    }
                };
                xhr.send(data);
            });
        });
    </script>
</body>

</html>
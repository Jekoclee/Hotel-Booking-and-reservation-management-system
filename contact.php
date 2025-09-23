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
    <title>Contact Us</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require('inc/links.php'); ?>
</head>
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

    .contact-card {
        background: var(--white);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-xl);
        box-shadow: var(--shadow-md);
        transition: all var(--transition-normal);
        border: 1px solid var(--gray-light);
        height: 100%;
    }

    .contact-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }

    .contact-card h5 {
        color: var(--primary);
        font-family: var(--font-heading);
        margin-bottom: var(--spacing-md);
    }

    .contact-card p {
        color: var(--text-muted);
        margin-bottom: var(--spacing-sm);
    }

    .contact-form {
        background: var(--white);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-xl);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--gray-light);
    }

    .form-control {
        border: 2px solid var(--gray-light);
        border-radius: var(--border-radius);
        padding: var(--spacing-md);
        transition: all var(--transition-normal);
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(52, 144, 220, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border: none;
        padding: var(--spacing-md) var(--spacing-xl);
        font-weight: 600;
        border-radius: var(--border-radius);
        transition: all var(--transition-normal);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-darker));
    }

    .contact-icon {
        font-size: 2rem;
        color: var(--primary);
        margin-bottom: var(--spacing-md);
    }

    .contact-info-card {
        background: var(--white);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-xl);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--gray-light);
        height: 100%;
    }

    .contact-info-card h3 {
        color: var(--primary);
        font-family: var(--font-heading);
        margin-bottom: var(--spacing-lg);
    }

    .contact-info-card h5 {
        color: var(--primary);
        font-family: var(--font-heading);
        margin-bottom: var(--spacing-sm);
        margin-top: var(--spacing-lg);
        font-size: 1.1rem;
    }

    .contact-info-card h5:first-of-type {
        margin-top: 0;
    }

    .contact-info-card a {
        color: var(--text-dark);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        margin-bottom: var(--spacing-sm);
        transition: all var(--transition-normal);
        padding: var(--spacing-xs) 0;
    }

    .contact-info-card a:hover {
        color: var(--primary);
        transform: translateX(5px);
    }

    .contact-info-card i {
        margin-right: var(--spacing-sm);
        color: var(--primary);
        width: 20px;
    }

    .map-container {
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        margin-top: var(--spacing-xl);
    }

    .map-container iframe {
        border: none;
        width: 100%;
        height: 400px;
    }

    .contact-form h3 {
        color: var(--primary);
        font-family: var(--font-heading);
        margin-bottom: var(--spacing-lg);
    }

    .form-label {
        color: var(--text-dark);
        font-weight: 600;
        margin-bottom: var(--spacing-xs);
    }

    .contact-form .form-control::placeholder {
        color: var(--text-muted);
        opacity: 0.7;
    }

    .contact-form textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    .success-message {
        background: linear-gradient(135deg, var(--success), var(--success-dark));
        color: var(--white);
        padding: var(--spacing-md);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-lg);
        border: none;
    }

    .error-message {
        background: linear-gradient(135deg, var(--danger), var(--danger-dark));
        color: var(--white);
        padding: var(--spacing-md);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-lg);
        border: none;
    }

    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 2.5rem;
        }

        .contact-form,
        .contact-info-card {
            margin-bottom: var(--spacing-lg);
        }

        .map-container iframe {
            height: 300px;
        }
    }
</style>


<body class="bg-dark">


    <!-- Header -->
    <?php require('inc/header.php'); ?>

    <!-- Consistent Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>Get in touch with us for any inquiries or assistance</p>
        </div>
    </div>

    <?php
    $contact_q = "SELECT * FROM `contact_details` WHERE `sr_no` =?";
    $values = [1];
    $contact_r = mysqli_fetch_assoc(select($contact_q, $values, 'i'));
    ?>


    <!-- Main Content -->
    <div class="container my-5">
        <div class="row g-4">
            <!-- Contact Info -->
            <div class="col-lg-5">
                <div class="contact-info-card">
                    <h3>Get in touch</h3>

                    <!-- Address -->
                    <h5><i class="bi bi-geo-alt-fill"></i> Address</h5>
                    <a href="<?php echo $contact_r['google_map'] ?>" target="_blank">
                        <?php echo $contact_r['address'] ?>
                    </a>

                    <!-- Phone -->
                    <h5><i class="bi bi-telephone-fill"></i> Call us</h5>
                    <a href="tel:+<?php echo $contact_r['pn1'] ?>">
                        +<?php echo $contact_r['pn1'] ?>
                    </a>
                    <?php
                    if ($contact_r['pn2'] != '') {
                        echo '<br><a href="tel:+' . $contact_r['pn2'] . '">+' . $contact_r['pn2'] . '</a>';
                    }
                    ?>

                    <!-- Email -->
                    <h5><i class="bi bi-envelope-fill"></i> Email</h5>
                    <a href="mailto:<?php echo $contact_r['email'] ?>">
                        <?php echo $contact_r['email'] ?>
                    </a>

                    <!-- Social Media -->
                    <h5><i class="bi bi-share-fill"></i> Follow us</h5>
                    <a href="<?php echo $contact_r['fb'] ?>" target="_blank">
                        <i class="bi bi-facebook"></i> Facebook
                    </a>
                    <br>
                    <a href="<?php echo $contact_r['insta'] ?>" target="_blank">
                        <i class="bi bi-instagram"></i> Instagram
                    </a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="contact-form">
                    <h3 class="mb-4">Send us a message</h3>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input name="name" type="text" class="form-control" placeholder="Enter your name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input name="email" type="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input name="subject" type="text" class="form-control" placeholder="Enter subject" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Write your message here..." required></textarea>
                        </div>
                        <button type="submit" name="send" class="btn btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($_POST['send'])) {
        $frm_data = filteration($_POST);

        $q = "INSERT INTO `user_queries`(`sr_no`, `name`, `email`, `subject`, `message`, `date`, `seen`) VALUES (NULL, ?, ?, ?, ?, NOW(), 0)";
        $values = [$frm_data['name'], $frm_data['email'], $frm_data['subject'], $frm_data['message']];
        $res = insert($q, $values, 'ssss');
        if ($res == 1) {
            alert('success', 'E-Mail sent!');
        } else {
            alert('error', 'Try again later.');
        }
    }
    ?>

    <!-- Google Map -->
    <div class="map-container">
        <iframe src="<?php echo $contact_r['iframe'] ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    </div>
    </div>
    <?php require('inc/footer.php'); ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
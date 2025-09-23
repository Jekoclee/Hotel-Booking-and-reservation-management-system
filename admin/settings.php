<?php
require('inc/essentials.php');
adminLogin();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Settings</title>
    <?php require('inc/links.php'); ?>
</head>

<body class="bg-light">
    <!-- Top Navbar -->
    <?php require('inc/header.php'); ?>

    <!-- Main Content -->
    <div class="col-lg-10 ms-auto p-4 overflow-hidden" id="main-content">
        <h2 class="mb-4">Dashboard SETTINGS</h2>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title m-0">General Settings</h5>
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#general-s">
                        <i class="bi bi-pencil-square"></i>Edit
                    </button>
                </div>
                <h6 class="card-subtitle mb-1 fw-bold">Card title</h6>
                <p class="card-text" id="site_title"></p>
                <h6 class="card-subtitle mb-1 fw-bold">About us</h6>
                <p class="card-text" id="site_about"></p>
            </div>
        </div>

        <!-- general settings modal -->
        <div class="modal fade" id="general-s" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="general_s_form">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">General Settings</h5>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Site Title</label>
                                <input type="text" name="site_title" id="site_title_inp" class="form-control shadow-none" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">About us</label>
                                <textarea class="form-control shadow-none" rows="6" name="site_about"
                                    id="site_about_inp" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn custom-bg shadow-none"
                                onclick="document.getElementById('site_title_inp').value = general_data.site_title; document.getElementById('site_about_inp').value = general_data.site_about;"
                                data-bs-dismiss="modal">Cancel</button>

                            <button type="submit" class="btn btn-primary text-white shadow-none">
                                Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ✅ Now you can freely add more divs here -->
        <div class="card mt-4 border-0 shadow-s mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title m-0">Shutdown Settings</h5>
                    <div class="form-check form-switch">
                        <form>
                            <input onchange="upd_shutdown(this.value)" class="form-check-input" type="checkbox" id="shutdown-toggle">
                        </form>
                    </div>

                </div>

                <p class="card-text">No customer will be allowed booked hotel room when shutdown</p>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title m-0">General Settings</h5>
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#contacts-s">
                        <i class="bi bi-pencil-square"></i>Edit
                    </button>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">Address</h6>
                            <p class="card-text" id="address"></p>
                        </div>
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">Google Map</h6>
                            <p class="card-text" id="google_map"></p>
                        </div>
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">Phone Number</h6>
                            <p class="card-text mb-1"><i class="bi bi-phone-fill"></i>
                                <span id="pn1"></span>
                            </p>
                            <p class="card-text"><i class="bi bi-phone-fill"></i>
                                <span id="pn2"></span>
                            </p>
                        </div>
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">E-mail</h6>
                            <p class="card-text" id="email"></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">Social Links</h6>
                            <p class="card-text mb-1"><i class="bi bi-facebook"></i>
                                <span id="fb"></span>
                            </p>
                            <p class="card-text"><i class="bi bi-instagram"></i>
                                <span id="insta"></span>
                            </p>
                        </div>
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">iFrame Links</h6>
                            <iframe loading="lazy" class="border p-2 w-100" id="iframe"></iframe>
                        </div>

                    </div>
                </div>

            </div>
        </div>
        <!-- contacts details modal heree -->
        <div class="modal fade" id="contacts-s" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form id="contacts_s_form">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Contacts Settings</h5>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid p-0">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Address</label>
                                            <input type="text" name="address" id="address_inp" class="form-control shadow-none" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Google map Links</label>
                                            <input type="text" name="google_map" id="google_map_inp" class="form-control shadow-none" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Phone Numbers (with country code)</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="bi bi-phone-fill"></i></span>
                                                <input type="number" name="pn1" id="pn1_inp" class="form-control shadow-none" required>
                                            </div>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="bi bi-phone-fill"></i></span>
                                                <input type="number" name="pn2" id="pn2_inp" class="form-control shadow-none">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Emails</label>
                                            <input type="text" name="email" id="email_inp" class="form-control shadow-none" required>
                                        </div>

                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Social Links</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="bi bi-facebook"></i></span>
                                                <input type="text" name="fb" id="facebook_inp" class="form-control shadow-none" required>
                                            </div>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="bi bi-instagram"></i></span>
                                                <input type="text" name="insta" id="instagram_inp" class="form-control shadow-none">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">iFrame</label>
                                            <input type="text" name="iframe" id="iframe_inp" class="form-control shadow-none" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn custom-bg shadow-none"
                                onclick="contacts_inp(contacts_data)"
                                data-bs-dismiss="modal">Cancel</button>

                            <button type="submit" class="btn btn-primary text-white shadow-none">
                                Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- management here -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title m-0">Management</h5>
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#managementModal">
                        <i class="bi bi-plus-square"></i> Add Member
                    </button>
                </div>

                <!-- Management List -->
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Position</th>
                                <th scope="col">Photo</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Juan Dela Cruz</td>
                                <td>Manager</td>
                                <td><img src="<?= SITE_URL ?>images/sample.jpg" alt="Photo" class="rounded-circle" width="40" height="40"></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#managementModal"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <!-- More rows go here -->
                            <div class="row" id="team-dtb">
                                <div class="col-md-2 mb-3">
                                    <div class="card bg-dark text-white">
                                        <img src="../images/about/IMG_56937.jpg" class="card-img">
                                        <div class="card-img-overlay text-end">
                                            <button type="button" class="btn btn-danger btn-sm shadow-none">
                                                <i class="bi bi-trash-fill"></i> DELETE
                                            </button>
                                        </div>
                                        <p class="card-text text-center px-3 py-2">Last updated 3 mins ago</p>
                                    </div>

                                </div>
                            </div>

                </div>
                </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Management -->
    <div class="modal fade" id="managementModal" tabindex="-1" aria-labelledby="managementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content"> <!-- ✅ This was missing -->
                <form id="team_s_form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="managementModalLabel">Add Management Member</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control shadow-none" name="member_name" id="member_name_inp" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload Photo</label>
                            <input type="file" class="form-control shadow-none" name="member_picture" id="member_picture_inp" accept=".jpg, .png, .webp, .jpeg">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark shadow-none">Save</button>
                        <button type="button" onclick="member_name_inp.value='', member_picture_inp.value=''" class="btn btn-secondary shadow-none" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    </div> <!-- end main-content -->








    <!-- /Sidebar + Main Content Row -->


    <!-- Custom Hover Style -->


    <!-- Bootstrap CSS & JS -->
    <?php require('inc/scripts.php'); ?>
    <script src="scripts/settings.js"></script>
</body>

</html>
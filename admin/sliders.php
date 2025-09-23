<?php
require('inc/essentials.php');
adminLogin();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - SLIDERS</title>
    <?php require('inc/links.php'); ?>
</head>

<body class="bg-light">
    <!-- Top Navbar -->
    <?php require('inc/header.php'); ?>

    <!-- Main Content -->
    <div class="col-lg-10 ms-auto p-4 overflow-hidden" id="main-content">
        <h2 class="mb-4">Sliders</h2>

        <!-- Sliders here -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title m-0">Sliders-show</h5>
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#sliders-s">
                        <i class="bi bi-plus-square"></i> Add Member
                    </button>
                </div>

                <div class="row" id="sliders-data">
                </div>

            </div>
        </div>
    </div> <!-- end main-content -->

    <!-- Modal for Add/Edit Management -->
    <div class="modal fade" id="sliders-s" tabindex="-1" aria-labelledby="managementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="sliders_s_form">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Sliders</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Upload Photo</label>
                            <input type="file" class="form-control shadow-none" name="sliders_picture" id="sliders_picture_inp" accept=".jpg, .png, .webp, .jpeg">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark shadow-none">Save</button>
                        <button type="button" onclick="sliders_picture_inp.value=''" class="btn btn-secondary shadow-none" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>









    <!-- /Sidebar + Main Content Row -->


    <!-- Custom Hover Style -->
    

    <!-- Bootstrap CSS & JS -->
<?php require('inc/scripts.php'); ?>
    <script src="scripts/sliders.js"></script>
</body>

</html>
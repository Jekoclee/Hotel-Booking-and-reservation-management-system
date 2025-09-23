<?php
require('inc/essentials.php');
require('inc/db_config.php');
adminLogin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Features | Events</title>
    <?php require('inc/links.php'); ?>
</head>

<body class="bg-light">
    <!-- Top Navbar -->
    <?php require('inc/header.php'); ?>

    <!-- Main Content -->
    <div class="col-lg-10 ms-auto p-4 overflow-hidden" id="main-content">
        <h2 class="mb-4">Features & Events</h2>

        <!-- User Inquiries here -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title m-0">ROOM AMENITIES</h5>
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#feature-s">
                        <i class="bi bi-pencil-square"></i>Add
                    </button>
                </div>

                <div class="table-responsive-md" style="height: 250px; overflow-y: scroll;">
                    <table class="table table-hover border">
                        <thead">
                            <tr class="bg-danger text-light">
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">action</th>
                            </tr>
                            </thead>
                            <tbody id="features-data">

                            </tbody>
                    </table>

                </div>
            </div>

        </div>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title m-0">Place</h5>
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#facility-s">
                        <i class="bi bi-pencil-square"></i>Add
                    </button>
                </div>

                <div class="table-responsive-md" style="height: 250px; overflow-y: scroll;">
                    <table class="table table-hover border">
                        <thead>
                            <tr class="bg-danger text-light">
                                <th scope="col">#</th>
                                <th scope="col">icon</th>
                                <th scope="col">Name</th>
                                <th scope="col" width="40%">description</th>
                                <th scope="col">action</th>
                            </tr>
                        </thead>
                        <tbody id="facilities-data">

                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>





    </div> <!-- end main-content -->

    <!-- Modal for Add/Edit Management -->
    <div class="modal fade" id="feature-s" tabindex="-1" aria-labelledby="managementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content"> <!-- ✅ This was missing -->
                <form id="feature_s_form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="managementModalLabel">Add Management Member</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control shadow-none" name="features_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark shadow-none">Save</button>
                        <button type="reset" class="btn btn-secondary shadow-none" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <!-- place modal for add/edit facilities -->
    <div class="modal fade" id="facility-s" tabindex="-1" aria-labelledby="facilityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="facility_s_form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="facilityModalLabel">Add Facility</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Icon</label>
                            <input type="file" class="form-control shadow-none" accept=".svg" name="facility_icon" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control shadow-none" name="facility_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control shadow-none" name="facility_desc" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark shadow-none">Save</button>
                        <button type="reset" class="btn btn-secondary shadow-none" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- /Sidebar + Main Content Row -->


    <!-- Custom Hover Style -->


    <!-- Bootstrap CSS & JS -->
    <?php require('inc/scripts.php'); ?>
    <script src="scripts/feature_events.js">
        let feature_s_form = document.getElementById('feature_s_form');
        let facility_s_form = document.getElementById('facility_s_form');

        feature_s_form.addEventListener('submit', function(e) {
            e.preventDefault();
            add_feature();
        });

        function add_feature() {

            let data = new FormData();
            data.append('name', feature_s_form.elements['features_name'].value);
            data.append('add_feature', '');

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/features_events.php", true);

            xhr.onload = function() {

                var myModal = document.getElementById('feature-s');
                var modal = bootstrap.Modal.getInstance(myModal)
                modal.hide();

                if (this.responseText == 1) {
                    alert('success', 'new feature added');
                    feature_s_form.elements['features_name'].value = '';
                    get_features();

                } else if (this.responseText == 'inv_size') {
                    alert('error', 'server error');


                }

            }


            xhr.send(data);
        }

        function get_features() {

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/features_events.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                document.getElementById('features-data').innerHTML = this.responseText;

            }





            xhr.send('get_features');

        }

        function rem_feature(val) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/features_events.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (this.responseText == 1) {
                    alert('success', 'Feature removed');
                    get_features();
                } else if (this.responseText == 'added_room') {
                    alert('error', 'Feature is added in room!');

                } else {
                    alert('error', 'server down!');
                }


            }


            xhr.send('rem_feature=' + val);
        }

        facility_s_form.addEventListener('submit', function(e) {
            e.preventDefault();
            add_facility();
        });

        function add_facility() {

            let data = new FormData();
            data.append('name', facility_s_form.elements['facility_name'].value);
            data.append('icon', facility_s_form.elements['facility_icon'].files[0]);
            data.append('desc', facility_s_form.elements['facility_desc'].value);
            data.append('add_facility', '');

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/features_events.php", true);

            xhr.onload = function() {

                var myModal = document.getElementById('facility-s');
                var modal = bootstrap.Modal.getInstance(myModal)
                modal.hide();

                if (this.responseText == 1) {
                    alert('success', 'new facility added');
                    facility_s_form.reset();
                    get_facilities();

                } else if (this.responseText == 'inv_img') {
                    alert('error', 'Only SVG images are allowed!');

                } else if (this.responseText == 'inv_size') {
                    alert('error', 'Image size is too large!');

                } else {
                    alert('error', 'server error');

                }
            }


            xhr.send(data);
        }

        function get_facilities() {

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/features_events.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                document.getElementById('facilities-data').innerHTML = this.responseText;

            }





            xhr.send('get_facilities');

        }

        function rem_facility(val) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/features_events.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (this.responseText == 1) {
                    alert('success', 'Facility removed');
                    get_facilities();
                } else if (this.responseText == 'added_room') {
                    alert('error', 'Facility is added in room!');

                } else {
                    alert('error', 'server down!');
                }


            }


            xhr.send('rem_facility=' + val);
        }


        window.onload = function() {
            get_features();
            get_facilities();
        }
    </script>
</body>

</html>
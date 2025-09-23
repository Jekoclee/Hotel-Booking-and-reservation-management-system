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
    <title>Admin Panel - Rooms</title>
    <?php require('inc/links.php'); ?>

</head>

<body class="bg-light">
    <!-- Top Navbar -->
    <?php require('inc/header.php'); ?>

    <!-- Main Content -->
    <div class="col-lg-10 ms-auto p-4 overflow-hidden" id="main-content">
        <h2 class="mb-4">Rooms</h2>
        <!-- User Inquiries here -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                <div class="text-end mb-4">
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#arooms-s">
                        <i class="bi bi-pencil-square"></i>Add
                    </button>
                </div>
                <div class="table-responsive-lg" style="height: 350px; overflow-y: scroll;">
                    <table class="table table-hover border text-center">
                        <thead>
                            <tr class="bg-danger text-light">
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Area</th>
                                <th scope="col">Guest</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="room-data">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div> <!-- end main-content -->

    <!-- Add_ROOM Management -->
    <div class="modal fade" id="arooms-s" tabindex="-1" aria-labelledby="managementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content"> <!-- ✅ This was missing -->
                <form id="arooms_s_form" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="managementModalLabel">Add Room</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control shadow-none" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Area</label>
                                <input type="number" min="1" class="form-control shadow-none" name="area" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" min="1" class="form-control shadow-none" name="price" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" min="1" class="form-control shadow-none" name="quantity" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Adult (Max.)</label>
                                <input type="number" min="1" class="form-control shadow-none" name="adult" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Child (Max.)</label>
                                <input type="number" min="1" class="form-control shadow-none" name="child" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">features</label>
                                <div class="row">
                                    <?php
                                    $res = selectAll('features');
                                    while ($opt = mysqli_fetch_assoc($res)) {
                                        echo "
                                        <div class='col-md-3 mb-1'>
                                        <label>
                                            <input type='checkbox' name='features' value='$opt[id]' class='form-check-input shadow-none me-1'>
                                            $opt[name]
                                        </label>
                                    </div>
                                    ";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">facilities</label>
                                <div class="row">
                                    <?php
                                    $res = selectAll('facilities');
                                    while ($opt = mysqli_fetch_assoc($res)) {
                                        echo "
                                        <div class='col-md-3 mb-1'>
                                        <label>
                                            <input type='checkbox' name='facilities' value='$opt[id]' class='form-check-input shadow-none me-1'>
                                            $opt[name]
                                        </label>
                                    </div>
                                    ";
                                    }
                                    ?>
                                </div>

                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control shadow-none" rows="4" name="desc" required></textarea>
                            </div>
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

    <!-- edit room -->
    <div class="modal fade" id="edit-room" tabindex="-1" aria-labelledby="managementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content"> <!-- ✅ This was missing -->
                <form id="edit_room_form" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="managementModalLabel">Edit Room</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control shadow-none" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Area</label>
                                <input type="number" min="1" class="form-control shadow-none" name="area" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" min="1" class="form-control shadow-none" name="price" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" min="1" class="form-control shadow-none" name="quantity" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Adult (Max.)</label>
                                <input type="number" min="1" class="form-control shadow-none" name="adult" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Child (Max.)</label>
                                <input type="number" min="1" class="form-control shadow-none" name="child" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">features</label>
                                <div class="row">
                                    <?php
                                    $res = selectAll('features');
                                    while ($opt = mysqli_fetch_assoc($res)) {
                                        echo "
                                        <div class='col-md-3 mb-1'>
                                        <label>
                                            <input type='checkbox' name='features' value='$opt[id]' class='form-check-input shadow-none me-1'>
                                            $opt[name]
                                        </label>
                                    </div>
                                    ";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">facilities</label>
                                <div class="row">
                                    <?php
                                    $res = selectAll('facilities');
                                    while ($opt = mysqli_fetch_assoc($res)) {
                                        echo "
                                        <div class='col-md-3 mb-1'>
                                        <label>
                                            <input type='checkbox' name='facilities' value='$opt[id]' class='form-check-input shadow-none me-1'>
                                            $opt[name]
                                        </label>
                                    </div>
                                    ";
                                    }
                                    ?>
                                </div>

                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control shadow-none" rows="4" name="desc" required></textarea>
                            </div>
                            <input type="hidden" name="room_id">
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

    <!--manage room image here -->
    <div class="modal fade" id="room-images" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Room Name</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="image-alert"></div>
                    <div class="border-bottom border-3 pb-3 mb-3">
                        <form id="add-image-form">
                            <label class="form-label fw-bold">ADD Photo</label>
                            <input type="file" class="form-control shadow-none mb-3" name="image" accept=".jpg, .png, .webp, .jpeg" required>
                            <button class="btn btn-dark shadow-none">Add</button>
                            <input type="hidden" name="room_id">


                        </form>
                    </div>
                    <div class="table-responsive-lg" style="height: 350px; overflow-y: scroll;">
                        <table class="table table-hover border text-center">
                            <thead>
                                <tr class="bg-danger text-light sticky-top">
                                    <th scope="col" width="40%">Image</th>
                                    <th scope="col">thumb</th>
                                    <th scope="col">delete</th>
                                </tr>
                            </thead>
                            <tbody id="room-image-data">

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>






    <!-- Bootstrap CSS & JS -->
    <?php require('inc/scripts.php'); ?>
    <script src="scripts/rooms.js">

    </script>


</body>

</html>
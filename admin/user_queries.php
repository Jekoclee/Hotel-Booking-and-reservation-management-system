<?php
require('inc/essentials.php');
require('inc/db_config.php');
adminLogin();

if (isset($_GET['seen'])) {
    $frm_data = filteration($_GET);

    if ($frm_data['seen'] == 'all') {
        $q = "UPDATE `user_queries` SET `seen`=?";
        $values = [1];
        if (update($q, $values, 'i')) {
            alert('success', 'Marked all as read');
        } else {
            alert('error', 'Operation failed');
        }
    } else {
        $q = "UPDATE `user_queries` SET `seen`=? WHERE `sr_no`=?";
        $values = [1, $frm_data['seen']];
        if (update($q, $values, 'ii')) {
            alert('success', 'Marked as read');
        } else {
            alert('error', 'Operation failed');
        }
    }
}

if (isset($_GET['del'])) {
    $frm_data = filteration($_GET);

    if ($frm_data['del'] == 'all') {
        $q = "DELETE FROM `user_queries`";

        if (mysqli_query($con, $q)) {
            alert('success', 'All Deleted successfully');
        } else {
            alert('error', 'Operation failed');
        }
    } else {
        $q = "DELETE FROM `user_queries` WHERE `sr_no`=?";
        $values = [$frm_data['del']];
        if (delete($q, $values, 'i')) {
            alert('success', 'Deleted successfully');
        } else {
            alert('error', 'Operation failed');
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - User inquries</title>
    <?php require('inc/links.php'); ?>
</head>

<body class="bg-light">
    <!-- Top Navbar -->
    <?php require('inc/header.php'); ?>

    <!-- Main Content -->
    <div class="col-lg-10 ms-auto p-4 overflow-hidden" id="main-content">
        <h2 class="mb-4">User Inquiries</h2>

        <!-- User Inquiries here -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="text-end mb-4">
                    <a href="?seen=all" class="btn btn-dark btn-sm rounded-pill">Mark all as read</a>
                    <a href="?del=all" class="btn btn-danger btn-sm rounded-pill">Delete all</a>

                </div>
                <div class="table-responsive-md" style="height: 150px; overflow-y: scroll;">
                    <table class="table table-hover border">
                        <thead class="sticky-top">
                            <tr class="bg-danger text-light">
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">email</th>
                                <th scope="col">sub</th>
                                <th scope="col">mess</th>
                                <th scope="col">date</th>
                                <th scope="col">action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = "SELECT * FROM `user_queries` ORDER BY `sr_no` DESC";
                            $data = mysqli_query($con, $q);
                            $i = 1;

                            while ($row = mysqli_fetch_assoc($data)) {
                                $seen = '';
                                if ($row['seen'] != 1) {
                                    $seen = "<a href='?seen=$row[sr_no]' class='btn btn-sm rounded-pill btn-primary'>New</a>";
                                }
                                $seen .= "<a href='?del=$row[sr_no]' class='btn btn-sm rounded-pill btn-danger mt-2'>delete</a>";

                                echo "<tr>";
                                echo "<th scope='row'>" . $i++ . "</th>";

                                echo "<td>" . $row['name'] . "</td>";
                                echo "<td>" . $row['email'] . "</td>";
                                echo "<td>" . $row['subject'] . "</td>";
                                echo "<td>" . $row['message'] . "</td>";
                                echo "<td>" . $row['date'] . "</td>";
                                echo "<td>" . $seen . "
                                        
                                      </td>";
                                echo "</tr>";
                            }

                            ?>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
    </div> <!-- end main-content -->

    <!-- Modal for Add/Edit Management -->










    <!-- /Sidebar + Main Content Row -->


    <!-- Custom Hover Style -->
    

    <!-- Bootstrap CSS & JS -->
<?php require('inc/scripts.php'); ?>
    <script src="scripts/sliders.js"></script>
</body>

</html>
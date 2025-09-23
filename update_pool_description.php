<?php
require 'admin/inc/db_config.php';

$new_description = "Swim in a 4 ft. pool with a view of the city or simply sit back, relax and order refreshing beverages and sumptuous snacks at the Poolside Bar for ultimate relaxation.";

$result = mysqli_query($con, "UPDATE facilities SET description = '$new_description' WHERE id = 10");

if ($result) {
    echo "Pool description updated successfully!\n";
} else {
    echo "Error updating pool description: " . mysqli_error($con) . "\n";
}

// Verify the update
$check = mysqli_query($con, "SELECT name, description FROM facilities WHERE id = 10");
$row = mysqli_fetch_assoc($check);
echo "Updated facility: " . $row['name'] . "\n";
echo "New description: " . $row['description'] . "\n";

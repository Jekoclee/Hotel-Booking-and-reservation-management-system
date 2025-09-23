<?php
require 'admin/inc/db_config.php';

$new_description = "Experience elegance and sophistication in our spacious hall, perfect for weddings, corporate events, conferences, and special celebrations. With modern amenities and flexible seating arrangements, our hall can accommodate various event sizes and styles.";

$result = mysqli_query($con, "UPDATE facilities SET description = '$new_description' WHERE id = 12");

if ($result) {
    echo "Hall description updated successfully!\n";
} else {
    echo "Error updating hall description: " . mysqli_error($con) . "\n";
}

// Verify the update
$check = mysqli_query($con, "SELECT name, description FROM facilities WHERE id = 12");
$row = mysqli_fetch_assoc($check);
echo "Updated facility: " . $row['name'] . "\n";
echo "New description: " . $row['description'] . "\n";

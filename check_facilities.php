<?php
require_once('admin/inc/db_config.php');

echo "Checking facilities in database...\n";

// Check if facilities table exists and has data
$result = mysqli_query($con, "SELECT COUNT(*) as count FROM facilities");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Total facilities in database: " . $row['count'] . "\n";
} else {
    echo "Error checking facilities count: " . mysqli_error($con) . "\n";
}

// Get sample facilities
$result2 = mysqli_query($con, "SELECT * FROM facilities LIMIT 5");
if ($result2) {
    echo "\nSample facilities:\n";
    while($facility = mysqli_fetch_assoc($result2)) {
        echo "- ID: " . $facility['id'] . ", Name: " . $facility['name'] . ", Description: " . $facility['description'] . "\n";
        echo "  Icon: " . $facility['icon'] . "\n";
    }
} else {
    echo "Error getting facilities: " . mysqli_error($con) . "\n";
}

// Test the select function
echo "\nTesting select function:\n";
$facilities_res = select("SELECT * FROM `facilities` ORDER BY `id` DESC LIMIT 3", [], "");
if ($facilities_res) {
    echo "Select function works! Number of rows: " . mysqli_num_rows($facilities_res) . "\n";
    while($facilities_data = mysqli_fetch_assoc($facilities_res)) {
        echo "- " . $facilities_data['name'] . ": " . $facilities_data['description'] . "\n";
    }
} else {
    echo "Select function failed!\n";
}
?>
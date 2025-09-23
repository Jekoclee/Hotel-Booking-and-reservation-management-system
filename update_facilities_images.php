<?php
require 'admin/inc/db_config.php';

$conn = new mysqli($hname, $uname, $password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update wedding facility
$result1 = $conn->query("UPDATE facilities SET icon = 'wedding_facility.svg' WHERE name = 'wedding'");
echo "Wedding facility updated: " . ($result1 ? "Success" : "Failed") . "\n";

// Update pool facility  
$result2 = $conn->query("UPDATE facilities SET icon = 'pool_facility.svg' WHERE name = 'pool'");
echo "Pool facility updated: " . ($result2 ? "Success" : "Failed") . "\n";

// Update hall facility
$result3 = $conn->query("UPDATE facilities SET icon = 'hall_facility.svg' WHERE name = 'hall'");
echo "Hall facility updated: " . ($result3 ? "Success" : "Failed") . "\n";

// Update kids pool facility
$result4 = $conn->query("UPDATE facilities SET icon = 'kids_pool_facility.svg' WHERE name = 'pools for kids'");
echo "Kids pool facility updated: " . ($result4 ? "Success" : "Failed") . "\n";

echo "\nAll facilities images updated successfully!\n";
$conn->close();

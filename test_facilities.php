<?php
require 'admin/inc/db_config.php';
require 'admin/inc/essentials.php';

echo "Testing facilities data...\n";

$facilities_res = select("SELECT * FROM `facilities` ORDER BY `id` DESC", [], "");
$count = 0;

while ($facilities_data = mysqli_fetch_assoc($facilities_res)) {
    $count++;
    echo "Facility $count: " . $facilities_data['name'] . " - Icon: " . $facilities_data['icon'] . "\n";
}

if ($count == 0) {
    echo "No facilities found in database!\n";
} else {
    echo "Total facilities: $count\n";
}

echo "FACILITIES_IMG_PATH: " . FACILITIES_IMG_PATH . "\n";

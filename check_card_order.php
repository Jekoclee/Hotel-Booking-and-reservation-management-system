<?php
require 'admin/inc/db_config.php';

echo "Current facilities and their card positions:\n";
echo "==========================================\n";

$result = mysqli_query($con, 'SELECT id, name FROM facilities ORDER BY id ASC');
$counter = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $layout = ($counter % 2 == 0) ? 'image-left-text-right' : 'text-left-image-right';
    echo "Card " . ($counter + 1) . ": ID=" . $row['id'] . ", Name=" . $row['name'] . " (counter=" . $counter . ", layout=" . $layout . ")\n";
    $counter++;
}

echo "\nProblem: Hall card should have text on left when image is on right\n";
echo "Current Hall card position: Card 2 (counter=1, should be text-left-image-right)\n";

<?php
require 'admin/inc/db_config.php';

$result = mysqli_query($con, "SELECT id, name, description FROM facilities ORDER BY id ASC");

echo "Facilities in database:\n";
$counter = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $counter++;
    echo "Card $counter - ID: " . $row['id'] . " - Name: " . $row['name'] . "\n";
    echo "Description: " . substr($row['description'], 0, 100) . "...\n\n";
}

<?php
require 'admin/inc/db_config.php';

echo "Current facilities before deletion:\n";
$check_before = mysqli_query($con, "SELECT id, name FROM facilities ORDER BY id ASC");
while ($row = mysqli_fetch_assoc($check_before)) {
    echo "ID: " . $row['id'] . " - Name: " . $row['name'] . "\n";
}

// First, remove any related records in room_facilities table
echo "\nRemoving related records from room_facilities table...\n";

// Remove room_facilities records for wedding (ID: 9)
$remove_rf1 = mysqli_query($con, "DELETE FROM room_facilities WHERE facilities_id = 9");
if ($remove_rf1) {
    echo "Related room_facilities records for wedding removed.\n";
}

// Remove room_facilities records for pools for kids (ID: 15)
$remove_rf2 = mysqli_query($con, "DELETE FROM room_facilities WHERE facilities_id = 15");
if ($remove_rf2) {
    echo "Related room_facilities records for pools for kids removed.\n";
}

echo "\nRemoving wedding facility (ID: 9)...\n";
$result1 = mysqli_query($con, "DELETE FROM facilities WHERE id = 9");
if ($result1) {
    echo "Wedding facility removed successfully!\n";
} else {
    echo "Error removing wedding facility: " . mysqli_error($con) . "\n";
}

echo "\nRemoving pools for kids facility (ID: 15)...\n";
$result2 = mysqli_query($con, "DELETE FROM facilities WHERE id = 15");
if ($result2) {
    echo "Pools for kids facility removed successfully!\n";
} else {
    echo "Error removing pools for kids facility: " . mysqli_error($con) . "\n";
}

echo "\nRemaining facilities after deletion:\n";
$check_after = mysqli_query($con, "SELECT id, name, description FROM facilities ORDER BY id ASC");
$counter = 0;
while ($row = mysqli_fetch_assoc($check_after)) {
    $counter++;
    echo "Card $counter - ID: " . $row['id'] . " - Name: " . $row['name'] . "\n";
    echo "Description: " . substr($row['description'], 0, 50) . "...\n\n";
}

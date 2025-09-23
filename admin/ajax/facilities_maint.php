<?php
/**
 * Facility maintenance operations - centralized endpoint
 * Replaces scattered root-level update/remove scripts
 */

require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST requests allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$action = $input['action'];

switch ($action) {
    case 'update_facility_description':
        updateFacilityDescription($con, $input);
        break;
    case 'update_facility_icon':
        updateFacilityIcon($con, $input);
        break;
    case 'remove_facility':
        removeFacility($con, $input);
        break;
    case 'list_facilities':
        listFacilities($con);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function updateFacilityDescription($con, $input) {
    if (empty($input['facility_id']) || empty($input['description'])) {
        echo json_encode(['success' => false, 'message' => 'facility_id and description are required']);
        return;
    }

    $facility_id = (int)$input['facility_id'];
    $description = trim($input['description']);

    $q = "UPDATE facilities SET description = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $q);
    mysqli_stmt_bind_param($stmt, 'si', $description, $facility_id);

    if (mysqli_stmt_execute($stmt)) {
        // Verify update
        $check_q = "SELECT name, description FROM facilities WHERE id = ?";
        $check_stmt = mysqli_prepare($con, $check_q);
        mysqli_stmt_bind_param($check_stmt, 'i', $facility_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        $row = mysqli_fetch_assoc($result);

        echo json_encode([
            'success' => true, 
            'message' => 'Facility description updated successfully',
            'facility' => $row
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update description: ' . mysqli_error($con)]);
    }
}

function updateFacilityIcon($con, $input) {
    if (empty($input['facility_id']) || empty($input['icon'])) {
        echo json_encode(['success' => false, 'message' => 'facility_id and icon are required']);
        return;
    }

    $facility_id = (int)$input['facility_id'];
    $icon = trim($input['icon']);

    $q = "UPDATE facilities SET icon = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $q);
    mysqli_stmt_bind_param($stmt, 'si', $icon, $facility_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Facility icon updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update icon: ' . mysqli_error($con)]);
    }
}

function removeFacility($con, $input) {
    if (empty($input['facility_id'])) {
        echo json_encode(['success' => false, 'message' => 'facility_id is required']);
        return;
    }

    $facility_id = (int)$input['facility_id'];
    $safe_remove = $input['safe_remove'] ?? true; // Default to safe removal

    // Start transaction
    mysqli_begin_transaction($con);

    try {
        if ($safe_remove) {
            // Remove related records first
            $q1 = "DELETE FROM room_facilities WHERE facilities_id = ?";
            $stmt1 = mysqli_prepare($con, $q1);
            mysqli_stmt_bind_param($stmt1, 'i', $facility_id);
            mysqli_stmt_execute($stmt1);
        }

        // Remove facility
        $q2 = "DELETE FROM facilities WHERE id = ?";
        $stmt2 = mysqli_prepare($con, $q2);
        mysqli_stmt_bind_param($stmt2, 'i', $facility_id);
        
        if (mysqli_stmt_execute($stmt2)) {
            mysqli_commit($con);
            echo json_encode(['success' => true, 'message' => 'Facility removed successfully']);
        } else {
            throw new Exception('Failed to remove facility');
        }
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function listFacilities($con) {
    $q = "SELECT id, name, description, icon FROM facilities ORDER BY id ASC";
    $result = mysqli_query($con, $q);
    
    $facilities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $facilities[] = $row;
    }
    
    echo json_encode(['success' => true, 'facilities' => $facilities]);
}
<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if (isset($_POST['get_users'])) {
    $search = filteration($_POST['search']);
    $status = filteration($_POST['status']);
    $verified = filteration($_POST['verified']);
    
    $query = "SELECT * FROM user_cred WHERE 1=1";
    
    if (!empty($search)) {
        $query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";
    }
    
    if ($status !== '') {
        $query .= " AND status = '$status'";
    }
    
    if ($verified !== '') {
        $query .= " AND is_verified = '$verified'";
    }
    
    $query .= " ORDER BY datentime DESC";
    
    $res = mysqli_query($con, $query);
    $i = 1;
    
    $data = "";
    
    while ($row = mysqli_fetch_assoc($res)) {
        $date = date("d-m-Y", strtotime($row['datentime']));
        
        // Enhanced status button with banned functionality
        if ($row['banned'] == 1) {
            $status_btn = "<button onclick='toggle_ban({$row['id']}, 0)' class='btn btn-danger btn-sm'>Banned</button>";
        } else if ($row['status'] == 1) {
            $status_btn = "<button onclick='toggle_status({$row['id']}, 0)' class='btn btn-success btn-sm'>Active</button>";
        } else {
            $status_btn = "<button onclick='toggle_status({$row['id']}, 1)' class='btn btn-warning btn-sm'>Inactive</button>";
        }
        
        $verified_btn = ($row['is_verified'] == 1) ? 
            "<span class='badge bg-success'>Verified</span>" :
            "<button onclick='verify_user({$row['id']})' class='btn btn-warning btn-sm'>Verify</button>";
        
        $profile_img = (!empty($row['profile'])) ? 
            "<img src='../images/users/{$row['profile']}' width='50px' class='rounded-circle'>" :
            "<img src='../images/users/default.png' width='50px' class='rounded-circle'>";
        
        // Add ban/unban button
        $ban_btn = ($row['banned'] == 1) ? 
            "<button onclick='toggle_ban({$row['id']}, 0)' class='btn btn-sm btn-outline-success' title='Unban User'><i class='bi bi-unlock'></i></button>" :
            "<button onclick='toggle_ban({$row['id']}, 1)' class='btn btn-sm btn-outline-danger' title='Ban User'><i class='bi bi-lock'></i></button>";
        
        $data .= "
            <tr class='align-middle'>
                <td>$i</td>
                <td>$profile_img</td>
                <td>{$row['name']}</td>
                <td>{$row['email']}</td>
                <td>{$row['phonenum']}</td>
                <td>{$row['address']}</td>
                <td>{$row['dob']}</td>
                <td>$verified_btn</td>
                <td>$status_btn</td>
                <td>$date</td>
                <td>
                    <button onclick='edit_user({$row['id']})' class='btn btn-primary btn-sm me-1'>
                        <i class='bi bi-pencil-square'></i>
                    </button>
                    $ban_btn
                    <button onclick='delete_user({$row['id']})' class='btn btn-danger btn-sm'>
                        <i class='bi bi-trash'></i>
                    </button>
                </td>
            </tr>
        ";
        $i++;
    }
    
    echo $data;
}

if (isset($_POST['get_user_stats'])) {
    $total_q = "SELECT COUNT(*) as count FROM user_cred";
    $active_q = "SELECT COUNT(*) as count FROM user_cred WHERE status = 1 AND banned = 0";
    $unverified_q = "SELECT COUNT(*) as count FROM user_cred WHERE is_verified = 0";
    $inactive_q = "SELECT COUNT(*) as count FROM user_cred WHERE status = 0 AND banned = 0";
    $banned_q = "SELECT COUNT(*) as count FROM user_cred WHERE banned = 1";
    
    $total = mysqli_fetch_assoc(mysqli_query($con, $total_q))['count'];
    $active = mysqli_fetch_assoc(mysqli_query($con, $active_q))['count'];
    $unverified = mysqli_fetch_assoc(mysqli_query($con, $unverified_q))['count'];
    $inactive = mysqli_fetch_assoc(mysqli_query($con, $inactive_q))['count'];
    $banned = mysqli_fetch_assoc(mysqli_query($con, $banned_q))['count'];
    
    $stats = [
        'total' => $total,
        'active' => $active,
        'unverified' => $unverified,
        'inactive' => $inactive,
        'banned' => $banned
    ];
    
    echo json_encode($stats);
}

if (isset($_POST['get_user'])) {
    $id = filteration($_POST['id']);
    
    $query = "SELECT * FROM user_cred WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($res) == 1) {
        $user = mysqli_fetch_assoc($res);
        echo json_encode($user);
    } else {
        echo 0;
    }
}

if (isset($_POST['update_user'])) {
    $id = filteration($_POST['id']);
    $name = filteration($_POST['name']);
    $email = filteration($_POST['email']);
    $phone = filteration($_POST['phone']);
    $address = filteration($_POST['address']);
    $dob = filteration($_POST['dob']);
    $status = filteration($_POST['status']);
    $verified = filteration($_POST['verified']);
    
    // Check if email already exists for other users
    $check_email = "SELECT id FROM user_cred WHERE email = ? AND id != ?";
    $stmt = mysqli_prepare($con, $check_email);
    mysqli_stmt_bind_param($stmt, "si", $email, $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($res) > 0) {
        echo 0; // Email already exists
        exit;
    }
    
    $query = "UPDATE user_cred SET name = ?, email = ?, phonenum = ?, address = ?, dob = ?, status = ?, is_verified = ?, token = ? WHERE id = ?";
    $token = ($verified == 1) ? NULL : md5(rand());
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssssssssi", $name, $email, $phone, $address, $dob, $status, $verified, $token, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo 1;
    } else {
        echo 0;
    }
}

if (isset($_POST['toggle_status'])) {
    $id = filteration($_POST['id']);
    $status = filteration($_POST['status']);
    
    $query = "UPDATE user_cred SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ii", $status, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo 1;
    } else {
        echo 0;
    }
}

if (isset($_POST['delete_user'])) {
    $id = filteration($_POST['id']);
    
    // Get user profile image to delete
    $get_img = "SELECT profile FROM user_cred WHERE id = ?";
    $stmt = mysqli_prepare($con, $get_img);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($res) == 1) {
        $user = mysqli_fetch_assoc($res);
        
        // Delete user from database
        $delete_query = "DELETE FROM user_cred WHERE id = ?";
        $stmt = mysqli_prepare($con, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Delete profile image if exists
            if (!empty($user['profile']) && file_exists("../../images/users/{$user['profile']}")) {
                unlink("../../images/users/{$user['profile']}");
            }
            echo 1;
        } else {
            echo 0;
        }
    } else {
        echo 0;
    }
}

if (isset($_POST['verify_user'])) {
    $id = filteration($_POST['id']);
    
    $query = "UPDATE user_cred SET is_verified = 1, token = NULL WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo 1;
    } else {
        echo 0;
    }
}

if (isset($_POST['toggle_ban'])) {
    $id = filteration($_POST['id']);
    $banned = filteration($_POST['banned']);
    
    $query = "UPDATE user_cred SET banned = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ii", $banned, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo 1;
    } else {
        echo 0;
    }
}
?>
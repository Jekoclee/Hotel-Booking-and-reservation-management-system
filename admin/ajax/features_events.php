<?php

require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if (isset($_POST['add_feature'])) {
    $frm_data = filteration($_POST);

    $q = "INSERT INTO `features`(`name`) VALUES (?)";
    $values = [$frm_data['name']];
    $res = insert($q, $values, 's');
    echo $res;
}

if (isset($_POST['get_features'])) {
    $res = selectAll('features');
    $i = 1;
    while ($row = mysqli_fetch_assoc($res)) {
        $path = ABOUT_IMG_PATH;

        echo <<<data
        <tr>
               <td>$i</td>
               <td>$row[name]</td>
               <td>
                   <button type="button" class="btn btn-danger btn-sm shadow-none" onclick="rem_feature($row[id])">
                       <i class="bi bi-trash-fill"></i> DELETE
                   </button>
               </td>
        </tr>
    data;
        $i++;
    }
}

if (isset($_POST['rem_feature'])) {
    $frm_data = filteration($_POST);
    $values = [$frm_data['rem_feature']];

    $check_q = select('SELECT * FROM `room_features` WHERE `features_id`=?', [$frm_data['rem_feature']], 'i');

    if (mysqli_num_rows($check_q) == 0) {


        $q = "DELETE FROM `features` WHERE `id`=?";
        $res = delete($q, $values, 'i');
        echo $res;
    } else {
        echo 'room_added';
    }
}

if (isset($_POST['add_facility'])) {
    $frm_data = filteration($_POST);

    $img_r =  uploadSVGImage($_FILES['icon'], EVENTS_FOLDER);

    if ($img_r == 'inv_img') {
        echo $img_r;
    } else if ($img_r == 'inv_size') {
        echo $img_r;
    } else if ($img_r == 'upd_failed') {
        echo $img_r;
    } else {
        // Insert into database
        $q = "INSERT INTO `facilities`(`name`, `icon`, `description`) VALUES (?,?,?)";
        $values = [$frm_data['name'], $img_r, $frm_data['desc']];
        $res = insert($q, $values, 'sss');
        echo $res;
    }
}

if (isset($_POST['get_facilities'])) {
    $res = selectAll('facilities');
    $i = 1;
    $path = EVENTS_IMG_PATH;

    while ($row = mysqli_fetch_assoc($res)) {


        echo <<<data
        <tr>
               <td>$i</td>
               <td><img src="$path$row[icon]" style="max-width: 50px;"></td>
               <td>$row[name]</td>
                <td>$row[description]</td>
               <td>
                   <button type="button" class="btn btn-danger btn-sm shadow-none" onclick="rem_facility($row[id])">
                       <i class="bi bi-trash-fill"></i> DELETE
                   </button>
               </td>
        </tr>
    data;
        $i++;
    }
}

if (isset($_POST['rem_facility'])) {
    $frm_data = filteration($_POST);
    $values = [$frm_data['rem_facility']];

    $check_q = select('SELECT * FROM `room_facilities` WHERE `facilities_id`=?', [$frm_data['rem_facility']], 'i');
    if (mysqli_num_rows($check_q) == 0) {


        $pre_q = "SELECT * FROM `facilities` WHERE `id`=?";
        $res = select($pre_q, $values, 'i');
        $img = mysqli_fetch_assoc($res);

        //delete image
        if (deleteImage($img['icon'], EVENTS_FOLDER)) {
            $q = "DELETE FROM `facilities` WHERE `id`=?";
            $res = delete($q, $values, 'i');
            echo $res;
        } else {
            echo 0;
        }
    } else {
        echo 'room_added';
    }
}

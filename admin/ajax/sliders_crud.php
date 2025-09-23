<?php

require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if (isset($_POST['add_sliders'])) {
    $img_r =  uploadImage($_FILES['picture'], SLIDERS_FOLDER);

    if ($img_r == 'inv_img') {
        echo $img_r;
    } else if ($img_r == 'inv_size') {
        echo $img_r;
    } else if ($img_r == 'upd_failed') {
        echo $img_r;
    } else {
        // Insert into database
        $q = "INSERT INTO `sliders`(`sliders`) VALUES (?)";
        $values = [$img_r];
        $res = insert($q, $values, 's');
        echo $res;
    }
}

if (isset($_POST['get_sliders'])) {
    $res = selectAll('sliders');

    while ($row = mysqli_fetch_assoc($res)) {
        $path = SLIDERS_IMG_PATH;

        echo <<<data
    <div class="col-md-4 mb-3">
                                    <div class="card bg-dark text-white">
                                        <img src="$path$row[sliders]" class="card-img">
                                        <div class="card-img-overlay text-end">
                                            <button type="button" class="btn btn-danger btn-sm shadow-none" onclick="rem_sliders($row[sr_no])">
                                                <i class="bi bi-trash-fill"></i> DELETE
                                            </button>
                                        </div>
                                    </div>

                                </div>
    data;
    }
}

if (isset($_POST['rem_sliders'])) {
    $frm_data = filteration($_POST);
    $values = [$frm_data['rem_sliders']];

    //get image name
    $pre_q = "SELECT * FROM `sliders` WHERE `sr_no`=?";
    $res = select($pre_q, $values, 'i');
    $img = mysqli_fetch_assoc($res);

    //delete image
    if (deleteImage($img['sliders'], SLIDERS_FOLDER)) {
        //delete record
        $q = "DELETE FROM `sliders` WHERE `sr_no`=?";
        $res = delete($q, $values, 'i');
        echo $res;
    } else {
        echo 0;
    }
}

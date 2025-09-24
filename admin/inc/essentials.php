<?php

// Prevent multiple inclusions
if (!defined('ESSENTIALS_INCLUDED')) {
    define('ESSENTIALS_INCLUDED', true);

    //frontend data
    define('SITE_URL', 'http://localhost:8000/');
    define('ABOUT_IMG_PATH', SITE_URL . 'images/about/');
    define('SLIDERS_IMG_PATH', SITE_URL . 'images/sliders/');
    define('EVENTS_IMG_PATH', SITE_URL . 'images/events/');
    define('FACILITIES_IMG_PATH', SITE_URL . 'images/events/');
    define('FEATURES_IMG_PATH', SITE_URL . 'images/features/');
    define('ROOMS_IMG_PATH', SITE_URL . 'images/rooms/');
    define('USERS_IMG_PATH', SITE_URL . 'images/users/');


    //backend process need this data
    // Base path resolves to project root: admin/inc -> ../../
    define('BASE_PATH', realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR);
    define('UPLOAD_IMAGE_PATH', BASE_PATH . 'images' . DIRECTORY_SEPARATOR);
    define('ABOUT_FOLDER', 'about/');
    define('SLIDERS_FOLDER', 'sliders/');
    define('EVENTS_FOLDER', 'events/');
    define('FEATURES_FOLDER', 'features/');
    define('ROOMS_FOLDER', 'rooms/');
    define('USERS_FOLDER', 'users/');

    ///sendgrid api key

    define('SENDGRID_API_KEY', getenv('SENDGRID_API_KEY') ?: '')

    function adminLogin()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!(isset($_SESSION['adminLogin']) && $_SESSION['adminLogin'] == true)) {
            echo
            "<script>
        window.location.href='index.php';
        </script>";
            exit;
        }
    }

    function redirect($url)
    {
        echo
        "<script>
    window.location.href='$url';
    </script>";
        exit;
    }


    function alert($type, $msg)
    {
        $bs_class =  ($type == "success") ? "alert-success" : "alert-danger";
        echo <<<alert
                    <div class="alert $bs_class alert-dismissible fade show custom-alert" role="alert">
                        <strong class="me-3">$msg</strong> 
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
        alert;
    }

    function uploadImage($image, $folder)
    {
        $valid_mime = ['image/jpeg', 'image/png', 'image/webp'];
        $img_mime = $image['type'];


        if (!in_array($img_mime, $valid_mime)) {
            return 'inv_img'; //invalid image mime or format
        } else if (($image['size'] / (1024 * 1024) > 5)) { // 2MB limit
            return 'inv_size'; //invalid image size
        } else {
            $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $rname = 'IMG_' . random_int(11111, 99999) . ".$ext";

            $img_path = UPLOAD_IMAGE_PATH . $folder . $rname;
            if (move_uploaded_file($image['tmp_name'], $img_path)) {;
                return $rname; //return image name
            } else {
                return 'upd_failed';
            }
        }
    }

    function deleteImage($image, $folder)
    {

        if (unlink(UPLOAD_IMAGE_PATH . $folder . $image)) {
            return true;
        } else {
            return false;
        }
    }

    function uploadSVGImage($image, $folder)
    {
        $valid_mime = ['image/svg+xml'];
        $img_mime = $image['type'];


        if (!in_array($img_mime, $valid_mime)) {
            return 'inv_img'; //invalid image mime or format
        } else if (($image['size'] / (1024 * 1024) > 5)) { // 2MB limit
            return 'inv_size'; //invalid image size
        } else {
            $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $rname = 'IMG_' . random_int(11111, 99999) . ".$ext";

            $img_path = UPLOAD_IMAGE_PATH . $folder . $rname;
            if (move_uploaded_file($image['tmp_name'], $img_path)) {;
                return $rname; //return image name
            } else {
                return 'upd_failed';
            }
        }
    }


    function uploadUserImage($image)
    {

        $valid_mime = ['image/jpeg', 'image/png', 'image/webp'];
        $img_mime = $image['type'];


        if (!in_array($img_mime, $valid_mime)) {
            return 'inv_img'; //invalid image mime or format
        } else {
            $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $rname = 'IMG_' . random_int(11111, 99999) . ".jpeg";

            $img_path = UPLOAD_IMAGE_PATH . USERS_FOLDER . $rname;
            if ($ext == 'png' || $ext == 'PNG') {
                $img = imagecreatefrompng($image['tmp_name']);
            } else if ($ext == 'webp' || $ext == 'WEBP') {
                $img = imagecreatefromwebp($image['tmp_name']);
            } else {
                $img = imagecreatefromjpeg($image['tmp_name']);
            }

            if (imagejpeg($img, $img_path, 75)) {;
                return $rname; //return image name
            } else {
                return 'upd_failed';
            }
        }
    }
} // End of include guard

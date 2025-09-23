<?php
require('inc/essentials.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
redirect('index.php');

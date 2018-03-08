<?php
//error_reporting(E_ALL^E_NOTICE);
//set_time_limit(0);
ini_set ( 'session.save_path' , dirname ( __FILE__ ) . '/dirname/' ) ; 
session_start();
require_once '../init.php';
require FRAMEWORK_PATH . '/library/image.class.php';
//require FRAMEWORK_PATH . '/library/session.class.php';
//session_start();
//$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$image= new cls_image;
$image_result = $image->generate_captcha();
$_SESSION['blob'] = $image_result['blob'];
$_SESSION['format'] = $image_result['format'];
$_SESSION['code'] = strtolower($image_result['code']);
header("Content-type: image/{$_SESSION['format']}");
echo $_SESSION['blob'];
?>









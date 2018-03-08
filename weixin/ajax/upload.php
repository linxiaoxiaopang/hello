<?php
ini_set ( 'session.save_path' , dirname ( __FILE__ ) . '/../dirname/' ) ; 
session_id($_GET['SESSION_ID']);
session_start();

error_reporting(E_ALL^E_NOTICE^E_WARNING);
//ini_set("display_errors",1);
set_time_limit(0);
require_once '../../init.php';
require_once FRAMEWORK_PATH . '/library/common.lib.php';
//require_once FRAMEWORK_PATH . '/library/session.class.php';

load_module_config('account');
//load_module_config('session');

$admin_id = $_SESSION['admin_id'];

if($admin_id > 0){
	
}else{
	header('location: '.$GLOBALS['ADMIN_URL'].'/index.php?act=login');
	exit;
}

$goods_img		=   "";
//上传图片
if (($_FILES['imgFile']['tmp_name'] != '' && $_FILES['imgFile']['tmp_name'] != 'none')){
	require_once FRAMEWORK_PATH . '/library/upload.class.php';

	//上传配置
	$config = array(
		"savePath" => $GLOBALS['DATA_PATH']."/images/" ,
		"maxSize" => 2048 , //单位KB
		"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp"  )
	);
	//生成上传实例对象并完成上传
	$up = new Uploader( "imgFile" , $config );
	$info = $up->getFileInfo();
	
	$goods_img   = str_replace($GLOBALS['DATA_PATH'], '', $info["url"]."_400.jpg"); // 本地备份原始图片
	
	echo json_encode(array('error' => 0, 'url' => $GLOBALS['CDN_URL']. $goods_img));
}elseif(($_FILES['multiFile']['tmp_name'] != '' && $_FILES['multiFile']['tmp_name'] != 'none')){
	require_once FRAMEWORK_PATH . '/library/upload.class.php';

	//上传配置
	$config = array(
		"savePath" => $GLOBALS['DATA_PATH']."/images/" ,
		"maxSize" => 2048 , //单位KB
		"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp"  )
	);
	//生成上传实例对象并完成上传
	$up = new Uploader( "multiFile" , $config );
	$info = $up->getFileInfo();
	
	$goods_img   = str_replace($GLOBALS['DATA_PATH'], '', $info["url"]); // 本地备份原始图片
	
	echo $goods_img;
}


/**
 * *
 * 瀑布流根据宽度等比缩放
 * @param unknown_type $srcImage   源图片路径
 * @param unknown_type $toFile     目标图片路径
 * @param unknown_type $maxWidth   缩放宽度
 * @param unknown_type $imgQuality 图片质量
 * @return unknown
 */
function resize($srcImage,$toFile,$maxWidth = 100,$imgQuality=100)
{
 
    list($width, $height, $type, $attr) = getimagesize($srcImage);
    if($width < $maxWidth ) return ;
    switch ($type) {
    case 1: $img = imagecreatefromgif($srcImage); break;
    case 2: $img = imagecreatefromjpeg($srcImage); break;
    case 3: $img = imagecreatefrompng($srcImage); break;
    }
    $scale = $maxWidth/$width; //求出绽放比例
   
    if($scale < 1) {
    $newWidth = floor($scale*$width);
    $newHeight = floor($scale*$height);
    $newImg = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    $newName = "";
    $toFile = preg_replace("/(.gif|.jpg|.jpeg|.png)/i","",$toFile);

    switch($type) {
        case 1: if(imagegif($newImg, "$toFile$newName.gif", $imgQuality))
        return "$newName.gif"; break;
        case 2: if(imagejpeg($newImg, "$toFile$newName.jpg", $imgQuality))
        return "$newName.jpg"; break;
        case 3: if(imagepng($newImg, "$toFile$newName.png", $imgQuality))
        return "$newName.png"; break;
        default: if(imagejpeg($newImg, "$toFile$newName.jpg", $imgQuality))
        return "$newName.jpg"; break;
    }
    imagedestroy($newImg);
    }
    imagedestroy($img);
    return false;
}

?>
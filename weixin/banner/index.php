<?php
ini_set ( 'session.save_path' , dirname ( __FILE__ ) . '/../dirname/' ) ; 
session_start();
header("Content-Type:text/html;charset=utf-8");
require_once '../../init.php';
require_once '../../init_smarty.php';
require_once FRAMEWORK_PATH . '/library/common.lib.php';
require_once FRAMEWORK_PATH . '/library/fastcgi.lib.php';
require_once FRAMEWORK_PATH . '/library/mysql.class.php';

load_module_config('account');

$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);

$admin_id = $_SESSION['admin_id'];

if($admin_id > 0){
	
}else{
	header('location:'.$GLOBALS['ADMIN_URL'].'/index.php?act=login');
	exit;
}
//判断权限
//admin_priv('goods_manage');
//load模块菜单
load_menu_module();

$GLOBALS['T']->assign('session_id', session_id());

$_REQUEST['act'] = ($_REQUEST['act']=='')?'list':$_REQUEST['act'];
$act = $_REQUEST['act'];

switch($act){
	
	
	case "list":
		
		$type = ForceIntFrom('type',1);
		
		$list = $GLOBALS['DB']->get_results("SELECT * FROM `banner` WHERE `type`={$type} ORDER BY `order_by` DESC ");
		$GLOBALS['T']->assign('list',$list);
		$GLOBALS['T']->assign('type',$type);
		$GLOBALS['T']->display('admin/banner/list.html');
		break;
	case "banner_update":
		
		$banner_id = ForceIntFrom('banner_id');
		$banner = $GLOBALS['DB']->get_row("SELECT * FROM `banner` WHERE `id`={$banner_id} ");
		
		$GLOBALS['T']->assign('info',$banner);
		if($banner['type']){
			$type = $banner['type'];
		}else{
			$type = ForceIntFrom('type',1);
		}
		$GLOBALS['T']->assign('type',$type);
		
		$GLOBALS['T']->assign('select_full',1);
		$GLOBALS['T']->display('admin/banner/banner_update.html');
		break;
	case "save": //保存
		$banner_id			= 	ForceIntFrom('banner_id');
		$order_by			= 	ForceIntFrom('order_by');
		$link 		= 	ForceStringFrom('link');
		$name 		= 	ForceStringFrom('name');
		$type			= 	ForceIntFrom('type');
		
		//上传图片
		if (($_FILES['banner_img']['tmp_name'] != '' && $_FILES['banner_img']['tmp_name'] != 'none')){
			require_once FRAMEWORK_PATH . '/library/upload.class.php';
			//上传配置
			$config = array(
				"savePath" => $GLOBALS['DATA_PATH']."/images/" ,
				"maxSize" => 20480 , //单位KB
				"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg"  )
			);
			//生成上传实例对象并完成上传
			$up = new Uploader( "banner_img" , $config );
			$info = $up->getFileInfo();
			//thumn($info["url"],$info["url"]."_90.jpg", 90, 90, 1);
			$pic   = str_replace($GLOBALS['DATA_PATH'], '', $info["url"]); // 本地备份原始图片
		}
		//上传图片
		if (($_FILES['banner_img_m']['tmp_name'] != '' && $_FILES['banner_img_m']['tmp_name'] != 'none')){
			require_once FRAMEWORK_PATH . '/library/upload.class.php';
			//上传配置
			$config = array(
				"savePath" => $GLOBALS['DATA_PATH']."/images/" ,
				"maxSize" => 20480 , //单位KB
				"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg"  )
			);
			//生成上传实例对象并完成上传
			$up = new Uploader( "banner_img_m" , $config );
			$info = $up->getFileInfo();
			$mpic   = str_replace($GLOBALS['DATA_PATH'], '', $info["url"]); // 本地备份原始图片
		}
		
		$sql_set = "";
		if($pic){
			$sql_set .= " ,`pic`='{$pic}' ";
		}
		if($mpic){
			$sql_set .= " ,`mpic`='{$mpic}' ";
		}
		
		if($banner_id > 0){
			$sql = "UPDATE `banner` SET `order_by` = '{$order_by}',`link` = '{$link}',`name` = '{$name}' $sql_set WHERE id='{$banner_id}' ";
			$res = $GLOBALS['DB']->query($sql);
		}
		else{
			$sql = "INSERT INTO `banner` SET `order_by` = '{$order_by}',`link` = '{$link}',`pic`='{$pic}',`mpic`='{$mpic}',`name` = '{$name}',`type` = '{$type}' ";
			$res = $GLOBALS['DB']->query($sql);
		}
		if($res){
			echo "<script>window.parent.php_callback('保存成功');setTimeout(function(){window.parent.location='".$GLOBALS['ADMIN_URL'].'/banner/?act=list&type='.$type."';},1500);</script>";exit;
		}
		else{
			echo "<script>window.parent.php_callback('操作失败');</script>";exit;
		}
		break;
	case 'banner_del':
	
		$return_link=$GLOBALS['ADMIN_URL']."/banner/";
		$banner_id			= 	ForceIntFrom('banner_id');
		if($banner_id){
			$sql = "DELETE FROM `banner` WHERE `id` = {$banner_id}";
			$re=$GLOBALS['DB']->query($sql);
			if($re){
				sys_msg(1,"操作成功",$return_link);
			}else{
				sys_msg(0,"操作失败",$return_link);
			}
		}else{
			sys_msg(0,"操作失败",$return_link);
		}
		break;

}
?>
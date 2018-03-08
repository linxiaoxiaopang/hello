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
		
		$info = $GLOBALS['DB']->get_row("SELECT * FROM `index_set` WHERE id=2");
		$GLOBALS['T']->assign('info',$info);
		$GLOBALS['T']->display('admin/ExpressFee/index.html');
		break;
	case "save": //保存
		
		$price = ForceStringFrom('price');
		
		if($price > 0){
			$sql = "UPDATE `index_set` SET `info` = '{$price}' WHERE id=2 ";
			$res = $GLOBALS['DB']->query($sql);
		}
		if($res){
			echo "<script>window.parent.php_callback('保存成功');setTimeout(function(){window.parent.location='".$GLOBALS['ADMIN_URL']."/ExpressFee/index.php?act=list';},1500);</script>";exit;
		}
		else{
			echo "<script>window.parent.php_callback('操作失败');</script>";exit;
		}
		break;

}
?>
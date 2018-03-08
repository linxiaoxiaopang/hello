<?php
//error_reporting(E_ALL^E_NOTICE);
//set_time_limit(0);
ini_set ( 'session.save_path' , dirname ( __FILE__ ) . '/dirname/' ) ; 
session_start();
require_once '../www/init.php';
require_once '../www/init_smarty.php';
require_once FRAMEWORK_PATH . '/library/common.lib.php';
require_once FRAMEWORK_PATH . '/library/mysql.class.php';
require_once FRAMEWORK_PATH . '/library/session.class.php';
require_once FRAMEWORK_PATH . '/module/admin.class.php';
require_once FRAMEWORK_PATH . '/library/captcha.class.php';

load_module_config('account');
//load_module_config('session');

define('CAPTCHA_ADMIN',true); //后台登录时使用验证码

//$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);


$admin = new cls_admin();
//print_r($_SESSION);

$act = $_REQUEST['act'];

$GLOBALS['T']->assign('date',date("Y-m-d H:i:s"));

//load模块菜单
load_menu_module();

switch($act){
	case "login":
		$_SESSION['admin_id'] = 0;
		$GLOBALS['T']->display('admin/login.html');
		break;
	case "do_login":
	
		if(!empty($_SESSION['code']) && CAPTCHA_ADMIN)
		{
			if(empty($_REQUEST['captcha'])){
				sys_msg(2,'验证码不能为空');
			}
			if (!empty($_REQUEST['captcha']) && $_SESSION['code'] != strtolower($_REQUEST['captcha']))
			{
				sys_msg(2,'验证码输入错误');
			}
		}
	
		$_REQUEST['admin_name'] = isset($_REQUEST['admin_name']) ? trim($_REQUEST['admin_name']) : '';
		$_REQUEST['password'] = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : '';
		
		$result = $admin->login($_REQUEST['admin_name'],$_REQUEST['password']);

		if($result){
			//print_r($_SESSION);
			sys_msg(1,'登录成功');
		}else{
			sys_msg(0,'帐号密码错误');
		}
		break;
	default:
		if(empty($_SESSION['admin_id']))
		{
			$GLOBALS['T']->display('admin/login.html');
			exit;
		}
		$GLOBALS['T']->display('admin/index.html');
		break;
}

?>
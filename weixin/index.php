<?php
//error_reporting(E_ALL^E_NOTICE);
//set_time_limit(0);
ini_set ( 'session.save_path' , dirname ( __FILE__ ) . '/dirname/' ) ; 
session_start();
header("Content-Type:text/html;charset=utf-8");
require_once '../init.php';
require_once '../init_smarty.php';
require_once FRAMEWORK_PATH . '/library/common.lib.php';
require_once FRAMEWORK_PATH . '/library/mysql.class.php';
//require_once FRAMEWORK_PATH . '/library/session.class.php';
require_once FRAMEWORK_PATH . '/module/admin.class.php';
require_once FRAMEWORK_PATH . '/library/captcha.class.php';

load_module_config('account');
//load_module_config('session');
//ini_set("display_errors", "On");
define('CAPTCHA_ADMIN',true); //后台登录时使用验证码

//$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);


$admin = new cls_admin();


$act = $_REQUEST['act'];
$GLOBALS['T']->assign('date',date("Y-m-d H:i:s"));

//load模块菜单
load_menu_module();
//var_dump($_SESSION);
switch($act){
	case "login":
		//$_SESSION['admin_id'] = 0;
		$GLOBALS['T']->display('admin/login.html');
		break;
	case "do_login":
		
		if(!empty($_SESSION['code']) && CAPTCHA_ADMIN)
		{
			if(empty($_REQUEST['captcha'])){
				sys_msg(2,'验证码不能为空',"{$GLOBALS['ADMIN_URL']}/index.php");
			}
			if (!empty($_REQUEST['captcha']) && $_SESSION['code'] != strtolower($_REQUEST['captcha']))
			{
				sys_msg(2,'验证码输入错误',"{$GLOBALS['ADMIN_URL']}/index.php");
			}
		}
	
		$_REQUEST['admin_name'] = isset($_REQUEST['admin_name']) ? trim($_REQUEST['admin_name']) : '';
		$_REQUEST['password'] = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : '';
		
		$result = $admin->login($_REQUEST['admin_name'],$_REQUEST['password']);

		if($result){
			//var_dump($_SESSION);
			sys_msg(1,'登录成功',"{$GLOBALS['ADMIN_URL']}/index.php");
		}else{
			sys_msg(0,'帐号密码错误',"{$GLOBALS['ADMIN_URL']}/index.php");
		}
		break;
	case "logout":
		$admin->logout();
		//$GLOBALS['T']->display('admin/login.html');
		header("location: {$GLOBALS['ADMIN_URL']}/index.php");exit;
		break;
	//修改密码
	case "change_pwd":
		if(empty($_SESSION['admin_id']))
		{
			header("Location:{$GLOBALS['ADMIN_URL']}/?act=login");
			exit;
		}
		$GLOBALS['T']->assign('_SESSION', $_SESSION);
		$GLOBALS['T']->display('admin/role/change_pwd.html');
		break;
	//提交修改
	case "renew_pwd":
		$id = 	$_SESSION['admin_id'];
		$old_pwd = 	ForceStringFrom('old_pwd');
		$new_pwd = 	ForceStringFrom('new_pwd');
		$renew_pwd = 	ForceStringFrom('renew_pwd');
		if($renew_pwd != $new_pwd){
			sys_msg_json(0,"两次密码输入不一致",'/?act=change_pwd');
		}
		$sql = "SELECT * FROM `admin_account` WHERE admin_id = '{$id}' AND password= '".md5($old_pwd)."'";
		if(!$GLOBALS['DB']->get_row($sql)){
			sys_msg_json(0,"旧密码输入错误");
		}
		require_once FRAMEWORK_PATH . '/module/role.class.php';
		$cls_role = new cls_role();
		if($cls_role->change_pwd($id,$new_pwd)){
			sys_msg_json(1,"密码修改成功");
		}else{
			sys_msg_json(0,"密码修改失败");
		}
		break;
	default:
		if($_SESSION['admin_id'] > 0){
			//var_dump($_SESSION);
			$GLOBALS['T']->display('admin/index.html');
		}else{
			//var_dump($_SESSION);
			$GLOBALS['T']->display('admin/login.html');
		}	
		break;
		
}



?>
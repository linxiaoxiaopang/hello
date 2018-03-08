<?php
error_reporting(E_ALL^E_NOTICE);
set_time_limit(0);
require_once '../www/init.php';
require_once '../www/init_smarty.php';
require_once FRAMEWORK_PATH . '/library/common.lib.php';
require_once FRAMEWORK_PATH . '/library/session.class.php';
require_once FRAMEWORK_PATH . '/library/mysql.class.php';
require_once FRAMEWORK_PATH . '/library/memcached.class.php';
require_once FRAMEWORK_PATH . '/library/captcha.class.php';
require_once FRAMEWORK_PATH . '/module/admin.class.php';
require_once FRAMEWORK_PATH . '/module/operation.class.php';

load_module_config('account');
load_module_config('session');

$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);
$admin = new cls_admin();
define('CAPTCHA_ADMIN',true); //后台登录时使用验证码
//print_r($_POST);
/* action操作项的初始化 */
$act = $_REQUEST['act'];
//echo $act;

$cls_operation = new cls_operation();

if(empty($act))
{
	$act = 'login';
}else
{
	$act = trim($act);
}

/*------------------------------------------------------ */
//-- 退出登录
/*------------------------------------------------------ */
if($act == 'logout')
{
	
	$log_type = 2;//1登录  2退出  3系统管理  4订单管理  5会员资金管理  6开奖资金管理  7合买管理  8资讯管理  9会员管理
	$log_bind = '';
	$admin_id = $_SESSION['admin_id'];
	$remark = '退出';
	$cls_operation->addOperationLog($log_type,$log_bind,$admin_id,$remark);
	#清除
	session_destroy();
	$act = 'login';
}
/*------------------------------------------------------ */
//-- 登陆界面
/*------------------------------------------------------ */
if($act == 'login')
{
	/* PHP 脚本通常会产生一些动态内容，这些内容必须不被浏览器或代理服务器缓存。很多代理服务器和浏览器都可以被下面的方法禁止缓存*/
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); //
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
	if(CAPTCHA_ADMIN)
	{
		$GLOBALS['T']->assign('random',     mt_rand()); //随机值 以免生成验证码缓存		
	}
	$GLOBALS['T']->display("admin/login.html");
	
}
/*------------------------------------------------------ */
//-- 验证登陆信息
/*------------------------------------------------------ */
elseif($act == 'signin')
{
	if(empty($_REQUEST['admin_name']) || empty($_REQUEST['password'])){
		$GLOBALS['T']->assign('defaultUrl',"privilege.php?act=login");
		$GLOBALS['T']->assign('msg','用户名或密码不能为空');
		$GLOBALS['T']->display('admin/info.html');	
		die();
	}
	if(!empty($_SESSION['code']) && CAPTCHA_ADMIN)
	{
		if(empty($_REQUEST['captcha'])){
			$GLOBALS['T']->assign('defaultUrl',"privilege.php?act=login");
			$GLOBALS['T']->assign('msg','验证码不能为空');
			$GLOBALS['T']->display('admin/info.html');	
			die();
		}
		if (!empty($_REQUEST['captcha']) && $_SESSION['code'] != strtolower($_REQUEST['captcha']))
        {
            $GLOBALS['T']->assign('defaultUrl',"privilege.php?act=login");
			$GLOBALS['T']->assign('msg','验证码错误');
			$GLOBALS['T']->display('admin/info.html');	
			die();
        }
	}
	$_REQUEST['admin_name'] = isset($_REQUEST['admin_name']) ? trim($_REQUEST['admin_name']) : '';
    $_REQUEST['password'] = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : '';
    
    $result = $admin->login($_REQUEST['admin_name'],$_REQUEST['password']);
    
    //var_dump($_SESSION);
    
	if($result){
		
		$log_type = 1;//1登录2退出3系统管理4订单管理5会员资金管理6开奖资金管理7合买管理8资讯管理9会员管理
		$log_bind = '';
		$admin_id = $_SESSION['admin_id'];
		$remark = '登录';
		$cls_operation->addOperationLog($log_type,$log_bind,$admin_id,$remark);
		
		header('location:index.php');
	}else{
		$GLOBALS['T']->assign('defaultUrl',"privilege.php?act=login");
		$GLOBALS['T']->assign('msg','您的账号信息不正确');
		$GLOBALS['T']->display('admin/info.html');	
	}

}
?>
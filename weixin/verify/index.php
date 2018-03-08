<?php
ini_set ( 'session.save_path' , dirname ( __FILE__ ) . '/../dirname/' ) ; 
session_start();
header("Content-Type:text/html;charset=utf-8");
require_once '../../init.php';
require_once '../../init_smarty.php';
require_once FRAMEWORK_PATH . '/library/common.lib.php';
require_once FRAMEWORK_PATH . '/library/fastcgi.lib.php';
require_once FRAMEWORK_PATH . '/library/mysql.class.php';
//require_once FRAMEWORK_PATH . '/library/session.class.php';
require_once FRAMEWORK_PATH . '/module/verify.class.php';
require_once FRAMEWORK_PATH . '/library/memcached.class.php';

load_module_config('account');
//load_module_config('session');

//$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);
//$GLOBALS['MC'] = new Memcache;
//$GLOBALS['MC']->pconnect( $GLOBALS['account_settings']['mc_server']['default']['ip'], $GLOBALS['account_settings']['mc_server']['default']['port']);

$admin_id = $_SESSION['admin_id'];

$cls_verify = new cls_verify();

if($admin_id > 0){

}else{
	header('location: /index.php');
	exit;
}
//判断权限
admin_priv('member_manage');
//load模块菜单
load_menu_module();
$act = ForceStringFrom('act');
switch($act){
	case "add":
		//$member_id	= 	ForceIntFrom('member_id');
		
		$GLOBALS['T']->display('admin/verify/add.html');

		break;
	
	case "verify_list":
		$list = $cls_verify->verify_list();
		$GLOBALS['T']->assign('full_page',  	1);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],1,$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);

		$GLOBALS['T']->display('admin/verify/list.html');
		break;

	case "create":
		$addnum =0; //添加成功数
		$num= ForceIntFrom('num');
		for ($i=0; $i <$num ; $i++) { 
			$verify = $cls_verify->createVerify(6);
			$cls_verify->addVerify($verify);
			$addnum++;
		}
		
		if ($addnum == $num) {
			echo "<script>window.parent.php_callback('操作成功');</script>";
		}else{
			echo "<script>window.parent.php_callback('操作失败 ERROR:{$rs}');</script>";exit;
		}
		$GLOBALS['T']->display('admin/verify/list.html');
		break;
}

?>
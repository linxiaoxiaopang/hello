<?php
ini_set ( 'session.save_path' , dirname ( __FILE__ ) . '/../dirname/' ) ; 
session_start();
require_once '../../init.php';
require_once '../../init_smarty.php';
require_once FRAMEWORK_PATH . '/library/common.lib.php';
require_once FRAMEWORK_PATH . '/library/mysql.class.php';
require_once FRAMEWORK_PATH . '/module/advice.class.php';
load_module_config('account');

$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);

$cls_advice   = new cls_advice();

$admin_id = $_SESSION['admin_id'];

if($admin_id > 0){
	
}else{
	header('location:'.$GLOBALS['ADMIN_URL'].'/index.php?act=login');
	exit;
}
//判断权限
admin_priv('advice_manage');
//load模块菜单
load_menu_module();

$act = $_REQUEST['act'];

//$GLOBALS['T']->assign('session_id', $GLOBALS['S']->session_id);
$GLOBALS['T']->assign('session_id', session_id());

if($_REQUEST['act'] == 'list'){//列表
	
	
    $list = $cls_advice->getList();
    //var_dump($list);
	$GLOBALS['T']->assign('list',           $list['list']);
   	$GLOBALS['T']->assign('filter',  		$list['filter']);
	$GLOBALS['T']->assign('page_html',  	create_pages_html($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
	$GLOBALS['T']->assign('record_count', 	$list['record_count']);
	$GLOBALS['T']->assign('page_count',   	$list['page_count']);
	$GLOBALS['T']->display('admin/advice/list.html');
	
	
}elseif($_REQUEST['act'] == 'batch'){
	if(isset($_POST['deletea_ids'])){
		$del_ids = implode(",",$_POST['deletea_ids']);
		$sql = "UPDATE advice_log SET status = 3 WHERE id IN({$del_ids})";
		$res = $GLOBALS['DB']->query($sql);
		if(!empty($res)){
			sys_msg(1,"删除成功",$GLOBALS['ADMIN_URL'].'/advice/?act=list');
		}
		else{
			sys_msg(0,"删除失败",$GLOBALS['ADMIN_URL'].'/advice/?act=list');
		}
	}	
}
?>
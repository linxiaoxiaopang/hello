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
require_once FRAMEWORK_PATH . '/module/admin_member.class.php';
require_once FRAMEWORK_PATH . '/module/role.class.php';
//require_once FRAMEWORK_PATH . '/library/memcached.class.php';

load_module_config('account');
//load_module_config('session');

//$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);
//$GLOBALS['MC'] = new Memcache;
//$GLOBALS['MC']->pconnect( $GLOBALS['account_settings']['mc_server']['default']['ip'], $GLOBALS['account_settings']['mc_server']['default']['port']);

$admin_id = $_SESSION['admin_id'];

$cls_role = new cls_role();

if($admin_id > 0){

}else{
	header('location:'.$GLOBALS['ADMIN_URL'].'/index.php');
	exit;
}
//判断权限
admin_priv('role_manage');
//load模块菜单
load_menu_module();

$act = ForceStringFrom('act');
switch($act){
	
	case "role_batch":
		if(isset($_POST['deletea_ids'])){
			$del_ids = implode(",",$_POST['deletea_ids']);
			$info = $cls_role->role_batch_no($del_ids);
			if(!empty($info)){
				sys_msg(1,"删除成功",$GLOBALS['ADMIN_URL'].'/role/?act=role_list');
			}
			else{
				sys_msg(0,"删除失败",$GLOBALS['ADMIN_URL'].'/role/?act=role_list');
			}
		}
		break;
	case "role_list":
		$list = $cls_role->role_list();
		$GLOBALS['T']->assign('full_page',  	1);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],1,$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		$GLOBALS['T']->display('admin/role/list.html');
		break;
	case "ajax_role_list":
		$list = $cls_role->role_list();
		$GLOBALS['T']->assign('full_page',  	0);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		make_json_result($GLOBALS['T']->fetch('admin/role/list.html'),0,'',$list['filter']);
		break;
	case "add":
		$action_list = $cls_role->action_list();
		$GLOBALS['T']->assign('action_list',   	$action_list);
		$GLOBALS['T']->display('admin/role/info.html');
		break;
	case "edit":
		$role_id = 	ForceIntFrom('role_id');
		$role_info = $cls_role->role_info($role_id);
		
		if(empty($role_info)){
			sys_msg(0,"角色不存在",$GLOBALS['ADMIN_URL'].'/role/?act=role_list');
		}
		$action_arr = explode(",",$role_info['action_list']);
		$action_list = $cls_role->action_list();
		foreach($action_list as $key=>$value){
			$action_list[$key]['check'] = "";
			if(in_array($value['action_code'], $action_arr)){
				$action_list[$key]['check'] = "checked";
			}
		}
		
		$GLOBALS['T']->assign('action_list',   	$action_list);
		$GLOBALS['T']->assign('info',   	$role_info);
		
		$GLOBALS['T']->display('admin/role/info.html');
	
		break;
	case "do_save":
		$role_id		= 	ForceIntFrom('role_id');
		$role_name		= 	ForceStringFrom('role_name');
		$role_describe	= 	ForceStringFrom('role_describe');
		
	
		
		$act_list 		= @join(",", $_POST['action_code']);

		if(empty($role_name)){
			sys_msg(0,"角色名称不能为空",$GLOBALS['ADMIN_URL'].'/role/?act=add');
		}
		
		$rs = $cls_role->do_role_handle($role_id , $act_list ,$role_name, $role_describe,$admin_id);
		//exit;
		if($rs){
			sys_msg(1,"操作成功",$GLOBALS['ADMIN_URL'].'/role/?act=role_list');
		}
		else{
			sys_msg(0,"操作失败",$GLOBALS['ADMIN_URL'].'/role/?act=add');
		}
		break;
		
	case "user_batch":
		if(isset($_POST['deletea_ids'])){
			$del_ids = implode(",",$_POST['deletea_ids']);
			$info = $cls_role->user_batch_no($del_ids);
			if(!empty($info)){
				sys_msg(1,"删除成功",$GLOBALS['ADMIN_URL'].'/role/?act=user_list');
			}
			else{
				sys_msg(0,"删除失败",$GLOBALS['ADMIN_URL'].'/role/?act=user_list');
			}
		}
		break;
	case "user_list":
		$list = $cls_role->user_list();
		$GLOBALS['T']->assign('full_page',  	1);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],1,$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		$GLOBALS['T']->display('admin/role/user_list.html');
		break;
	case "ajax_user_list":
		$list = $cls_role->user_list();
		$GLOBALS['T']->assign('full_page',  	0);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		make_json_result($GLOBALS['T']->fetch('admin/role/user_list.html'),0,'',$list['filter']);
		break;
	case "user_add":
		$role_list = $cls_role->role_list();
		if($role_list) $GLOBALS['T']->assign('role_list',   	$role_list['list']);
		$GLOBALS['T']->display('admin/role/user_info.html');
		break;
	case "user_edit":
		$user_id = 	ForceIntFrom('id');
		
		if($_SESSION['admin_id'] != 1 && $user_id == 1){
			sys_msg(0,"超级管理账号不能修改",$GLOBALS['ADMIN_URL'].'/role/?act=user_list');
		}
		
		$role_info = $cls_role->user_info($user_id);
		
		if(empty($role_info)){
			sys_msg(0,"管理员不存在",$GLOBALS['ADMIN_URL'].'/role/?act=user_list');
		}
		
		$role_list = $cls_role->role_list();
		if($role_list) $GLOBALS['T']->assign('role_list',   	$role_list['list']);
		
		$GLOBALS['T']->assign('info',   	$role_info);
		
		$GLOBALS['T']->display('admin/role/user_info.html');
	
		break;
	case "do_save_user":
		$user_id		= 	ForceIntFrom('admin_id');
		$role_id		= 	ForceIntFrom('role_id');
		$status		= 	ForceIntFrom('status');
		$admin_name		= 	ForceStringFrom('admin_name');
		$admin_pass	= 	ForceStringFrom('admin_pass');
		
		$link = $user_id > 0 ? $GLOBALS['ADMIN_URL']."/role/?act=user_edit&id=".$user_id : $GLOBALS['ADMIN_URL']."/role/?act=user_add";
		
		if(empty($admin_name)){
			sys_msg(0,"帐号不能为空", $link);
		}
		
		if(empty($admin_pass) && $user_id == 0){
			sys_msg(0,"密码不能为空", $link);
		}
		
		if(empty($role_id)){
			sys_msg(0,"角色不能为空", $link);
		}
		
		if($_SESSION['admin_id'] != 1 && $user_id == 1){
			sys_msg(0,"超级管理账号不能修改", $link);
		}
		
		$rs = $cls_role->check_uniuq($admin_name, $user_id);
		if($rs){
			sys_msg(0,"帐号已经存在", $link);
		}
		
		$info 		= $cls_role->role_info($role_id);
		$act_list = $info['action_list'];
		
		$rs = $cls_role->do_user_handle($user_id , $act_list ,$admin_name, $admin_pass,$admin_id, $role_id, $status);
		//exit;
		if($rs){
			sys_msg(1,"操作成功",$GLOBALS['ADMIN_URL'].'/role/?act=user_list');
		}
		else{
			sys_msg(0,"操作失败",  $link);
		}
		break;
}

?>
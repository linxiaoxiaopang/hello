<?php
ini_set ( 'session.save_path' , dirname ( __FILE__ ) . '/../dirname/' ) ; 
session_start();
require_once '../../init.php';
require_once '../../init_smarty.php';
require_once FRAMEWORK_PATH . '/library/common.lib.php';
require_once FRAMEWORK_PATH . '/library/mysql.class.php';
require_once FRAMEWORK_PATH . '/module/notice.class.php';
load_module_config('account');

$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);

$cls_notice   = new cls_notice();

$admin_id = $_SESSION['admin_id'];

if($admin_id > 0){
	
}else{
	header('location:'.$GLOBALS['ADMIN_URL'].'/index.php?act=login');
	exit;
}
//判断权限
admin_priv('notice_manage');
//load模块菜单
load_menu_module();

$act = $_REQUEST['act'];

//$GLOBALS['T']->assign('session_id', $GLOBALS['S']->session_id);
$GLOBALS['T']->assign('session_id', session_id());

if($_REQUEST['act'] == 'list'){//列表
	
	
    $list = $cls_notice->getList();
    //var_dump($list);
	$GLOBALS['T']->assign('list',           $list['list']);
   	$GLOBALS['T']->assign('filter',  		$list['filter']);
	$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
	$GLOBALS['T']->assign('record_count', 	$list['record_count']);
	$GLOBALS['T']->assign('page_count',   	$list['page_count']);
	$GLOBALS['T']->display('admin/notice/list.html');
	
	
}elseif($_REQUEST['act'] == 'add'){//发送通知
	$GLOBALS['T']->display('admin/notice/info.html');
}elseif($_REQUEST['act'] == 'send'){//发送通知
	
	$info = $_POST['info'];
	$user_tel = $_POST['user_tel'];
	$all_user = $_POST['all_user'];
	if($all_user == 1){//全部会员
		$sql = "SELECT `member_id` FROM `member_login` ";
    	$member_id_arr = $GLOBALS['DB']->get_col($sql);
		foreach($member_id_arr as $member_id){
    		if($member_id>0){
    			$sql_ins = "INSERT INTO `notice_log` SET `uid`='{$member_id}',`info`='{$info}',`add_time`='".time()."'";
				$GLOBALS['DB']->query($sql_ins);
    		}
		}	
	}else{
		$user_tel = str_replace("，",",",$user_tel);
		$user_tel_arr = explode(",",$user_tel);
		
		if($user_tel_arr){
			foreach($user_tel_arr as $v){
				$sql = "SELECT `member_id` FROM `member_login` WHERE `mphone`='{$v}' ";
    			$member_id = $GLOBALS['DB']->get_var($sql);
    			if($member_id>0){
    				$sql_ins = "INSERT INTO `notice_log` SET `uid`='{$member_id}',`info`='{$info}',`add_time`='".time()."'";
					$GLOBALS['DB']->query($sql_ins);
    			}
			}
		}
	}
	echo "<script>window.parent.php_callback('操作成功');setTimeout(function(){window.parent.location='{$GLOBALS['ADMIN_URL']}/notice/?act=list';},1500);</script>";
	//$GLOBALS['T']->display('admin/notice/info.html');
}elseif($_REQUEST['act'] == 'batch'){
	if(isset($_POST['deletea_ids'])){
		$del_ids = implode(",",$_POST['deletea_ids']);
		$sql = "UPDATE notice_log SET status = 3 WHERE id IN({$del_ids})";
		$res = $GLOBALS['DB']->query($sql);
		if($res){
			sys_msg(1,"删除成功",$GLOBALS['ADMIN_URL'].'/notice/?act=list');
		}
		else{
			sys_msg(0,"删除失败",$GLOBALS['ADMIN_URL'].'/notice/?act=list');
		}
	}	
}
?>
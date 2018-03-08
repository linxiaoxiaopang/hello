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
require_once FRAMEWORK_PATH . '/library/memcached.class.php';

load_module_config('account');
//load_module_config('session');

//$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);
//$GLOBALS['MC'] = new Memcache;
//$GLOBALS['MC']->pconnect( $GLOBALS['account_settings']['mc_server']['default']['ip'], $GLOBALS['account_settings']['mc_server']['default']['port']);

$admin_id = $_SESSION['admin_id'];

$cls_admin_member = new cls_admin_member();

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
		//$member_id		= 	ForceIntFrom('member_id');
		$GLOBALS['T']->display('admin/user/user_update.html');
		break;
	case "edit":
		$member_id		= 	ForceIntFrom('member_id');
		$GLOBALS['T']->display('admin/user/user_update.html');
		break;
	case "change_remark_name":
		$member_id		= 	ForceIntFrom('member_id');
		$RemarkName	    = 	ForceStringFrom('RemarkName');
		if($RemarkName == ''){
			die();
		}
		if(empty($member_id)) die();
		$info = $cls_admin_member->change_remark_name($member_id, $RemarkName);
		if(!empty($info)){
			sys_msg_json(1,"操作成功");
		}
		else{
			sys_msg_json(0,"操作成功");
		}
		break;
	case "change_member_status":
		$member_id		= 	ForceIntFrom('member_id');
		$status	= 	ForceIntFrom('status');
		if($status != 1 && $status != 2){
			die();
		}
		if(empty($member_id)) die();
		$info = $cls_admin_member->change_member_status($member_id, $status);
		if(!empty($info)){
			sys_msg_json(1,"操作成功");
		}
		else{
			sys_msg_json(0,"操作成功");
		}
		break;
	case "change_user_rank":
		$member_id		= 	ForceIntFrom('member_id');
		$rank	= 	ForceIntFrom('rank');
		if($rank != 0 && $rank != 3 && $rank != 5){
			die();
		}
		if(empty($member_id)) die();
		$info = $cls_admin_member->change_member_rank($member_id, $rank);
		if(!empty($info)){
			sys_msg_json(1,"操作成功");
		}
		else{
			sys_msg_json(0,"操作成功");
		}
		break;
	case "member_batch":
		if(isset($_POST['deletea_ids'])){
			$del_ids = implode(",",$_POST['deletea_ids']);
			$info = $cls_admin_member->member_batch_no($del_ids);
			if(!empty($info)){
				sys_msg(1,"禁用成功",'/user/?act=member_list');
			}
			else{
				sys_msg(0,"禁用失败",'/user/?act=member_list');
			}
		}else{
			sys_msg(0,"操作失败,请选择禁用会员!",'/user/?act=member_list');
		}
		break;
	case "member_list":
		$list = $cls_admin_member->member_list();
		$GLOBALS['T']->assign('full_page',  	1);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],1,$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		$GLOBALS['T']->display('admin/user/list.html');
		break;
	case "ajax_member_list":
		$list = $cls_admin_member->member_list();
		$GLOBALS['T']->assign('full_page',  	1);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		$GLOBALS['T']->display('admin/user/list.html');
		break;
	case "change_online_status":
		$join_id		= 	ForceIntFrom('join_id');
		$status	= 	ForceIntFrom('status');
		if($status != 1 && $status != 0){
			die();
		}
		if(empty($join_id)) die();
		$info = $cls_admin_member->change_online_status($join_id, $status);
		if(!empty($info)){
			sys_msg_json(1,"操作成功");
		}
		else{
			sys_msg_json(0,"操作成功");
		}
		break;
	case "online_batch":
		if(isset($_POST['deletea_ids'])){
			$del_ids = implode(",",$_POST['deletea_ids']);
			$info = $cls_admin_member->online_batch_no($del_ids);
			if(!empty($info)){
				sys_msg(1,"处理成功",'/user/?act=online_join');
			}
			else{
				sys_msg(0,"处理失败",'/user/?act=online_join');
			}
		}
		break;
	case "online_join":
		$list = $cls_admin_member->online_join_list();
		$GLOBALS['T']->assign('full_page',  	1);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],1,$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		$GLOBALS['T']->display('admin/user/join_list.html');
		break;
	case "ajax_online_list":
		$list = $cls_admin_member->online_join_list();
		$GLOBALS['T']->assign('full_page',  	0);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		make_json_result($GLOBALS['T']->fetch('admin/user/join_list.html'),0,'',$list['filter']);
		break;
	case "user_update":
		$member_id = ForceIntFrom('member_id');
		if($_POST && $member_id > 0){
			//$user_name = $_POST['user_name'];
			//$account = $_POST['account'];
			$remark_name = $_POST['remark_name'];
			$rank = $_POST['rank'];
			$birthday = $_POST['birthday'];
			//$sql = "UPDATE `member_login` SET `account`='{$account}',`user_name`='{$user_name}' WHERE `member_id` ={$member_id}";
			//if($GLOBALS['DB']->query($sql)){
				$sql = "UPDATE `member_base` SET `remark_name`='{$remark_name}',`rank`='{$rank}',`birthday`='{$birthday}' WHERE `member_id` ={$member_id}";
				if($GLOBALS['DB']->query($sql)){
					sys_msg(1,"处理成功",'/user/?act=member_list');
				}else{
					sys_msg(0,"处理失败",'/user/?act=member_list');
				}
			//}else{
				
			//}
			
		}else{
			if($member_id > 0){
				//require_once FRAMEWORK_PATH . '/module/member.class.php';
				//$cls_user = new cls_user();
				//$info = $cls_user->get_user_info($member_id);
				$info = $GLOBALS['DB']->get_row("SELECT * FROM member_login as A,member_base as B WHERE A.member_id = '{$member_id}' and A.member_id = B.member_id ");
    			
				$GLOBALS['T']->assign('info',$info);
				
				$rank_list = $GLOBALS['DB']->get_results("SELECT * FROM member_rank ");
				$GLOBALS['T']->assign('rank_list',$rank_list);
			}
			$GLOBALS['T']->display('admin/user/user_update.html');
		}
		break;
	case "member_rank":
		$list = $GLOBALS['DB']->get_results("SELECT * FROM member_rank ");
		$GLOBALS['T']->assign('list',$list);
		
		$GLOBALS['T']->display('admin/user/rank.html');
		break;
	case "rank_save":
		$rank_id = ForceIntFrom('rank_id');
		$rank = ForceStringFrom('rank');
		$rank_des = ForceStringFrom('rank_des');
		$discount = ForceIntFrom('discount');
		if($rank != '' && $rank_des != '' && $discount >= 0 && $discount <= 100){
			if($rank_id > 0){
				$sql = "UPDATE `member_rank` SET `rank`='{$rank}',`rank_des`='{$rank_des}',`discount`='{$discount}' WHERE `rank_id` ={$rank_id}";
			}else{
				$sql = "INSERT INTO `member_rank` SET `rank`='{$rank}',`rank_des`='{$rank_des}',`discount`='{$discount}' ";
			}
			if($GLOBALS['DB']->query($sql)){
				sys_msg(1,"处理成功",'/user/?act=member_rank');
			}else{
				sys_msg(0,"处理失败",'/user/?act=member_rank');
			}
		}else{
			sys_msg(0,"信息有误！请重新填写",'/user/?act=member_rank');
		}
		break;
	case "rank_del":
		$rank_id = ForceIntFrom('rank_id');
		if($rank_id){
			$mbres = $GLOBALS['DB']->get_results("SELECT * FROM `member_base` WHERE `rank` ={$rank_id} ");
			if($mbres){
				sys_msg(0,"该等级下存在会员，不可删除",'/user/?act=member_rank');
			}else{
				$res = $GLOBALS['DB']->query("DELETE FROM member_rank WHERE `rank_id` ={$rank_id} ");
				if($res){
					sys_msg(1,"处理成功",'/user/?act=member_rank');
				}else{
					sys_msg(0,"处理失败",'/user/?act=member_rank');
				}
			}
		}else{
			sys_msg(0,"信息有误",'/user/?act=member_rank');
		}
		break;
	case "baozhang"://无忧保障
		
		$member_id = ForceIntFrom('member_id');
		$member = $GLOBALS['DB']->get_row("SELECT * FROM `member_login` WHERE `member_id` ={$member_id} ");
		//user_name
		$GLOBALS['T']->assign('member',$member);
		
		//$protection.chuchong_date
		$protection['chuchong_date'] = time();
		$protection['first_yimiao_date'] = time()-86400*366;
		$protection['yimiao_date'] = time();
		$protection['peizhong_date'] = time();
		$GLOBALS['T']->assign('protection',$protection);
		
		$year = date("Y");
		$s_year = $year-10;
		$year_arr = array();
		for($i=$year;$i>=$s_year;$i--){
			$year_arr[] = $i;
		}
		$GLOBALS['T']->assign('year_arr',$year_arr);
		
		
		$GLOBALS['T']->display('admin/user/baozhang.html');
		break;
		
	case "save_baozhang"://无忧保障
		$protection['id'] = ForceIntFrom('id');
		$protection['cat_name'] = ForceStringFrom('cat_name');
		$protection['cat_no'] = ForceStringFrom('cat_no');
		
		$user_name = ForceStringFrom('user_name');
		
		$protection['chuchong_date'] = ForceStringFrom('chuchong_date_y').'-'.ForceStringFrom('chuchong_date_m').'-'.ForceStringFrom('chuchong_date_d');
		if(!ForceStringFrom('chuchong_date_y') || !ForceStringFrom('chuchong_date_m') || !ForceStringFrom('chuchong_date_d')){
			$protection['chuchong_date'] = "0";
		}else{
			$protection['chuchong_date'] = strtotime($protection['chuchong_date']);
		}
		
		$protection['first_yimiao_date'] = ForceStringFrom('first_yimiao_date_y').'-'.ForceStringFrom('first_yimiao_date_m').'-'.ForceStringFrom('first_yimiao_date_d');
		if(!ForceStringFrom('first_yimiao_date_y') || !ForceStringFrom('first_yimiao_date_m') || !ForceStringFrom('first_yimiao_date_d')){
			$protection['first_yimiao_date'] = "0";
		}else{
			$protection['first_yimiao_date'] = strtotime($protection['first_yimiao_date']);
		}
		
		$protection['yimiao_date'] = ForceStringFrom('yimiao_date_y').'-'.ForceStringFrom('yimiao_date_m').'-'.ForceStringFrom('yimiao_date_d');
		if(!ForceStringFrom('yimiao_date_y') || !ForceStringFrom('yimiao_date_m') || !ForceStringFrom('yimiao_date_d')){
			$protection['yimiao_date'] = "0";
		}else{
			$protection['yimiao_date'] = strtotime($protection['yimiao_date']);
		}
		
		$protection['peizhong_date'] = ForceStringFrom('peizhong_date_y').'-'.ForceStringFrom('peizhong_date_m').'-'.ForceStringFrom('peizhong_date_d');
		if(!ForceStringFrom('peizhong_date_y') || !ForceStringFrom('peizhong_date_m') || !ForceStringFrom('peizhong_date_d')){
			$protection['peizhong_date'] = "0";
		}else{
			$protection['peizhong_date'] = strtotime($protection['peizhong_date']);
		}
		
		require_once FRAMEWORK_PATH . '/module/protection.class.php';
		$cls_protection = new cls_protection();
		
		$protection['uid'] = $GLOBALS['DB']->get_var("SELECT `member_id` FROM `member_login` WHERE `user_name` ={$user_name} ");
		
		$res = $cls_protection->protectionUpdate($protection);
		if($res){
			echo "<script>window.parent.php_callback('录入成功成功！');</script>";
		}else{
			echo "<script>window.parent.php_callback('录入成功失败！');</script>";
		}
		break;
}

?>
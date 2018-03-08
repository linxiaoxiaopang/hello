<?php
error_reporting(E_ALL^E_NOTICE);
set_time_limit(0);
require_once '../../www/init.php';
require_once '../../www/init_smarty.php';
require_once FRAMEWORK_PATH . '/library/common.lib.php';
require_once FRAMEWORK_PATH . '/library/fastcgi.lib.php';
require_once FRAMEWORK_PATH . '/library/mysql.class.php';
require_once FRAMEWORK_PATH . '/library/session.class.php';
//require FRAMEWORK_PATH . '/module/user.class.php';
//require FRAMEWORK_PATH . '/module/adminlog.class.php';
require_once FRAMEWORK_PATH . '/module/admin.class.php';
require_once FRAMEWORK_PATH . '/module/news.class.php';
require_once FRAMEWORK_PATH . '/module/operation.class.php';
load_module_config('account');
load_module_config('session');

$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);

$admin_id = $_SESSION['admin_id'];

$cls_admin = new cls_admin();
$cls_news = new cls_news();
$cls_operation = new cls_operation();

if($admin_id > 0){
//	$module_id = 2;
//	$cRes = $cls_admin->competence($admin_id,$module_id);
//	if(!$cRes){
//		$GLOBALS['T']->assign('defaultUrl',"/index.php");
//		$GLOBALS['T']->assign('msg','抱歉，帐号没有开放该权限');
//		$GLOBALS['T']->display('admin/info.html');
//		exit;
//	}
}else{
	header('location: /index.php');
	exit;
}

$admin_menu = $cls_admin->getAdminMenu($_SESSION['admin_id']);
$GLOBALS['T']->assign('admin_menu',  $admin_menu);

$GLOBALS['T']->assign('active_class','会员资金管理');

$_REQUEST['action'] = ($_REQUEST['action']=='')?'news_list':$_REQUEST['action'];

if($_REQUEST['action'] == 'withdrawal_list'){//列表
	
	$module_id = 10;
	$cRes = $cls_admin->competence($admin_id,$module_id);
	if(!$cRes){
		$GLOBALS['T']->assign('defaultUrl',"/index.php");
		$GLOBALS['T']->assign('msg','抱歉，帐号没有开放该权限');
		$GLOBALS['T']->display('admin/info.html');
		exit;
	}
	
	$page = (intval($_GET['page']) < 1)?1:intval($_GET['page']);
	$page_size = 15;
	
	$sqlWhere = '';
	if($_GET['apply_status'] > 0){
		$as = $_GET['apply_status']-1;
		$sqlWhere = " WHERE `apply_status`='{$as}' ";
		$GLOBALS['T']->assign('apply_status',$_GET['apply_status']);
		$page_link = "&apply_status={$_GET['apply_status']}";
	}
	$sum = $GLOBALS['DB']->get_var("SELECT COUNT(*) FROM `cash_apply` $sqlWhere ");
	$page_sum = ceil($sum / $page_size);
	$page = ($page > $page_sum)?$page_sum:$page;
	$start_item = ($page-1)*$page_size;
	
    $withdrawal_list = $GLOBALS['DB']->get_results("SELECT * FROM `cash_apply` $sqlWhere order by `apply_id` DESC limit $start_item,$page_size ");
    //var_dump($withdrawal_list);
    
    $pageHtmlNum = 7;//最大显示页数 最好单数
	$link = 'withdrawal.php?action=withdrawal_list'.$page_link.'&';
	$page_html = getPageHtml($page,$page_sum,$pageHtmlNum,$link);
    
	$GLOBALS['T']->assign('result',$withdrawal_list);
	
	$GLOBALS['T']->assign('page_html',$page_html);
	$GLOBALS['T']->assign('action',$_REQUEST['action']);
	
	//var_dump($withdrawal_list);
	
	$GLOBALS['T']->assign('active_class_open','提现管理');
	$GLOBALS['T']->display('admin/user_fund/withdrawal_list.html');
	
}elseif($_REQUEST['action'] == 'status'){//
	
	$module_id = 10;
	$cRes = $cls_admin->competence($admin_id,$module_id);
	if(!$cRes){
		$GLOBALS['T']->assign('defaultUrl',"/index.php");
		$GLOBALS['T']->assign('msg','抱歉，帐号没有开放该权限');
		$GLOBALS['T']->display('admin/info.html');
		exit;
	}
	
	$apply_id = $_POST['apply_id'];
	$status = $_POST['status'];
	if($apply_id > 0){
		$GLOBALS['DB']->autocommit(false);
		if($GLOBALS['DB']->query("UPDATE `cash_apply` SET `apply_status`='{$status}' WHERE `apply_id`='{$apply_id}'")){
			$log_type = 6;//1登录  2退出  3系统管理  4订单管理  5会员资金管理  6开奖资金管理  7合买管理  8资讯管理  9会员管理
			$log_bind = $oid;
			$admin_id = $_SESSION['admin_id'];
			$remark = "提醒申请状态修改$status";
			$cls_operation->addOperationLog($log_type,$log_bind,$admin_id,$remark);
			$GLOBALS['DB']->commit();
		}else{
			$GLOBALS['DB']->rollback();
		}
	}
	//header('location: index.php');
}elseif($_REQUEST['action'] == 'single'){//single
	
	$module_id = 12;
	$cRes = $cls_admin->competence($admin_id,$module_id);
	if(!$cRes){
		$GLOBALS['T']->assign('defaultUrl',"/index.php");
		$GLOBALS['T']->assign('msg','抱歉，帐号没有开放该权限');
		$GLOBALS['T']->display('admin/info.html');
		exit;
	}
	
	$page = (intval($_GET['page']) < 1)?1:intval($_GET['page']);
	$page_size = 15;
	
	$sum = $GLOBALS['DB']->get_var("SELECT COUNT(*) FROM `cash_apply` WHERE `apply_status`=5 ");
	$page_sum = ceil($sum / $page_size);
	$page = ($page > $page_sum)?$page_sum:$page;
	$start_item = ($page-1)*$page_size;
	
    $withdrawal_list = $GLOBALS['DB']->get_results("SELECT * FROM `cash_apply` WHERE `apply_status`=5 order by `apply_id` DESC limit $start_item,$page_size ");
    
    $pageHtmlNum = 7;//最大显示页数 最好单数
	$link = 'withdrawal.php?action=single_list'.$page_link.'&';
	$page_html = getPageHtml($page,$page_sum,$pageHtmlNum,$link);
    
	$GLOBALS['T']->assign('result',$withdrawal_list);
	
	$GLOBALS['T']->assign('page_html',$page_html);
	$GLOBALS['T']->assign('action',$_REQUEST['action']);
	
	$GLOBALS['T']->assign('active_class_open','提现出单');
	$GLOBALS['T']->display('admin/user_fund/single_list.html');
}elseif($_REQUEST['action'] == 'do_single'){//
	
	$module_id = 12;
	$cRes = $cls_admin->competence($admin_id,$module_id);
	if(!$cRes){
		$GLOBALS['T']->assign('defaultUrl',"/index.php");
		$GLOBALS['T']->assign('msg','抱歉，帐号没有开放该权限');
		$GLOBALS['T']->display('admin/info.html');
		exit;
	}
	
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	
	$apply_id = $_REQUEST['apply_id'];
	
	$cash_apply = $GLOBALS['DB']->get_row("SELECT * FROM `cash_apply`,`user_login`,`user_base` WHERE `cash_apply`.`apply_id`='{$apply_id}' and `user_login`.`uid`=`cash_apply`.`uid` and `user_base`.`uid`=`cash_apply`.`uid` ");
	
	$GLOBALS['T']->assign('cash_apply',$cash_apply);
	
	$adminName = $GLOBALS['DB']->get_var("SELECT `admin_name` FROM `admin_account` WHERE `admin_id`='{$admin_id}' ");
	$GLOBALS['T']->assign('adminName',$adminName);
	$GLOBALS['T']->assign('ndate',date('Y-m-d H-i-s'));
	
	$GLOBALS['T']->assign('active_class_open','提现出单');
	$GLOBALS['T']->display('admin/user_fund/single_info.html');
	
}elseif($_REQUEST['action'] == 'funds_flow'){
	
	$module_id = 9;
	$cRes = $cls_admin->competence($admin_id,$module_id);
	if(!$cRes){
		$GLOBALS['T']->assign('defaultUrl',"/index.php");
		$GLOBALS['T']->assign('msg','抱歉，帐号没有开放该权限');
		$GLOBALS['T']->display('admin/info.html');
		exit;
	}
	
	if($_REQUEST['trade_date'] != ''){
		$date = $_REQUEST['trade_date'];
		$date = explode(' - ',$date);
		$sdate = $date[0];
		$edate = $date[1];
		$sqlWhere .= " and `trade_date`>'{$sdate}' and `trade_date`<'{$edate}' ";
		$GLOBALS['T']->assign('sdate',$sdate);
		$GLOBALS['T']->assign('edate',$edate);
	}else{
		$sdate = $_REQUEST['sdate'];
		$edate = $_REQUEST['edate'];
		if($sdate != ''){
			$sqlWhere = " and `trade_date`>'{$sdate}' ";
			$GLOBALS['T']->assign('sdate',$sdate);
		}
		if($edate != ''){
			$sqlWhere .= " and `trade_date`<'{$edate}' ";
			$GLOBALS['T']->assign('edate',$edate);
		}
	}
	
	$account_fund = $GLOBALS['DB']->get_row("SELECT sum(`fund`) as f,sum(`blocked_fund`) as bf FROM `account_fund` ");
	$GLOBALS['T']->assign('account_fund',$account_fund);

	$recharge = $GLOBALS['DB']->get_var("SELECT sum(`amount`) FROM `account_log` WHERE `trade_detail`=1 $sqlWhere ");
	$recharge = $recharge?$recharge:0;
	$GLOBALS['T']->assign('recharge',$recharge);
	
	$withdrawal = $GLOBALS['DB']->get_var("SELECT sum(`amount`) FROM `account_log` WHERE `trade_detail`=9 $sqlWhere ");
	$withdrawal = $withdrawal?$withdrawal:0;
	$GLOBALS['T']->assign('withdrawal',$withdrawal);
	
	$consume = $GLOBALS['DB']->get_var("SELECT sum(`amount`) FROM `account_log` WHERE (`trade_detail`=6 or `trade_detail`=10) $sqlWhere ");
	$consume = $consume?$consume:0;
	$GLOBALS['T']->assign('consume',$consume);
	
	require FRAMEWORK_PATH . '/module/interface_base.class.php';
	$cls_interface_base = new cls_interface_base($GLOBALS['DB']);
	
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	echo '<div style="display:none;">';
	$AccountBalance = $cls_interface_base->getAccountBalance();
	echo '</div>';
	
	$GLOBALS['T']->assign('AccountBalance',$AccountBalance);
	//$regex = '/<account amount="(.*)"><\/account>/U';
	//preg_match_all($regex,$AccountBalance,$res);
	//var_dump($res);
	
	$GLOBALS['T']->assign('active_class_open','会员流水');
	$GLOBALS['T']->display('admin/user_fund/funds_flow.html');
}



function getPageHtml($page,$pageSum,$pageHtmlNum,$link){
	
	$pageHtmlMid = ceil($pageHtmlNum/2);
	
	if($page <= $pageHtmlMid){
		$starPage = 1;
		if($pageSum > $page){
			if($pageSum >= $pageHtmlNum){
				$endPage = $pageHtmlNum;
			}else{
				$endPage = $pageSum;
			}
		}else{
			$endPage = $page;
		}
	}else{
		if($pageSum > $pageHtmlNum){
			if(($pageSum-$page)>=$pageHtmlMid){
				$starPage = $page-$pageHtmlMid+1;
				$endPage = $page+$pageHtmlMid-1;
			}else{
				$starPage = $pageSum-$pageHtmlNum+1;
				$endPage = $pageSum;
			}
		}else{
			$starPage = 1;
			$endPage = $pageSum;
		}
	}
	
	$page_html = '<ul><li><a href="'.$link.'page=1">首页</a></li>';
	for($i=$starPage;$i<=$endPage;$i++){
		if($i==$page){
			$page_html .= '<li class="active"><a href="javascript:;">'.$i.'</a></li>';
		}else{
			$page_html .= '<li><a href="'.$link.'page='.$i.'">'.$i.'</a></li>';
		}
	}
	$page_html .= '<li><a href="'.$link.'page='.$pageSum.'">尾页</a></li></ul>';
	
	return $page_html;
	
}




?>
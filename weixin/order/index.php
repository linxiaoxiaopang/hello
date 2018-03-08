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
require_once FRAMEWORK_PATH . '/module/account.class.php';
require_once FRAMEWORK_PATH . '/module/account_log.class.php';
require_once FRAMEWORK_PATH . '/module/order.class.php';
require_once FRAMEWORK_PATH . '/module/admin_order.class.php';

load_module_config('account');
//load_module_config('session');

//$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);

$admin_id = $_SESSION['admin_id'];

$cls_order = new cls_order($GLOBALS['DB']);
$cls_admin_order = new cls_admin_order();

if($admin_id > 0){
	
}else{
	header('location: /index.php?act=login');
	exit;
}

//判断权限
admin_priv('order_manage');
//load模块菜单
load_menu_module();

$act = $_REQUEST['act'];

if($act == 'list'){
	$model = $_REQUEST['model'];
	if($model == 2){
		$act = 'get_order_csv';
	}
}

switch($act){

	case "item_pic":
		$item_id = ForceIntFrom('id');
		$info = $GLOBALS['DB']->get_row("SELECT B.* FROM order_base A,order_item B WHERE A.id=B.order_id AND B.id={$item_id} LIMIT 1");
		if($info){
			$GLOBALS['T']->assign('info',$info);
			
			if($info['pic_more']){
				$pic_more = json_decode($info['pic_more'],true);
			}
			$GLOBALS['T']->assign('pic_more',$pic_more);
			
			$GLOBALS['T']->assign('session_id', session_id());
			
			$GLOBALS['T']->display('admin/order/item_pic.html');
		}else{
			header("location:{$GLOBALS['ADMIN_URL']}/order/?act=list");
			exit;
		}
		break;
	case "save_item_pic":
		
		$item_id = ForceIntFrom('item_id');
		
		$pic_arr = array();
		if(isset($_POST['multiImg'])) $pic_arr = $_POST['multiImg'];
		if($pic_arr){
			$pic_list =array();
			foreach($pic_arr as $k=>$v){
				$pic_list[$k]['pic'] = $v;
				$pic_list[$k]['date'] = $_POST['pic_date'][$k];
			}
			
			$pic_more = json_encode($pic_list);
			$sql = "UPDATE `order_item` SET `pic_more`='{$pic_more}' WHERE `id`={$item_id} ";
			$GLOBALS['DB']->query($sql);//修改
		}
		
		//$order_id = $GLOBALS['DB']->get_var("SELECT order_id FROM order_item WHERE id={$item_id} LIMIT 1");
		
		//header("location:{$GLOBALS['ADMIN_URL']}/order/?act=order_shipping&ord_id={$order_id}");
		echo "<script>window.parent.php_callback('操作成功');setTimeout(function(){window.parent.history.go(-2);},1500);</script>";
		
		break;
	case "ajax_order_list":
		$list = $cls_admin_order->order_list();
		$order_list = $list['list'];
		if($order_list){
			foreach($order_list as $key=>$value){
				$order_list[$key]['items'] = $cls_order->order_item_list($value['id']);
			}
		}
		$GLOBALS['T']->assign('full_page',  	0);
		$GLOBALS['T']->assign('list',  			$order_list);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		make_json_result($GLOBALS['T']->fetch('admin/order/list.html'),0,'',$list['filter']);
		break;
		
		
	case "list": //订单列表
		$list = $cls_admin_order->order_list();
		$order_list = $list['list'];
		if($order_list){
			foreach($order_list as $key=>$value){
				$order_list[$key]['items'] = $cls_order->order_item_list($value['id']);
			}
		}
		if(isset($_GET['shipping_status'])){
			$GLOBALS['T']->assign('shipping_status',  	intval($_GET['shipping_status'])+1);
		}
		$GLOBALS['T']->assign('full_page',  	1);
		$GLOBALS['T']->assign('list',  			$order_list);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		$GLOBALS['T']->display('admin/order/list.html');
		break;
	case "ajax_menu_list":
		$list = $cls_admin_order->mymenu_order_list();
		$order_list = $list['list'];
		if($order_list){
			foreach($order_list as $key=>$value){
				$order_list[$key]['items'] = $cls_order->mymenu_order_item_list($value['id']);
			}
		}
		$GLOBALS['T']->assign('full_page',  	0);
		$GLOBALS['T']->assign('list',  			$order_list);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		make_json_result($GLOBALS['T']->fetch('admin/order/menu_list.html'),0,'',$list['filter']);
		break;
		
		
	case "menu_list": //订单列表
		$list = $cls_order->admin_mymenu_order_list();
		$order_list = $list['list'];
		if($order_list){
			foreach($order_list as $key=>$value){
				$order_list[$key]['items'] = $cls_order->mymenu_order_item_list($value['id']);
			}
		}
		if(isset($_GET['shipping_status'])){
			$GLOBALS['T']->assign('shipping_status',  	intval($_GET['shipping_status'])+1);
		}
		$GLOBALS['T']->assign('full_page',  	1);
		$GLOBALS['T']->assign('list',  			$order_list);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		$GLOBALS['T']->display('admin/order/menu_list.html');
		break;
		
	case "add_shipping_info":
		$order_id 	 = ForceStringFrom('order_id');
		$deliver_id 	 = ForceStringFrom('deliver_id');
		$deliver_compony 	 = ForceStringFrom('deliver_compony');
		$deliver_no 	 = ForceStringFrom('deliver_no');
		
		if(empty($order_id)){
			sys_msg(2,'订单ID为空','/order/?act=order_shipping&ord_id='.$order_id);
		}
		if(empty($deliver_compony)){
			sys_msg(2,'请选择物流公司','/order/?act=order_shipping&ord_id='.$order_id);
		}
		if(empty($deliver_no)){
			sys_msg(2,'请填写物流单号','/order/?act=order_shipping&ord_id='.$order_id);
		}
		
		$rs = $cls_admin_order->order_deliver_handle($deliver_id,$order_id,$deliver_compony, $deliver_no);
		//print_r($rs);
		sys_msg(1,'成功设置已发货','/order/?act=order_shipping&ord_id='.$order_id);
		break;
	
	//配货单
	case "order_shipping":
		$order_id 	 = ForceIntFrom('ord_id');
		if(empty($order_id)){
			header("location:/");exit;
		}
		$order_list = $cls_order->order_item_list($order_id);
		if(empty($order_list)){
			header("location:/");exit;
		}
		
		foreach($order_list as $k=>$v){
			if($v['pic_more']){
				$order_list[$k]['pic_more'] = json_decode($v['pic_more'],true);
			}
		}
		
		$GLOBALS['T']->assign('full_page',  	1);
		if(isset($_GET['print'])){
			$GLOBALS['T']->assign('print',  		1);
		}
		
		$order_info = $cls_admin_order->order_info($order_id);
		$GLOBALS['T']->assign('order_info',  		$order_info);
		
		if($order_info['order_time'] > 0 && $order_info['pay_status'] == 1){
			$day_num = floor((time()-$order_info['order_time'])/86400);
		}else{
			$day_num = 0;
		}
		if($order_list){
			foreach($order_list as $k=>$v){
				if($day_num){
					$order_list[$k]['mature_day'] = $v['mature_time']-$day_num;
				}else{
					$order_list[$k]['mature_day'] = $v['mature_time'];
				}
			}
		}
		
		$GLOBALS['T']->assign('list',  			$order_list);
		$deliver_info = $cls_admin_order->deliver_info($order_id);
		$GLOBALS['T']->assign('deliver_info',  			$deliver_info);
		$order_log = $cls_admin_order->order_log($order_id);
		$GLOBALS['T']->assign('order_log',  		$order_log);
		
		//$shipping_list = $cls_admin_order->shipping_list();
		
		$shipping_lists = "申通-EMS-顺丰-圆通-中通-如风达-韵达-天天-汇通-全峰-德邦-宅急送-安信达-包裹平邮-邦送物流-DHL快递-大田物流-德邦物流-EMS国内-EMS国际-E邮宝-凡客配送-国通快递-挂号信-共速达-国际小包-汇通快递-华宇物流-汇强快递-佳吉快运-佳怡物流-加拿大邮政-快捷速递-龙邦速递-联邦快递-联昊通-能达速递-如风达-瑞典邮政-全一快递-全峰快递-全日通-申通快递-顺丰快递-速尔快递-TNT快递-天天快递-天地华宇-UPS快递-新邦物流-新蛋物流-香港邮政-圆通快递-韵达快递-邮政包裹-优速快递-中通快递-中铁快运-宅急送-中邮物流"; 
		
		$shipping_list = explode("-", $shipping_lists);
		
		$GLOBALS['T']->assign('shipping_list',  			$shipping_list);
		
		$GLOBALS['T']->assign('select_full',  	1);
		$GLOBALS['T']->display('admin/order/order_list_info.html');
		break;
	//配货单
	case "mune_order_info":
		$order_id = ForceIntFrom('oid');
		$sql = "SELECT A.*,B.account,C.* FROM `menu_order` AS A,member_login AS B,member_base AS C WHERE A.`id`={$order_id} AND A.member_id=B.member_id AND B.member_id=C.member_id ";
		$menu_order = $GLOBALS['DB']->get_row($sql);
		$sql = "SELECT * FROM `menu_order_item` WHERE `mo_id`={$menu_order['id']} ";
		$list = $GLOBALS['DB']->get_results($sql);
		
		if(empty($list)){
			header("location:/");exit;
		}
		$GLOBALS['T']->assign('order_info',  		$menu_order);
		$GLOBALS['T']->assign('list',  			$list);
		
		$sql = "SELECT * FROM menu_order_deliver WHERE order_id = '{$order_id}' LIMIT 1";
		$deliver_info = $GLOBALS['DB']->get_row($sql);
		$GLOBALS['T']->assign('deliver_info',  			$deliver_info);
		
		$GLOBALS['T']->display('admin/order/menu_info.html');
		break;
	case "company"://修改快递公司
		$order_id 	 = $_POST['oid'];
		$value = $_POST['value'];
		$time = time();
		$row = "SELECT * FROM order_deliver WHERE order_id = '{$order_id}'";
		$info = $GLOBALS['DB']->get_row($row);
		if($info){
			$sql = "UPDATE `order_deliver` SET `company`='{$value}' WHERE `order_id`='{$order_id}'";
		}
		else{
			$sql = "INSERT INTO `order_deliver` SET `company`='{$value}' , express_num = '',`order_id`='{$order_id}', send_time = '".$time."', update_time = '".$time."'";
		}
		$GLOBALS['DB']->query($sql);
		break;
	case "express_num"://修改快递单号
		$order_id 	 = $_POST['oid'];
		$value = $_POST['value'];
		$time = time();
		$row = "SELECT * FROM order_deliver WHERE order_id = '{$order_id}'";
		$info = $GLOBALS['DB']->get_row($row);
		if($info){
			$sql = "UPDATE `order_deliver` SET `express_num`='{$value}' WHERE `order_id`='{$order_id}'";
		}
		else{
			$sql = "INSERT INTO `order_deliver` SET `company`='' , express_num = '{$value}',`order_id`='{$order_id}', send_time = '".$time."', update_time = '".$time."'";
		}
		//$sql = "UPDATE `goods_deliver` SET `company`='{$value}' WHERE `order_item_id`='{$order_id}'";
		$GLOBALS['DB']->query($sql);
		break;
	case "aName"://修改收货人
		$order_id 	 = $_POST['oid'];
		$value = $_POST['value'];
		if($value != ''){
			$sql = "UPDATE `order_base` SET `s_name`='{$value}' WHERE `id`='{$order_id}'";
			$GLOBALS['DB']->query($sql);
		}
		break;
	case "aTel"://修改收货电话
		$order_id 	 = $_POST['oid'];
		$value = $_POST['value'];
		if($value != ''){
			$sql = "UPDATE `order_base` SET `s_tel`='{$value}' WHERE `id`='{$order_id}'";
			$GLOBALS['DB']->query($sql);
		}
		break;
	case "aAddress"://修改收货地址
		$order_id 	 = $_POST['oid'];
		$Aprovince = $_POST['Aprovince'];
		$Acity = $_POST['Acity'];
		$Aarea = $_POST['Aarea'];
		$Aaddress = $_POST['Aaddress'];
		$Azip = $_POST['Azip'];
		//$address_id = $GLOBALS['DB']->get_var("SELECT `address_id` FROM `order_base` WHERE `id`='{$order_id}' ");
		$order_base = $cls_admin_order->order_info($order_id);
		if($Aprovince != ''){
			$order_base['s_province'] = $Aprovince;
			$sqlSet = " `s_province`='{$Aprovince}' ";
		}
		if($Acity != ''){
			$order_base['s_city'] = $Acity;
			$sqlSet = ($sqlSet == '')?" `s_city`='{$Acity}' ":$sqlSet." ,`s_city`='{$Acity}' ";
		}
		if($Aarea != ''){
			$order_base['s_area'] = $Aarea;
			$sqlSet = ($sqlSet == '')?" `s_area`='{$Aarea}' ":$sqlSet." ,`s_area`='{$Aarea}' ";
		}
		if($Aaddress != ''){
			$order_base['s_address'] = $Aaddress;
			$sqlSet = ($sqlSet == '')?" `s_address`='{$Aaddress}' ":$sqlSet." ,`s_address`='{$Aaddress}' ";
		}
		if($Azip != ''){
			$order_base['s_zip'] = $Azip;
			$sqlSet = ($sqlSet == '')?" `s_zip`='{$Azip}' ":$sqlSet." ,`s_zip`='{$Azip}' ";
		}
		if($sqlSet != ''){
			$sql = "UPDATE `order_base` SET $sqlSet WHERE `id`='{$order_id}'";
			$GLOBALS['DB']->query($sql);
		}
		break;
	case "m_company"://修改快递公司
		$order_id 	 = $_POST['oid'];
		$value = $_POST['value'];
		$time = time();
		$row = "SELECT * FROM menu_order_deliver WHERE order_id = '{$order_id}'";
		$info = $GLOBALS['DB']->get_row($row);
		if($info){
			$sql = "UPDATE `menu_order_deliver` SET `company`='{$value}' WHERE `order_id`='{$order_id}'";
		}
		else{
			$sql = "INSERT INTO `menu_order_deliver` SET `company`='{$value}' , express_num = '',`order_id`='{$order_id}', send_time = '".$time."', update_time = '".$time."'";
		}
		$GLOBALS['DB']->query($sql);
		break;
	case "m_express_num"://修改快递单号
		$order_id 	 = $_POST['oid'];
		$value = $_POST['value'];
		$time = time();
		$row = "SELECT * FROM menu_order_deliver WHERE order_id = '{$order_id}'";
		$info = $GLOBALS['DB']->get_row($row);
		if($info){
			$sql = "UPDATE `menu_order_deliver` SET `express_num`='{$value}' WHERE `order_id`='{$order_id}'";
		}
		else{
			$sql = "INSERT INTO `menu_order_deliver` SET `company`='' , express_num = '{$value}',`order_id`='{$order_id}', send_time = '".$time."', update_time = '".$time."'";
		}
		$GLOBALS['DB']->query($sql);
		break;
	case "m_aName"://修改收货人
		$order_id 	 = $_POST['oid'];
		$value = $_POST['value'];
		if($value != ''){
			$sql = "UPDATE `menu_order` SET `s_name`='{$value}' WHERE `id`='{$order_id}'";
			$GLOBALS['DB']->query($sql);
		}
		break;
	case "m_aTel"://修改收货电话
		$order_id 	 = $_POST['oid'];
		$value = $_POST['value'];
		if($value != ''){
			$sql = "UPDATE `menu_order` SET `s_tel`='{$value}' WHERE `id`='{$order_id}'";
			$GLOBALS['DB']->query($sql);
		}
		break;
	case "m_aAddress"://修改收货地址
		$order_id 	 = $_POST['oid'];
		$Acity = $_POST['Acity'];
		$Aaddress = $_POST['Aaddress'];
		$order_base = $cls_admin_order->order_info($order_id);
		if($Acity != ''){
			$order_base['s_city'] = $Acity;
			$sqlSet = ($sqlSet == '')?" `s_city`='{$Acity}' ":$sqlSet." ,`s_city`='{$Acity}' ";
		}
		if($Aaddress != ''){
			$order_base['s_address'] = $Aaddress;
			$sqlSet = ($sqlSet == '')?" `s_address`='{$Aaddress}' ":$sqlSet." ,`s_address`='{$Aaddress}' ";
		}
		if($sqlSet != ''){
			$sql = "UPDATE `menu_order` SET $sqlSet WHERE `id`='{$order_id}'";
			$GLOBALS['DB']->query($sql);
		}
		break;
	case "invoice"://修改发票信息
		$order_id 	 = $_POST['oid'];
		$invoice_type = $_POST['invoice_type'];
		$invoice_info = $_POST['invoice_info'];
		$order_base = $cls_admin_order->order_info($order_id);
		if($invoice_type != ''){
//			if($invoice_type == 0){
//				$order_base['invoice_type'] = 'N';
//			}else{
//				$order_base['invoice_type'] = 'Y';
//			}
			$sqlSet = " `invoice_type`='{$invoice_type}' ";
		}
		if($invoice_info != ''){
			$order_base['invoice_info'] = $invoice_info;
			$sqlSet = ($sqlSet == '')?" `invoice_info`='{$invoice_info}' ":$sqlSet." ,`invoice_info`='{$invoice_info}' ";
		}
		if($sqlSet != ''){
			$wms_oid = $GLOBALS['DB']->get_var("SELECT `wms_oid` FROM `wms_order_relation` WHERE `order_id`='{$order_id}' ");
			$order_base['wms_oid'] = $wms_oid;
			if($wms_oid > 0){
				require_once FRAMEWORK_PATH . '/library/rest.class.php';
				require_once FRAMEWORK_PATH . '/module/wms.class.php';
				$cls_wms = new cls_wms();
				$wms_res = $cls_wms->UpdateSalesOrder($order_base);
				if($wms_res){
					$sql = "UPDATE `order_base` SET $sqlSet WHERE `id`='{$order_id}'";
					$GLOBALS['DB']->query($sql);
				}
			}else{
				$sql = "UPDATE `order_base` SET $sqlSet WHERE `id`='{$order_id}'";
				$GLOBALS['DB']->query($sql);
			}
		}
		break;
	case "deliver_fee"://修改邮费
		$order_id 	 = $_POST['oid'];
		$value = $_POST['value'];
		$order_base = $cls_admin_order->order_info($order_id);
		if($value != '' && $value >= 0 && $order_base['pay_status'] == 0){
			$order_base2 = $GLOBALS['DB']->get_row("SELECT `deliver_fee`,`total_price` FROM `order_base` WHERE `id`='{$order_id}' ");
			$deliver_fee = $order_base2['deliver_fee'];
			$total_price = $order_base2['total_price'];
			if($value > $deliver_fee){
				$total_price = $total_price+($value-$deliver_fee);
				if($total_price > 0){
					$wms_oid = $GLOBALS['DB']->get_var("SELECT `wms_oid` FROM `wms_order_relation` WHERE `order_id`='{$order_id}' ");
					$order_base['wms_oid'] = $wms_oid;
					$order_base['total_price'] = $total_price;
					if($wms_oid > 0){
						require_once FRAMEWORK_PATH . '/library/rest.class.php';
						require_once FRAMEWORK_PATH . '/module/wms.class.php';
						$cls_wms = new cls_wms();
						$wms_res = $cls_wms->UpdateSalesOrder($order_base);
						if($wms_res){
							$sql = "UPDATE `order_base` SET `deliver_fee`='{$value}',`total_price`='{$total_price}' WHERE `id`='{$order_id}'";
							$GLOBALS['DB']->query($sql);
						}
					}else{
						$sql = "UPDATE `order_base` SET `deliver_fee`='{$value}',`total_price`='{$total_price}' WHERE `id`='{$order_id}'";
						$GLOBALS['DB']->query($sql);
					}
					echo $total_price;
				}
			}elseif($value < $deliver_fee){
				$total_price = $total_price-($deliver_fee-$value);
				if($total_price > 0){
					$wms_oid = $GLOBALS['DB']->get_var("SELECT `wms_oid` FROM `wms_order_relation` WHERE `order_id`='{$order_id}' ");
					$order_base['wms_oid'] = $wms_oid;
					$order_base['total_price'] = $total_price;
					if($wms_oid > 0){
						require_once FRAMEWORK_PATH . '/library/rest.class.php';
						require_once FRAMEWORK_PATH . '/module/wms.class.php';
						$cls_wms = new cls_wms();
						$wms_res = $cls_wms->UpdateSalesOrder($order_base);
						if($wms_res){
							$sql = "UPDATE `order_base` SET `deliver_fee`='{$value}',`total_price`='{$total_price}' WHERE `id`='{$order_id}'";
							$GLOBALS['DB']->query($sql);
						}
					}else{
						$sql = "UPDATE `order_base` SET `deliver_fee`='{$value}',`total_price`='{$total_price}' WHERE `id`='{$order_id}'";
						$GLOBALS['DB']->query($sql);
					}
					echo $total_price;
				}
			}else{
				echo $total_price;
			}
		}
		break;
	case "price"://修改商品价格
		$order_id 	 = $_POST['oid'];
		$itemID 	 = $_POST['itemID'];
		$value = $_POST['value'];
		$wms_oid = $GLOBALS['DB']->get_var("SELECT `wms_oid` FROM `wms_order_relation` WHERE `order_id`='{$order_id}' ");
		if($wms_oid > 0){
			$total_price = $GLOBALS['DB']->get_var("SELECT `total_price` FROM `order_base` WHERE `id`='{$order_id}' ");
			echo $total_price;
		}else{
			$order_base = $cls_admin_order->order_info($order_id);
			if($value != '' && $value >= 0){
				$total_price = $GLOBALS['DB']->get_var("SELECT `total_price` FROM `order_base` WHERE `id`='{$order_id}' ");
				$item_price = $GLOBALS['DB']->get_var("SELECT `price` FROM `order_item` WHERE `id`='{$itemID}' ");
				if($value > $item_price){
					$total_price = $total_price+($value-$item_price);
					if($total_price > 0){
						$sql = "UPDATE `order_base` SET `total_price`='{$total_price}' WHERE `id`='{$order_id}' ";
						$GLOBALS['DB']->query($sql);
						$sql = "UPDATE `order_item` SET `price`='{$value}' WHERE `id`='{$itemID}' ";
						$GLOBALS['DB']->query($sql);
						echo $total_price;
					}
				}elseif($value < $item_price){
					$total_price = $total_price-($item_price-$value);
					if($total_price > 0){
						$sql = "UPDATE `order_base` SET `total_price`='{$total_price}' WHERE `id`='{$order_id}' ";
						$GLOBALS['DB']->query($sql);
						$sql = "UPDATE `order_item` SET `price`='{$value}' WHERE `id`='{$itemID}' ";
						$GLOBALS['DB']->query($sql);
						echo $total_price;
					}
				}else{
					echo $total_price;
				}
			}
		}
		break;
	case "num"://修改商品数量
		$order_id 	 = $_POST['oid'];
		$itemID 	 = $_POST['itemID'];
		$value = $_POST['value'];
		$wms_oid = $GLOBALS['DB']->get_var("SELECT `wms_oid` FROM `wms_order_relation` WHERE `order_id`='{$order_id}' ");
		if($wms_oid > 0){
			$total_price = $GLOBALS['DB']->get_var("SELECT `total_price` FROM `order_base` WHERE `id`='{$order_id}' ");
			echo $total_price;
		}else{
			if($value != '' && $value > 0){
				$total_price = $GLOBALS['DB']->get_var("SELECT `total_price` FROM `order_base` WHERE `id`='{$order_id}' ");
				$order_item = $GLOBALS['DB']->get_row("SELECT `num`,`price` FROM `order_item` WHERE `id`='{$itemID}' ");
				$num = $order_item['num'];
				$price = $order_item['price'];
				$Nprice = ($value/$num)*$price;
				if($value > $num){
					$total_price = $total_price+($Nprice-$price);
					if($total_price > 0){
						$sql = "UPDATE `order_base` SET `total_price`='{$total_price}' WHERE `id`='{$order_id}' ";
						$GLOBALS['DB']->query($sql);
						$sql = "UPDATE `order_item` SET `price`='{$Nprice}',`num`='{$value}' WHERE `id`='{$itemID}' ";
						$GLOBALS['DB']->query($sql);
						echo $total_price;
					}
				}elseif($value < $num){
					$total_price = $total_price-($price-$Nprice);
					if($total_price > 0){
						$sql = "UPDATE `order_base` SET `total_price`='{$total_price}' WHERE `id`='{$order_id}' ";
						$GLOBALS['DB']->query($sql);
						$sql = "UPDATE `order_item` SET `price`='{$Nprice}',`num`='{$value}' WHERE `id`='{$itemID}' ";
						$GLOBALS['DB']->query($sql);
						echo $total_price;
					}
				}else{
					echo $total_price;
				}
			}
		}
		break;
	case "order_status"://修改订单状态
		$order_id 	 = $_POST['oid'];
		$status = $_POST['status'];
		if($status == 1 || $status == 2 || $status == 3){
			$sql = "UPDATE `order_base` SET `order_status`='{$status}' WHERE `id`='{$order_id}'";
			$GLOBALS['DB']->query($sql);
		}
		break;
	case "pay_status"://修改支付状态
		$order_id 	 = $_POST['oid'];
		$status = $_POST['status'];
		if($status == 0 || $status == 1){
			$sql = "UPDATE `order_base` SET `pay_status`='{$status}' WHERE `id`='{$order_id}'";
			$GLOBALS['DB']->query($sql);
		}
		break;
	case "shipping_status"://修改配送状态
		$order_id 	 = $_POST['oid'];
		$status = $_POST['status'];
		if($status == 0 || $status == 1 || $status == 2){
			$sql = "UPDATE `order_base` SET `shipping_status`='{$status}' WHERE `id`='{$order_id}'";
			$GLOBALS['DB']->query($sql);
		}
		break;
	case "m_shipping_status"://修改配送状态
		$order_id 	 = $_POST['oid'];
		$status = $_POST['status'];
		if($status == 0 || $status == 1 || $status == 2){
			$sql = "UPDATE `menu_order` SET `shipping_status`='{$status}' WHERE `id`='{$order_id}'";
			$GLOBALS['DB']->query($sql);
		}
		break;
	case "zz_save"://保存 种植/养殖信息
		
		$item_id = ForceIntFrom('item_id');
		
		if($item_id > 0){
			
			$zz_type = ForceStringFrom('zz_type');
			$zz_tiandi = ForceStringFrom('zz_tiandi');
			$zz_status = ForceStringFrom('zz_status');
			$zz_zhouqi = ForceStringFrom('zz_zhouqi');
			$zz_mianji = ForceStringFrom('zz_mianji');
			$zz_shoucheng = ForceStringFrom('zz_shoucheng');
			
			$zz_riqi = ForceIntFrom('zz_riqi',0);
			if($zz_riqi > 0){
				$sql  = "SELECT A.`order_time`,A.`pay_status` FROM order_base A,order_item B WHERE A.id = B.order_id AND B.id = '{$item_id}'";
				$order_info = $GLOBALS['DB']->get_row($sql);
				//var_dump($order_info);
				if($order_info['order_time'] > 0 && $order_info['pay_status'] == 1){
					$mature_time = floor((time()-$order_info['order_time'])/86400)+$zz_riqi;
					$sql_set = ",`mature_time`='{$mature_time}'";
				}
				
			}
			
			$zz_num_free = ForceStringFrom('zz_num_free');
			$zz_num_ing = ForceStringFrom('zz_num_ing');
			
			$price = ForceStringFrom('goods_price');
			$num = ForceStringFrom('goods_num');
			
			$sql = "UPDATE `order_item` SET `price`='{$price}',`num`='{$num}',`zz_type`='{$zz_type}',`zz_tiandi`='{$zz_tiandi}',`zz_status`='{$zz_status}',`zz_zhouqi`='{$zz_zhouqi}'
			,`zz_mianji`='{$zz_mianji}',`zz_shoucheng`='{$zz_shoucheng}',`zz_riqi`='{$zz_riqi}',`zz_num_free`='{$zz_num_free}',`zz_num_ing`='{$zz_num_ing}'$sql_set WHERE `id`='{$item_id}'";
			$res = $GLOBALS['DB']->query($sql);
			
			if($res){
				echo "<script>window.parent.php_callback('保存成功');</script>";exit;
			}else{
				echo "<script>window.parent.php_callback('保存失败');</script>";exit;
			}
		}else{
			echo "<script>window.parent.php_callback('保存失败');</script>";exit;
		}
		break;	
	case "remark"://修改备注
		$order_id 	 = $_POST['oid'];
		$value = $_POST['value'];
		if($value != ''){
			$sql = "UPDATE `order_base` SET `order_remark`='{$value}' WHERE `id`='{$order_id}'";
			$GLOBALS['DB']->query($sql);
		}
		break;
		
	case "changeOitem":
		
		//var_dump($_POST); status : 1数据处理失败   2数据有误  3商品库存不足   4已经同步不可修改  5修改成功  6订单已支付，修改商品后的价格必须与原价格相等
		$order_id = $_POST['OID'];
		$goodsID = $_POST['goodsID'];
		$gPrice = $_POST['gPrice'];
		$gNum = $_POST['gNum'];
		$goodsIID = $_POST['goodsIID'];
		$goodsDel = $_POST['goodsDel'];
		
		$wms_oid = $GLOBALS['DB']->get_var("SELECT `wms_oid` FROM `wms_order_relation` WHERE `order_id`='{$order_id}' ");
		if($wms_oid > 0){
			echo json_encode ( array ('status'=>'4') );
		}else{
			if($goodsID != '' && $order_id > 0){
				$allPrice = 0;
				$GLOBALS['DB']->autocommit(false);
				$break_y = true;
				foreach($goodsID as $key=>$value){
					if($gNum[$key] >= 0 && $gPrice[$key] >= 0 && $value > 0){
						if($goodsDel[$key] == 1){
							if($goodsIID[$key] > 0){//修改
								if($gNum[$key] > 0){//修改
									$oit = $GLOBALS['DB']->get_row("SELECT * FROM `order_item` WHERE `id`='{$goodsIID[$key]}' ");
									if($gNum[$key] > $oit['num']){
										$subNum = $gNum[$key]-$oit['num'];
										$gbase = $GLOBALS['DB']->get_row("SELECT * FROM `goods_base` WHERE `goods_id`='{$value}' ");
										if($gbase['amount'] > 0 && $gbase['amount'] >= $subNum){
											$gres = $GLOBALS['DB']->query("UPDATE `goods_base` SET `amount`=`amount`-{$subNum} WHERE `goods_id`='{$value}'");
											$ires = $GLOBALS['DB']->query("UPDATE `order_item` SET `price`='{$gPrice[$key]}',`num`='{$gNum[$key]}' WHERE `id`='{$goodsIID[$key]}'");
											if($gres && $ires){
												$allPrice += $gPrice[$key];
											}else{
												$GLOBALS['DB']->rollback ();
												echo json_encode ( array ('status'=>'1','err'=>'1001') );
												$break_y = false;
												break;
											}
										}else{
											$GLOBALS['DB']->rollback ();
											echo json_encode ( array ('status'=>'3','err'=>"商品{$gbase['code']}库存不足") );
											$break_y = false;
											break;
										}
									}elseif($gNum[$key] < $oit['num']){
										$subNum = $oit['num']-$gNum[$key];
										$gres = $GLOBALS['DB']->query("UPDATE `goods_base` SET `amount`=`amount`+{$subNum} WHERE `goods_id`='{$value}'");
										$ires = $GLOBALS['DB']->query("UPDATE `order_item` SET `price`='{$gPrice[$key]}',`num`='{$gNum[$key]}' WHERE `id`='{$goodsIID[$key]}'");
										if($gres && $ires){
											$allPrice += $gPrice[$key];
										}else{
											$GLOBALS['DB']->rollback ();
											echo json_encode ( array ('status'=>'1','err'=>'1002') );
											$break_y = false;
											break;
										}
									}else{
										if($gPrice[$key] != $oit['price']){
											$res = $GLOBALS['DB']->query("UPDATE `order_item` SET `price`='{$gPrice[$key]}' WHERE `id`='{$goodsIID[$key]}'");
											if($res){
												$allPrice += $gPrice[$key];
											}else{
												$GLOBALS['DB']->rollback ();
												echo json_encode ( array ('status'=>'1','err'=>'1003') );
												$break_y = false;
												break;
											}
										}else{
											$allPrice += $gPrice[$key];
										}
									}
								}else{//数量为0删除
									$oit = $GLOBALS['DB']->get_row("SELECT * FROM `order_item` WHERE `id`='{$goodsIID[$key]}' ");
									$gres = $GLOBALS['DB']->query("UPDATE `goods_base` SET `amount`=`amount`+{$oit['num']} WHERE `goods_id`='{$oit['goods_id']}'");
									$ires = $GLOBALS['DB']->query("DELETE FROM `order_item` WHERE `id`='{$goodsIID[$key]}' ");
									if($gres && $ires){
									
									}else{
										$GLOBALS['DB']->rollback ();
										echo json_encode ( array ('status'=>'1','err'=>'1004') );
										$break_y = false;
										break;
									}
								}
							}else{//新加
								if($gNum[$key] > 0){//数量大于0新加
									$gbase = $GLOBALS['DB']->get_row("SELECT * FROM `goods_base` WHERE `goods_id`='{$value}' ");
									if($gbase['amount'] > 0 && $gbase['amount'] >= $gNum[$key]){
										$gres = $GLOBALS['DB']->query("UPDATE `goods_base` SET `amount`=`amount`-{$gNum[$key]} WHERE `goods_id`='{$value}'");
										$sql = "INSERT INTO order_item SET order_id='{$order_id}',
												goods_id='{$value}',
												code='{$gbase['code']}',
												goods_name='{$gbase['goods_name']}',
												pic='{$gbase['pic']}',
												price='{$gPrice[$key]}',
												num='{$gNum[$key]}' ";
										$ires = $GLOBALS['DB']->query($sql);
										//$ires = $GLOBALS['DB']->query("INSERT INTO `order_item` SET `price`='{$gPrice[$key]}',`num`='{$gNum[$key]}' ");
										if($gres && $ires){
											$allPrice += $gPrice[$key];
										}else{
											$GLOBALS['DB']->rollback ();
											echo json_encode ( array ('status'=>'1','err'=>'1005') );
											$break_y = false;
											break;
										}
									}else{
										$GLOBALS['DB']->rollback ();
										echo json_encode ( array ('status'=>'3','err'=>"商品{$gbase['code']}库存不足") );
										$break_y = false;
										break;
									}
								}
							}
						}elseif($goodsDel[$key] == 2){//删除的
							if($goodsIID[$key] > 0){
								$oit = $GLOBALS['DB']->get_row("SELECT * FROM `order_item` WHERE `id`='{$goodsIID[$key]}' ");
								$gres = $GLOBALS['DB']->query("UPDATE `goods_base` SET `amount`=`amount`+{$oit['num']} WHERE `goods_id`='{$oit['goods_id']}'");
								$ires = $GLOBALS['DB']->query("DELETE FROM `order_item` WHERE `id`='{$goodsIID[$key]}' ");
								if($gres && $ires){
								
								}else{
									$GLOBALS['DB']->rollback ();
									echo json_encode ( array ('status'=>'1','err'=>'1006') );
									$break_y = false;
									break;
								}
							}
						}else{
							$GLOBALS['DB']->rollback ();
							echo json_encode ( array ('status'=>'2','err'=>'1007') );
							$break_y = false;
							break;
						}
					}else{
						$GLOBALS['DB']->rollback ();
						echo json_encode ( array ('status'=>'2','err'=>'1008') );
						$break_y = false;
						break;
					}
				}
				if($break_y){
					$order_base = $GLOBALS['DB']->get_row("SELECT * FROM `order_base` WHERE `id`='{$order_id}' ");
					if($order_base['id'] > 0){
						if($order_base['pay_status'] == 0){
							$total_price = $order_base['total_price']+($allPrice-$order_base['original_totle_price']);
							if($total_price >= 0){
								$obres = $GLOBALS['DB']->query("UPDATE `order_base` SET `original_totle_price`='{$allPrice}',`total_price`='{$total_price}' WHERE `id`='{$order_id}'");
								if($obres){
									echo json_encode ( array ('status'=>'5') );
									$GLOBALS['DB']->commit();
								}else{
									$GLOBALS['DB']->rollback ();
									echo json_encode ( array ('status'=>'1','err'=>'1009') );
									break;
								}
							}else{
								$GLOBALS['DB']->rollback ();
								echo json_encode ( array ('status'=>'2','err'=>'1010') );
								break;
							}
						}else{
							if($allPrice == $order_base['original_totle_price']){
								echo json_encode ( array ('status'=>'5') );
								$GLOBALS['DB']->commit();
							}else{
								$GLOBALS['DB']->rollback ();
								echo json_encode ( array ('status'=>'6','err'=>'订单已支付，修改商品后的价格必须与原价格相等') );
								break;
							}
						}
					}else{
						$GLOBALS['DB']->rollback ();
						echo json_encode ( array ('status'=>'1','err'=>'1011') );
						break;
					}
				}
			}else{
				echo json_encode ( array ('status'=>'2','err'=>'1012') );
			}
		}
		break;
	case "get_order_csv"://导出订单CSV
		
		
		$list = $cls_admin_order->order_list(99999);
		$order_list = $list['list'];
//		if($order_list){
//			foreach($order_list as $key=>$value){
//				$order_list[$key]['items'] = $cls_order->order_item_list($value['id']);
//			}
//		}
		
		$add_date = date("Y-m-d");
		$csv_name = $add_date.".csv";
		$str = "";
		if($order_list){
			$str .= "商品,";
			$str .= "订单号,";
			$str .= "会员信息,";
			$str .= "联系方式,";
			$str .= "下单时间,";
			$str .= "支付时间,";
			$str .= "订单金额,";
			$str .= "订单状态,";
			$str .= "支付状态,";
			$str .= "配送状态,";
			$str .= "\r\n";
			
			foreach($order_list as $key=>$value){
				$iName = '';
				
				$items = $cls_order->order_item_list($value['id']);
				if($items){
					foreach($items as $iValue){
						$iName .= "{$iValue['goods_name']}[数量{$iValue['num']}]\n";
					}
				}
				
				if($value['s_name'] == ''){
					$value['s_name'] = $value['name'];
				}
				if($value['s_area'] == ''){
					$value['s_area'] = $value['area'];
				}
				if($value['s_address'] == ''){
					$value['s_address'] = $value['address'];
				}
				if($value['s_zip'] == ''){
					$value['s_zip'] = $value['zip'];
				}
				if($value['s_tel'] == ''){
					$value['s_tel'] = $value['tel'];
				}
				
				$pay_time = $value['pay_time']>0?date("Y-m-d H:i:s",$value['pay_time']):'';
				$order_time = $value['order_time']>0?date("Y-m-d H:i:s",$value['order_time']):'';
				
				$value['order_status'] = get_order_status($value['order_status']);
				$value['pay_status'] = get_pay_status($value['pay_status']);
				$value['shipping_status'] = get_shipping_status($value['shipping_status']);
				
				$str .= "\"$iName\",";
				$str .= "{$value['order_no']},";
				//$str .= " ,";
				$str .= "会员ID:{$value['member_id']}|会员帐号:{$value['account']}|备注名称:{$value['remark_name']},";
				$str .= "{$value['s_name']}:{$value['s_tel']}|{$value['s_area']} {$value['s_address']} {$value['s_zip']},";
				$str .= "$order_time,";
				$str .= "$pay_time,";
				$str .= "{$value['total_price']},";
				$str .= "{$value['order_status']},";
				$str .= "{$value['pay_status']},";
				$str .= "{$value['shipping_status']},";
				$str .= "\r\n";
				
//				$items = $cls_order->order_item_list($value['id']);
//				if($items){
//					foreach($items as $iValue){
//						//$iName .= "{$iValue['goods_name']}|";
//						$str .= " ,";
//						$str .= "{$iValue['goods_name']},";
//						$str .= " ,";
//						$str .= " ,";
//						$str .= " ,";
//						$str .= " ,";
//						$str .= " ,";
//						$str .= " ,";
//						$str .= " ,";
//						$str .= " ,";
//						$str .= "\r\n";
//					}
//				}
//				
//				$str .= " ,";
//				$str .= " ,";
//				$str .= " ,";
//				$str .= " ,";
//				$str .= " ,";
//				$str .= " ,";
//				$str .= " ,";
//				$str .= " ,";
//				$str .= " ,";
//				$str .= " ,";
//				$str .= "\r\n";
				
			}
			
		}
		header("Content-type: application/octet-stream; charset=utf-8"); 
		//header("Content-type:   application/octet-stream"); 
	    header("Accept-Ranges:bytes"); 
	    header("Content-type:application/vnd.ms-excel");    
	    header("Content-Disposition:attachment;filename=".$csv_name); 
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		$str =iconv('utf-8','gb2312',$str);//转换编码
		//$str = mb_convert_encoding($str, "UTF-8", "UTF-8,GBK,GB2312");
		echo $str;
		
		break;
		
}


function get_order_status($status){
	if($status == 0){
		return '未生成';
	}elseif($status == 1){
		return '已生成';
	}elseif($status == 2){
		return '已取消';
	}elseif($status == 3){
		return '过期';
	}elseif($status == 4){
		return '处理中';
	}elseif($status == 5){
		return '已完成';
	}else{
		return '未知';
	}
}

function get_pay_status($status){
	if($status == 0){
		return '未支付';
	}elseif($status == 1){
		return '已支付';
	}elseif($status == 2){
		return '申请退款';
	}elseif($status == 3){
		return '已退款';
	}elseif($status == 4){
		return '退款失败';
	}elseif($status == 11){
		return '确认收款失败';
	}elseif($status == 12){
		return '确认收款成功';
	}else{
		return '未知';
	}
}

function get_shipping_status($status){
	if($status == 0){
		return '等待发货';
	}elseif($status == 1){
		return '已发货';
	}elseif($status == 2){
		return '已收货';
	}elseif($status == 3){
		return '申请退货';
	}elseif($status == 4){
		return '退货中';
	}elseif($status == 5){
		return '货品退回';
	}elseif($status == 6){
		return '待备货';
	}elseif($status == 7){
		return '备货中';
	}elseif($status == 8){
		return '备货完毕';
	}elseif($status == 9){
		return '缺货';
	}elseif($status == 10){
		return '缺货等待';
	}elseif($status == 11){
		return '检货中';
	}elseif($status == 12){
		return '捡货完毕';
	}elseif($status == 13){
		return '未投妥';
	}elseif($status == 14){
		return '再次配送';
	}elseif($status == 15){
		return '已拒收';
	}else{
		return '未知';
	}
}
















?>
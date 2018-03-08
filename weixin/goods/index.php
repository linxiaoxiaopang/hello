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
require_once FRAMEWORK_PATH . '/module/admin_goods.class.php';

load_module_config('account');
load_module_config('session');

//$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);

//$GLOBALS['MC'] = new Memcache;
//$GLOBALS['MC']->pconnect( $GLOBALS['account_settings']['mc_server']['default']['ip'], $GLOBALS['account_settings']['mc_server']['default']['port']);


$admin_id = $_SESSION['admin_id'];

$cls_admin_goods = new cls_admin_goods();

if($admin_id > 0){
	
}else{
	header('location:'.$GLOBALS['ADMIN_URL'].'/index.php?act=login');
	exit;
}
//判断权限
admin_priv('goods_manage');
//load模块菜单
load_menu_module();

$act = $_REQUEST['act'];

//$GLOBALS['T']->assign('session_id', $GLOBALS['S']->session_id);
$GLOBALS['T']->assign('session_id', session_id());

$type = ForceIntFrom('type',0);
$GLOBALS['T']->assign('type',$type);

switch($act){
	
	//2014-08-12
	case "quick_ajax_goods_list":
		$list = $cls_admin_goods->goods_list($admin_id,10,1);
		
		$GLOBALS['T']->assign('select_full',  	0);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		make_json_result($GLOBALS['T']->fetch('admin/goods/model_goods_select.html'),0,'',$list['filter']);
		break;
	
	case "goods_search":
		//$list = $cls_admin_goods->goods_list($admin_id,9999);
		$list = $cls_admin_goods->goods_list($admin_id);
		
		$GLOBALS['T']->assign('select_full',  	0);
		$GLOBALS['T']->assign('goods_list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		make_json_result($GLOBALS['T']->fetch('admin/order/goods_select.html'),0,'',$list['filter']);
		break;
	
	
	//商品分类设置 -----------------------------------------------------------------------------------------------------
	
	case "cate_batch":
		if(isset($_POST['deletea_ids'])){
			$del_ids = implode(",",$_POST['deletea_ids']);
			$info = $cls_admin_goods->goods_cate_batch_del($del_ids);
			if(!empty($info)){
				sys_msg(1,"删除成功",$GLOBALS['ADMIN_URL'].'/goods/?act=cate_list');
			}
			else{
				sys_msg(0,"删除失败");
			}
		}
		elseif(isset($_POST['is_show'])){
			foreach($_POST['is_show'] as $key=>$value){
				$info = $cls_admin_goods->goods_cate_is_show($key,$value);
			}
			sys_msg(1,"操作成功",$GLOBALS['ADMIN_URL'].'/goods/?act=cate_list');
		}
		
		break;

	
	case "ajax_cate_list":
		$list = $cls_admin_goods->category_list($admin_id);
		$GLOBALS['T']->assign('full_page',  	0);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		make_json_result($GLOBALS['T']->fetch('admin/goods/cate_list.html'),0,'',$list['filter']);
		break;
	
	case "cate_list":
		//$parent_id = ForceIntFrom('parent_id');
		$list = $cls_admin_goods->category_list($admin_id);
		//print_r($list);
		$GLOBALS['T']->assign('full_page',  	1);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],1,$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		$GLOBALS['T']->display('admin/goods/cate_list.html');
		break;
	case "cate_add":  //添加分类
		$info = array();
		$list = $cls_admin_goods->category_list($admin_id);
		//print_r($list);
		$GLOBALS['T']->assign('list', $list['list']);
		$GLOBALS['T']->display('admin/goods/cate_info.html');
		break;
	case "cate_edit":  //修改分类
		$cate_id		= 	ForceIntFrom('cate_id');
		if(empty($cate_id)){
			sys_msg(2,"商品分类ID不能为空");
		}
		$list = $cls_admin_goods->category_list($admin_id);
		
		$info = array();
		$info = $cls_admin_goods->category_info_edit($cate_id);
		if(empty($info)){
			sys_msg(2,"您无权修改该分类");
		}
		
		$GLOBALS['T']->assign('list', $list['list']);
		$GLOBALS['T']->assign('info', $info);
		$GLOBALS['T']->display('admin/goods/cate_info.html');
		break;
	case "cate_save": //保存分类
		$cate_id			= 	ForceIntFrom('cate_id');
		$sort			= 	ForceIntFrom('sort');
		$is_show		= 	ForceIntFrom('is_show');
		$show_in_nav	= 	ForceIntFrom('show_in_nav');
		$parent_id	= 	ForceIntFrom('parent_id');
		$cat_name 		= 	ForceStringFrom('cat_name');
		$first_char 	= 	ForceStringFrom('first_char');
		$html_title 	= 	ForceStringFrom('html_title');
		$html_desc 		= 	ForceStringFrom('html_desc');
		$html_kw 		= 	ForceStringFrom('html_kw');
		if(empty($cat_name)){
			sys_msg(2,"商品分类名称不能为空");
		}
		
		$old_pic	= 	ForceStringFrom('old_pic');
		$pic		=   $old_pic;
		//上传图片
		if (($_FILES['cate_img']['tmp_name'] != '' && $_FILES['cate_img']['tmp_name'] != 'none')){
			require_once FRAMEWORK_PATH . '/library/upload.class.php';
			//上传配置
			$config = array(
				"savePath" => $GLOBALS['DATA_PATH']."/images/" ,
				"maxSize" => 20480 , //单位KB
				"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg"  )
			);
			//生成上传实例对象并完成上传
			$up = new Uploader( "cate_img" , $config );
			$info = $up->getFileInfo();
			thumn($info["url"],$info["url"]."_90.jpg", 90, 90, 1);
			$pic   = str_replace($GLOBALS['DATA_PATH'], '', $info["url"]."_90.jpg"); // 本地备份原始图片
		}
		
		$rs = $cls_admin_goods->goods_cate_handle($cate_id, $cat_name, $parent_id, $is_show, $sort, $show_in_nav, $first_char , $html_title , $html_desc , $html_kw,$pic,$admin_id );
		
		if($rs == 1000){
			$type = ForceIntFrom('type',0);
			if($type > 0){
				sys_msg(1,"操作成功",$GLOBALS['ADMIN_URL'].'/goods/?act=cate_list&type='.$type);
			}else{
				sys_msg(1,"操作成功",$GLOBALS['ADMIN_URL'].'/goods/?act=cate_list');
			}
		}
		else{
			sys_msg(0,"操作失败 ERROR:{$rs}");
		}
		
		
		break;
	
	
	case "ajax_goods_list":
		$list = $cls_admin_goods->goods_list($admin_id);
//		if($list['list']){
//			foreach($list['list'] as $key=>$value){
//				$info = $cls_admin_goods->category_info($value['cate_id']);
//				$list['list'][$key]['cat_name'] = $info['cat_name'];
//				$info = $cls_admin_goods->brand_info($value['brand_id']);
//				$list['list'][$key]['brand_name'] = $info['brand_name'];
//			}
//		}
		if($list['list']){
			foreach($list['list'] as $key=>$value){
				$cat_name = $cls_admin_goods->category_info($value['cate_id']);
				$cat_name = implode(',',$cat_name);
				$list['list'][$key]['cat_name'] = $cat_name;
				$info = $cls_admin_goods->brand_info($value['brand_id']);
				$list['list'][$key]['brand_name'] = $info['brand_name'];
			}
		}
		$GLOBALS['T']->assign('full_page',  	0);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		make_json_result($GLOBALS['T']->fetch('admin/goods/list.html'),0,'',$list['filter']);
		break;
	
	//商品设置 ---------------------------------------------------------------------------------------------------------
	case "list":  //商品列表
		$list = $cls_admin_goods->goods_list($admin_id);
		if($list['list']){
			foreach($list['list'] as $key=>$value){
				
				$cat_name = $cls_admin_goods->category_info($value['cate_id']);
				$cat_name = implode(',',$cat_name);
				
				//$list['list'][$key]['cat_name'] = $info['cat_name'];
				$list['list'][$key]['cat_name'] = $cat_name;
				
			}
		}
		$GLOBALS['T']->assign('full_page',  	1);
		$GLOBALS['T']->assign('list',  			$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);

		// $res = $cls_admin_goods->goods_cart(57);
		// var_dump($res['list']['0']);exit;

		$GLOBALS['T']->display('admin/goods/list.html');
		break;
	
	
	case "batch":
		if(isset($_POST['deletea_ids'])){
			$del_ids = implode(",",$_POST['deletea_ids']);
			$info = $cls_admin_goods->goods_batch_del($del_ids);
			if(!empty($info)){
				sys_msg(1,"删除成功",$GLOBALS['ADMIN_URL'].'/goods/?act=list');
			}
			else{
				sys_msg(0,"删除失败",$GLOBALS['ADMIN_URL'].'/goods/?act=list');
			}
		}
		elseif(isset($_POST['is_show'])){
			foreach($_POST['is_show'] as $key=>$value){
				$info = $cls_admin_goods->goods_is_show($key,$value);
			}
			sys_msg(1,"操作成功",$GLOBALS['ADMIN_URL'].'/goods/?act=list');
		}
		
		break;

	case "add":  //添加商品
		$info = array();
		
		$info['code'] = "";//"NO".(100000000+intval($cls_admin_goods->get_max_id())+1);
		
		
		//设置分类
		$cate_list = $cls_admin_goods->category_list($admin_id);
		$goodslistbytype = $cls_admin_goods->goods_list_by_Type($type);

		// print_r($goodslistbytype);exit;
		$GLOBALS['T']->assign('cate_list', $cate_list['list']);
		
		$GLOBALS['T']->assign('info', $info);

		$GLOBALS['T'] ->assign('goodslistbytype',$goodslistbytype['list']);
		
		$GLOBALS['T']->display('admin/goods/info.html');
		break;

	case "edit":  //编辑商品
		$goods_id		= 	ForceIntFrom('goods_id');
		if(empty($goods_id)){
			sys_msg(2,"商品ID不能为空");
		}
		$info = array();
		
		$info = $cls_admin_goods->goods_info($goods_id);
		
		if(empty($info)){
			sys_msg(2,"权限不足");
		}
		
		//分类
		$cate_list = $cls_admin_goods->category_list($admin_id);

		$goodslistbytype = $cls_admin_goods->goods_list_by_Type($type);


		//var_dump($cate_list);
		$GLOBALS['T']->assign('cate_list', $cate_list['list']);
		//var_dump($info);
		$GLOBALS['T']->assign('info', $info);
		$infoCate = explode(',',$info['cate_id']);
		$GLOBALS['T']->assign('infoCate', $infoCate);
		$GLOBALS['T'] ->assign('goodslistbytype',$goodslistbytype['list']);
		$GLOBALS['T']->display('admin/goods/info.html');
		break;
	case "save": //保存
		$goods_id		= 	ForceIntFrom('goods_id');
		$code 			= 	ForceStringFrom('code');
		$goods_name 	= 	ForceStringFrom('goods_name');
		$short_intro	= 	ForceStringFrom('short_intro');
		$amount			= 	ForceIntFrom('amount');
		$cate_id		= 	ForceIntFrom('cate_id');
		$order_by		= 	ForceIntFrom('order_by');
		$status		    = 	ForceIntFrom('status');
		$price          =   ForcePriceFrom('price');
		$zz_zhouqi          =   ForceStringFrom('zz_zhouqi');
		
		$mature_time = 	ForceIntFrom('mature_time');
		$old_goods_pic	= 	ForceStringFrom('old_goods_pic');
		$tcA_id = ForceIntFrom('tcA_id');
		$tcB_id = ForceIntFrom('tcB_id');
		$tcC_id = ForceIntFrom('tcC_id');
		$tcD_id = ForceIntFrom('tcD_id');
		$tcE_id = ForceIntFrom('tcE_id');
		$tcA_num = ForceIntFrom('tcA_num');
		$tcB_num = ForceIntFrom('tcB_num');
		$tcC_num = ForceIntFrom('tcC_num');
		$tcD_num = ForceIntFrom('tcD_num');
		$tcE_num = ForceIntFrom('tcE_num');
		$isTC = ForceIntFrom('isTC');
		
		
		if(empty($goods_name)){
			if($goods_id > 0){
				$default_url = $GLOBALS['ADMIN_URL']."/goods/?act=edit&goods_id=".$goods_id;
			}else{
				$default_url = $GLOBALS['ADMIN_URL']."/goods/?act=add";
			}
			echo "<script>window.parent.php_callback('商品名称不能为空');</script>";exit;
		}
		
		//这里是各种判断  确实参数判断
		
		$goods_img		=   $old_goods_pic;
		//上传图片
		if (($_FILES['goods_img']['tmp_name'] != '' && $_FILES['goods_img']['tmp_name'] != 'none')){
			require_once FRAMEWORK_PATH . '/library/upload.class.php';
			//上传配置
			$config = array(
				"savePath" => $GLOBALS['DATA_PATH']."/images/" ,
				"maxSize" => 20480 , //单位KB
				"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg"  )
			);
			//生成上传实例对象并完成上传
			$up = new Uploader( "goods_img" , $config );
			$info = $up->getFileInfo();
			thumn($info["url"],$info["url"]."_200.jpg", 200, 200, 1);
			$goods_img   = str_replace($GLOBALS['DATA_PATH'], '', $info["url"]."_200.jpg"); // 本地备份原始图片
		}
		
		$rs = $cls_admin_goods->goods_info_handle($goods_id,$goods_name,$cate_id,$code,$price,$zz_zhouqi,$mature_time,$amount,$short_intro,$goods_img,$order_by,$status,$admin_id,$tcA_id,$tcA_num,$tcB_id,$tcB_num,$tcC_id,$tcC_num,$tcD_id,$tcD_num,$tcE_id,$tcE_num,$isTC);
		
		if($rs == 1000){
			echo "<script>window.parent.php_callback('操作成功');</script>";
			// echo "<script>window.parent.php_callback('操作成功');setTimeout(function(){window.parent.history.go(-2);},1500);</script>";
		}else{
			$type = ForceIntFrom('type',0);
			if($type > 0){
				if($goods_id > 0){
					$default_url = $GLOBALS['ADMIN_URL']."/goods/?act=edit&goods_id=".$goods_id."&type=".$type;
				}else{
					$default_url = $GLOBALS['ADMIN_URL']."/goods/?act=add"."&type=".$type;
				}
			}else{
				if($goods_id > 0){
					$default_url = $GLOBALS['ADMIN_URL']."/goods/?act=edit&goods_id=".$goods_id;
				}else{
					$default_url = $GLOBALS['ADMIN_URL']."/goods/?act=add";
				}
			}
			echo "<script>window.parent.php_callback('操作失败 ERROR:{$rs}');</script>";exit;
		}
		break;
		
	
	case "editor_upload":
		
		$goods_img		=   "";
		//上传图片
		if (($_FILES['imgFile']['tmp_name'] != '' && $_FILES['imgFile']['tmp_name'] != 'none')){
			require_once FRAMEWORK_PATH . '/library/upload.class.php';
			//上传配置
			$config = array(
				"savePath" => $GLOBALS['DATA_PATH']."/upload/" ,
				"maxSize" => 20480 , //单位KB
				"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp", ".txt", ".doc", ".docx", ".pdf", ".xls", ".xlsx", ".csv",".mp4",".wma",".rm",".rmvb",".zip",".rar",".ppt",".mp3"  )
			);
			//生成上传实例对象并完成上传
			$up = new Uploader( "imgFile" , $config );
			$info = $up->getFileInfo();
			$goods_img   = str_replace($GLOBALS['DATA_PATH'], '', $info["url"]); // 本地备份原始图片
			//echo json_encode(array('error' => 0,'url' => $goods_img,'trueurl' => $upload[0]['name']));
			echo json_encode(array('error' => 0, 'url' => $GLOBALS['CDN_URL']. $goods_img));
		}elseif(($_FILES['multiFile']['tmp_name'] != '' && $_FILES['multiFile']['tmp_name'] != 'none')){
			require_once FRAMEWORK_PATH . '/library/upload.class.php';
			//上传配置
			$config = array(
				"savePath" => $GLOBALS['DATA_PATH']."/images/" ,
				"maxSize" => 20480 , //单位KB
				"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp"  )
			);
			//生成上传实例对象并完成上传
			$up = new Uploader( "multiFile" , $config );
			$info = $up->getFileInfo();
			$goods_img   = str_replace($GLOBALS['DATA_PATH'], '', $info["url"]); // 本地备份原始图片
			echo $goods_img;
		}
		break;
	default:		//商品列表
		$GLOBALS['T']->display('admin/goods/list.html');
		break;
}
?>
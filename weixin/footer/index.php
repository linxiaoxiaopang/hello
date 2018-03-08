<?php
ini_set ( 'session.save_path' , dirname ( __FILE__ ) . '/../dirname/' ) ; 
session_start();
header("Content-Type:text/html;charset=utf-8");
require_once '../../init.php';
require_once '../../init_smarty.php';
require_once FRAMEWORK_PATH . '/library/common.lib.php';
require_once FRAMEWORK_PATH . '/library/mysql.class.php';
//require_once FRAMEWORK_PATH . '/library/session.class.php';
require_once FRAMEWORK_PATH . '/module/footer.class.php';


load_module_config('account');
load_module_config('session');

//$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);

//$admin_id = getAdminID();
$admin_id = $_SESSION['admin_id'];
$GLOBALS['T']->assign('_SESSION',$_SESSION);

$cls_footer = new cls_footer();

//判断权限
admin_priv('footer_manage');
//load模块菜单
load_menu_module();

//$act = $_REQUEST['act'];
$_REQUEST['act'] = ($_REQUEST['act']=='')?'footer_list':$_REQUEST['act'];

if($_REQUEST['act'] == 'footer_list'){//资讯列表
	
	if(!empty($_REQUEST['cate'])){
		$cateid = $_REQUEST['cate'];
		$GLOBALS['T']->assign('Cateid',  		$cateid);
	}
	
    $list = $cls_footer->getfooterList($admin_id);
	
	$flag_cate=ForceIntFrom('cate');
	
	$GLOBALS['T']->assign('flag_cate',  		$flag_cate);
	
	$cate = $GLOBALS['DB']->get_results("SELECT * FROM `base_footer_cate` WHERE `cate_status`=1 AND admin_id IN (".$admin_id.") order by `cate_order` DESC ");
	$GLOBALS['T']->assign('cate_list',$cate);
	
    $GLOBALS['T']->assign('list',           $list['list']);
	$GLOBALS['T']->assign('full_page',  	1);
	$GLOBALS['T']->assign('filter',  		$list['filter']);
	
	$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
	$GLOBALS['T']->assign('record_count', 	$list['record_count']);
	$GLOBALS['T']->assign('page_count',   	$list['page_count']);
	$GLOBALS['T']->assign('current',   	'footer_list');
	
	$GLOBALS['T']->display('admin/footer/footer_list.html');
	
}elseif($_REQUEST['act'] == 'ajax_footer_list'){//资讯列表
    $list = $cls_footer->getfooterList();
    $GLOBALS['T']->assign('list',           $list['list']);
	$GLOBALS['T']->assign('full_page',  	0);
	$GLOBALS['T']->assign('filter',  		$list['filter']);
	$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
	$GLOBALS['T']->assign('record_count', 	$list['record_count']);
	$GLOBALS['T']->assign('page_count',   	$list['page_count']);
	
	make_json_result($GLOBALS['T']->fetch('admin/footer/footer_list.html'),0,'',$list['filter']);
	//$GLOBALS['T']->display('admin/footer/footer_list.html');
	
}elseif($_REQUEST['act'] == 'footer_search'){
	
		$subject_id = ForceIntFrom('subject_id',0);
		//$subject_id = 25;
		
		$list = $cls_footer->getfooterListBySubject($subject_id);
		
		$GLOBALS['T']->assign('full_page',  	0);
		//print_r($list['list']);
		$GLOBALS['T']->assign('footer_list',  	$list['list']);
		$GLOBALS['T']->assign('filter',  		$list['filter']);
		//$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
		$GLOBALS['T']->assign('record_count', 	$list['record_count']);
		$GLOBALS['T']->assign('page_count',   	$list['page_count']);
		make_json_result($GLOBALS['T']->fetch('admin/news_subject/footer_select.html'),0,'',$list['filter']);
			
}elseif($_REQUEST['act'] == 'bind'){
	
		//$footer_ids      = ForceIntFrom('bind', 0);
		
		//$subject_id   = ForceIntFrom('aaa', 0);
		$subject_id=$_POST['aaa'];
		$footer_ids=$_POST['aids'];
		$aids=implode(",",$footer_ids);
		
	
		if($aids&&$subject_id){
			if($cls_footer->batchBind($aids,$subject_id)){
				$GLOBALS['T']->assign('status',1);
				$GLOBALS['T']->assign('msg','操作成功');
				$GLOBALS['T']->assign('defaultUrl',"/news_subject/index.php?act=edit&id=".$subject_id);
				//$GLOBALS['T']->assign('defaultUrl',"/news_subject/index.php?act=list");
			}else{
				$GLOBALS['T']->assign('status',2);
				$GLOBALS['T']->assign('msg','操作失败2');
			}

		}else{
		
		}
		$GLOBALS['T']->display('admin/info.html');
		exit;
			
}elseif($_REQUEST['act'] == 'footer_update'){//
	$GLOBALS['T']->assign('session_id', $GLOBALS['S']->session_id);
	$footer_id = $_REQUEST['footer_id'];
	if($footer_id){
		$GLOBALS['T']->assign('current',   	'footer_update');
	}else{
		$GLOBALS['T']->assign('current',   	'footer_add');
	}
	
	if($_POST){
		
		//$cate_id        = ForceIntFrom('cate_id');
		//$pic            = ForceStringFrom('pic');
		$title          = ForceStringFrom('title');
		$subtitle        = ForceStringFrom('subtitle');
		//$goods_id          = ForceStringFrom('goods_id');
		//$author_id          = ForceStringFrom('author_id');
		//$subject   = ForceIntFrom('subject_id');
		//var_dump($_POST);
		//$content        = ForceStringFrom('content');
		//$footer_date   = ForceStringFrom('footer_date');
		$footer_order  = ForceIntFrom('footer_order');
		//$footer_status = ForceIntFrom('footer_status');
		//$is_contribute = ForceIntFrom('is_contribute');
		$old_pic 		= 	ForceStringFrom('old_pic');
		//$author 		= 	ForceStringFrom('author');
		//$source 		= 	ForceStringFrom('source');
		$pic		    =   $old_pic;
		
		if($title == ''){
			//echo "<script>window.parent.php_callback('请输入标题');</script>";exit;
		}
		if(!$cate_id > 0){
			//echo "<script>window.parent.php_callback('请选择分类');</script>";exit;
		}
		
		//if($cate_id > 0 && $title != ''){
		//上传图片
		if (($_FILES['footer_img']['tmp_name'] != '' && $_FILES['footer_img']['tmp_name'] != 'none'))
		{
			require_once FRAMEWORK_PATH . '/library/upload.class.php';
			//上传配置
			$config = array(
				"savePath" => $GLOBALS['DATA_PATH']."/images/" ,
				"maxSize" => 2048 , //单位KB
				"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp"  )
			);
			//生成上传实例对象并完成上传
			$up = new Uploader( "footer_img" , $config );
			$info = $up->getFileInfo();
			$pic   = str_replace($GLOBALS['DATA_PATH'], '', $info["url"]); // 本地备份原始图片
		}
		$res = $cls_footer->getfooterUpdate($footer_id,$cate_id,$pic,$title,$content,$footer_date,$footer_order,$footer_status,$is_contribute,$subject,$goods_id,$author_id, $author,$source,$admin_id,$subtitle);
		
		if($res){
			if($footer_id){
				$defaultUrl = $GLOBALS['ADMIN_URL']."/footer/?act=footer_update&footer_id=$footer_id";
			}else{
				$defaultUrl = $GLOBALS['ADMIN_URL']."/footer/?act=footer_update&footer_id=$res";
			}
			$defaultUrl = $GLOBALS['ADMIN_URL']."/footer/";
			echo "<script>window.parent.php_callback('保存成功');setTimeout(function(){window.parent.location='$defaultUrl';},1500);</script>";
		}else{
			echo "<script>window.parent.php_callback('操作失败');</script>";exit;
		}
		exit;
	}else{
		if($footer_id > 0){
			$footer = $cls_footer->getfooterInfo($footer_id);    //一条数据
			
			if($footer['artist_id'] > 0){
				$footer['artist_name'] = $GLOBALS['DB']->get_var("SELECT `realName` FROM `artist` WHERE `id`='{$footer['artist_id']}' ");
			}
		}else{
			$footer['cate_id'] = $_REQUEST['cate'];
			$footer['artist_id'] = $_REQUEST['artist_id'];
			if($footer['artist_id'] > 0){
				$footer['artist_name'] = $GLOBALS['DB']->get_var("SELECT `realName` FROM `artist` WHERE `id`='{$footer['artist_id']}' ");
			}
		}
		$GLOBALS['T']->assign('info',$footer);
		
		//$cate = $GLOBALS['DB']->get_results("SELECT * FROM `base_footer_cate` WHERE `cate_status`=1 AND admin_id IN (".$admin_id.") order by `cate_order` DESC ");
		$cate = $GLOBALS['DB']->get_results("SELECT * FROM `base_footer_cate` WHERE `cate_status`=1 order by `cate_order` DESC ");
		if(empty($cate)){
			//sys_msg(0,"请先创建厂商分类",$defaultUrl);
			//echo "<script>window.parent.php_callback('请先创建厂商分类');</script>";exit;
		}
		$GLOBALS['T']->assign('cate_list',$cate);
		$GLOBALS['T']->assign('select_full',1);
		$GLOBALS['T']->display('admin/footer/footer_update.html');
	}
}elseif($_REQUEST['act'] == 'footer_del'){//
	
	$return_link=$GLOBALS['ADMIN_URL']."/footer/";
	//$status_type = $_POST['status_type'];
	
	if(isset($_POST['deletea_ids'])){
		$del_ids = implode(",",$_POST['deletea_ids']);
		//if($status_type == 1){
		$sql = "UPDATE base_footer SET footer_status = 3 WHERE footer_id IN({$del_ids})";
		$re=$GLOBALS['DB']->query($sql);
		//var_dump($re);
		if($re){
			//$GLOBALS['T']->assign('status',1);
			//$GLOBALS['T']->assign('msg','操作成功');
			sys_msg(1,"操作成功",$return_link);
		}else{
			//$GLOBALS['T']->assign('status',2);
			//$GLOBALS['T']->assign('msg','操作失败');
			sys_msg(0,"操作失败",$return_link);
		}
	}else{
		//$GLOBALS['T']->assign('status',2);
		//$GLOBALS['T']->assign('msg','操作失败');
		sys_msg(0,"操作失败",$return_link);
	}
	//$GLOBALS['T']->assign('defaultUrl',"index.php?act=footer_list");
	//$GLOBALS['T']->display('admin/info.html');
}elseif($_REQUEST['act'] == 'cate_list'){//列表
	$pid = $_GET['pid'];
	$cate_list = $cls_footer->getfooterCateList($admin_id,$pid);
	$GLOBALS['T']->assign('list',$cate_list);
	$GLOBALS['T']->assign('active_class_open','资讯分类');
	$GLOBALS['T']->assign('current',   	'cate_list');
	$GLOBALS['T']->display('admin/footer/cate_list.html');
}elseif($_REQUEST['act'] == 'cate_update'){//
	$cate_id = $_REQUEST['cate_id'];
	
	if($cate_id){
		$GLOBALS['T']->assign('current',   	'cate_update');
	}else{
		$GLOBALS['T']->assign('current',   	'cate_add');
	}
	
	$return_link=$GLOBALS['ADMIN_URL']."/footer/?act=cate_list";
	if($_POST){
		$parent_id = $_POST['parent_id'];
		
		$cate_name = $_POST['cate_name'];
		
		$cate_order = $_POST['cate_order'];
		
		$cate_status = $_POST['cate_status'];
		if($cate_name != ''){
			$res = $cls_footer->getfooterCateUpdate($cate_id,$parent_id,$cate_name,$cate_order,$cate_status,$admin_id);
			$GLOBALS['T']->assign('defaultUrl',"index.php?act=cate_list");
			if($res){
				//$GLOBALS['T']->assign('status',1);
				//$GLOBALS['T']->assign('msg','操作成功');
				//sys_msg(1,"操作成功",$return_link);
				//echo "<script>window.parent.php_callback('保存成功');setTimeout(function(){window.parent.location.reload();},1500);</script>";
				echo "<script>window.parent.php_callback('保存成功');setTimeout(function(){window.parent.reload_this();},1500);</script>";
			}else{
				//$GLOBALS['T']->assign('status',2);
				//$GLOBALS['T']->assign('msg','操作失败');
				//sys_msg(2,"操作失败",$return_link);
				echo "<script>window.parent.php_callback('操作失败');</script>";exit;
			}
		}else{
			//$GLOBALS['T']->assign('status',2);
			//$GLOBALS['T']->assign('msg','操作失败');
			//sys_msg(2,"操作失败",$return_link);
			echo "<script>window.parent.php_callback('分类名称不能为空');</script>";exit;
		}
		//$GLOBALS['T']->display('admin/info.html');
		exit;
	}else{
		if($cate_id > 0){
			$cate = $cls_footer->getfooterCateInfo($cate_id);
			$GLOBALS['T']->assign('info',$cate);
		}
		$Pcate = $GLOBALS['DB']->get_results("SELECT * FROM `base_footer_cate` WHERE `parent_id`='0' and `cate_status`=1 order by `cate_order` DESC ");
		//$Pcate = $GLOBALS['DB']->get_results("SELECT * FROM `base_footer_cate` WHERE `cate_status`=1 AND admin_id IN (".$admin_id.") order by `cate_order` DESC,`cate_id` ASC ");
		$GLOBALS['T']->assign('cate_list',$Pcate);
		$GLOBALS['T']->display('admin/footer/cate_update.html');
	}
}elseif($_REQUEST['act'] == 'cate_del'){//
	
	//admin_priv('footer_manage_Super');
	$return_link="/footer/?act=cate_list";
	if(isset($_POST['deletea_ids'])){
		$del_ids = implode(",",$_POST['deletea_ids']);
		var_dump($del_ids);
		$sql = "UPDATE base_footer_cate SET cate_status = 2 WHERE cate_id IN({$del_ids})";
		if($GLOBALS['DB']->query($sql)){
			//$GLOBALS['T']->assign('status',1);
			//$GLOBALS['T']->assign('msg','操作成功');
			sys_msg(1,"操作成功",$return_link);
		}else{
			//$GLOBALS['T']->assign('status',2);
			//$GLOBALS['T']->assign('msg','操作失败');
			sys_msg(2,"操作失败",$return_link);
		}
	}else{
		//$GLOBALS['T']->assign('status',2);
		//$GLOBALS['T']->assign('msg','操作失败');
		sys_msg(2,"操作失败",$return_link);
	}
	//$GLOBALS['T']->assign('defaultUrl',"index.php?act=cate_list");
	//$GLOBALS['T']->display('admin/footer/cate_list.html');
	
}elseif($_REQUEST['act'] == 'artist_search'){//
	require_once FRAMEWORK_PATH . '/module/artist.class.php';
	$cls_artist  = new cls_artist();
	$list = $cls_artist->getArtistList();
	$GLOBALS['T']->assign('full_page',  	0);
	$GLOBALS['T']->assign('artist_list',  	$list['list']);
	$GLOBALS['T']->assign('filter',  		$list['filter']);
	//$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
	$GLOBALS['T']->assign('record_count', 	$list['record_count']);
	$GLOBALS['T']->assign('page_count',   	$list['page_count']);
	make_json_result($GLOBALS['T']->fetch('admin/footer/artist_select.html'),0,'',$list['filter']);
}elseif($_REQUEST['act'] == 'change_status'){//
	
	//admin_priv('footer_manage_Super');
	
	$id =  ForceIntFrom('id');
	$status =  ForceIntFrom('status');
	if($id > 0 && $status > 0){
		$del_ids = implode(",",$_POST['deletea_ids']);
		$sql = "UPDATE `base_footer` SET `footer_status`='{$status}' WHERE `footer_id`='{$id}' ";
		if($GLOBALS['DB']->query($sql)){
			echo json_encode(array('data'=>"操作成功"));
		}else{
			echo json_encode(array('data'=>"操作失败"));
		}
	}else{
		echo json_encode(array('data'=>"数据有误"));
	}
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
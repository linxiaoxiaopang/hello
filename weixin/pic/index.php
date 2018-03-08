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
require_once FRAMEWORK_PATH . '/module/pic.class.php';

load_module_config('account');
//load_module_config('session');

//$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);

$admin_id = $_SESSION['admin_id'];

$cls_pic = new cls_pic();

if($admin_id > 0){
	
}else{
	header('location: /index.php?act=login');
	exit;
}


//判断权限
admin_priv('pic_manage');
//load模块菜单
load_menu_module();

$type = $_REQUEST['type'];
$GLOBALS['T']->assign('type',$type);
$_REQUEST['act'] = ($_REQUEST['act']=='')?'pic_list':$_REQUEST['act'];

if($_REQUEST['act'] == 'pic_list'){//图片列表
	
    $list = $cls_pic->getpicList($type);
    $GLOBALS['T']->assign('list',           $list['list']);
	$GLOBALS['T']->assign('full_page',  	1);
	$GLOBALS['T']->assign('filter',  		$list['filter']);
	
	$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],1,$list['filter']['page_size']));
	$GLOBALS['T']->assign('record_count', 	$list['record_count']);
	$GLOBALS['T']->assign('page_count',   	$list['page_count']);
	
	$GLOBALS['T']->display('admin/pic/pic_list.html');
	
}elseif($_REQUEST['act'] == 'ajax_pic_list'){//图片列表
	
    $list = $cls_pic->getpicList($type);
    $GLOBALS['T']->assign('list',           $list['list']);
	$GLOBALS['T']->assign('full_page',  	0);
	$GLOBALS['T']->assign('filter',  		$list['filter']);
	$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
	$GLOBALS['T']->assign('record_count', 	$list['record_count']);
	$GLOBALS['T']->assign('page_count',   	$list['page_count']);
	
	make_json_result($GLOBALS['T']->fetch('admin/pic/pic_list.html'),0,'',$list['filter']);
	//$GLOBALS['T']->display('admin/pic/pic_list.html');
	
}elseif($_REQUEST['act'] == 'pic_update'){//
	$pic_id = $_REQUEST['pic_id'];
	if($_POST){
		$type         = $_POST['type'];
		$pic          = $_POST['pic'];
		$title        = $_POST['title'];
		$str_1        = ForceStringFrom('str_1');
		$str_2        = ForceStringFrom('str_2');
		$str_3        = ForceStringFrom('str_3');
		$pic_order    = $_POST['pic_order'];
		$old_pic 	  = ForceStringFrom('old_pic');
		$pic		  = $old_pic;
		
		$content = $_POST['content'];
		
		if(empty($title)){
			echo "<script>window.parent.php_callback('标题不能为空');</script>";exit;
		}
		if(empty($type)){
			echo "<script>window.parent.php_callback('类型不能为空');</script>";exit;
		}
		if($title != ''){
			//上传图片
			if (($_FILES['pic_img']['tmp_name'] != '' && $_FILES['pic_img']['tmp_name'] != 'none'))
			{
				require_once FRAMEWORK_PATH . '/library/upload.class.php';
				//上传配置
				$config = array(
					"savePath" => $GLOBALS['DATA_PATH']."/images/" ,
					"maxSize" => 2048 , //单位KB
					"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp"  )
				);
				//生成上传实例对象并完成上传
				$up = new Uploader( "pic_img" , $config );
				$info = $up->getFileInfo();
				$pic   = str_replace($GLOBALS['DATA_PATH'], '', $info["url"]); // 本地备份原始图片
			}
			$res = $cls_pic->getpicUpdate($pic_id,$type,$pic,$title,$str_1,$str_2,$str_3,$pic_order,$content);
			if($res){
				echo "<script>window.parent.php_callback('保存成功');setTimeout(function(){window.parent.location.reload();},1500);</script>";
			}else{
				echo "<script>window.parent.php_callback('保存失败 ERROR10001');</script>";exit;
			}
		}else{
			echo "<script>window.parent.php_callback('保存失败 ERROR10002');</script>";exit;
		}
		exit;
	}else{
		if($pic_id > 0){
			$pic = $cls_pic->getpicInfo($pic_id);
			$GLOBALS['T']->assign('info',$pic);
		}else{
			$pic['type'] = $_REQUEST['type'];
			$GLOBALS['T']->assign('info',$pic);
		}
		$GLOBALS['T']->display('admin/pic/pic_update.html');
	}
}elseif($_REQUEST['act'] == 'pic_del'){//
	if(isset($_POST['deletea_ids'])){
		$del_ids = implode(",",$_POST['deletea_ids']);
		$sql = "DELETE FROM base_pic WHERE pic_id IN ({$del_ids})";
		//echo $sql;exit;
		$GLOBALS['DB']->query($sql);
		if($GLOBALS['DB']->query($sql)){
			$GLOBALS['T']->assign('status',1);
			$GLOBALS['T']->assign('msg','操作成功');
		}else{
			$GLOBALS['T']->assign('status',2);
			$GLOBALS['T']->assign('msg','操作失败');
		}
	}else{
		$GLOBALS['T']->assign('status',2);
		$GLOBALS['T']->assign('msg','操作失败');
	}
	$GLOBALS['T']->assign('defaultUrl',"index.php?act=pic_list&type=".$type);
	$GLOBALS['T']->display('admin/info.html');
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
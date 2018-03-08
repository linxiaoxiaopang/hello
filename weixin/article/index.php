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

require_once FRAMEWORK_PATH . '/module/article.class.php';

load_module_config('account');
//load_module_config('session');

//$GLOBALS['S'] = new cls_session($GLOBALS['MAIN_DOMAIN'], $GLOBALS['ADMIN_MAIN_SID'], $GLOBALS['ADMIN_MAIN_KID']);
$GLOBALS['DB'] = new cls_mysql($GLOBALS['account_settings']['dbserver']['default'] . '/?' . $GLOBALS['account_settings']['dbname']);

$admin_id = $_SESSION['admin_id'];

$cls_article = new cls_article();

if($admin_id > 0){
	
}else{
	header('location:'.$GLOBALS['ADMIN_URL'].'/index.php?act=login');
	exit;
}


//判断权限
admin_priv('article_manage');
//load模块菜单
load_menu_module();

//$act = $_REQUEST['act'];
$_REQUEST['act'] = ($_REQUEST['act']=='')?'article_list':$_REQUEST['act'];

if($_REQUEST['act'] == 'article_list'){//资讯列表
	
    $list = $cls_article->getArticleList();
    $GLOBALS['T']->assign('list',           $list['list']);
	$GLOBALS['T']->assign('full_page',  	1);
	$GLOBALS['T']->assign('filter',  		$list['filter']);
	
	$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],1,$list['filter']['page_size']));
	$GLOBALS['T']->assign('record_count', 	$list['record_count']);
	$GLOBALS['T']->assign('page_count',   	$list['page_count']);
	
	$GLOBALS['T']->display('admin/article/article_list.html');
	
}elseif($_REQUEST['act'] == 'ajax_article_list'){//资讯列表
	
    $list = $cls_article->getArticleList();
    $GLOBALS['T']->assign('list',           $list['list']);
	$GLOBALS['T']->assign('full_page',  	0);
	$GLOBALS['T']->assign('filter',  		$list['filter']);
	$GLOBALS['T']->assign('page_html',  	create_pages_html_admin($list['record_count'],$list['filter']['page'],$list['filter']['page_size']));
	$GLOBALS['T']->assign('record_count', 	$list['record_count']);
	$GLOBALS['T']->assign('page_count',   	$list['page_count']);
	
	make_json_result($GLOBALS['T']->fetch('admin/article/article_list.html'),0,'',$list['filter']);
	//$GLOBALS['T']->display('admin/article/article_list.html');
	
}elseif($_REQUEST['act'] == 'article_update'){//
	$article_id = $_REQUEST['article_id'];
	if($_POST){
		$cate_id        = $_POST['cate_id'];
		$pic            = $_POST['pic'];
		$title          = $_POST['title'];
		$content        = ForceStringFrom('content');
		$article_date   = $_POST['article_date'];
		$article_order  = $_POST['article_order'];
		$article_status = $_POST['article_status'];
		$author = $_POST['author'];
		$source = $_POST['source'];
		$article_hot   = $_POST['article_hot'];
		$article_intro   = $_POST['article_intro'];
		$old_pic 		= 	ForceStringFrom('old_pic');
		$pic		    =   $old_pic;
			//header("Content-Type:text/html;charset=utf-8");
//echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
// charset="UTF-8"
		if(empty($title)){
				echo "<script>window.parent.php_callback('文章标题不能为空');</script>";exit;
			}
		if(empty($cate_id)){
				echo "<script>window.parent.php_callback('文章分类不能为空');</script>";exit;
			}
		if($cate_id > 0 && $title != ''){
			//上传图片
			if (($_FILES['article_img']['tmp_name'] != '' && $_FILES['article_img']['tmp_name'] != 'none'))
			{
				require_once FRAMEWORK_PATH . '/library/upload.class.php';
				//上传配置
				$config = array(
					"savePath" => $GLOBALS['DATA_PATH']."/images/" ,
					"maxSize" => 2048 , //单位KB
					"allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp"  )
				);
				//生成上传实例对象并完成上传
				$up = new Uploader( "article_img" , $config );
				$info = $up->getFileInfo();
				$pic   = str_replace($GLOBALS['DATA_PATH'], '', $info["url"]); // 本地备份原始图片
			}
			$res = $cls_article->getArticleUpdate($article_id,$cate_id,$pic,$title,$content,$article_date,$article_order,$article_status,$article_hot,$article_intro,$author,$source);
			$GLOBALS['T']->assign('defaultUrl',"index.php?act=article_list");
			if($res){
				//$GLOBALS['T']->assign('status',1);
				//$GLOBALS['T']->assign('msg','操作成功');
				echo "<script>window.parent.php_callback('保存成功');setTimeout(function(){window.parent.location.reload();},1500);</script>";exit;
			}else{
				//$GLOBALS['T']->assign('status',2);
				//$GLOBALS['T']->assign('msg','操作失败1');
				echo "<script>window.parent.php_callback('保存失败 ERROR1:{$rs}');</script>";exit;
			}
		}else{
			//$GLOBALS['T']->assign('status',2);
			//$GLOBALS['T']->assign('msg','操作失败2');
			echo "<script>window.parent.php_callback('保存失败 ERROR2:{$rs}');</script>";exit;
		}
		//$GLOBALS['T']->display('admin/info.html');
		
		exit;
	}else{
		if($article_id > 0){
			$article = $cls_article->getArticleInfo($article_id);
			$GLOBALS['T']->assign('info',$article);
		}else{
			$article['cate_id'] = $_REQUEST['cate'];
			$GLOBALS['T']->assign('info',$article);
		}
		$cate = $GLOBALS['DB']->get_results("SELECT * FROM `base_article_cate` WHERE `cate_status`=1 order by `cate_order` DESC ");
		$GLOBALS['T']->assign('cate_list',$cate);
		$GLOBALS['T']->display('admin/article/article_update.html');
	}
}elseif($_REQUEST['act'] == 'article_del'){//
	if(isset($_POST['deletea_ids'])){
		$del_ids = implode(",",$_POST['deletea_ids']);
		$sql = "UPDATE base_article SET article_status = 2 WHERE article_id IN({$del_ids})";
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
	$GLOBALS['T']->assign('defaultUrl',"index.php?act=article_list");
	$GLOBALS['T']->display('admin/info.html');
}elseif($_REQUEST['act'] == 'cate_list'){//列表
	$pid = $_GET['pid'];
	$cate_list = $cls_article->getArticleCateList($pid);
	$GLOBALS['T']->assign('list',$cate_list);
	$GLOBALS['T']->assign('active_class_open','资讯分类');
	$GLOBALS['T']->display('admin/article/cate_list.html');
}elseif($_REQUEST['act'] == 'cate_update'){//
	$cate_id = $_REQUEST['cate_id'];
	if($_POST){
		$parent_id = $_POST['parent_id'];
		$cate_name = $_POST['cate_name'];
		$cate_order = $_POST['cate_order'];
		$cate_status = $_POST['cate_status'];
		if($cate_name != ''){
			$res = $cls_article->getArticleCateUpdate($cate_id,$parent_id,$cate_name,$cate_order,$cate_status);
			$GLOBALS['T']->assign('defaultUrl',"index.php?act=cate_list");
			if($res){
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
		$GLOBALS['T']->display('admin/info.html');
		exit;
	}else{
		if($cate_id > 0){
			$cate = $cls_article->getArticleCateInfo($cate_id);
			$GLOBALS['T']->assign('info',$cate);
		}
		$Pcate = $GLOBALS['DB']->get_results("SELECT * FROM `base_article_cate` WHERE `parent_id`='0' and `cate_status`=1 order by `cate_order` DESC ");
		$GLOBALS['T']->assign('cate_list',$Pcate);
		$GLOBALS['T']->display('admin/article/cate_update.html');
	}
}elseif($_REQUEST['act'] == 'cate_del'){//
	
	if(isset($_POST['deletea_ids'])){
		$del_ids = implode(",",$_POST['deletea_ids']);
		$sql = "UPDATE base_article_cate SET cate_status = 2 WHERE cate_id IN({$del_ids})";
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
	$GLOBALS['T']->assign('defaultUrl',"index.php?act=cate_list");
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
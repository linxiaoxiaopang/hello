
<?php 
	function getFile(){
		if(!isset($_FILES['img'])){
			$GLOBALS['message'] = '你在都逗我吗？';
			return;
		};
		// if(!isset($_FILES['img']['error'])){

		// }
		if($_FILES['img']['error']!==UPLOAD_ERR_OK){
		  	$GLOBALS['message'] = '上传失败';
		  	return;
		};
		if(!is_dir('./upload1/')){
			mkdir('./upload1/');
		};
		$source=$_FILES['img']['tmp_name'];
		$target='./upload1/'.$_FILES['img']['name'];
		$move=move_uploaded_file($source,$target);
		if(!$move){
			$GLOBALS['message'] = '上传失败';
			return;
		}
		$GLOBALS['message'] = '上传成功';

	};
	// header('content-type: text/css');
	if($_SERVER['REQUEST_METHOD']==='POST'){

		var_dump($_FILES);
		getFile();
	}
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<a href="./index.html">back</a>
	<a href="./upload1/">点击</a>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
		<input type="file" name="img" id="">
		<?php if (isset($message)): ?>
			<p><?php echo $message; ?></p>
		<?php endif ?>
		<button>提交</button>
	</form>
</body>
</html>
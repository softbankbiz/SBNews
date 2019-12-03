<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";

session_start();

$mysqli = getConnection();

////////////////// user_id 認証
if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
	return;
}

if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	return;
} else if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
	echo '<script>alert("あなたには編集権限がありません。"); location.href = "/' . BASE . '/";</script>';
	return;
} else if (empty($_POST)) {
	echo 'パラメータが不足しています。';
	return;
} else if (! $_POST['news_id']) {
	echo 'パラメータが不正です。';
	return;
} else if ($_POST['cmd'] == 'upload') {
	$target_dir = 'images/' . $_SESSION["company_id"] . '/' . $_POST['news_id'];
	// top or bottom image
	// if (count($_FILES['file']['name']) == 1) {
	if ($_POST['place'] == 'top' || $_POST['place'] == 'bottom') {
		if (is_uploaded_file($_FILES ['file'] ['tmp_name'])) {
			if (! file_exists($target_dir)) {
				if(! mkdir ($target_dir, 0775, true)) {
					echo 'mkdir error';
					exit;
				}
		    }
		    if ($_POST['place'] == 'top') {
		    	$file = $target_dir . '/title_image.png'; // . $_FILES['file']['name'];
		    } else if ($_POST['place'] == 'bottom') {
		    	$file = $target_dir . '/bottom_image.png';
		    }
		    if (move_uploaded_file($_FILES ['file'] ['tmp_name'], $file)) {
		    	chmod($file, 0644);
		        echo 'アップロードに成功しました。';
		    } else {
		        echo 'アップロードに失敗しました。';
		    }
		} else {
		    echo 'ファイルを選択してください。';
		}
	// category icons
	} else {
		$sucsses = 0;
		for ($i=0; $i<count($_FILES['file']['name']); $i++) {
			if (is_uploaded_file($_FILES ['file'] ['tmp_name'][$i])) {
				if (! file_exists($target_dir)) {
					mkdir ($target_dir, 0755, true);
			    }
			    $file = $target_dir . '/' . $_FILES['file']['name'][$i];
			    if (move_uploaded_file($_FILES ['file'] ['tmp_name'][$i], $file)) {
			    	chmod($file, 0644);
			    	$sucsses += 1;
			    }
			} else {
			    echo 'ファイルを選択してください。';
			}
		}
		if($sucsses == count($_FILES['file']['name'])) {
			echo $sucsses . '個のアップロードに成功しました。';
		} else {
			echo 'アップロードに失敗しました。';
		}
	}
} else if ($_POST['cmd'] == 'delete') {
	$target_dir = 'images/' . $_SESSION["company_id"] . '/' . $_POST['news_id'];
	if ($_POST['place'] == 'top') {
    	$file = $target_dir . '/title_image.png';
    	if (is_file($file)) {
    		if (unlink($file)) {
	    		echo 1;
	    	} else {
	    		echo 0;
	    	}
    	} else {
    		echo 2;
    	}
    	
    } else if ($_POST['place'] == 'bottom') {
    	$file = $target_dir . '/bottom_image.png';
    	if (is_file($file)) {
    		if (unlink($file)) {
	    		echo 1;
	    	} else {
	    		echo 0;
	    	}
    	} else {
    		echo 2;
    	}
    } else if ($_POST['place'] == 'icons') {
    	$count = 0;
    	foreach(glob($target_dir . '/*') as $file){
		    if(is_file($file)){
		    	if ($file != ($target_dir . '/title_image.png') && $file != ($target_dir. '/bottom_image.png')) {
			        if (unlink($file)) {
			    		$count++;
			    	}		    		
		    	}
		    }
		}
		echo $count;
    }
} else {
	echo 'パラメータが不正です。';
	return;
}
exit();
?>

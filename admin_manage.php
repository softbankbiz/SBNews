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
} else if ($_POST['news_id']) {
	$target_dir = 'images/' . $_SESSION["company_id"] . '/' . $_POST['news_id'];
	// top image
	if (count($_FILES['file']['name']) == 1) {
		if (is_uploaded_file($_FILES ['file'] ['tmp_name'])) {
			if (! file_exists($target_dir)) {
				if(! mkdir ($target_dir, 0775, true)) {
					echo 'mkdir error';
					exit;
				}
		    }
		    $file = $target_dir . '/title_image.png'; // . $_FILES['file']['name'];
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
	} else if (count($_FILES['file']['name']) >= 1) {
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
} else {
	echo 'パラメータが不正です。';
	return;
}
exit();
?>
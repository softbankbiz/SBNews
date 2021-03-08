<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";

$mysqli = getConnection();

session_start();

if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	return;
} else if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
	echo '<script>alert("あなたには編集権限がありません。"); location.href = "/' . BASE . '/";</script>';
	return;
} else if (empty($_POST)) {
	echo 'パラメータが不足しています。';
	return;
} else if ($_POST['cmd'] === "delete" && $_POST['news_id']) {
	try {
	    ////////////////// user_id 認証
	    if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
	        return;
	    }
	    $query = "DELETE FROM preference WHERE company_id = ? AND news_id = ?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss", $_SESSION["company_id"], $_POST['news_id']);
		$stmt->execute();
		$result = $stmt->execute();
		if ($result) {
			// 画像格納用ディレクトリも削除
			$target_dir = 'images/' . $_SESSION["company_id"] . '/' . $_POST['news_id'];
			if (file_exists($target_dir)) {
				delTree($target_dir);
			}
		}
		echo $result;
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die("SQL error.");
	}
} else if ($_POST['cmd'] === "update") {
	if ($_POST['news_id'] && $_POST['cid_alias'] && $_POST['rss_id'] && $_POST['category_id'] && $_POST['site_names_id'] &&
		$_POST['default_title'] && $_POST['period_day'] && $_POST['period_hour'] && $_POST['fetch_num'] && $_POST['signature']) {
	    try {
		    ////////////////// user_id 認証
		    if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
		        return;
		    }
		    $query = "UPDATE preference SET cid_alias = ?, rss_id = ?, category_id = ?, site_names_id = ?, " .
		             "default_title = ?, period_day = ?, period_hour = ?, fetch_num = ?, signature = ? WHERE company_id = ? AND news_id = ?";
		    $stmt = $mysqli->prepare($query);
		    $stmt->bind_param("sssssssssss",
		    	              $_POST['cid_alias'], $_POST['rss_id'], $_POST['category_id'], $_POST['site_names_id'],
		    	              $_POST['default_title'], $_POST['period_day'], $_POST['period_hour'], $_POST['fetch_num'], $_POST['signature'], 
		    	              $_SESSION["company_id"], $_POST['news_id']);
		    $stmt->execute();
		    $result = $stmt->execute();
			echo $result;
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die("SQL error.");
		}
	} else {
		die("パラメータが不足しています。");
	}
} else if ($_POST['cmd'] === "add") {
	if ($_POST['news_id'] && $_POST['cid_alias'] && $_POST['rss_id'] && $_POST['category_id'] && $_POST['site_names_id'] &&
		$_POST['default_title'] && $_POST['period_day'] && $_POST['period_hour'] && $_POST['fetch_num'] && $_POST['signature']) {
		try {
		    ////////////////// user_id 認証
		    if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
		        return;
		    }
		    $query = "INSERT INTO preference (news_id,cid_alias,company_id,category_id,rss_id,site_names_id,default_title,period_day,period_hour,fetch_num,signature) " .
                     "VALUES (?,?,?,?,?,?,?,?,?,?,?)";
		    $stmt = $mysqli->prepare($query);
		    $stmt->bind_param("sssssssssss",
		    	              $_POST['news_id'], $_POST['cid_alias'], $_SESSION["company_id"], $_POST['category_id'], $_POST['rss_id'], $_POST['site_names_id'],
		    	              $_POST['default_title'], $_POST['period_day'], $_POST['period_hour'], $_POST['fetch_num'], $_POST['signature']);
			$result = $stmt->execute();
		    echo $result;
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die("SQL error.");
		}
	} else {
		die("パラメータが不足しています。");
	}
} else {
	echo "";
}
exit();
?>
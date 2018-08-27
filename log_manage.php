<?php

require_once dirname(__FILE__) . "/functions.php";

$mysqli = getConnection();

session_start();

# 認証済みかどうかのセッション変数を初期化
if (! isset($_SESSION['auth'])) {
  $_SESSION['auth'] = false;
}

# エラーメッセージ初期化
$error = '';

if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	exit();
} else if (empty($_POST)) {
	echo 'パラメータが不足しています。';
	exit();
} else if ( $_POST['year'] && $_POST['month'] && $_POST['date'] && $_POST['cmd'] ) {
	if ($_POST['cmd'] === "log_rss") {
		try {
		    ////////////////// user_id 認証
		    if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
		        return;
		    }
		    $query = "SELECT title,class_name,url,created,category,confidence,site_name,news_id FROM article_candidate WHERE company_id = ? AND ts > ?";
		    $period = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['date'] . " 00:00:00";
		    $stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $_SESSION["company_id"], $period);
			$stmt->execute();
			$result = $stmt->get_result();
		    $csv = "title,class_name,url,created,category,confidence,site_name,news_id\r\n";
		    while ($row = $result->fetch_assoc()) {
		    	//$csv .= $row["title"] . "," . $row["class_name"] . "," . $row["url"] . "," . $row["created"] . "," . $row["category"] . "," . $row["confidence"] . "," . $row["site_name"] . "," . $row["news_id"] . "\n";
		    	$row["title"] = str_replace(',', '', $row["title"]);
		    	$csv .= implode(",", $row). "\r\n";
		    }
		    $csv = pack('C*',0xEF,0xBB,0xBF). $csv;
		    header('Content-Type: application/force-download');
		    header('Content-Disposition: attachment; filename="log_rss_' . $period . '.csv"');
		    echo trim($csv);
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
	} else if ($_POST['cmd'] === "log_click") {
		try {
		    ////////////////// user_id 認証
		    if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
		        return;
		    }
		    $query = "SELECT news_id,url,issue,ts FROM click_counter WHERE company_id = ? AND ts > ?";
		    $period = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['date'] . " 00:00:00";
		    $stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $_SESSION["company_id"], $period);
			$stmt->execute();
			$result = $stmt->get_result();
		    $csv = "ニュースID,url,発行年月日,タイムスタンプ\n";
		    while ($row = $result->fetch_assoc()) {
		    	$csv .= implode(",", $row). "\r\n";
		    }
		    header('Content-Type: application/force-download');
		    header('Content-Disposition: attachment; filename="log_click_' . $period . '.csv"');
		    echo $csv;
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
	} else if ($_POST['cmd'] === "log_access") {
		try {
		    ////////////////// user_id 認証
		    if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
		        return;
		    }
		    $query = "SELECT news_id,issue,ts FROM access_counter WHERE company_id = ? AND ts > ?";
		    $period = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['date'] . " 00:00:00";
		    $stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $_SESSION["company_id"], $period);
			$stmt->execute();
			$result = $stmt->get_result();
		    $csv = "ニュースID,発行年月日,タイムスタンプ\n";
		    while ($row = $result->fetch_assoc()) {
		    	$csv .= implode(",", $row). "\r\n";
		    }
		    header('Content-Type: application/force-download');
		    header('Content-Disposition: attachment; filename="log_access_' . $period . '.csv"');
		    echo $csv;
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
	}
}

exit();
?>
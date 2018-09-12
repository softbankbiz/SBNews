<?php

require_once dirname(__FILE__) . "/functions.php";

$mysqli = getConnection();

session_start();

////////////////// user_id 認証
if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
    exit;
}

# 認証済みかどうかのセッション変数を初期化
if (! isset($_SESSION['auth'])) {
  $_SESSION['auth'] = false;
}


if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	exit;
} else if (empty($_POST)) {
	echo 'パラメータが不足しています。';
	exit;
} else if ( $_POST['year_s'] && $_POST['month_s'] && $_POST['date_s'] && $_POST['year_e'] && $_POST['month_e'] && $_POST['date_e'] && $_POST['cmd'] ) {
	if ( ! yyyymmdd($_POST['year_s'], $_POST['month_s'], $_POST['date_s']) || ! yyyymmdd($_POST['year_e'], $_POST['month_e'], $_POST['date_e']) ) {
		echo '日付のフォーマットが違っています。';
		exit;
	}
	if ($_POST['cmd'] === "log_rss") {
		try {
		    $query = "SELECT title,class_name,url,created,category,confidence,site_name,news_id,cid,cid_alias FROM article_candidate WHERE company_id = ? AND ts > ? AND ts < ?";
		    $period_s = $_POST['year_s'] . "-" . $_POST['month_s'] . "-" . $_POST['date_s'] . " 00:00:00";
		    $period_e = $_POST['year_e'] . "-" . $_POST['month_e'] . "-" . $_POST['date_e'] . " 23:59:59";
		    $stmt = $mysqli->prepare($query);
			$stmt->bind_param("sss", $_SESSION["company_id"], $period_s, $period_e);
			$stmt->execute();
			$result = $stmt->get_result();
		    $csv = "記事タイトル,分類名,url,記事作成日,カテゴリー,確信度,サイト名,ニュースID,分類子,分類子エイリアス\r\n";
		    while ($row = $result->fetch_assoc()) {
		    	$row["title"] = str_replace(',', '', $row["title"]);
		    	$csv .= implode(",", $row). "\r\n";
		    }
		    $csv = pack('C*',0xEF,0xBB,0xBF). $csv;
		    $filename = explode(' ', $period_s)[0] . '_' . explode(' ', $period_e)[0];
		    header('Content-Type: application/force-download');
		    header('Content-Disposition: attachment; filename="log_rss_' . $filename . '.csv"');
		    echo trim($csv);
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
	} else if ($_POST['cmd'] === "log_click") {
		try {
		    $query = "SELECT news_id,url,issue,ts FROM click_counter WHERE company_id = ? AND ts > ? AND ts < ?";
		    $period_s = $_POST['year_s'] . "-" . $_POST['month_s'] . "-" . $_POST['date_s'] . " 00:00:00";
		    $period_e = $_POST['year_e'] . "-" . $_POST['month_e'] . "-" . $_POST['date_e'] . " 23:59:59";
		    $stmt = $mysqli->prepare($query);
			$stmt->bind_param("sss", $_SESSION["company_id"], $period_s, $period_e);
			$stmt->execute();
			$result = $stmt->get_result();
		    $csv = "ニュースID,url,発行年月日,タイムスタンプ\n";
		    while ($row = $result->fetch_assoc()) {
		    	$csv .= implode(",", $row). "\r\n";
		    }
		    $csv = pack('C*',0xEF,0xBB,0xBF). $csv;
		    $filename = explode(' ', $period_s)[0] . '_' . explode(' ', $period_e)[0];
		    header('Content-Type: application/force-download');
		    header('Content-Disposition: attachment; filename="log_click_' . $filename . '.csv"');
		    echo trim($csv);
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
	} else if ($_POST['cmd'] === "log_access") {
		try {
		    $query = "SELECT news_id,issue,ts FROM access_counter WHERE company_id = ? AND ts > ? AND ts < ?";
		    $period_s = $_POST['year_s'] . "-" . $_POST['month_s'] . "-" . $_POST['date_s'] . " 00:00:00";
		    $period_e = $_POST['year_e'] . "-" . $_POST['month_e'] . "-" . $_POST['date_e'] . " 23:59:59";
		    $stmt = $mysqli->prepare($query);
			$stmt->bind_param("sss", $_SESSION["company_id"], $period_s, $period_e);
			$stmt->execute();
			$result = $stmt->get_result();
		    $csv = "ニュースID,発行年月日,タイムスタンプ\n";
		    while ($row = $result->fetch_assoc()) {
		    	$csv .= implode(",", $row). "\r\n";
		    }
		    $csv = pack('C*',0xEF,0xBB,0xBF). $csv;
		    $filename = explode(' ', $period_s)[0] . '_' . explode(' ', $period_e)[0];
		    header('Content-Type: application/force-download');
		    header('Content-Disposition: attachment; filename="log_access_' . $filename . '.csv"');
		    echo trim($csv);
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
	}
} else {
	echo 'パラメータが不正です。';
	exit();
}

exit();
?>
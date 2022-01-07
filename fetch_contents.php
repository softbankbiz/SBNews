<?php

require_once dirname(__FILE__) . "/functions.php";
require_once dirname(__FILE__) . "/Feed.php";

session_start();

# 認証済みかどうかのセッション変数を初期化
if (! isset($_SESSION['auth'])) {
  $_SESSION['auth'] = false;
}

$mysqli = getConnection();


if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	exit();
} else if (empty($_GET)) {
	echo 'パラメータが不足しています。';
	exit();
} else {
	if ($_GET['news_id'] && two_step_auth($mysqli, $_SESSION['company_id'], $_SESSION['user_id'])) {
		try {
			$query_rss = "SELECT rss_data FROM rss_list WHERE rss_id = (SELECT rss_id FROM preference WHERE company_id = ? AND news_id = ?) AND company_id = ?";
			$stmt = $mysqli->prepare($query_rss);
			$stmt->bind_param("sss", $_SESSION["company_id"], $_GET['news_id'], $_SESSION["company_id"]);
			$stmt->execute();
			$rss_data = $stmt->get_result();
			$rss_arr = explode("\n", $rss_data->fetch_array(MYSQLI_NUM)[0]);

			$query_site_names = "SELECT site_names_data FROM site_names_list WHERE site_names_id = (SELECT site_names_id FROM preference WHERE company_id = ? AND news_id = ?) AND company_id = ?";
			$stmt = $mysqli->prepare($query_site_names);
			$stmt->bind_param("sss", $_SESSION["company_id"], $_GET['news_id'], $_SESSION["company_id"]);
			$stmt->execute();
			$site_names_data = $stmt->get_result();
			$site_names_arr = explode("\n", $site_names_data->fetch_array(MYSQLI_NUM)[0]);

			$insert_suffix = '","' . $_SESSION["company_id"] . '","' . $_GET["news_id"] . '"';

			$msg = do_fetch($mysqli, $rss_arr, $site_names_arr, $insert_suffix);
			$mysqli->close();

      echo $msg;

		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
	} else {
		echo 'パラメータが不正です。';
	}
}
?>

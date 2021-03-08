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
} else if ($_POST['rss_id']) {
	////////////////// user_id 認証
    if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
        return;
    }

    $rss_id = mb_escape($_POST['rss_id']);

	if ($_POST['cmd'] === "update" && $_POST['rss_data']) {
		try {
			// rss_idが同一かテスト
			$query_test = "SELECT count(rss_id) FROM rss_list WHERE rss_id = ? AND company_id = ?";
			$stmt = $mysqli->prepare($query_test);
		    $stmt->bind_param("ss", $rss_id, $_SESSION["company_id"]);
		    $stmt->execute();

		    $res_test = $stmt->get_result();
		    $row = $res_test->fetch_row();

		    if ($row[0] != 0) {
		    	// 同一
			    $query = "UPDATE rss_list SET rss_data = ? WHERE rss_id = ? AND company_id = ?";
			    $rss_data = $_POST['rss_data'];
			    $stmt = $mysqli->prepare($query);
				$stmt->bind_param("sss", $rss_data, $rss_id, $_SESSION["company_id"]);
				$result = $stmt->execute();
			    echo $result;
			} else {
				// 同一じゃない
				echo "RSSリスト IDが一致しません。";
			}
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die("SQL error.");
		}
	} else if ($_POST['cmd'] === "insert" && $_POST['rss_data']) {
		try {
		    ////// rss_idの重複テスト
		    $query_test = "SELECT count(rss_id) FROM rss_list WHERE rss_id = ? AND company_id = ?";
		    $stmt = $mysqli->prepare($query_test);
		    $stmt->bind_param("ss", $rss_id, $_SESSION["company_id"]);
		    $stmt->execute();

		    $res_test = $stmt->get_result();
		    $row = $res_test->fetch_row();

		    if ($row[0] == 0) {
		    	// 重複していない
		    	$query = "INSERT INTO rss_list (rss_id, rss_data, company_id) VALUES (?,?,?)";
		    	$rss_data = $_POST['rss_data'];
		    	$stmt = $mysqli->prepare($query);
				$stmt->bind_param("sss", $rss_id, $rss_data, $_SESSION["company_id"]);
				$result = $stmt->execute();
		    	echo $result;
		    } else {
		    	// 重複している
		    	echo "RSSリスト IDが重複しています。";
		    }
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die("SQL error.");
		}
	} else if ($_POST['cmd'] === "delete") {
		try {
		    $query = "DELETE FROM rss_list WHERE rss_id = ? AND company_id = ?";
		    $stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $rss_id, $_SESSION["company_id"]);
			$result = $stmt->execute();
		    echo $result;
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die("SQL error.");
		}
	}
}
exit();
?>
<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";

session_start();

$mysqli = getConnection();

if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	return;
} else if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
	echo '<script>alert("あなたには編集権限がありません。"); location.href = "/' . BASE . '/";</script>';
	return;
} else if (empty($_POST)) {
	echo 'パラメータが不足しています。';
	return;
} else if ($_POST['site_names_id']) {
	////////////////// user_id 認証
    if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
        return;
    }

    $site_names_id = mb_escape($_POST['site_names_id']);

	if ($_POST['cmd'] === "update" && $_POST['site_names_data']) {
		try {
			// site_names_idが同一かテスト
			$query_test = "SELECT count(site_names_id) FROM site_names_list WHERE site_names_id = ? AND company_id = ?";
			$stmt = $mysqli->prepare($query_test);
		    $stmt->bind_param("ss", $site_names_id, $_SESSION["company_id"]);
		    $stmt->execute();

		    $res_test = $stmt->get_result();
		    $row = $res_test->fetch_row();

		    if ($row[0] != 0) {
		    	// 同一
				$query = "UPDATE site_names_list SET site_names_data = ? WHERE site_names_id = ? AND company_id = ?";
				$site_names_data = $_POST['site_names_data'];
			    $stmt = $mysqli->prepare($query);
				$stmt->bind_param("sss", $site_names_data, $site_names_id, $_SESSION["company_id"]);
				$result = $stmt->execute();
			    echo $result;
			} else {
				// 同一じゃない
				echo "サイト名リスト IDが一致しません。";
			}
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die("SQL error.");
		}
	} else if ($_POST['cmd'] === "insert" && $_POST['site_names_data']) {
		try {
		    ////// site_names_idの重複テスト
			$query_test = "SELECT count(site_names_id) FROM site_names_list WHERE site_names_id = ? AND company_id = ?";
			$stmt = $mysqli->prepare($query_test);
		    $stmt->bind_param("ss", $site_names_id, $_SESSION["company_id"]);
		    $stmt->execute();

		    $res_test = $stmt->get_result();
		    $row = $res_test->fetch_row();

		    if ($row[0] == 0) {
		    	// 重複していない
		    	$query = "INSERT INTO site_names_list (site_names_id, site_names_data, company_id) VALUES (?,?,?)";
		    	$site_names_data = $_POST['site_names_data'];
		    	$stmt = $mysqli->prepare($query);
				$stmt->bind_param("sss", $site_names_id, $site_names_data, $_SESSION["company_id"]);
				$result = $stmt->execute();
		    	echo $result;
		    } else {
		    	// 重複している
		    	echo "site_names_idが重複しています。";
		    }
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die("SQL error.");
		}
	} else if ($_POST['cmd'] === "delete") {
		try {
			$query = "DELETE FROM site_names_list WHERE site_names_id = ? AND company_id = ?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $site_names_id, $_SESSION["company_id"]);
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
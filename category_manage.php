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
} else if ($_POST['category_id']) {
	////////////////// user_id 認証
	if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
		return;
	}

	$category_id = mb_escape($_POST['category_id']);

	if ($_POST['cmd'] === "update" && $_POST['category_data']) {
		try {
			// category_idが同一かテスト
			$query_test = "SELECT count(category_id) FROM category_list WHERE category_id = ? AND company_id = ?";
			$stmt = $mysqli->prepare($query_test);
		    $stmt->bind_param("ss", $category_id, $_SESSION["company_id"]);
		    $stmt->execute();

		    $res_test = $stmt->get_result();
		    $row = $res_test->fetch_row();

		    if ($row[0] != 0) {
		    	// 同一
			    $query = "UPDATE category_list SET category_data = ? WHERE category_id = ? AND company_id = ?";
			    $category_data = $_POST['category_data'];
			    $stmt = $mysqli->prepare($query);
				$stmt->bind_param("sss", $category_data, $category_id, $_SESSION["company_id"]);
				$result = $stmt->execute();
			    echo $result;
			} else {
				// 同一じゃない
				echo "カテゴリ リスト IDが一致しません。";
			}
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die("SQL error.");
		}
	} else if ($_POST['cmd'] === "insert" && $_POST['category_data']) {
		try {
		    ////// category_idの重複テスト, PoCやデモ環境では複数企業が混ざるので、category_idにユニーク制約をつけられない
		    $query_test = "SELECT count(category_id) FROM category_list WHERE category_id = ? AND company_id = ?";
		    $stmt = $mysqli->prepare($query_test);
		    $stmt->bind_param("ss", $category_id, $_SESSION["company_id"]);
		    $stmt->execute();

		    $res_test = $stmt->get_result();
		    $row = $res_test->fetch_row();

		    if ($row[0] == 0) {
		    	// 重複していない
		    	$query = "INSERT INTO category_list (category_id, category_data, company_id) VALUES (?,?,?)";
		    	$category_data = $_POST['category_data'];
		    	$stmt = $mysqli->prepare($query);
				$stmt->bind_param("sss", $category_id, $category_data, $_SESSION["company_id"]);
				$result = $stmt->execute();
		    	echo $result;
		    } else {
		    	// 重複している
		    	echo "カテゴリ リスト IDが重複しています。";
		    }
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die("SQL error.");
		}
	} else if ($_POST['cmd'] === "delete") {
		try {
			$query = "DELETE FROM category_list WHERE category_id = ? AND company_id = ?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $category_id, $_SESSION["company_id"]);
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
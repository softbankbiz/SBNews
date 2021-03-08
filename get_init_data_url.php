<?php

require_once dirname(__FILE__) . "/functions.php";

session_start();

$mysqli = getConnection();

////////////////// user_id 認証
if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
    return;
}

$result = array();

if ($_POST["news_id"]) {
	$query_a = "SELECT category_data FROM category_list WHERE category_id = (SELECT category_id FROM preference WHERE company_id = ? AND news_id = ?) AND company_id = ?";
	$query_b = "SELECT default_title,period_day,period_hour,fetch_num,signature from preference WHERE company_id = ? AND news_id = ?";
} else {
	die("パラメータが足りません");
}

try {
	$stmt_a = $mysqli->prepare($query_a);
    $stmt_a->bind_param("sss", $_SESSION["company_id"], $_POST["news_id"], $_SESSION["company_id"]);
    $stmt_a->execute();
    $category_data = $stmt_a->get_result();

    $stmt_b = $mysqli->prepare($query_b);
    $stmt_b->bind_param("ss", $_SESSION["company_id"], $_POST["news_id"]);
    $stmt_b->execute();
    $preference = $stmt_b->get_result();

	if($category_data !== FALSE and $preference !== FALSE) {
		$category_name = array();
		$category_icon = array();
		$lines = $category_data->fetch_array(MYSQLI_NUM)[0];
		$arr = explode("\n", $lines);
		
		foreach($arr as $num => $line) {
			if ($num !== 0) {
				$items = explode(",", $line);
				array_push($category_name, $items[0]);
				$category_icon[$items[0]] = $items[1];
			}
		}
		$result["category_name"] = $category_name;
		$result["category_icon"] = $category_icon;

		foreach($preference as $pref) {
			$result["default_title"]  = $pref["default_title"];
			$result["period_day"]     = $pref["period_day"];
			$result["period_hour"]    = $pref["period_hour"];
			$result["fetch_num"]      = $pref["fetch_num"];
			$result["signature"]      = $pref["signature"];
		}
		echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	} else {
		echo "ng1";
	}
} catch (mysqli_sql_exception $e) {
    throw $e;
    die("ng2");
}

?>
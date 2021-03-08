<?php

require_once dirname(__FILE__) . "/functions.php";

session_start();

$mysqli = getConnection();

////////////////// user_id 認証
if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
    return;
}

// ランキングが取れている日付リストの取得
if ($_POST["ranking_issue_list"] == 'exec' && $_POST["news_id"]) {
	$query = "SELECT DISTINCT(issue) FROM click_counter WHERE company_id = ? AND news_id = ? ORDER BY issue DESC LIMIT 30";
	try {
    	$stmt = $mysqli->prepare($query);
	    $stmt->bind_param("ss", $_SESSION["company_id"], $_POST["news_id"]);
	    $stmt->execute();
	    $result = $stmt->get_result();

    	if ($result !== FALSE) {
    		while ($row = $result->fetch_assoc()) {
	            $ranking_issue_list[] = $row["issue"];
	        }
    		echo json_encode($ranking_issue_list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    	} else {
    		echo "ng1";
    	}
    } catch (mysqli_sql_exception $e) {
	    throw $e;
	    die("ng2");
	}

// 指定した発行号のランキングデータ取得
} else if ($_POST["issue"] && $_POST["news_id"]) {
	$query = "SELECT DISTINCT(url) AS unique_url, " .
	         //"(SELECT title FROM article_candidate WHERE url = unique_url AND company_id = ? AND news_id = ?) AS unique_title, " .
			 "title AS unique_title, " .
	         "(SELECT count(unique_url) FROM click_counter WHERE url = unique_url AND issue = ? AND company_id = ? AND news_id = ?) AS cnt " .
	         "FROM click_counter WHERE issue = ? AND company_id = ? AND news_id = ? ORDER BY cnt DESC";
	try {
    	$stmt = $mysqli->prepare($query);
	    $stmt->bind_param("ssssss",
	    	              $_POST["issue"], $_SESSION["company_id"],
	    	              $_POST["news_id"], $_POST["issue"], $_SESSION["company_id"], $_POST["news_id"]);
	    $stmt->execute();
	    $result = $stmt->get_result();

    	if ($result !== FALSE) {
    		while ($row = $result->fetch_assoc()) {
    			$ranking[] = array($row["unique_url"],$row["unique_title"],$row["cnt"]);
    		}
    		echo json_encode($ranking, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    	} else {
    		echo "ng1";
    	}
    } catch (mysqli_sql_exception $e) {
	    throw $e;
	    die("ng2");
	}
} else {
	die("パラメータが足りません");
}

?>
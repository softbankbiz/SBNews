<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";
require_once dirname(__FILE__) . "/WatsonNLC.php";

session_start();

$mysqli = getConnection();

if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	exit();
} else if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
	echo '<script>alert("あなたには編集権限がありません。"); location.href = "/' . BASE . '/";</script>';
	return;
} else if (empty($_GET)) {
	echo 'パラメータが不足しています。';
	exit();
} else {
	if (isset($_GET['news_id']) && !is_null($_GET['news_id'])) {
		try {
			if (! two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
		        return;
		    }
		    $counter = 0;

		    // コンテンツの取得
		    $query_a = "SELECT url,title,summary from article_candidate WHERE class_name IS NULL AND company_id = ? AND news_id = ?";
		    $stmt = $mysqli->prepare($query_a);
		    $stmt->bind_param("ss", $_SESSION["company_id"], $_GET["news_id"]);
		    $stmt->execute();
			$contents_for_judgement = $stmt->get_result();

			// cid_alias の取得
			$query_b = "SELECT cid_alias FROM preference WHERE company_id = ? AND news_id = ?";
			$stmt = $mysqli->prepare($query_b);
		    $stmt->bind_param("ss", $_SESSION["company_id"], $_GET["news_id"]);
		    $stmt->execute();
		    $cid_alias = $stmt->get_result()->fetch_array(MYSQLI_NUM)[0];

		    if ($cid_alias == 'dummy_watson') {
		    	foreach($contents_for_judgement as $content) {
			    	$watson_res = dummy_watson();
			    	if (!empty($watson_res)) {
						$res = json_decode($watson_res);
						update_watson_res($mysqli, $content["url"], $res->{"classes"}[0]->{"class_name"}, $res->{"classes"}[0]->{"confidence"});
						$counter++;
					} else {
						echo "No data!";
					}
				}
				echo "Dummy Watson Judgemant had " . $counter . " updated.<br>";
		    } else {
		    	$result = get_configuration($mysqli, $_SESSION["company_id"]);
		    	$w_username = $result["w_username"];
				$w_password = $result["w_password"];		
				$query_d = "SELECT cid FROM classifier_list WHERE company_id = ? AND cid_alias = ?";
				$stmt = $mysqli->prepare($query_d);
				$stmt->bind_param("ss", $_SESSION["company_id"], $cid_alias);
				$stmt->execute();
				$cid = $stmt->get_result()->fetch_array(MYSQLI_NUM)[0];
				$wnlc = new WatsonNLC;
				foreach($contents_for_judgement as $content) {
					$text_to_judge = $content["title"] . $content["summary"];
					$watson_res = $wnlc->classify_phrase($w_username, $w_password, $cid, $text_to_judge);

					if (!empty($watson_res)) {
						$res = json_decode($watson_res);
						update_watson_res($mysqli, $content["url"], $res->{"classes"}[0]->{"class_name"}, $res->{"classes"}[0]->{"confidence"});
						$counter++;
					} else {
						echo "No data!";
					}
				}
				echo "Watson Judgemant had " . $counter . " updated.<br>";
		    }
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
	} else {
		echo 'パラメータが不正です。';
	}
}
?>
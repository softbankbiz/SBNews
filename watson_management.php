<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";
require_once dirname(__FILE__) . "/WatsonNLC.php";

session_start();


if (! isset($_SESSION['auth'])) {
  $_SESSION['auth'] = false;
}

$mysqli = getConnection();

if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	exit();
} else if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
	echo '<script>alert("あなたには編集権限がありません。"); location.href = "/' . BASE . '/";</script>';
	return;
} else if (is_null($_POST["cmd"])) {
	die("パラメータが足りません");
} else {
	try {
		if (! two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
	    die("Who?");
	  }
    $result = get_configuration($mysqli, $_SESSION["company_id"]);
    $w_apikey = $result["w_apikey"];
		$w_url = $result["w_url"];

		if ($_POST["cmd"] === "create") {
			if ($_POST["training_data"] && $_POST["training_data_name"]) {
				$wnlc = new WatsonNLC;
				$watson_res = $wnlc->create_classifier($w_apikey, $w_url, $_POST["training_data"], $_POST["training_data_name"]);
				if (!empty($watson_res)) {
					$res = json_decode($watson_res);
					$cid = $res->{"classifier_id"};
					try {
						$query_b = "INSERT INTO classifier_list (cid,cid_alias,company_id) VALUES (?,?,?)";
						$stmt_b = $mysqli->prepare($query_b);
						$stmt_b->bind_param("sss", $cid, $_POST["training_data_name"], $_SESSION["company_id"]);
						$result = $stmt_b->execute();
						if ($result == 1) {
							echo "ok";
						} else {
							echo $watson_res;
						}
					} catch (mysqli_sql_exception $e) {
					    throw $e;
					    echo "SQL error.";
					}
				} else {
					echo "null";
				}
			} else {
				echo "No data!";
			}
		} else if ($_POST["cmd"] === "list") {
			$wnlc = new WatsonNLC;
			$result_c = $wnlc->list_classifiers($w_apikey, $w_url);
			if ($result_c) {
				echo json_encode($result_c, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			} else {
				echo "No data!<br>";
			}
		} else if ($_POST["cmd"] === "configuration") {
			if (! $_POST["apikey"] || ! $_POST["url"]) {
				die ("パラメータが不足しています。");
			}
			////// company_idの重複テスト
			$query_test = "SELECT count(company_id) FROM configuration WHERE company_id = ?";
			$stmt = $mysqli->prepare($query_test);
		  $stmt->bind_param("s", $_SESSION["company_id"]);
		  $stmt->execute();

		  $res_test = $stmt->get_result();
		  $row = $res_test->fetch_row();

		  if ($row[0] == 0) {
		  	// 重複していないのでINSERT
		  	$query_insert = "INSERT INTO configuration (w_apikey,w_url,company_id) VALUES (?,?,?)";
		  	$stmt = $mysqli->prepare($query_insert);
				$stmt->bind_param("sss", $_POST["apikey"], $_POST["url"], $_SESSION["company_id"]);
				$result = $stmt->execute();
		    echo $result;
		  } else {
		  	// 重複しているのでUPDATE
		  	$query_update = "UPDATE configuration SET w_apikey = ?, w_url = ? WHERE company_id = ?";
		   	$stmt = $mysqli->prepare($query_update);
		   	$stmt->bind_param("sss", $_POST["apikey"], $_POST["url"], $_SESSION["company_id"]);
		   	$result = $stmt->execute();
		   	echo $result;
		   }
		} else if ($_POST["cmd"] === "delete") {
			if ($_POST["cid"] && $_POST["cid_alias"]) {
				$wnlc = new WatsonNLC;
				$result_e = $wnlc->delete_classifier($w_apikey, $w_url, $_POST["cid"]);
				if ($result_e) {
					$query_e = "UPDATE preference SET cid_alias = \"dummy_watson\" WHERE company_id = ? AND cid_alias = ?";
					$query_f = "DELETE FROM classifier_list WHERE company_id = ? AND cid = ?";
					$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
					try {
						$stmt_e = $mysqli->prepare($query_e);
						$stmt_e->bind_param("ss", $_SESSION["company_id"], $_POST["cid_alias"]);
						$stmt_e->execute();
						$stmt_f = $mysqli->prepare($query_f);
						$stmt_f->bind_param("ss", $_SESSION["company_id"], $_POST["cid"]);
						$stmt_f->execute();
						$mysqli->commit();
						echo "deleted";
					} catch (mysqli_sql_exception $e) {
						$mysqli->rollback();
						echo "deleted but .....";
					}
				} else {
					echo "NG";
				}
			}
		} else {
			die("error!");
		}
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}
?>

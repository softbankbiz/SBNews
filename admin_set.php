<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";

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
} else {
	print_header("管理者メニュー", $_SESSION);
	print_mennu($_GET['page']);
	if($_GET["task"] == "watson_account") {
		try {
			if (! two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
		        die("Who?");
	    }
      // DBに列（w_apikey）を追加
      $query_info_apikey = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'sbnews_db' AND TABLE_NAME = 'configuration' AND COLUMN_NAME = 'w_apikey'";
      $result_apikey = $mysqli->query($query_info_apikey);
      if ($result_apikey->num_rows == 0) {
        $query_A = "ALTER TABLE configuration ADD w_apikey VARCHAR (80)";
        $result_A = $mysqli->query($query_A);
        if ($result_A) {
          // echo "create w_apikey column.";
        } else {
          // echo "could not create w_apikey column.";
        }
      } else {
        // echo "w_apikey exist.";
      }
      // DBに列（w_url）を追加
      $query_info_url = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'sbnews_db' AND TABLE_NAME = 'configuration' AND COLUMN_NAME = 'w_url'";
      $result_url = $mysqli->query($query_info_url);
      if ($result_url->num_rows == 0) {
        $query_U = "ALTER TABLE configuration ADD w_url VARCHAR (200)";
        $result_U = $mysqli->query($query_U);
        if ($result_U) {
          // echo "create w_url column.";
        } else {
          // echo "could not create w_url column.";
        }
      } else {
        // echo "w_url exist.";
      }
      //
	    $result = get_configuration($mysqli, $_SESSION["company_id"]);

      // 2つのテーブル(classifier_list, article_candidate)に、cidのDBカラムを32に増量
      $query_info_cid = "SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'sbnews_db' AND TABLE_NAME = 'classifier_list' AND COLUMN_NAME = 'cid'";
      $result_cid = $mysqli->query($query_info_cid)->fetch_row()[0];
      if ($result_cid == 24) {
        // echo($result_cid);
        $query = "ALTER TABLE classifier_list MODIFY cid VARCHAR(36)";
        $result = $mysqli->query($query);
        if ($result) {
          // echo "changed cid to 36.";
        } else {
          die("classifier_list: could not change cid to 36.");
        }
      }
      $query_info_cid = "SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'sbnews_db' AND TABLE_NAME = 'article_candidate' AND COLUMN_NAME = 'cid'";
      $result_cid = $mysqli->query($query_info_cid)->fetch_row()[0];
      if ($result_cid == 24) {
        // echo($result_cid);
        $query = "ALTER TABLE article_candidate MODIFY cid VARCHAR(36)";
        $result = $mysqli->query($query);
        if ($result) {
          // echo "changed cid to 36.";
        } else {
          die("article_candidate: could not change cid to 36.");
        }
      }


		} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
		}
?>
			<div class="main_area">
				<h3>Watson NLU（Natural Language Understanding）のサービス資格情報を登録</h3>
				<table class="conf_table">
					<tr>
						<th id="label_apikey">API鍵</th>
            <td>
              <input type="text" name="apikey" id="apikey" size="58" value="<?php echo $result["w_apikey"]; ?>">
            </td>
					</tr>
					<tr>
						<th id="label_url">URL</th>
						<td>
							<input type="text" name="url" id="url" size="58" value="<?php echo $result["w_url"]; ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:center"><button id="submit">登録する</button></td>
					</tr>
				</table>
			</div>
			<br><br>
			<div>
				<a href="/<?php echo BASE; ?>/?page=admin_menu"><button class="button_back">戻る</button></a>
			</div>
			<script>
				var watson_management = "watson_management.php";
				document.getElementById('submit').addEventListener('click', function (evt) {
					$.post(watson_management,
				    {
				        apikey: $("#apikey").val(),
				        url:    $("#url").val(),
				        cmd:    "configuration",
				    },
				    function(data, status){
				        if(status == 'success') {
				        	//alert(data);
				        	if (data == 1) {
				        		alert("Watson NLUのサービス資格情報を登録しました。");
				        		location.href = "/<?php echo BASE; ?>/?page=admin_menu";
				        	} else {
				        		alert("Watson NLUのサービス資格情報の登録に失敗しました。");
				        		location.href = "/<?php echo BASE; ?>/?page=admin_menu";
				        	}
				        } else {
				        	alert("error.");
				        }
					});
				});
			</script>
<?php
	} else {
		echo "<br><br>パラメータが不正です。";
	}
	print_javascript("others");
	print_footer();
}
exit();
?>

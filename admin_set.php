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
<<<<<<< HEAD
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
		} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
		}
?>
			<div class="main_area">
				<h3>Watson NLC（Natural Language Classifier）のサービス資格情報を登録</h3>
				<table class="conf_table">
					<tr>
						<th id="label_apikey">apikey</th>
            <td>
              <input type="text" name="apikey" id="apikey" size="58" value="<?php echo $result["w_apikey"]; ?>">
            </td>
					</tr>
					<tr>
						<th id="label_url">url</th>
						<td>
							<input type="text" name="url" id="url" size="58" value="<?php echo $result["w_url"]; ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:center"><button id="submit">登録する</button></td>
=======
		    }
		    $result = get_configuration($mysqli, $_SESSION["company_id"]);
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
?>
			<div class="main_area">
				<h3>Watson NLCの資格情報を登録</h3>
				<table class="conf_table">
					<tr>
						<th>認証方式を選ぶ</th>
						<td>
							<?php
							if ($result["w_username"] == 'apikey' || $result["w_username"] == '') {
								echo '<input type="radio" name="auth_type" id="apikey" value="apikey" checked> API鍵　　';
								echo '<input type="radio" name="auth_type" id="idpass" value="idpass"> ユーザーネーム／パスワード';
							} else {
								echo '<input type="radio" name="auth_type" id="apikey" value="apikey"> API鍵　　';
								echo '<input type="radio" name="auth_type" id="idpass" value="idpass" checked> ユーザーネーム／パスワード';
							}
							?>
						</td>
					</tr>
					<tr>
						<?php
						if ($result["w_username"] == 'apikey' || $result["w_username"] == '') {
							echo '<th id="label_username">ラベル（変更不可）</th>';
							echo '<td><input type="text" name="username" id="_username" size="40" value="apikey" disabled="true"></td>';
						} else {
							echo '<th id="label_username">ユーザーネーム</th>';
							echo '<td><input type="text" name="username" id="_username" size="40" value="';
							echo $result["w_username"];
							echo '"></td>';
						}
						?>
					</tr>
					<tr>
						<?php
						if ($result["w_username"] == 'apikey') {
							echo '<th id="label_password">API鍵</th>';
						} else {
							echo '<th id="label_password">パスワード</th>';
						}
						?>
						<td>
							<input type="password" name="password" id="_password" size="40" value="<?php echo $result["w_password"]; ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2"><button id="submit">登録する</button></td>
>>>>>>> origin/master
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
<<<<<<< HEAD
				        apikey: $("#apikey").val(),
				        url:    $("#url").val(),
				        cmd:    "configuration",
=======
				        username:   $("#_username").val(),
				        password:   $("#_password").val(),
				        cmd:        "configuration",
>>>>>>> origin/master
				    },
				    function(data, status){
				        if(status == 'success') {
				        	//alert(data);
				        	if (data == 1) {
<<<<<<< HEAD
				        		alert("Watson NLCのサービス資格情報を登録しました。");
				        		location.href = "/<?php echo BASE; ?>/?page=admin_menu";
				        	} else {
				        		alert("Watson NLCのサービス資格情報の登録に失敗しました。");
=======
				        		alert("Watson NLCの資格情報を登録しました。");
				        		location.href = "/<?php echo BASE; ?>/?page=admin_menu";
				        	} else {
				        		alert("Watson NLCの資格情報の登録に失敗しました。");
>>>>>>> origin/master
				        		location.href = "/<?php echo BASE; ?>/?page=admin_menu";
				        	}
				        } else {
				        	alert("error.");
				        }
					});
				});
<<<<<<< HEAD
=======
				document.getElementById('apikey').addEventListener('click', function (evt) {
					document.getElementById('label_username').innerHTML = 'ラベル（変更不可）';
					document.getElementById('_username').setAttribute('value','apikey');
					document.getElementById('_username').setAttribute('disabled','true');

					document.getElementById('label_password').innerHTML = 'API鍵';
					document.getElementById('_password').setAttribute('value','');
				});
				document.getElementById('idpass').addEventListener('click', function (evt) {
					document.getElementById('label_username').innerHTML = 'ユーザーネーム';
					document.getElementById('_username').setAttribute('value','');
					document.getElementById('_username').removeAttribute('disabled');

					document.getElementById('label_password').innerHTML = 'パスワード';
					document.getElementById('_password').setAttribute('value','');
				});
>>>>>>> origin/master
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

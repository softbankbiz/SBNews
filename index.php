<?php
/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version. See LICENSE.
 */

/** Define ABSPATH as this file's directory */
define( 'ABSPATH', dirname(__FILE__) . '/' );

/*
 * If sbnews_config.php exists in the root, load sbnews_config.php. 
 * If not exist, initiate loading the setup process.
 */
if ( file_exists( ABSPATH . 'sbnews_config.php') ) {

	/** The config file resides in ABSPATH */
	require_once( ABSPATH . 'sbnews_config.php' );

} else {
	if ( false === strpos( $_SERVER['REQUEST_URI'], 'setup-config' ) ) {
		header( 'Location: ' . 'admin/setup-config.php' );
		exit;
	}
}


define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";

session_start();

if (! isset($_SESSION['auth'])) {
  $_SESSION['auth'] = false;
}

# DB Connection
$mysqli = getConnection();

if ($_POST) {
	if (! $_POST['company_id'] || ! $_POST['user_id'] || ! $_POST['password']) {
		print_header("SBNews Top", null);
		echo "<br><br>入力項目が不足しています。<br><br>";
		echo '<a href="/' . BASE . '/"><button>戻る</button></a>';
		print_footer();
		exit;
	} else {
		try {
			////////////////// brute-force-attack check
			if ( ! bfa_check($mysqli, $_POST["company_id"], $_POST["user_id"]) ) {
				// login_record はしない
		    	echo '<script>alert("認証に失敗しました。アカウントはロックされています。"); location.href = "/' . BASE . '/";</script>';
		        exit;
		    }

			////////////////// user_id 認証
		    if ( ! two_step_auth($mysqli, $_POST["company_id"], $_POST["user_id"]) ) {
		    	login_record($mysqli, $_POST["company_id"], $_POST["user_id"], "fail", "two_step_auth rejected");
		    	echo '<script>alert("認証に失敗しました。"); location.href = "/' . BASE . '/";</script>';
		        exit;
		    }

		    $query = "SELECT password,password_expires,role from users_list WHERE company_id = ? AND user_id = ?";
		    $stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $_POST["company_id"], $_POST["user_id"]);
			$stmt->execute();
			$result = $stmt->get_result();
			$row = $result->fetch_array(MYSQLI_ASSOC);
			$password = $row["password"];
			$password_expires = $row["password_expires"];
			$role = $row["role"];

			$password_inputed = password_hash($_POST["password"], PASSWORD_DEFAULT);

			// 初回ログイン、ユーザーIDとパスワードが一致するなら、パスワード変更を促す
			if (password_verify($_POST["user_id"], $password_inputed)) {
				login_record($mysqli, $_POST["company_id"], $_POST["user_id"], "succeed", "login first time");
				print_header("SBNews ログイン", null);
				echo '<script src="js/utility.js"></script>';
				echo '<div class="login">';
				echo '<div class="leader-text">ログインページ</div>';
				echo '<p>初回ログインです。パスワードを変更してください。<br>パスワードは英数半角文字で6文字以上かつ12文字以内でお願いします。</p>';
				echo '<form action="change_password.php" method="post" name="change_password_form">';
				echo '<table class="form-table">';
				echo '<input type="hidden" name="company_id" value="' . $_POST['company_id'] . '">';
				echo '<input type="hidden" name="user_id" value="' . $_POST["user_id"] . '">';
				echo '<tr><th>新パスワード：</th><td><input type="password" name="password_01"></td></tr>';
				echo '<tr><th>新パスワード（再入力）：</th><td><input type="password" name="password_02"></td></tr>';
				echo '<tr><td colspan="2"><input type="submit" name="submit" value="パスワード変更" onclick="return check_change_password();"></td></tr>';
				echo '</table>';
				echo '</form>';
				echo '</div>';
				print_footer();
				exit;

			// ログイン成功
			} else if (password_verify($_POST["password"], $password)) {
				// Password 有効期限のチェク
				$now = new DateTime();
				$date_db = new DateTime($password_expires);
				
				if ($now >= $date_db) {
					login_record($mysqli, $_POST["company_id"], $_POST["user_id"], "fail", "password expired");
					echo '<script>alert("パスワードの有効期限が切れています。"); location.href = "/' . BASE . '/";</script>';
					exit;
				}

				login_record($mysqli, $_POST["company_id"], $_POST["user_id"], "succeed", "login normal");

		        // セッション固定化攻撃対策(セッションIDを変更)
				session_regenerate_id(true);

		    	// セッション情報を記録
		    	$_SESSION['auth'] = true;
		    	$_SESSION['company_id'] = $_POST["company_id"];
		    	$_SESSION['user_id'] = $_POST["user_id"];
		    	$_SESSION['password_expires'] = $password_expires;
		    	$_SESSION['role'] = $role;

		    	// root ユーザーは管理者メニューに着地。ログアウトのみ有効
		    	if( $_POST["company_id"] == 'root' && $_POST["user_id"] == 'root') {
		    		echo '<script>location.href = "/' . BASE . '/?page=admin_menu";</script>';
		    	}

		    // ログイン失敗
		    } else {
		    	login_record($mysqli, $_POST["company_id"], $_POST["user_id"], "fail", "wrong password");
		    	if ( ! bfa_check($mysqli, $_POST["company_id"], $_POST["user_id"]) ) {
					// login_record はしない
			    	echo '<script>alert("認証に失敗しました。アカウントはロックされました。"); location.href = "/' . BASE . '/";</script>';
			        //exit;
			    } else {
			    	echo '<script>alert("認証に失敗しました。"); location.href = "/' . BASE . '/";</script>';
			    }
		    	//print_header("SBNews ログイン", null);
		        
		        //print_footer();
		        exit;
		    }
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
	}
}


if ($_SESSION['auth'] != true) {
	print_header("SBNews ログイン", null);
?>
		<div class="login">
			<div class="leader-text">ログインページ</div>
			<form action="<?php echo '/' . BASE . '/' ?>" method="post">
				<table class="form-table">
					<tr><th>企業ID：</th><td><input type="text" name="company_id" id="company_id" value="<?php echo $_GET['company_id']; ?>"></td></tr>
					<tr><th>ユーザーID：</th><td><input type="text" name="user_id" id="user_id" value="<?php echo $_GET['user_id']; ?>"></td></tr>
					<tr><th>パスワード：</th><td><input type="password" name="password" id="password" value=""></td></tr>
					<tr><td colspan="2"><input type="submit" name="submit" value="ログイン"></td></tr>
				</table>
			</form>
		</div>

<?php
	print_footer();
} else {
	print_header("SBNews Top", $_SESSION);
	print_mennu($_GET['page']);
?>
			<div class="main_area">
				<div class="menu_content" id="news_make">
					<h3>ニュース作成</h3>
					<?php
					if ($_SESSION['role'] == 'su' || is_null($_SESSION['role'])) {
						echo '<table class="ope_table"><tr><td> rootユーザーであるあなたには編集権限がありません。 </td></tr></table>';
					} else {
					?>
					<table class="ope_table">
						<tr>
							<th>No</th><th>ニュース ID</th><th>最終更新時刻</th><th></th>
						</tr>
						<?php
						$news_id_list = get_news_id($mysqli, $_SESSION['company_id']);
						if ($news_id_list->num_rows == 0) {
							echo '<td colspan="2"> ニュースはありません </td>';						
						} else {
							for ($i = 0; $i < $news_id_list->num_rows; $i++) {
								echo '<tr>';
								echo '<td>' . ($i+1) . '</td>';
								$news_id = $news_id_list->fetch_array(MYSQLI_NUM)[0];
								if (isset($news_id)) {
									echo '<td>' . $news_id . '</td>';
									echo '<td>' . get_news_ts($mysqli, $news_id, $_SESSION['company_id']) . '</td>';
									echo '<td><a href="/' . BASE . '/news_maker.php?news_id=' . urlencode($news_id) . '"><button>ニュース作成</button></a></td>';
								}
								echo '</tr>';
							}
						}
						?>
					</table>
					<?php
					}
					?>
				</div>

				<div class="menu_content" id="news_conf">
					<h3>ニュース設定</h3>
					<?php
					if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
						echo '<table class="ope_table"><tr><td colspan="4"> あなたには編集権限がありません。 </td></tr></table>';
					} else if ($_SESSION['role'] == 'su') {
						echo '<table class="ope_table"><tr><td colspan="4"> rootユーザーであるあなたには編集権限がありません。</td></tr></table>';
					} else {
					?>
					<table class="ope_table">
						<tr>
							<th>No</th><th>ニュース ID</th><th></th><th></th><th></th>
						</tr>
						<?php
						$news_id_list = get_news_id($mysqli, $_SESSION['company_id']);
						if ($news_id_list->num_rows == 0) {
							echo '<tr><td colspan="4"> ニュースはありません </td></tr>';
							echo '<tr><td colspan=3><a href="/' . BASE . '/news_add.php?page=news_conf"><button>ニュースを追加</button></a></td></tr>';					
						} else {
							for ($i = 0; $i < $news_id_list->num_rows; $i++) { 
								echo '<tr>';
								echo '<td>' . ($i+1) . '</td>';
								$news_id = $news_id_list->fetch_array(MYSQLI_NUM)[0];
								if (isset($news_id)) {
									echo '<td>' . $news_id . '</td>';
									echo '<td><a href="/' . BASE . '/news_set.php?page=news_conf&news_id=' . urlencode($news_id) . '"><button>設定変更・削除</button></a></td>';
									echo '<td><button onclick="update_news(\'' . $news_id . '\')">ニュース更新（手動）</button></td>';
									echo '<td><img src="images/bx_loader.gif" class="bx_loader" style="display: none;" id="bx_loader' . $news_id . '"></td>';
								}
								echo '</tr>';

							}
							echo '<tr><td colspan="5"><a href="/' . BASE . '/news_add.php?page=news_conf"><button>ニュースを追加</button></a></td></tr>';
						}
						?>
					</table>
					<?php
					}
					?>
				</div>
				<script>
				function update_news(_news_id) {
					var msg = '';
					$("#bx_loader" + _news_id).css("display","block");
					$.get('fetch_contents.php', 
						{
				        	news_id: _news_id
				        },
				        function(data){
				        	msg += data + '\n\n';
				        	$.get('watson_judgement.php', 
								{
						        	news_id: _news_id
						        },
						        function(data){
						        	msg += data;
						        	alert(msg);
						        	$("#bx_loader" + _news_id).css("display","none");
						    	}
					    	);
				    	}
				    );
				}
				</script>


				<div class="menu_content" id="watson_conf">
					<h3>Watson設定</h3>
					<?php
					if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
						echo '<table class="ope_table"><tr><td colspan="4"> あなたには編集権限がありません。 </td></tr></table>';
					} else if ($_SESSION['role'] == 'su') {
						echo '<table class="ope_table"><tr><td colspan="4"> rootユーザーであるあなたには編集権限がありません。</td></tr></table>';
					} else {
					?>
					<table class="ope_table">
						<tr>
							<th>No</th><th>分類子エイリアス</th><th>分類子</th><th>状態</th>
						</tr>
						<?php
						// Watson NLC に問い合わせると遅いので、このページだけURLパラメータを見る
						if ($_GET['page'] == 'watson_conf') {
							$result = get_classifier_list($mysqli, $_SESSION["company_id"]);
							$config = get_configuration($mysqli, $_SESSION["company_id"]);
							$w_username = $config["w_username"];
							$w_password = $config["w_password"];

							if ($result->num_rows > 0) {
								for ($i = 0; $i < $result->num_rows; $i++) {
									echo '<tr>';
									echo '<td>' . ($i+1) . '</td>';
									$row = $result->fetch_array(MYSQLI_ASSOC);
									if (isset($row["cid_alias"]) && isset($row["cid"])) {
										echo '<td>' . $row["cid_alias"] . '</td>';
										echo '<td>' . $row["cid"] . '</td>';
										echo '<td>' . get_cid_status($w_username, $w_password, $row["cid"]) . '</td>';
									}
									echo '</tr>';
								}
							} else {
								echo '<tr>';
								echo '<td> -- </td>';
								echo '<td> -- </td>';
								echo '<td> -- </td>';
								echo '<td> -- </td>';
								echo '</tr>';
							}
							echo '<tr><td colspan=3><a href="/' . BASE . '/watson_add.php?page=watson_conf"><button>分類子を追加／削除</button></a></td></tr>';
						}
						?>
					</table>
					<?php
					}
					?>
				</div>

				<div class="menu_content" id="crawler_conf">
					<h3>クローラ設定</h3>
					<?php
					if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
						echo '<table class="ope_table"><tr><td colspan="4"> あなたには編集権限がありません。 </td></tr></table>';
					} else if ($_SESSION['role'] == 'su') {
						echo '<table class="ope_table"><tr><td colspan="4"> rootユーザーであるあなたには編集権限がありません。</td></tr></table>';
					} else {
					?>
					<h4>＜RSSリストの設定＞</h4>
					<p class="ope_description">
						ニュースを取得するために登録したRSSリストを表示しています。ファイルの差し替え、削除をするには「設定変更」をクリックします。
						RSSリストを新規に追加するには「追加」をクリックします。
					</p>
					<table class="ope_table">
						<tr>
							<th>No</th><th>RSSリスト ID</th><th></th>
						</tr>
						<?php
						$where_condition = 'WHERE company_id = "' . $_SESSION['company_id'] . '"';
						$query_rss = 'SELECT rss_id from rss_list ' . $where_condition;
						try {
							$result_rss = $mysqli->query($query_rss);
							for ($i = 0; $i < $result_rss->num_rows; $i++) {
								echo '<tr>';
								echo '<td>' . ($i+1) . '</td>';
								$row_rss = $result_rss->fetch_array(MYSQLI_ASSOC);
								if (isset($row_rss["rss_id"])) {
									echo '<td>' . $row_rss["rss_id"] . '</td>';
									echo '<td><a href="/' . BASE . '/rss_set.php?page=crawler_conf&rss_id=' . urlencode($row_rss["rss_id"]) . '"><button>設定変更</button></a></td>';
								}
								echo '</tr>';
							}
							echo '<tr><td colspan=3><a href="/' . BASE . '/rss_add.php?page=crawler_conf"><button>追加</button></a></td></tr>';
						} catch (mysqli_sql_exception $e) {
						    throw $e;
						    die();
						}
						?>
					</table>

					<h4>＜カテゴリ リストの設定＞</h4>
					<p class="ope_description">
						ニュースをカテゴリに分類するためのカテゴリ リストを表示しています。ファイルの差し替え、削除をするには「設定変更」をクリックします。
						カテゴリ リストを新規に追加するには「追加」をクリックします。
					</p>
					<table class="ope_table">
						<tr>
							<th>No</th><th>カテゴリ リスト ID</th><th></th>
						</tr>
						<?php
						$where_condition = 'WHERE company_id = "' . $_SESSION['company_id'] . '"';
						$query_cat = 'SELECT category_id from category_list ' . $where_condition;
						try {
							$result_cat = $mysqli->query($query_cat);
							for ($i = 0; $i < $result_cat->num_rows; $i++) {
								echo '<tr>';
								echo '<td>' . ($i+1) . '</td>';
								$row_cat = $result_cat->fetch_array(MYSQLI_ASSOC);
								if (isset($row_cat["category_id"])) {
									echo '<td>' . $row_cat["category_id"] . '</td>';
									echo '<td><a href="/' . BASE . '/category_set.php?page=crawler_conf&category_id=' . urlencode($row_cat["category_id"]) . '"><button>設定変更</button></a></td>';
								}
								echo '</tr>';
							}
							echo '<tr><td colspan=3><a href="/' . BASE . '/category_add.php?page=crawler_conf"><button>追加</button></a></td></tr>';
						} catch (mysqli_sql_exception $e) {
						    throw $e;
						    die();
						}
						?>
					</table>

					<h4>＜サイト名リストの設定＞</h4>
					<p class="ope_description">
						ニュースを発行元を表示させるためのサイト名リストを表示しています。ファイルの差し替え、削除をするには「設定変更」をクリックします。
						サイト名リストを新規に追加するには「追加」をクリックします。
					</p>
					<table class="ope_table">
						<tr>
							<th>No</th><th>サイト名リスト ID</th><th></th>
						</tr>
						<?php
						$where_condition = 'WHERE company_id = "' . $_SESSION['company_id'] . '"';
						$query_site = 'SELECT site_names_id from site_names_list ' . $where_condition;
						try {
							$result_site = $mysqli->query($query_site);
							for ($i = 0; $i < $result_site->num_rows; $i++) {
								echo '<tr>';
								echo '<td>' . ($i+1) . '</td>';
								$row_site = $result_site->fetch_array(MYSQLI_ASSOC);
								if (isset($row_site["site_names_id"])) {
									echo '<td>' . $row_site["site_names_id"] . '</td>';
									echo '<td><a href="/' . BASE . '/site_names_set.php?page=crawler_conf&site_names_id=' . urlencode($row_site["site_names_id"]) . '"><button>設定変更</button></a></td>';
								}
								echo '</tr>';
							}
							echo '<tr><td colspan=3><a href="/' . BASE . '/site_names_add.php?page=crawler_conf"><button>追加</button></a></td></tr>';
						} catch (mysqli_sql_exception $e) {
						    throw $e;
						    die();
						}
						?>
					</table>
					<?php
					}
					?>
				</div>

				<div class="menu_content" id="log_mgmt">
					<h3>ログ管理</h3>
					<?php
					if ($_SESSION['role'] == 'su' || is_null($_SESSION['role'])) {
						echo '<table class="ope_table"><tr><td colspan="4"> rootユーザーであるあなたには編集権限がありません。 </td></tr></table>';
					} else {
					?>
					<p class="ope_description">
						ログを出力する期間を「YYYY-MM-DD」形式で指定してダウンロードしてください。ログ取得の終了時期には、本日の日付をデフォルトで入力しています。
					</p>
					<table class="ope_table bgcolor_gray border_none">
						<tr class="bgcolor_white">
							<th>No</th><th>ログ名</th><th>取得期間（YYYY-MM-DD）</th>
						</tr>
						<tr class="bgcolor_white">
							<td rowspan="2">1</td>
							<td rowspan="2">RSSが取得したニュース一覧</td>
							<td>
								<form method="POST" action="<?php echo '/' . BASE . '/' ?>log_manage.php">
									<input type="text" size="6" name="year_s" id="log_rss_year_s"> - 
									<input type="text" size="3" name="month_s" id="log_rss_month_s"> - 
									<input type="text" size="3" name="date_s" id="log_rss_date_s"> 以降 
							</td>
						</tr>
						<tr class="bgcolor_white">
							<td>
									<input type="text" size="6" name="year_e" id="log_rss_year_e" value="<?php echo get_today()[0]; ?>"> - 
									<input type="text" size="3" name="month_e" id="log_rss_month_e" value="<?php echo get_today()[1]; ?>"> - 
									<input type="text" size="3" name="date_e" id="log_rss_date_e" value="<?php echo get_today()[2]; ?>"> までを 
									<input type="hidden" name="cmd" value="log_rss">
									<input type="submit" value="ダウンロード" onclick="return check_date_format('log_rss')">
								</form>
							</td>
						</tr>

						<tr class="bgcolor_white">
							<td rowspan="2">2</td>
							<td rowspan="2">記事のクリックログ一覧</td>
							<td>
								<form method="POST" action="<?php echo '/' . BASE . '/' ?>log_manage.php">
									<input type="text" size="6" name="year_s" id="log_click_year_s"> - 
									<input type="text" size="3" name="month_s" id="log_click_month_s"> - 
									<input type="text" size="3" name="date_s" id="log_click_date_s"> 以降
							</td>
						</tr>
						<tr class="bgcolor_white">
							<td>
									<input type="text" size="6" name="year_e" id="log_click_year_e" value="<?php echo get_today()[0]; ?>"> - 
									<input type="text" size="3" name="month_e" id="log_click_month_e" value="<?php echo get_today()[1]; ?>"> - 
									<input type="text" size="3" name="date_e" id="log_click_date_e" value="<?php echo get_today()[2]; ?>"> までを 
									<input type="hidden" name="cmd" value="log_click">
									<input type="submit" value="ダウンロード" onclick="return check_date_format('log_click')">
								</form>
							</td>
						</tr>

						<tr class="bgcolor_white">
							<td rowspan="2">3</td>
							<td rowspan="2">メールの開封ログ一覧</td>
							<td>
								<form method="POST" action="<?php echo '/' . BASE . '/' ?>log_manage.php">
									<input type="text" size="6" name="year_s" id="log_access_year_s"> - 
									<input type="text" size="3" name="month_s" id="log_access_month_s"> - 
									<input type="text" size="3" name="date_s" id="log_access_date_s"> 以降 
							</td>
						</tr>
						<tr class="bgcolor_white">
							<td>
									<input type="text" size="6" name="year_e" id="log_access_year_e" value="<?php echo get_today()[0]; ?>"> - 
									<input type="text" size="3" name="month_e" id="log_access_month_e" value="<?php echo get_today()[1]; ?>"> - 
									<input type="text" size="3" name="date_e" id="log_access_date_e" value="<?php echo get_today()[2]; ?>"> までを 
									<input type="hidden" name="cmd" value="log_access">
									<input type="submit" value="ダウンロード" onclick="return check_date_format('log_access')">
								</form>
							</td>
						</tr>

						<?php 
						if ($_SESSION['role'] == 'admin' || is_null($_SESSION['role'])) {
						?>
						<tr class="bgcolor_white">
							<td rowspan="2">4</td>
							<td rowspan="2">ログインユーザーの記録</td>
							<td>
								<form method="POST" action="<?php echo '/' . BASE . '/' ?>log_manage.php">
									<input type="text" size="6" name="year_s" id="log_login_year_s"> - 
									<input type="text" size="3" name="month_s" id="log_login_month_s"> - 
									<input type="text" size="3" name="date_s" id="log_login_date_s"> 以降 
							</td>
						</tr>
						<tr class="bgcolor_white">
							<td>
									<input type="text" size="6" name="year_e" id="log_login_year_e" value="<?php echo get_today()[0]; ?>"> - 
									<input type="text" size="3" name="month_e" id="log_login_month_e" value="<?php echo get_today()[1]; ?>"> - 
									<input type="text" size="3" name="date_e" id="log_login_date_e" value="<?php echo get_today()[2]; ?>"> までを 
									<input type="hidden" name="cmd" value="log_login">
									<input type="submit" value="ダウンロード" onclick="return check_date_format('log_login')">
								</form>
							</td>
						</tr>
						<?php 
						}
						?>
					</table>
					<?php
					}
					?>
				</div>

				<div class="menu_content" id="document">
					<h3>ドキュメント</h3>
					<div class="docu_body">
						<p>SBNewsの使い方を説明します。</p>
						<ol>
							<li class="docu_h1"><a href="<?php echo '/' . BASE . '/' ?>doc/quickstart.php?page=document">クイックスタート</a>
								<ol>
									<li>ログイン</li>
									<li>Watson NLCを登録する</li>
									<li>Watson NLCの分類子を設定する</li>
									<li>クローラを設定する</li>
									<li>ニュースを設定する</li>
									<li>ニュースを作成する</li>
									<li>ログを取得する</li>
								</ol>
							</li>
							<li class="docu_h1"><a href="<?php echo '/' . BASE . '/' ?>doc/makenews.php?page=document">ニュース作成の手順</a>
								<ol>
									<li>ニュースを選ぶ</li>
									<li>メール件名を編集</li>
									<li>コンテンツ候補を取得</li>
									<li>ニュース本文を作成</li>
									<li>ランキングを作成</li>
									<li>プレビューを確認</li>
									<li>コンテンツを利用</li>
								</ol>
							</li>
							<li class="docu_h1"><a href="<?php echo '/' . BASE . '/' ?>doc/training.php?page=document">分類子のトレーニング方法</a>
								<ol>
									<li>Natural Language Classifier</li>
									<li>分類子の作成</li>
									<li>初めてのトレーニングデータ作成</li>
									<li>記事ランキングを利用した分類子のチューニング</li>
									<li>分類子のチューニング手順</li>
								</ol>
							</li>
							<li class="docu_h1"><a href="<?php echo '/' . BASE . '/' ?>doc/rss.php?page=document">RSSリストの作成</a>
								<ol>
									<li>RSSフィードの探し方</li>
									<li>Googleアラートの作成</li>
									<li>RSSフィードの独自開発</li>
								</ol>
							</li>
							<li class="docu_h1"><a href="<?php echo '/' . BASE . '/' ?>doc/user.php?page=document">ユーザー管理</a>
								<ol>
									<li>スーパーユーザー</li>
									<li>管理者ユーザー</li>
									<li>編集者ユーザー</li>
								</ol>
							</li>
							<li class="docu_h1"><a href="<?php echo '/' . BASE . '/' ?>doc/security.php?page=document">セキュリティ</a>
								<ol>
									<li>パスワードロック</li>
									<li>ログインユーザーの記録</li>
								</ol>
							</li>
							<li class="docu_h1"><a href="<?php echo '/' . BASE . '/' ?>doc/log.php?page=document">ログ出力</a>
								<ol>
									<li>RSSが取得したニュース一覧</li>
									<li>記事のクリックログ一覧</li>
									<li>メールの開封ログ一覧</li>
									<li>ログインユーザーの記録</li>
								</ol>
							</li>
							<li class="docu_h1"><a href="<?php echo '/' . BASE . '/' ?>doc/faq.php?page=document">FAQ</a>
								<ol>
									<li></li>
									<li></li>
									<li></li>
									<li></li>
									<li></li>
								</ol>
							</li>
						</ol>
					</div>
				</div>

				<div class="menu_content" id="admin_menu">
					<h3>管理者メニュー</h3>
					<?php 
					if (is_null($_SESSION['role'])) {
					?>
						<table class="ope_table">
						<tr><td> あなたには編集権限がありません。 </td></tr>
						</table>
					<?php
					} else if ($_SESSION['role'] == 'su') {
					?>
						<h4>＜Watsonアカウント登録＞</h4>
						<p class="ope_description wide">Watson NLCのユーザー名／パスワードは、管理者ユーザーが設定します。</p>
						<table class="ope_table">
						<tr><td> -- </td></tr>
						</table>
						<h4>＜ユーザー管理＞</h4>
					<?php
					} else {
					?>
						<h4>＜Watsonアカウント登録＞</h4>
						<p class="ope_description">Watson NLCのユーザネーム／パスワードを登録します。
						</p>
						<table class="ope_table">
						<tr>
						<th>Watsonアカウント登録</th>
						<td><a href="<?php echo '/' . BASE . '/' ?>admin_set.php?page=admin_menu&task=watson_account"><button>設定</button></a></td>
						</tr>
						</table>
						<h4>＜ユーザー管理＞</h4>
					<?php
					}
					if ($_SESSION['role'] == 'su') {
					?>
						<p class="ope_description wide">rootユーザーであるあなたは、企業IDを指定して、ユーザーを作成できます。
						企業IDはWatsonアカウントと1対1で対応するSBNewsの基本単位となります。
						追加するユーザーには、ニュースの各種設定を行える「管理者」とニュース作成およびログ管理のみ行える「編集者」のいずれかの役割を付与できます。
						</p>
						<table class="ope_table wide">
						<tr><th>No</th><th>企業ID</th><th>ユーザーID</th><th>パスワード有効期限</th><th>役割</th><th></th></tr>
						<?php
						$users_list = get_users_list($mysqli, null);
						if ($users_list) {
							foreach ($users_list as $num => $user) {
								if ($user['user_id'] == 'root') { continue; }
								echo '<tr>';
								echo '<td>' . $num . '</td>';
								echo '<td>' . $user['company_id'] . '</td>';
								echo '<td>' . $user['user_id'] . '</td>';
								echo '<td>' . explode(' ', $user['password_expires'])[0] . '</td>';
								echo '<td>' . translate_role($user['role']) . '</td>';
								echo '<td><a href="/' . BASE . '/user_set.php?page=admin_menu&task=user_edit&target=' . $user['user_id'] . '&company_id=' . $user['company_id'] . '"><button>修正</button></a></td>';
								if ($user['user_id'] == $_SESSION['user_id']) {
									echo '<td><button disabled>削除</button></td>';
								} else {
									echo '<td><a href="/' . BASE . '/user_set.php?page=admin_menu&task=user_delete&target=' . $user['user_id'] . '&company_id=' . $user['company_id'] . '"><button>削除</button></a></td>';
								}
								echo '</tr>';
							}
						} else {
							echo '<tr><td> -- </td><td> -- </td><td> -- </td><td> -- </td><td> -- </td></tr>';
						}
						?>
						<tr><td colspan="5"><a href="<?php echo '/' . BASE . '/' ?>user_add.php?page=admin_menu"><button>ユーザー追加</button></a></td>
						</table>
					<?php
					} else if ($_SESSION['role'] == 'admin') {
					?>
						<p class="ope_description">管理者ユーザーであるあなたは、ニュースの各種設定を行える「管理者ユーザー」、
						またはニュース作成およびログ管理のみ行える「編集者ユーザー」を修正・削除・追加できます。
						ただし、自分自身は削除できません。
						パスワードのリセットは「修正」から行えます。
						</p>
						<table class="ope_table">
						<tr><th>No</th><th>ユーザーID</th><th>パスワード有効期限</th><th>役割</th><th></th></tr>
					<?php
						$users_list = get_users_list($mysqli, $_SESSION['company_id']);
						if ($users_list) {
							foreach ($users_list as $num => $user) {
								if ($user['user_id'] == 'root') { continue; }
								echo '<tr>';
								echo '<td>' . ($num+1) . '</td>';
								echo '<td>' . $user['user_id'] . '</td>';
								echo '<td>' . explode(' ', $user['password_expires'])[0] . '</td>';
								echo '<td>' . translate_role($user['role']) . '</td>';
								echo '<td><a href="/' . BASE . '/user_set.php?page=admin_menu&task=user_edit&target=' . $user['user_id'] . '&company_id=' . $_SESSION['company_id'] . '"><button>修正</button></a></td>';
								if ($user['user_id'] == $_SESSION['user_id']) {
									echo '<td><button disabled>削除</button></td>';
								} else {
									echo '<td><a href="/' . BASE . '/user_set.php?page=admin_menu&task=user_delete&target=' . $user['user_id'] . '&company_id=' . $_SESSION['company_id'] . '"><button>削除</button></a></td>';
								}
								echo '</tr>';
							}
						} else {
							echo '<tr><td> -- </td><td> -- </td><td> -- </td><td> -- </td></tr>';
						}
						echo '<tr><td colspan="4"><a href="/' . BASE . '/user_add.php?page=admin_menu"><button>ユーザー追加</button></a></td>';
						echo '</table>';				
					}
					?>
					
					<script>
						// ユーザーの利用可能なメニューを制限する
						var uid = document.getElementById("role").value;
						var elements = document.getElementsByClassName("menu_not_selected");
						for (var i=0; i<elements.length; i++) {
							var param = elements.item(i).firstElementChild.getAttribute("href").split('=')[1];
							if(uid === "su") {
								if(param !== 'logout' && param !== 'admin_menu' && param !== 'document') {
									elements.item(i).firstElementChild.removeAttribute("href");
									elements.item(i).setAttribute("style","background-color: #fff;");
								}
							} else if(uid === "editor") {
								if(param !== 'logout' && param !== 'news_make' && param !== 'document' && param !== 'log_mgmt') {
									elements.item(i).firstElementChild.removeAttribute("href");
									elements.item(i).setAttribute("style","background-color: #fff;");
								}
							}
						}
					</script>
				</div>
			</div>
<?php
	print_javascript("others");
	print_footer();
}

exit();
?>
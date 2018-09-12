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
		    $result = get_configuration($mysqli, $_SESSION["company_id"]);
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
?>
			<div class="main_area">
				<h3>Watson NLCのユーザネーム／パスワードを登録</h3>
				<table class="conf_table">
					<tr>
						<th>ユーザーネーム</th><td><input type="text" name="username" id="_username" size="40" value="<?php echo $result["w_username"]; ?>"></td>
					</tr>
					<tr>
						<th>パスワード</th><td><input type="password" name="password" id="_password" size="40" value="<?php echo $result["w_password"]; ?>"></td>
					</tr>
					<tr>
						<td colspan="2"><button id="submit">登録する</button></td>
					</tr>
				</table>
			</div>
			<br><br>
			<div>
				<a href="/<?php echo BASE; ?>/?page=admin_menu"><button>戻る</button></a>
			</div>
			<script>
				var watson_management = "watson_management.php";
				document.getElementById('submit').addEventListener('click', function (evt) {
					$.post(watson_management,
				    {
				        username:   $("#_username").val(),
				        password:   $("#_password").val(),
				        cmd:        "configuration",
				    },
				    function(data, status){
				        if(status == 'success') {
				        	//alert(data);
				        	if (data == 1) {
				        		alert("Watson NLCのユーザネーム／パスワードを登録しました。");
				        		location.href = "/<?php echo BASE; ?>/?page=admin_menu";
				        	} else {
				        		alert("Watson NLCのユーザネーム／パスワードの登録に失敗しました。");
				        		location.href = "/<?php echo BASE; ?>/?page=admin_menu";
				        	}
				        } else {
				        	alert("error.");
				        }
					});
				});
			</script>
<?php
	} else if($_GET["task"] == "image_upload") {
?>
			<div class="main_area">
				<h3>トップ画像をアップロード</h3>
				<p class="ope_description">
					プレビュー画面の上部に表示される画像をアップロードします。ファイル名は何であってもかまいません。
					ファイルタイプは「PNG」のみ利用可能です。
					画像サイズは、幅：600ピクセル（固定）、高さ：120ピクセル（任意）です。
				</p>
				<form>
					<table class="conf_table">
						<tr>
							<th>ニュースIDを選ぶ</th>
							<td><?php echo get_news_id_as_select($mysqli, null, 'top_image'); ?></td>
						</tr>
						<tr>
							<th>画像ファイルを選ぶ</th>
							<td><input type="file" id="add_top_image" name="add_top_image" accept="image/png"></td>
						</tr>
						<tr>
							<th></th>
							<td><button type="button" onclick="top_image_upload()">アップロード</button></td>
						</tr>
					</table>
				</form>

				<br><br>

				<h3>カテゴリー画像をアップロード</h3>
				<p class="ope_description">
					プレビュー画像のカテゴリ見出しに表示させる画像をアップロードします。
					ファイル名は、該当する「カテゴリ リスト ID」に登録したファイル名と整合させてください。
					画像サイズは、幅：360ピクセル（任意）、高さ：70ピクセル（固定）です。
					カテゴリー画像を用意しない場合は、デフォルトの「画像＋テキスト」が使用されます。
				</p>
				<form>
					<table class="conf_table">
						<tr>
							<th>ニュースIDを選ぶ</th>
							<td><?php echo get_news_id_as_select($mysqli, null, 'category_icon'); ?></td>
						</tr>
						<tr>
							<th>画像ファイルを選ぶ（複数選択可）</th>
							<td><input type="file" id="add_category_icons" name="add_category_icons[]" accept="image/*,.png,.jpg,.jpeg,.gif" multiple></td>
						</tr>
						<tr>
							<th></th>
							<td><button type="button" onclick="category_icons_upload()">アップロード</button></td>
						</tr>
					</table>
				</form>
			</div>
			<br><br>
			<div>
				<a href="/<?php echo BASE; ?>/?page=admin_menu"><button>戻る</button></a>
			</div>

			<script>
				function top_image_upload() {
					if ($("#top_image").val() == '-- 未選択 --' || $("#top_image").val() == '') {
						alert("ニュースIDを選んでください。");
						return;
					} else if($("#add_top_image").prop("files")[0] === undefined) {
						alert("画像ファイルを選んでください。");
						return;
					}
				    var fd = new FormData();
				    fd.append("file", $("#add_top_image").prop("files")[0]);
				    fd.append("news_id", $("#top_image").val());
				    $.ajax({
				        url  : "admin_manage.php",
				        type : "POST",
				        data : fd,
				        contentType : false,
				        processData : false,
				    })
				    .done(function(data, textStatus, jqXHR){
				        alert(data);
				    })
				    .fail(function(jqXHR, textStatus, errorThrown){
				        alert("fail " + textStatus);
				    });
				}
				function category_icons_upload() {
					if ($("#category_icon").val() == '-- 未選択 --' || $("#category_icon").val() == '') {
						alert("ニュースIDを選んでください。");
						return;
					} else if($("#add_category_icons").prop("files")[0] === undefined) {
						alert("画像ファイルを選んでください。");
						return;
					}
				    var fd = new FormData();
					var files = $("#add_category_icons").prop("files");
					for (var i=0; i<files.length; i++) {
						fd.append("file[]", files[i]);
					}				    
				    fd.append("news_id", $("#category_icon").val());
				    $.ajax({
				        url  : "admin_manage.php",
				        type : "POST",
				        data : fd,
				        contentType : false,
				        processData : false,
				    })
				    .done(function(data, textStatus, jqXHR){
				        alert(data);
				    })
				    .fail(function(jqXHR, textStatus, errorThrown){
				        alert("fail " + textStatus);
				    });
				}
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
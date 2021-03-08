<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";

session_start();

$mysqli = getConnection();

if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	exit();
} else if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
	echo '<script>alert("あなたには編集権限がありません。"); location.href = "/' . BASE . '/";</script>';
	return;
} else if ($_SESSION['role'] == 'su') {
	echo '<script>alert("rootユーザーはニュースの追加はできません。\n「管理者ユーザー」でログインしてください。"); location.href = "/' . BASE . '/?page=news_conf";</script>';
} else {
	print_header("ニュースの新規追加", $_SESSION);
	print_mennu($_GET['page'])
?>
			<div class="main_area">
				<h3>ニュースの新規追加</h3>
				<table class="conf_table">
				<?php
					////////////////// user_id 認証
				    if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
				        return;
				    }
					echo '<tr><th>企業 ID</th><td>' . $_SESSION['company_id'] . '<span class="color_red small left_padding">※変更不可</span></td></tr>';
					echo '<tr><th>ニュース ID</th><td><input type="text" size="40" name="news_id" id="news_id"><span class="color_red small left_padding">※必須</span></td></tr>';
					echo '<tr><th>分類子エイリアス</th><td>' . get_cid_alias_as_select($mysqli, null) . '</td></tr>';
					echo '<tr><th>RSSリスト ID</th><td>' . get_rss_id_as_select($mysqli, null) . '</td></tr>';
					echo '<tr><th>カテゴリ リスト ID</th><td>' . get_category_id_as_select($mysqli, null) . '</td></tr>';
					echo '<tr><th>サイト名リスト ID</th><td>' . get_site_names_id_as_select($mysqli, null) . '</td></tr>';
					echo '<tr><th>メール件名</th><td><input name="default_title" id="default_title" type="text" value="■□ XXXXXXXX  20XX/XX/XX □■" size="50"></td></tr>';
					echo '<tr><th>ニュース取得開始日</th><td>' . get_period_day_as_select($mysqli, null) . '</td></tr>';
					echo '<tr><th>ニュース取得開始時刻</th><td>' . get_period_hour_as_select($mysqli, null) . '</td></tr>';
					echo '<tr><th>ニュース取得数</th><td>' . get_fetch_num_as_select($mysqli, null) . '</td></tr>';
					echo "<tr><th>署名</th><td><textarea rows=\"5\" cols=\"40\" name=\"signature\" id=\"signature\">XXXX株式会社\nXXXX部\nメルマガ送信チーム\nsome@where.com</textarea></td></tr>";
					echo '<tr><td><style="text-align: center;"><button id="add_news" style="margin-right: 20px;">ニュースを追加する</button></td><td></td></tr>';
					echo '</td></tr>';
					?>
				</table>
			</div>
			<br><br>
			<a href="/<?php echo BASE; ?>/?page=news_conf"><button class="button_back">戻る</button></a>
		<script src="js/vendor/jquery-3.2.1.min.js"></script>
		<script>
var news_manage = "news_manage.php";
document.getElementById('add_news').addEventListener('click', function (evt) {
	if($("#news_id").val() == '') {
		alert("ニュース IDを指定してください。");
		return;
	} else if($("#default_title").val() == '') {
		alert("メール件名を指定してください。");
		return;
	} else if($("#signature").val() == '') {
		alert("署名を指定してください。");
		return;
	}
	$.post(news_manage,
    {
        news_id:       $("#news_id").val(),
        cid_alias:     $("#cid_alias").val(),
        rss_id:        $("#rss_id").val(),
        category_id:   $("#category_id").val(),
        site_names_id: $("#site_names_id").val(),
        default_title: $("#default_title").val(),
        period_day:    $("#period_day").val(),
        period_hour:   $("#period_hour").val(),
        fetch_num:     $("#fetch_num").val(),
        signature:     $("#signature").val(),
        cmd:           "add",
    },
    function(data, status){
        if(status == 'success') {
        	if (data == 1) {
        		alert("ニュースを追加しました。");
        		location.href = "/<?php echo BASE; ?>/?page=news_conf";
        	} else {
        		alert("ニュースの追加に失敗しました。");
        		location.href = "/<?php echo BASE; ?>/news_add.php?page=news_conf";
        	}
        } else {
        	alert("error.");
        }
	});
});
		</script>
<?php
	print_javascript("others");
	print_footer();
}

exit();
?>
<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";
require_once dirname(__FILE__) . "/sbnews_config.php";

session_start();

$mysqli = getConnection();

if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	exit();
} else if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
	echo '<script>alert("あなたには編集権限がありません。"); location.href = "/' . BASE . '/";</script>';
	return;
} else if ($_GET['news_id'] && two_step_auth($mysqli, $_SESSION['company_id'], $_SESSION['user_id'])) {
	print_header("ニュース設定画面", $_SESSION);
	print_mennu($_GET['page'])
?>
			<div class="main_area">
				<h3>ニュースの設定変更</h3>
				<table class="conf_table">
				<?php
					$result = get_preference($mysqli, $_SESSION['company_id'], $_GET['news_id']);
					while ($row = $result->fetch_assoc()) {
						echo '<tr><th>企業 ID</th><td>' . $_SESSION['company_id'] . '</td></tr>';
						echo '<tr><th>ニュース ID</th><td>' . $_GET['news_id'] . '</td></tr>';
						echo '<tr><th>分類子エイリアス</th><td>' . get_cid_alias_as_select($mysqli, $row["cid_alias"]) . '</td></tr>';
						echo '<tr><th>RSSリスト ID</th><td>' . get_rss_id_as_select($mysqli, $row["rss_id"]) . '</td></tr>';
						echo '<tr><th>カテゴリ リスト ID</th><td>' . get_category_id_as_select($mysqli, $row["category_id"]) . '</td></tr>';
						echo '<tr><th>サイト名リスト ID</th><td>' . get_site_names_id_as_select($mysqli, $row["site_names_id"]) . '</td></tr>';
						echo '<tr><th>メール件名</th><td><input name="default_title" id="default_title" type="text" value="' . $row["default_title"] . '" size="50"></td></tr>';
						echo '<tr><th>ニュース取得開始日</th><td>' . get_period_day_as_select($mysqli, $row["period_day"]) . '</td></tr>';
						echo '<tr><th>ニュース取得開始時刻</th><td>' . get_period_hour_as_select($mysqli, $row["period_hour"]) . '</td></tr>';
						echo '<tr><th>ニュース取得数</th><td>' . get_fetch_num_as_select($mysqli, $row["fetch_num"]) . '</td></tr>';
						echo '<tr><th>署名</th><td><textarea rows="5" cols="40" name="signature" id="signature">' . $row["signature"] . '</textarea></td></tr>';
						echo '<input type="hidden" name="news_id" id="news_id" value="' . $row["news_id"] . '">';
						echo '<tr><td colspan="2" style="text-align: center;"><button id="update_news">データを更新する</button>';
						echo '<button id="delete_news" style="margin: 0 20px;">このニュースを削除する</button></td></tr>';
				    }
				?>
				</table>
			</div>
			<br><br>
			<a href="/<?php echo BASE; ?>/?page=news_conf"><button>戻る</button></a>
			<script>
var news_manage = "news_manage.php";
document.getElementById('delete_news').addEventListener('click', function (evt) {
	$.post(news_manage,
    {
        news_id: $("#news_id").val(),
        cmd:     "delete",
    },
    function(data, status){
        if(status == 'success') {
        	if (data == 1) {
        		alert("このニュースを削除しました。");
        		location.href = "/<?php echo BASE; ?>/?page=news_conf";
        	} else {
        		alert("このニュースの削除に失敗しました。");
        		location.href = "/<?php echo BASE; ?>/?page=news_conf";
        	}
        } else {
        	alert("error.");
        }
	});
});

document.getElementById('update_news').addEventListener('click', function (evt) {
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
        cmd:           "update",
    },
    function(data, status){
    	//alert(data);
        if(status == 'success') {
        	if (data == 1) {
        		alert("データを更新しました。");
        		location.href = "/<?php echo BASE; ?>/?page=news_conf";
        	} else {
        		alert("データの更新に失敗しました。");
        		location.href = "/<?php echo BASE; ?>/?page=news_conf";
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
} else {
	echo 'パラメータが不正です。';
}

exit();
?>
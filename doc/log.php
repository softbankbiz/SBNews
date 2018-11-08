<?php

define('BASE', basename(dirname(dirname(__FILE__))));
require_once dirname(dirname(__FILE__)) . "/functions.php";

# クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

session_start();

if (! isset($_SESSION['auth'])) {
  $_SESSION['auth'] = false;
}

if ($_SESSION['auth'] == true) {
	print_header("Document Make News", $_SESSION);
	print_mennu($_GET['page']);
?>
				<div class="main_area">
					<div class="docu_body_detail">
						<h3>ログ出力</h3>
						<p>
							SBNewsでは「<a href="<?php echo '/'. BASE .'/' ?>?page=log_mgmt" target="_blank">ログ取得</a>
							」メニューから「RSSが取得したニュース一覧」「記事のクリックログ一覧」「メールの開封ログ一覧」
							「ログインユーザーの記録」のログデータをCSVファイルとしてダウンロードできます。
							いずれのログも自身が所属する企業ID単位で出力されます。
						</p>
						<ol>
							<li class="docu_h1">RSS
								<div>
<p>
	SBNewsへロ
</p>
<p>
	ロックされたユ
</p>
								</div>
							</li>
							<li class="docu_h1">記事の
								<div>
<p>
	管理者ユーザーは「ログ
</p>
<p>
	「ログインユーザ
	
</p>
								</div>
							</li>

						</ol>
<p>
	<a href="<?php echo '/'. BASE .'/' ?>?page=document"><button class="button_back">戻る</button></a>
</p>
					</div>
				</div>

<?php
	print_javascript("others");
	print_footer();
}

exit();
?>
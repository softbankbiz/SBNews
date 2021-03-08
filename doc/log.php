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
							<li class="docu_h1">RSSが取得したニュース一覧
								<div>
<p>
	RSSを使って1時間おきに取得したニュース情報をダウンロードできます。ここには、Watson NLCが不採用と判定したニュースも含まれます。
</p>
<p>
	出力される項目は、「記事タイトル」「分類名」「url」「記事作成日」「カテゴリー」「確信度」「サイト名」「ニュースID」「分類子」「分類子エイリアス」です。
</p>
								</div>
							</li>
							<li class="docu_h1">記事のクリックログ一覧
								<div>
<p>
	SBNewsが作成したニュースを配信後、ユーザーが記事リンクをクリックしたログを取得できます。このデータはアクセスランキングを生成するために使われます。
</p>
<p>
	出力される項目は、「ニュースID」「url」「発行年月日」「タイムスタンプ」です。
	
</p>
								</div>
							</li>
							<li class="docu_h1">メールの開封ログ一覧
								<div>
<p>
	SBNewsが作成したニュースを配信後、ユーザーがメールを開封した回数を記録したログを取得できます。Webサーバやプロキシサーバのキャッシュ設定により、必ずしもユニークユーザー数とはならないことに留意してください。
</p>
<p>
	出力される項目は、「ニュースID」「発行年月日」「タイムスタンプ」です。
	
</p>
								</div>
							</li>
							<li class="docu_h1">ログインユーザーの記録
								<div>
<p>
	SBNewsにログインしたユーザーを記録したログを取得できます。
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
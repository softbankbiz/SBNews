<?php

define('BASE', basename(dirname(dirname(__FILE__))));
require_once dirname(dirname(__FILE__)) . "/functions.php";

//echo BASE . "/doc/makenews.php";

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
						<h3>ニュース作成の手順</h3>
						<p>ニュースの作成方法を解説します。</p>
						<ol>
							<li class="docu_h1_nodeco">ニュース作成ページに移動する
								<div>
<p>
	ニュースを作成するには、「<a href="<?php echo '/'. BASE .'/' ?>?page=news_make">ニュース作成</a>」を開き、
	作成したい「ニュースID」の「ニュース作成」ボタンをクリックします。
</p>
								</div>
							</li>
							<li class="docu_h1_nodeco">Step_1 メール件名を編集
								<div>
<p>
	あらかじめ登録しておいたメール件名が表示されますが、この場で修正もできます。
</p>
								</div>
							<li class="docu_h1_nodeco">Step_2　コンテンツ候補を取得
								<div>
<p>
	あらかじめ登録しておいた「ニュース取得開始日」「ニュース取得開始時刻」
	「ニュース取得数」が表示されますが、この場で修正もできます。例えば、月曜日であれば「ニュース取得開始日」を「3日前」として
	先週金曜日以降のニュースを取得できます。最後に「取得」ボタンをクリックすると「Step_3」のエリアにニュースが取り込まれます。
</p>
								</div>
							<li class="docu_h1_nodeco">Step_3　ニュース本文を作成
								<div>
<p>
	「連絡メモ欄（オプション）」に文字を入力すると、タイトル画像直下にお知らせメッセージを
	表示できます。その下には、カテゴリごとにニュースのURLとタイトル、メディア名がセットされます。
	各囲み内の「注目記事！」「削除」「要ログイン」「参考リンク追加」などを使って、適宜修正ください。
	囲み枠はドラッグ＆ドロップで順序を入れ替えられます。
	また、手動でカテゴリーや記事を追加したい場合は「カテゴリーを追加」「記事を追加」ボタンを使用できます。
</p>
								</div>
							<li class="docu_h1_nodeco">Step_4　ランキングを作成
								<div>
<p>
	発行号を指定して記事のクリック数によるランキングを作成できます。なお、
	ランキングに含めたくないニュースが表示されている場合、右の「×」ボタンで削除できます。この場合、ランク外だった記事が
	繰り上がってランクインします。
</p>
								</div>
							<li class="docu_h1_nodeco">Step_5　プレビューを確認
								<div>
<p>
	「プレビュー」ボタンをクリックしてニュースの実物を確認できます。修正が必要な場合は、
	前のステップに戻って修正を加え、再度「プレビュー」をクリックします。
</p>
								</div>
							<li class="docu_h1_nodeco">Step_6　コンテンツを利用
								<div>
<p>
	修正が完了したら、プレビューされている件名と本文をそれぞれコピーして、
	メーラーやポータルサイトに貼り付けてご利用ください。
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
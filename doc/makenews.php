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
						<h3>ニュース作成の手順</h3>
						<p>ニュースの作成方法を解説します。ニュース作成は、<b>管理者</b>および<b>編集者</b>の権限を持つユーザーが行えます。</p>
						<ol>
							<li class="docu_h1_nodeco">ニュース作成ページに移動する
								<div>
<p>
	ニュースを作成するには、「<a href="<?php echo '/'. BASE .'/' ?>?page=news_make">ニュース作成</a>」を開き、
	作成したい「ニュースID」の「ニュース作成」ボタンをクリックします。なお、各ニュースについて、RSSの取得およびWatson NLCによる
	判定が完了した「最終更新時刻」が表示されています。
</p>
<div class="capture_area"><img src="../images/doc/make/news_make.png"></div>
								</div>
							</li>
							<li class="docu_h1_nodeco">Step_1 メール件名を編集
								<div>
<p>
	あらかじめ登録しておいたメール件名が表示されますが、この場で修正もできます。「20XX/XX/XX」の部分は、プレビュー表示の時に
	年月日に変換されます。
</p>
<div class="capture_area"><img src="../images/doc/make/step_1.png"></div>
								</div>
							<li class="docu_h1_nodeco">Step_2　コンテンツ候補を編集
								<div>
<p>
	Watson NLCによる判定結果を確認し、必要ならば手動で変更するために、コンテンツ候補をダウンロードします。
	「ニュース取得開始日」「ニュース取得開始時刻」を指定して「CSVで書き出す」ボタンを押すと、コンテンツ候補をCSV形式で保存できます。
	これをExcelで開き、必要な修正（採用／非採用の書き換え、確信度の修正）を行ったら、<b>必ず「名前をつけて保存」→「Excelブック（*.xlsx）」</b>で
	保存し、「Excelから書き戻す」ボタンを押してExcelファイルをアップロードしてください。なお、この操作はオプションです。
</p>
<p>
	最後に「取得」ボタンをクリックすると「Step_3」のエリアにニュースが取り込まれます。
</p>
<div class="capture_area"><img src="../images/doc/make/step_2.png"></div>
								</div>
							<li class="docu_h1_nodeco">Step_3　コンテンツ候補を取得
								<div>
<p>
	あらかじめ登録しておいた「ニュース取得開始日」「ニュース取得開始時刻」
	「ニュース取得数」が表示されますが、この場で修正もできます。例えば、月曜日であれば「ニュース取得開始日」を「3日前」として
	先週金曜日以降のニュースを取得できます。
</p>
<p>
	最後に「取得」ボタンをクリックすると「Step_3」のエリアにニュースが取り込まれます。
</p>
<div class="capture_area"><img src="../images/doc/make/step_3.png"></div>
								</div>
							<li class="docu_h1_nodeco">Step_4　ニュース本文を作成
								<div>
<p>
	「連絡メモ欄（オプション）」に文字を入力すると、タイトル画像直下にお知らせメッセージを表示できます。
	例えば、年末年始など、通常と違う配信スケジュールになることを、事前にユーザーへ告知する場合などに利用します。
	必要ない場合は、空白のままにしておきます。
</p>
<p>
	その下には、カテゴリごとにニュースのURLとタイトル、メディア名がセットされます。
	各囲み内の「注目記事！」「削除」「要ログイン」「参考リンク追加」などを使って、適宜修正ください。
	囲み枠はドラッグ＆ドロップで順序を入れ替えられます。
	また、手動でカテゴリーや記事を追加したい場合は「カテゴリーを追加」「記事を追加」ボタンを使用できます。
</p>
<p>
	「カテゴリーを新規追加」ボタンから作成したカテゴリーは一時的に作成したものとなります。
	次回以降も継続して、そのカテゴリーを利用する場合は、「カテゴリ リスト」に項目を追加して、
	「<a href="<?php echo '/'. BASE .'/' ?>?page=crawler_conf">クローラ設定</a>」から再度アップロードしてください。
</p>
<div class="capture_area"><img src="../images/doc/make/step_4.png"></div>
								</div>
							<li class="docu_h1_nodeco"><a name="Step_4">Step_5　ランキングを作成</a>
								<div>
<p>
	発行号を指定して記事のクリック数によるランキングを作成できます。クリックされた記事はすべて表示されます。
	表示させる記事を絞り込む場合や、表示したくないニュースがある場合には、右の「×」ボタンで削除できます。
</p>
<div class="capture_area"><img src="../images/doc/make/step_5.png"></div>
								</div>
							<li class="docu_h1_nodeco">Step_6　プレビューを確認
								<div>
<p>
	「プレビュー」ボタンをクリックしてニュースの実物を確認できます。「プレビュー」ボタンの右の「並び順：...」から
	記事とランキングの上下を入れ替えられます。デフォルトでは、記事の下にランキングが表示されます。
	プレビューを確認して、修正が必要な場合は、前のステップに戻って修正を加え、再度「プレビュー」をクリックします。
</p>
<div class="capture_area"><img src="../images/doc/make/step_6.png"></div>
<p>
	トップ画像を設定していない場合、上記のような仮のトップ画像が表示されます。
	トップ画像、ボトム画像、カテゴリアイコンをオリジナル画像にするには、
	「<a href="<?php echo '/'. BASE .'/' ?>?page=news_conf">ニュース設定</a>」
	メニューから、該当ニュースの「設定変更・削除」ボタンをクリックし、「ニュースの設定変更」画面の下部から、
	それぞれの画像をアップロードします。
</p>
								</div>
							<li class="docu_h1_nodeco">Step_7　コンテンツを利用
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
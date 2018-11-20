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
	print_header("Document Quick Start", $_SESSION);
	print_mennu($_GET['page']);
?>
				<div class="main_area">
					<div class="docu_body_detail">
						<h3>クイックスタート</h3>
						<p>初めてSBNewsを使うために必要な初期設定を説明します。この作業を行えるのは、<b>管理者ユーザー</b>に限られます。</p>

						<ol>
							<li class="docu_h1">ログイン
								<div>
<p>
	<a href="<?php echo '/'. BASE .'/' ?>">SBNews</a>にアクセスするとはじめにログイン画面が表示されます。あらかじめ配布されている企業ID、ユーザーID、パスワードを入力してログインします。
</p>
<div class="capture_area"><img src="../images/doc/quick/login.png"></div>
								</div>
							</li>
							<li class="docu_h1">Watson NLCを登録する
								<div>
<p>
	ログインしたら、まず「Watson NLC」の「ユーザーネーム」と「パスワード」を登録します。この操作は初回のみ必要です。
	「<a href="<?php echo '/'. BASE .'/' ?>?page=admin_menu">管理者メニュー</a>」を開き、「Watsonアカウント登録」の右の
	「設定」ボタンをクリックします。
</p>
<div class="capture_area"><img src="../images/doc/quick/admin_menu.png"></div>
<p>
	事前に取得しておいた「ユーザーネーム」と「パスワード」を入力し、「登録する」ボタンをクリックします。「Watson NLC」の
	登録が完了すると、Watsonの頭脳に当たる「分類子」を作成できるようになります。
</p>
<div class="capture_area"><img src="../images/doc/quick/watson_register.png"></div>
								</div>
							</li>
							<li class="docu_h1"><a name="set_classifier">Watson NLCの分類子を設定する</a>
								<div>
<p>
	「分類子」を作成するには、あらかじめ「トレーニングデータ」を用意する必要があります。
	ExcelファイルのA列にメディア記事のタイトルを列記し、B列で「採用」または「非採用」のフラグを立てます（下記画像参照）。
</p>
<div class="capture_area"><img src="../images/doc/quick/tr_data_sample.png"></div>
<p>
	「トレーニングデータ」はCSV（カンマ区切り）ファイルでアップロードする必要があります。
	Excelを使ってファイルを管理・作成している場合は、<b>「名前をつけて保存」からCSV形式を選んで保存</b>して、さらに<b>文字コードを
	Windowsのデフォルトである「Shift-JIS」から「UTF-8」に変換</b>します。文字コードを変換できるエディターなどをお持ちで
	ない方は、「<a href="<?php echo '/'. BASE .'/' ?>files/SBNews_encoding_tool.bat">SBNews_encoding_tool.bat</a>」
	を任意の場所（デスクトップなど）にダウンロードし、CSVファイルをドラッグ＆ドロップしてください。すると同じ場所に
	「XXX.csv.utf8.csv」という拡張子を持ったファイルが作成されるので、これをトレーニングデータとして利用します。
	（サンプル用トレーニングデータは「<a href="<?php echo '/'. BASE .'/' ?>files/トレーニングデータ.csv">トレーニングデータ.csv</a>」
	からダウンロードできます）
</p>
<div class="capture_area"><img src="../images/doc/quick/encoding_toos.png"></div>
<p>
	「トレーニングデータ」が用意できたら、「<a href="<?php echo '/'. BASE .'/' ?>?page=watson_conf">Watson設定</a>」を開き、
	「分類子を追加／削除」ボタンをクリックします。
</p>
<div class="capture_area"><img src="../images/doc/quick/set_watson.png"></div>
<p>
	「分類子を新規に追加」のエリアから、「ファイルを選択」ボタンをクリックし、用意しておいたCSVファイルを選択します。
</p>
<div class="capture_area"><img src="../images/doc/quick/tr_data_register.png"></div>
<p>
	ファイルの読み込みが完了し、右側の「追加する」ボタンが有効なったらクリックします。なお「分類子」の正式なIDは18桁の英数文字
	となり覚えにくいため、Excelのファイル名を「分類子エイリアス」として登録しますので、以降は「分類子エイリアス」によって
	「分類子」を識別してください。「Watson設定」画面に戻ると、「No」「分類子エイリアス」「分類子」「状態」が表示されます。
</p>
<div class="capture_area"><img src="../images/doc/quick/tr_data_notaberable.png"></div>
<p>
	「分類子」の作成直後は「状態」に赤字で「<span style='color:red;'>学習中につき、まだ利用できません</span>」と表示されます。
	学習が完了するまでの所用時間は「トレーニングデータ」の行数によって増減します。「状態」が「利用可能」と表示されるまで、その「分類子」は使えません。
</p>
								</div>
							</li>
							<li class="docu_h1"><a name="crawler_conf">クローラを設定する</a>
								<div>
<p>
	インターネット上のニュース記事を収集するために「RSSリスト」「カテゴリ リスト」「サイト名リスト」という3つのExcelファイルが
	必要となります。
</p>
<p>
	「RSSリスト」はA列にそのRSSの内容が分かるメモ（Googleアラートに使用したキーワード、ニュース媒体など）。B列にRSSのURL。
	C列にカテゴリ名を入力します。収集したいニュース媒体のHPを調べ、「RSSフィード」などの記載や「RSSアイコン」があるか確認し、
	あればそのリンク先をRSSリストに転載します。もし収集したい媒体がRSSを提供していない場合は、「Googleアラート」を作成します。
	（サンプル用RSSリストは「<a href="<?php echo '/'. BASE .'/' ?>files/RSSリスト.xlsx">RSSリスト.xlsx</a>」
	からダウンロードできます）
</p>
<div class="capture_area"><img src="../images/doc/quick/rss_list_sample.png"></div>
<p>
	「カテゴリリスト」はA列にカテゴリ名を入力（RSSリストのC列と整合させてください）。A列の並び順にメルマガのコンテンツが生成
	されます。B列にはカテゴリアイコン（画像ファイル名）を指定します（オリジナルのカテゴリアイコンは「ニュース設定」からアップロードできます）。
	独自にカテゴリアイコンを作成しない場合には、「base_icon.png」と記載しておきます。
	（サンプル用カテゴリリストは「<a href="<?php echo '/'. BASE .'/' ?>files/カテゴリリスト.xlsx">カテゴリリスト.xlsx</a>」
	から、サンプル用カテゴリアイコンは「<a href="<?php echo '/'. BASE .'/' ?>files/サンプルカテゴリアイコン.zip">サンプルカテゴリアイコン.zip</a>」からダウンロードできます）
</p>
<div class="capture_area"><img src="../images/doc/quick/category_list_sample.png"></div>
<p>
	「サイト名リスト」はA列にURL（「http://」「https://」を除いた残りの部分）、B列にサイト名を記載します。
	サイト名のマッチができない場合、プレビュー画面から手動で入力できますが、こまめにリストに追加して更新することをお勧めします。
	（サンプル用サイト名リストは「<a href="<?php echo '/'. BASE .'/' ?>files/サイト名リスト.xlsx">サイト名リスト.xlsx</a>」
	からダウンロードできます）
</p>
<div class="capture_area"><img src="../images/doc/quick/sitename_list_sample.png"></div>
<p>
	上記3つのExcelファイルが用意できたら、「<a href="<?php echo '/'. BASE .'/' ?>?page=crawler_conf">クローラ設定</a>」を開き、
	各々のファイルの「追加」ボタンを押して、次ページの「ファイルを選択」にて該当のファイルをアップロードしてください。
</p>
								</div>
							</li>
							<li class="docu_h1"><a name="news_conf">ニュースを設定する</a>
								<div>
<p>
	Watsonの「分類子」が利用可能となり、クローラ用の3つのファイルを設定したら、これらのファイルを組み合わせて、ニュースIDを
	作成します。「<a href="<?php echo '/'. BASE .'/' ?>?page=news_conf">ニュース設定</a>」を開き、「ニュースを追加」ボタンをクリックします。
</p>
<div class="capture_area"><img src="../images/doc/quick/news_add.png"></div>
<p>
	「ニュースの新規追加」ページが開いたら、そのニュースの独自名称を「ニュース ID」で指定します。
</p>
<p>
	「分類子エイリアス」「RSSリスト」「カテゴリ リスト」「サイト名リスト」をそれぞれドロップダウンリストから選択します。
	「分類子エイリアス」にはWatson NLCを利用せずランダムに記事を選択する「dummy watson」が登録されていますが、これは
	あくまでもテスト用途のものですので、実運用時には使用しないでください。
</p>
<p>
	「メールの件名」の「20XX/XX/XX」はプレビュー時に作成当日の日付に差し変わります。そのほかの部分は適宜修正ください。
</p>
<p>
	「ニュース取得開始日」および「ニュース取得開始時刻」は、前回ニュースを発行した日時を指定します。例えば、毎日午前9時に
	ニュースを発行しているとしたら、ここは「1日前」および「09」となるでしょう。これにより、前日の午前9時以降にRSSが収集した
	ニュースを元に、その日のニュース候補が作成されます。
</p>
<p>
	「ニュース取得数」は、候補となるニュース記事の取得数です。10から50まで、10個刻みで指定できます。
</p>
<p>
	「署名」欄は、ニュースの末尾に掲載する発行部署などを記載します。
</p>
<div class="capture_area"><img src="../images/doc/quick/news_config.png"></div>
<p>
	以上の設定が完了したら、「ニュースを追加する」ボタンをクリックして、設定内容を確定します。
</p>
<p>
	ニュースを設定すると、アプリ側で毎日1時間ごとにRSSのサイトからニュース記事を収集しWatsonによる判定を実行しますが、
	もし手動でニュースを更新したい場合は「<a href="<?php echo '/'. BASE .'/' ?>?page=news_conf">ニュース設定</a>」
	画面から「ニュース更新（手動）」をクリックして手動更新することも可能です。
</p>
<div class="capture_area"><img src="../images/doc/quick/news_manual_update.png"></div>
<p>
	作成した各ニュースには、トップ画像、ボトム画像、カテゴリアイコンを個別に設定できます。画像をアップロードするには、
	「<a href="<?php echo '/'. BASE .'/' ?>?page=news_conf">ニュース設定</a>」
	メニューから、該当ニュースの「設定変更・削除」ボタンをクリックします。「ニュースの設定変更」画面の下部から、それぞれの画像を
	アップロードできます。
</p>
<div class="capture_area"><img src="../images/doc/quick/image_upload.png"></div>
<p>
	「トップ画像」はプレビュー画面の上部に表示される画像です。ファイル名は何であってもかまいません。
	ファイルタイプは「PNG」のみ利用可能です。
	画像サイズは、幅：600ピクセル（固定）、高さ：120ピクセル（任意）です。
</p>
<p>
	「カテゴリー画像」はプレビュー画面のカテゴリ見出しに表示させる画像です。
	ファイル名は、該当する「カテゴリ リスト ID」に登録したファイル名と整合させてください。
	画像サイズは、幅：360ピクセル（任意）、高さ：70ピクセル（固定）です。
	カテゴリー画像を用意しない場合は、デフォルトの「画像＋テキスト」が使用されます。
</p>
<p>
	「ボトム画像」はプレビュー画面の下部に表示させる画像（任意）です。ファイル名は何であってもかまいません。
	ファイルタイプは「PNG」のみ利用可能です。
	画像サイズは、幅：600ピクセル（固定）、高さ：120ピクセル（任意）です。
</p>
<p>
	ファイルを選択したら、必ずその都度「送信」ボタンを押してください。
</p>
								</div>
							</li>
							<li class="docu_h1">ニュースを作成する
								<div>
<p>
	ニュースを作成するには、「<a href="<?php echo '/'. BASE .'/' ?>?page=news_make">ニュース作成</a>」を開き、
	作成したい「ニュースID」の「ニュース作成」ボタンをクリックします。
</p>
<p>
	<b>Step_1 メール件名を編集</b><br>あらかじめ登録しておいたメール件名が表示されますが、この場で修正もできます。
</p>
<p>
	<b>Step_2　コンテンツ候補を編集</b><br>Watson NLCによる判定結果を確認し、必要ならば手動で変更するために、コンテンツ候補をダウンロードします。
	「ニュース取得開始日」「ニュース取得開始時刻」を指定して「CSVで書き出す」ボタンを押すと、コンテンツ候補をCSV形式で保存できます。
	これをExcelで開き、必要な修正（採用／非採用の書き換え、確信度の修正）を行ったら、<b>必ず「名前をつけて保存」→「Excelブック（*.xlsx）」</b>で
	保存し、「Excelから書き戻す」ボタンを押してExcelファイルをアップロードしてください。なお、この操作はオプションです。
</p>
<p>
	<b>Step_3　コンテンツ候補を取得</b><br>あらかじめ登録しておいた「ニュース取得開始日」「ニュース取得開始時刻」
	「ニュース取得数」が表示されますが、この場で修正もできます。例えば、月曜日であれば「ニュース取得開始日」を「3日前」として
	先週金曜日以降のニュースを取得できます。最後に「取得」ボタンをクリックすると「Step_3」のエリアにニュースが取り込まれます。
</p>
<p>
	<b>Step_4　ニュース本文を作成</b><br>「連絡メモ欄（オプション）」に文字を入力すると、タイトル画像直下にお知らせメッセージを
	表示できます。その下には、カテゴリごとにニュースのURLとタイトル、メディア名がセットされます。
	各囲み内の「注目記事！」「削除」「要ログイン」「参考リンク追加」などを使って、適宜修正ください。
	囲み枠はドラッグ＆ドロップで順序を入れ替えられます。
	また、手動でカテゴリーや記事を追加したい場合は「カテゴリーを追加」「記事を追加」ボタンを使用できます。
</p>
<p>
	<b>Step_5　ランキングを作成</b><br>発行号を指定して記事のクリック数によるランキングを作成できます。なお、
	ランキングに含めたくないニュースが表示されている場合、右の「×」ボタンで削除できます。この場合、ランク外だった記事が
	繰り上がってランクインします。
</p>
<p>
	<b>Step_6　プレビューを確認</b><br>「プレビュー」ボタンをクリックしてニュースの実物を確認できます。修正が必要な場合は、
	前のステップに戻って修正を加え、再度「プレビュー」をクリックします。
</p>
<p>
	<b>Step_7　コンテンツを利用</b><br>修正が完了したら、プレビューされている件名と本文をそれぞれコピーして、
	メーラーやポータルサイトに貼り付けてご利用ください。
</p>
								</div>
							</li>
							<li class="docu_h1">ログを取得する
								<div>
<p>
	
	SBNewsでは、「RSSが取得したニュース一覧」「記事のクリックログ一覧」「メールの開封ログ一覧」「ログインユーザーの記録」]をCSV形式でダウンロードできます。
	「<a href="<?php echo '/'. BASE .'/' ?>?page=log_mgmt">ログ取得</a>」を開き、取得したいログの項目に、YYYY-MM-DD形式で
	取得開始日と終了日を指定して「ダウンロード」ボタンをクリックします。CSVファイルをダウンロード後、ExcelやAccessなどを利用して、
	ニュースIDやタイムスタンプなどの属性ごとに集計、分析を行ってください。
</p>
<div class="capture_area"><img src="../images/doc/quick/log_mgmt.png"></div>
<p>
	<a href="<?php echo '/'. BASE .'/' ?>?page=document"><button class="button_back">戻る</button></a>
</p>
								</div>
							</li>
						</ol>

					</div>
				</div>

<?php
	print_javascript("others");
	print_footer();
}

exit();
?>
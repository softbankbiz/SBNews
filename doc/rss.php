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
						<h3>RSSリストの作成</h3>
						<p>
							SBNewsでは、インターネット上に公開されているニュース記事を収集する手段として
							<a href="https://ja.wikipedia.org/wiki/RSS" target="_blank">RSS</a>を利用しています。
							RSSとはニュースサイトなどが最新ユースを配信する手段として利用するデータフォーマットで、RSSリーダー（フィードリーダーとも）と呼ばれるソフトウェアを使って購読するのが一般的ですが、SBNewsのようにプログラムが読み込んで処理することも可能です。
						</p>
						<ol>
							<li class="docu_h1">RSSフィードの探し方
								<div>
<p>
	収集したいニュース媒体のHPを調べ、「RSSフィード」などの記載や「RSSアイコン」があるか確認。あればそのリンク先を<a href="<?php echo '/'. BASE .'/' ?>doc/quickstart.php?page=document#crawler_conf">RSSリスト</a>に転載します。
</p>
<div class="capture_area"><img src="../images/doc/rss/general_rss.png"></div>
								</div>
							</li>
							<li class="docu_h1">Googleアラートの作成
								<div>
<p>
	もし記事を収集したいメディアがRSSを提供していない場合は「Googleアラート」を作成します。
</p>
<p>
	「<a href="https://www.google.co.jp/alerts" target="_blank">https://www.google.co.jp/alerts</a>」にアクセスし、キーワードを入力して「アラートを作成」をクリック。生成された「アラート」の右側の編集アイコンをクリックします。
</p>
<div class="capture_area"><img src="../images/doc/rss/google_alert.png"></div>
<p>
	編集画面から、下記のように「RSSフィード」と設定し、「アラートを更新」をクリックします。
</p>
<div class="capture_area"><img src="../images/doc/rss/google_alert_set.png"></div>
<p>
	画面を戻ると「RSSアイコン」が追加されているので、クリックしてURLを取得し、<a href="<?php echo '/'. BASE .'/' ?>doc/quickstart.php?page=document#crawler_conf">RSSリスト</a>に追加します。
	RSSリストのA列に「Googleアラート」作成時のキーワードを記載しておきましょう。
</p>
<div class="capture_area"><img src="../images/doc/rss/google_alert_get.png"></div>
								</div>
							</li>
							<li class="docu_h1">RSSフィードの独自開発
								<div>
<p>
	業界紙や会員制サイトなど、特定のメディア記事を収集したいが、そのメディアがRSSフィードを持たない場合は、RSSフィードに相当する
	プログラム（ウェブスクレイピングなどと呼ばれる）を独自に開発する必要があります。
</p>
<p>
	詳しくは<a href="https://ja.wikipedia.org/wiki/%E3%82%A6%E3%82%A7%E3%83%96%E3%82%B9%E3%82%AF%E3%83%AC%E3%82%A4%E3%83%94%E3%83%B3%E3%82%B0" target="_blank">ウェブスクレイピング</a>などを参照してください。
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
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
						<h3>分類子のトレーニング方法</h3>
						<p>
							SBNewでは、RSSを使って収集したWeb上のニュースコンテンツから、ユーザーの目的に合ったニュースを絞り込む手段として、
							「Watson NLC」を利用します。「Watson NLC」の頭脳に当たる「分類子」は、人間が手動で作成した「トレーニングデータ」を
							「Watson NLC」にアップロードして作成します。本ページでは「トレーニングデータ」の作成方法やチューニングを説明します。
						</p>
						<ol>
							<li class="docu_h1">Natural Language Classifier
								<div>
<p>
	Natural Language Classifier（NLC：自然言語分類）とは、Watsonが提供するコグニティブ・テクノロジーAPIのひとつです。
	SBNewsでは、RSSが収集したニュース記事のタイトルに対して、「Watson NLC」に作成させた「分類子」により、「採用」ないし「非採用」の判定を行わせています。
	また、判定結果は「信頼度」（0〜1までの小数）も提供されます。この値は採用と非採用を合計すると「1」になるパーセント値です。
</p>
<p>
	例えば「ドコモのパケットパック海外オプション」という記事タイトルをある「分類子」に判定させると、下記のような応答が得られました。
	この記事は「"top_class" : "採用"」により、採用と判定され、「"confidence" : 0.9960793964755502」により、信頼度は99.6％
	となりました。SBNewsは「採用」された記事タイトルを「信頼度」の降順で並べ替えて、上位（10〜50件）を表示させています。
</p>
<code style="white-space: pre;">{
  "text" : "ドコモのパケットパック海外オプション",
  "top_class" : "採用",
  "classes" : [ {
    "class_name" : "採用",
    "confidence" : 0.9960793964755502
  }, {
    "class_name" : "非採用",
    "confidence" : 0.00392060352444969
  } ]
}
</code>
<p>
	詳しくはIBM Cloudの「<a href="https://console.bluemix.net/catalog/services/natural-language-classifier">Natural Language Classifier</a>」をご参照ください。
</p>
							<li class="docu_h1">分類子の作成
								<div>
<p>
	上記のような応答を得るためには、下記のように、テキスト列：記事タイトル、クラス列：採用／非採用という構造を持ったトレーニングデータを作成する必要があります。
</p>
<p>
	テキスト列の最大文字数は1024文字ですが、60文字未満が推奨されています。また、レコード数は最低5行以上、最大1万5000行以下とされています。
</p>
<div class="capture_area"><img src="../images/doc/training/tr_data_sample.png"></div>
								</div>
							</li>
							<li class="docu_h1">初めてのトレーニングデータ作成
								<div>
<p>
	分類子に期待する判定を行わせるには、最低でも1,000行のレコードを用意する必要があります。これだけの記事タイトルを手動で収集するのは大変なので、
	SBNewsでは、「dummy_watson」という分類子を用意してトレーニングデータを収集する方法を提供しています。
	「dummy_watson」は事前のトレーニング不要で使えますが、その代わり判定は不正確です。
	独自のトレーニングデータを作成する前段階で、「dummy_watson」を使ってトレーニングデータ用の記事を収集できます。
</p>
<p>
	その方法ですが、まず「<a href="<?php echo '/'. BASE .'/' ?>doc/quickstart.php?page=document#crawler_conf" target="_blank">クローラ設定</a>」を実施します。次に、「<a href="<?php echo '/'. BASE .'/' ?>doc/quickstart.php?page=document#news_conf" target="_blank">ニュースを設定</a>」で、「分類子エイリアス」から「dummy_watson」を選択。その他項目は適宜に設定しておきます。ニュース設定が完了すると、一時間ごとにRSSデータの収集が始まります。
</p>
<p>
	数時間後には、RSSで収集した記事データがデータベースに蓄積されます。これを「<a href="<?php echo '/'. BASE .'/' ?>?page=log_mgmt" target="_blank">ログ取得</a>」ページの「RSSが取得したニュース一覧」から期間を指定して、CSVファイルとしてダウンロードします。下記のようなログデータを取得できるので
	A列とB列だけを別ファイルにコピーしたのち、B列の「採用／非採用」のフラグを意図する方針に沿って修正します。
</p>
<div class="capture_area"><img src="../images/doc/training/rss_log.png" width="725px"></div>
<p>
	B列のフラグ修正が完了したら、これを既存のトレーニングデータに追加して、「<a href="<?php echo '/'. BASE .'/' ?>doc/quickstart.php?page=document#set_classifier" target="_blank">Watson NLCの分類子を設定する</a>」の記述に従って、分類子を作成します。
</p>
								</div>
							</li>
							<li class="docu_h1">記事ランキングを利用した分類子のチューニング
								<div>
<p>
	「Watson NLC」はクラス名（採用／非採用）の二者択一方式で分類するものであり、クラス名に数値による重み付けはできません。
	このため、分類子の精度を高めるには採用／非採用のフラグを適切に付与したトレーニングデータを地道に追加することが重要となります。
	SBNewsでは、発行したニュースがクリックされた数をカウントして、記事ランキングを表示させる機能を用意しています。
</p>
<p>
	「<a href="<?php echo '/'. BASE .'/' ?>doc/makenews.php?page=document" target="_blank">ニュース作成</a>」の手順の中で、
	「<a href="<?php echo '/'. BASE .'/' ?>doc/makenews.php?page=document#Step_4" target="_blank">ランキングを作成</a>」を実行します。
	ここで、トレーニングデータに反映させるしきい値として「PV数」ないし「順位」を決め、しきい値を超えた記事タイトルを「採用」、それ以外は「非採用」として
	トレーニングデータに追加することで、読者の反応に基づいた分類子のチューニングが可能になります。
</p>
								</div>
							</li>
							<li class="docu_h1">分類子のチューニング手順
								<div>
<p>
	「Watson NLC」の分類子は、いったん作成したものを後から更新できません。分類子をチューニングするとは、現在の分類子とは別の分類子を
	新たに作成し、ニュース作成に使用する分類子を新しいものに切り替える作業を行います。
</p>
<p>
	手元にあるトレーニングデータに新しい行を追加したり、古くなった過去のレコードを削除するなど、定期的なメンテナンスを実施したら、
	このトレーニングデータを使って分類子を新規に作成し、「ニュース設定」->「設定変更・削除」->「分類子エイリアス」で新しい分類子を選び、
	「データを更新する」をクリックします。
</p>
<p>
	ひとつの「Watson NLC」アカウントで最大8つの分類子を作成できますが、上記のように新旧の分類子を切り替えてチューニングを行うため、
	最低ひとつ切り替え用の分類子を残しておく必要があります。このため、実運用で利用できる分類子は最大7つになります。
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
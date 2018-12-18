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
						<h3>ユーザー管理</h3>
						<p>
							SBNewsのユーザーには、「スーパーユーザー」「管理者」「編集者」という3つの役割のいずれかが割り当てられています。
						</p>
						<ol>
							<li class="docu_h1">スーパーユーザー
								<div>
<p>
	SBNewsをインスートルすると、はじめに「企業ID：root」「ユーザーID：root」というユーザーが作成されます。
	rootユーザーは「スーパーユーザー権限」を持っている唯一のユーザーです。rootユーザーの特権は、「企業ID」を指定してユーザーを作成できることです。
</p>
<p>
	企業IDはWatson NLCのアカウントと1対1で紐づく、ニュース作成の基本単位となります。通常は1つの企業IDで運用を始めると思いますが、
	将来的に別部門や関連会社などが独自にWatson NLCのコストを負担して、独自にユースを作りたいとなった場合は、
	新たな企業IDを割り当てたユーザーを作成することで、コストや運用を独立させることが可能になります。
</p>
<p>
	rootユーザーは「管理者メニュー」の＜ユーザー管理＞および「ドキュメント」「ログアウト」にしかアクセスできません。
	実際にニュースを設定したり作成するには、「管理者」または「編集者」ユーザーを作成し、ログインし直す必要があります。
</p>
								</div>
							</li>
							<li class="docu_h1">管理者ユーザー
								<div>
<p>
	「管理者」の役割を持ったユーザーは、SBNewsのすべての機能を利用できます（企業IDを指定したユーザー作成を除く）。
	管理者ユーザーはWatson NLCの分類子を作成し、クローラを設定し、新規にニュースを設定することを想定しています。
	また、「管理者」および「編集者」の役割を持ったユーザーを作成したり、すべてのログデータを出力する権限も持っています。
</p>
								</div>
							</li>
							<li class="docu_h1">編集者ユーザー
								<div>
<p>
	「編集者」の役割を持ったユーザーは、日々のニュース作成を担当する実務者を想定しています。
	編集者ユーザーが利用できるのは「ニュース作成」「ドキュメント」および「ログ取得（「ログインユーザーの記録」を除く）」に限られます。
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
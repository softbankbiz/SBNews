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
						<h3>セキュリティ</h3>
						<p>
							SBNewsは不正ログインを防ぐために、一定回数パスワードを間違えたユーザーを一定時間ロックします。
							また、管理者ユーザーはログイン記録を確認できます。
						</p>
						<ol>
							<li class="docu_h1">パスワードロック
								<div>
<p>
	SBNewsへログインするには「企業ID」「ユーザーID」「パスワード」を正しく入力する必要があります。
	もし連続して5回パスワードを間違えると、そのユーザーはロックされ、その後1時間はログインできません。
</p>
<p>
	ロックされたユーザーが、管理者に依頼してパスワードを変更しても、ロックは解除されません。
</p>
								</div>
							</li>
							<li class="docu_h1">ログインユーザーの記録
								<div>
<p>
	管理者ユーザーは「ログ取得」メニューから「ログインユーザーの記録」をCSVファイルとしてダウンロードできます。
</p>
<p>
	「ログインユーザーの記録」には、タイムスタンプ、企業ID、ユーザーID、ログインの成否などが記録されています。
	不正なログインや攻撃を受けていないか、定期的に確認することを推奨します。
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
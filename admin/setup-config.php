<?php

define( 'ABSPATH', dirname( dirname( __FILE__ ) ) . '/' );

if (file_exists( ABSPATH . 'sbnews_config.php')) {
	die("セットアップは完了しています。");
}

$step = isset( $_GET['step'] ) ? (int) $_GET['step'] : -1;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow" />
	<title>Setup Configuration</title>
	<link rel="shortcut icon" href="../favicon.ico" >
	<style type="text/css">
		body { width: 600px; margin: auto; }
		.title { text-align: center; }
		.form-table th { text-align: right; padding: 0.4em; }
		.form-table input { width: 300px; }
		.step { text-align: center; }
		.button { font-size: 0.9em; background-color: #FFF; padding: 0.2em; }
		.button:hover { background-color: #59b1eb; transition: all .3s; }

	</style>
</head>
<body>
<?php
switch($step) {
	case -1:
?>
<h1 class="scren-leader-text">SBNewsのセットアップ</h1>
<p>SBNews へようこそ。利用を始める前にデータベースに関する以下の情報が必要となります。</p>
<ol>
<li>データベース名</li>
<li>データベースのユーザー名</li>
<li>データベースのパスワード</li>
</ol>
<p>この情報は <code>sbnews_config.php</code> ファイルを作成するために使用されます。
もしファイルが生成されない場合は、テキストエディターで <code>sbnews_config_sample.php</code> を開き、
データベース情報を記入し、<code>sbnews_config.php</code> として保存します。</strong></p>
<p class="step"><a href="setup-config.php?step=1"><button class="button">セットアップを始める</button></a></p>
<?php
		break;

	case 0:
		break;

	case 1:
		//echo 1;
?>
<h1 class="title">データベースのセットアップ</h1>
<form method="post" action="setup-config.php?step=2">
	<p style="text-align: center;">データベースの接続情報を入力してください。</p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="dbname">データベース名</label></th>
			<td><input name="dbname" id="dbname" type="text" size="25" value="" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="uname">データベースのユーザー名</label></th>
			<td><input name="uname" id="uname" type="text" size="25" value="" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="pwd">データベース接続のパスワード</label></th>
			<td><input name="pwd" id="pwd" type="text" size="25" value="" /></td>
		</tr>
	</table>
	<p class="step"><input name="submit" type="submit" value="セットアップを実行する" class="button" /></p>
</form>
<?php
	break;

	case 2:

	$dbname = trim( $_POST[ 'dbname' ] );
	$uname = trim( $_POST[ 'uname' ] );
	$pwd = trim( $_POST[ 'pwd' ] );

	$step_1 = 'setup-config.php?step=1';
	$install = 'install.php';

	$tryagain_link = '<p class="step"><a href="' . $step_1 . '" onclick="javascript:history.go(-1);return false;"><button class="button">やり直す</button></a>';

	// Test the db connection.
	define('DB_DATABASE', $dbname);
	define('DB_USERNAME', $uname);
	define('DB_PASSWORD', $pwd);

	$mysqli = mysqli_connect("localhost", DB_USERNAME, DB_PASSWORD, DB_DATABASE);

	if(! $mysqli) {
        die('<h1 class="title">データベースのセットアップ</h1><p style="text-align: center;">エラー：DB接続に失敗しました！' . $tryagain_link . '</p>');
    }
	$config_file[ 0 ] = "<?php\r\n";
    $config_file[ 1 ] = "define('DB_DATABASE', '" . $dbname . "');\r\n";
    $config_file[ 2 ] = "define('DB_USERNAME', '" . $uname . "');\r\n";
    $config_file[ 3 ] = "define('DB_PASSWORD', '" . $pwd . "');\r\n";
    $config_file[ 4 ] = "?>";

	if ( ! is_writable(ABSPATH) ) {
		die('<h1 class="title">データベースのセットアップ</h1><p style="text-align: center;"><code>sbnews_config.php</code>の書き込み権限がありません。手動で作成してください。</p>');
	} else if (file_exists( ABSPATH . 'sbnews_config_sample.php')) {
		$path_to_sbnews_config = ABSPATH . 'sbnews_config.php';
		$handle = fopen( $path_to_sbnews_config, 'w' );
		foreach ( $config_file as $line ) {
			fwrite( $handle, $line );
		}
		fclose( $handle );
		chmod( $path_to_sbnews_config, 0666 );

		// create Database and tables for SBNews //
		$path_to_sql = dirname( __FILE__ ) . '/sbnews_db_schema.sql';
		if (file_exists($path_to_sql)) {
			$sql = file_get_contents($path_to_sql);
			$array = explode("\n", $sql);
			$array = array_map('trim', $array);
			$array = array_filter($array, 'strlen');
			$array = array_values($array);
			foreach ($array as $key => $value) {
				if ( ! $mysqli->query($value) ) echo "error at: " . $key . ", " .  $mysqli->error . "<br>";
			}
		} else {
			echo '<h1 class="title">データベースのセットアップ</h1><p style="text-align: center;">DBスキーマ定義ファイルがありません</p>';
		}
	} else {
		die('<h1 class="title">データベースのセットアップ</h1><p style="text-align: center;"><code>sbnews_config_sample.php</code>が存在しません。</p>');
	}
?>
<h1 class="title">SBNewsのセットアップ完了</h1>
<p style="text-align: center;"><a href="../"><button class="button">利用開始する</button></a></p>

</body>
</html>
<?php
}
exit;
?>
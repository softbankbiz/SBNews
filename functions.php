<?php

require_once dirname(__FILE__) . "/sbnews_config.php";

function getConnection() {
	$mysqli = null;
    $mysqli = mysqli_connect("localhost", DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if(! $mysqli) {
        die("エラー：DB接続に失敗しました！");
    }
	return $mysqli;
}


/**
 * クロスサイトスクリプティング対策
 * htmlspecialcharsのラッパー関数
 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}


/**
 * Watson NLC用の入力チェック関数
 */
function nlc_text($s) {
	$chars = array(
		chr(0x00),chr(0x01),chr(0x02),chr(0x03),chr(0x04),chr(0x05),chr(0x06),chr(0x07),chr(0x08),chr(0x09),
		chr(0x0a),chr(0x0b),chr(0x0c),chr(0x0d),chr(0x0e),chr(0x0f),chr(0x10),chr(0x11),chr(0x12),chr(0x13),
		chr(0x14),chr(0x15),chr(0x16),chr(0x17),chr(0x18),chr(0x19),chr(0x1a),chr(0x1b),chr(0x1c),chr(0x1d),
		chr(0x1e),chr(0x1f),chr(0x7f),chr(0x22),",","`","'"
	);
	$s = str_replace($chars, "", $s);
	return substr( $s, 0, 1024);
}

/**
 * ログ出力用の日付フォーマットチェック
 */
function yyyymmdd($y, $m, $d){
	$date = $y . '/' . $m . '/' . $d;
    //書式
        //2012/1/1
    //年
        //4桁整数     1000-9999
    //月
        //1桁の場合は 01-09
        //2桁の場合は 10の位が1  1の位が0-2
    //日
        //1桁の場合は 01～09
        //2桁の場合は 10の位が1と2  1の位が0-9
        //2桁の場合は 10の位が3     1の位が0が1

    if (preg_match('/^([1-9][0-9]{3})\/(0[1-9]{1}|1[0-2]{1})\/(0[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1})$/', $date)) {
        return true;
    } else {
        return false;
    }
}

/**
 * BD入力用の日付フォーマットチェック
 */
function yyyymmdd_db($date){
    //書式
        //2012-1-1
    //年
        //4桁整数     1000-9999
    //月
        //1桁の場合は 01-09
        //2桁の場合は 10の位が1  1の位が0-2
    //日
        //1桁の場合は 01～09
        //2桁の場合は 10の位が1と2  1の位が0-9
        //2桁の場合は 10の位が3     1の位が0が1

    if (preg_match('/^([1-9][0-9]{3})-(0[1-9]{1}|1[0-2]{1})-(0[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1})$/', $date)) {
        return true;
    } else {
        return false;
    }
}

/**
 * ニュースを取得する際に、いつまで遡るか？
 * @param  string ex. "-1 day", "-1 week"
 * @param  string ex. "09", "14"
 * @return string
 */
function gat_period($day, $hour) {
	date_default_timezone_set ("Asia/Tokyo");
	$period = date("Y-m-d", strtotime($day)) . " " . $hour . ":00:00";
	return $period;
}

function gat_Ymd($day) {
	date_default_timezone_set ("Asia/Tokyo");
	return date("Y-m-d", strtotime($day));
}

function get_today() {
	date_default_timezone_set ("Asia/Tokyo");
	$today = array(date("Y"), date("m"), date("d"));
	return $today;
}


/**
 * パスワード総当たり攻撃を防ぐ。
 * @param  mysqliオブジェクト, company_id, user_id, 20文字以上は削除
 * @return 悪いやつじゃなければ true
 */
function bfa_check($mysqli, $company_id, $user_id) {
	if (is_null($company_id) || is_null($user_id)) {
		return false;
	}

	$_company_id = mb_strimwidth($company_id, 0, 20);
	$_user_id    = mb_strimwidth($user_id, 0, 20);
	$max_value   = 5;  // 連続して間違えるとロックされる回数
	$interval    = 1;  // ロックされる時間

	try {
		$query = "SELECT stamp FROM login_record WHERE company_id = ? AND user_id = ? ORDER BY ts DESC limit ?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssi", $_company_id, $_user_id, $max_value);
		$stmt->execute();
		$result = $stmt->get_result();

		// $max_value 未満のアクセス回数では、通過
		if ( $result->num_rows < $max_value ) {
			return true;
		}

		// 直近で $max_value 個のアクセスに "succeed" があれば、通過
		while ($row = $result->fetch_array(MYSQLI_NUM)) {
			if ($row[0] == "succeed") {
				return true;
			}
		}

		// $max_value 個のアクセスがすべて "fail" だったら、最後の "fail" から $interval 時間を過ぎているかチェック
		$query = "SELECT ts FROM login_record WHERE company_id = ? AND user_id = ? AND stamp = ? ORDER BY ts DESC limit 1";
		$stmt = $mysqli->prepare($query);
		$stamp = "fail";
		$stmt->bind_param("sss", $_company_id, $_user_id, $stamp);
		$stmt->execute();
		$result = $stmt->get_result();
		$ts = $result->fetch_array(MYSQLI_NUM);
		$dt = new DateTime($ts[0]);  // 最後の "fail" 時刻
		$now = new DateTime();       // 現在時刻
		$diff = intval($dt->diff($now)->format('%h'));  // 差分時間

		if ($diff >= $interval) {
			return true;        // ロック時間を過ぎていたら、通過
		} else {
			return false;       // ロックされている。ただしDBに "fail" は書き込まない
		}
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}

function login_record($mysqli, $company_id, $user_id, $stamp, $description) {
	if (is_null($company_id) || is_null($user_id)) {
		return false;
	}
	$_company_id = mb_strimwidth($company_id, 0, 20);
	$_user_id = mb_strimwidth($user_id, 0, 20);
	$_stamp = mb_strimwidth($stamp, 0, 10);
	$_description = mb_strimwidth($description, 0, 20);
	try {
		$query = "INSERT INTO login_record (company_id,user_id,stamp,description) VALUES (?,?,?,?)";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ssss", $_company_id, $_user_id, $_stamp, $_description);
		$stmt->execute();
		$stmt->close();
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}


/**
 * DBアクセス時に、ログイン用ID/PASSに加え
 * userIDも登録されているかチェックする。
 * @param  mysqliオブジェクト, company_id, user_id, 20文字以上は削除
 * @return 成功時は true
 */
function two_step_auth($mysqli, $company_id, $user_id) {
	if (is_null($company_id) || is_null($user_id)) {
		return false;
	}
	$_company_id = mb_strimwidth($company_id, 0, 20);
	$_user_id = mb_strimwidth($user_id, 0, 20);
	try {
		$query = "SELECT user_id FROM users_list WHERE company_id = ? AND user_id = ?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss", $_company_id, $_user_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_array(MYSQLI_NUM);

		if ($row[0] == $user_id) {
			return true;
		} else {
			return false;
		}

		$stmt->close();
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}

/**
 * set_news.phpでつかう<select>作成用関数
 */
define('PERIOD_DAY_LIST',
		array(
	   		array("-1 day","1日前"),
	   		array("-2 day","2日前"),
	   		array("-3 day","3日前"),
	   		array("-4 day","4日前"),
	   		array("-5 day","5日前"),
	   		array("-6 day","6日前"),
	   		array("-7 day","7日前")
	   	)
	);

define('PERIOD_HOUR_LIST',
		array("00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23")
	);

define('FETCH_NUM_LIST',
		array("10","20","30","40","50")
	);


/**
 * 画像アップロードで使う<select>作成用関数
 */
function get_news_id_as_select($mysqli, $news_id, $label) {
	$query = 'SELECT news_id from preference WHERE company_id ="' . $_SESSION['company_id'] . '"';
	try {
		$buf = '<select name="top_news_id" id="' . $label . '"><option> -- 未選択 -- </option>';
		$result = $mysqli->query($query);
		while( $news_id_from_db = $result->fetch_array(MYSQLI_NUM)[0] ) {
			if ($news_id_from_db === $news_id) {
				$buf .= '<option selected>' . $news_id_from_db . '</option>';
			} else {
				$buf .= '<option>' . $news_id_from_db . '</option>';
			}
		}
		$result->free();
		$buf .= '</select>';
		return $buf;
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}

/**
 * set_news.phpでつかう<select>作成用関数
 */
function get_cid_alias_as_select($mysqli, $cid_alias) {
	try {
		$result = get_classifier_list($mysqli, $_SESSION["company_id"]);

		$config = get_configuration($mysqli, $_SESSION["company_id"]);
		$w_apikey = $config["w_apikey"];
		$w_url = $config["w_url"];

		$buf = '<select name="cid_alias" id="cid_alias">';
		if ($cid_alias === 'dummy_watson') {
			$buf .= '<option selected>dummy_watson</option>';
		} else {
			$buf .= '<option>dummy_watson</option>';
		}

		while( $row = $result->fetch_array(MYSQLI_ASSOC) ) {
			if ("利用可能" !== get_cid_status($w_apikey, $w_url, $row["cid"])) {
				continue;
			} else if ($row["cid_alias"] === $cid_alias) {
				$buf .= '<option selected>' . $row["cid_alias"] . '</option>';
			} else {
				$buf .= '<option>' . $row["cid_alias"] . '</option>';
			}
		}
		$result->free();
		$buf .= '</select>';
		return $buf;
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}

function get_rss_id_as_select($mysqli, $rss_id) {
	$query = 'SELECT rss_id from rss_list WHERE company_id ="' . $_SESSION['company_id'] . '"';
	try {
		$buf = '<select name="rss_id" id="rss_id">';
		$result = $mysqli->query($query);
		while( $rss_id_from_db = $result->fetch_array(MYSQLI_NUM)[0] ) {
			if ($rss_id_from_db === $rss_id) {
				$buf .= '<option selected>' . $rss_id_from_db . '</option>';
			} else {
				$buf .= '<option>' . $rss_id_from_db . '</option>';
			}
		}
		$result->free();
		$buf .= '</select>';
		return $buf;
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}

function get_category_id_as_select($mysqli, $category_id) {
	$query = 'SELECT category_id from category_list WHERE company_id ="' . $_SESSION['company_id'] . '"';
	try {
		$buf = '<select name="category_id" id="category_id">';
		$result = $mysqli->query($query);
		while( $category_id_from_db = $result->fetch_array(MYSQLI_NUM)[0] ) {
			if ($category_id_from_db === $category_id) {
				$buf .= '<option selected>' . $category_id_from_db . '</option>';
			} else {
				$buf .= '<option>' . $category_id_from_db . '</option>';
			}
		}
		$result->free();
		$buf .= '</select>';
		return $buf;
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}


function get_site_names_id_as_select($mysqli, $site_names_id) {
	$query = 'SELECT site_names_id from site_names_list WHERE company_id ="' . $_SESSION['company_id'] . '"';
	try {
		$buf = '<select name="site_names_id" id="site_names_id">';
		$result = $mysqli->query($query);
		while( $site_names_id_from_db = $result->fetch_array(MYSQLI_NUM)[0] ) {
			if ($site_names_id_from_db === $site_names_id) {
				$buf .= '<option selected>' . $site_names_id_from_db . '</option>';
			} else {
				$buf .= '<option>' . $site_names_id_from_db . '</option>';
			}
		}
		$result->free();
		$buf .= '</select>';
		return $buf;
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}


function get_period_day_as_select($mysqli, $period_day) {
	$buf = '<select name="period_day" id="period_day">';
	foreach ( PERIOD_DAY_LIST as $row ) {
		if ($period_day === $row[0]) {
			$buf .= '<option selected value="' . $row[0] . '">' . $row[1] . '</option>';
		} else {
			$buf .= '<option value="' . $row[0] . '">' . $row[1] . '</option>';
		}
	}
	$buf .= '</select>';
	return $buf;
}

function get_period_hour_as_select($mysqli, $period_hour) {
	$buf = '<select name="period_hour" id="period_hour">';
	foreach ( PERIOD_HOUR_LIST as $item ) {
		if ($period_hour === $item) {
			$buf .= '<option selected value="' . $item . '">' . $item . '</option>';
		} else {
			$buf .= '<option value="' . $item . '">' . $item . '</option>';
		}
	}
	$buf .= '</select>';
	return $buf;
}

function get_fetch_num_as_select($mysqli, $fetch_num) {
	$buf = '<select name="fetch_num" id="fetch_num">';
	foreach ( FETCH_NUM_LIST as $item ) {
		if ($fetch_num === $item) {
			$buf .= '<option selected value="' . $item . '">' . $item . '</option>';
		} else {
			$buf .= '<option value="' . $item . '">' . $item . '</option>';
		}
	}
	$buf .= '</select>';
	return $buf;
}
/**
 * ここまで、set_news.phpでつかう<select>作成用関数
 */


function get_cid_status($w_apikey, $w_url, $classifier_id) {
	require_once "./WatsonNLU.php";
	$wnlu = new WatsonNLU;
	$result = $wnlu->info_model($w_apikey, $w_url, $classifier_id);
	$res = json_decode($result);
	if ($res->{"status"} === "available") {
		return "利用可能";
	} else if ($res->{"status"} === "starting") {
		return "<span style='color:red;'>学習を開始しました。まだ利用できません</span>";
	} else if ($res->{"status"} === "training") {
		return "<span style='color:red;'>学習中につき、まだ利用できません</span>";
	} else if ($res->{"status"} === "standby") {
		return "<span style='color:red;'>スタンバイ中につき、まだ利用できません</span>";
	} else if ($res->{"status"} === "deploying") {
		return "<span style='color:red;'>デプロイ中につき、まだ利用できません</span>";
	} else if ($res->{"status"} === "error") {
		$error_msg = '';
		$notice = json_decode($res->{"notices"});
		foreach ( $notice as $msg ) {
		  $error_msg .= $msg->{"message"} . ' ';
		}
		return "<span style='color:red;'>エラーが発生しました。　エラーメッセージ：　" . $error_msg . "</span>";
	} else if ($res->{"status"} === "deleted") {
		return "<span style='color:red;'>削除されました</span>";
	} else {
		return $res->{"status"} . ' : ' . $res->{"status_description"};
	}
}



function get_configuration($mysqli, $company_id) {
	$query = "SELECT w_apikey,w_url FROM configuration WHERE company_id = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s", $company_id);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_array();
	return $result;
}


/******************************************
 * レイアウト的な出力
 *
 *****************************************/
function print_header($title, $session) {
	defined('BASE') or define('BASE', basename(dirname(__FILE__)));
	echo '<!DOCTYPE html>';
	echo '<html lang="ja">';
	echo '	<head>';
	echo '		<meta charset="UTF-8">';
	echo '		<meta http-equiv="x-ua-compatible" content="ie=edge">';
	echo '		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
	echo '		<title>' . $title . '</title>';
	echo '		<meta name="description" content="SBNews">';
	echo '		<link rel="shortcut icon" href="/' . BASE . '/favicon.ico" >';
	echo '		<link rel="stylesheet" href="/' . BASE . '/css/normalize.css">';
	echo '		<link rel="stylesheet" href="/' . BASE . '/css/main.css">';
	echo '	</head>';
	echo '	<body>';
	echo '		<div class="header">';
	echo '			<img src="/' . BASE . '/images/top_title.png"><div class="header_title"></div>';
	if ($session) {
	echo '			<div class="login_user">' . $session['user_id'] . '@' . $session['company_id'] . '</div>';
	echo '			<input type="hidden" id="role" value="' . $session['role'] . '">';
	}
	echo '		</div>';
	echo '		<div class="wrapper clearfix">';
}

function print_javascript($opt) {
	defined('BASE') or define('BASE', basename(dirname(__FILE__)));
	if ($opt == "newsmaker") {
		echo '<script src="/' . BASE . '/js/vendor/jquery-3.2.1.min.js"></script>';
		echo '<script src="/' . BASE . '/js/vendor/jquery-ui.min.js"></script>';
		echo '<script src="/' . BASE . '/js/main.js"></script>';
	} else if($opt == "others") {
		echo '			<script src="/' . BASE . '/js/vendor/jquery-3.2.1.min.js"></script>';
		echo '			<script src="/' . BASE . '/js/utility.js"></script>';
		echo '			<script>';
		echo '$(document).ready( function() {';
		echo '	$(".menu_selected > a").removeAttr(\'href\');';
		echo '	var page = getParam(\'page\');';
		echo '	if(page == null) {';
		echo '		$("#news_make").css("display","block");';
		echo '	} else {';
		echo '		var _id = \'#\' + page;';
		echo '		$(_id).css("display","block");';
		echo '	}	';
		echo '});';
		echo '			</script>';
	}
}

function print_mennu($page) {
	defined('BASE') or define('BASE', basename(dirname(__FILE__)));
	$menu_items = array(
		"ニュース作成" => "news_make", "ニュース設定" => "news_conf", "Watson設定" => "watson_conf",
		"クローラ設定" => "crawler_conf", "ログ取得" => "log_mgmt", "ドキュメント" => "document",
		"管理者メニュー" => "admin_menu", "ログアウト" => "logout");
	echo '	<div class="nav">';
	echo '		<ul>';
	if ($page == null) {
		foreach ($menu_items as $menu => $param) {
			if ($menu == "ニュース作成") {
				echo '			<li class="menu_selected">'. $menu . '</li>';
			} else {
				echo '			<li class="menu_not_selected"><a href="/' . BASE . '/?page=' . $param . '">'. $menu . '</a></li>';
			}
		}
	} else {
		foreach ($menu_items as $menu => $param) {
			if ($page == $param) {
				if ($page == 'logout') {
					echo '<script>location.href = "/' . BASE . '/logout.php";</script>';
				} else {
					echo '			<li class="menu_selected"><a href="/' . BASE . '/?page=' . $param . '">'. $menu . '</a></li>';
				}
			} else {
				echo '			<li class="menu_not_selected"><a href="/' . BASE . '/?page=' . $param . '">'. $menu . '</a></li>';
			}
		}
	}
	echo '		</ul>';
	echo '	</div>';
}

function print_footer() {
	echo '		</div>';
	echo '		<div class="footer">' . 'Powerd by SBNews.project' . '</div>';
	echo '	</body>';
	echo '</html>';
}




/*  fetch_contents  */
function do_fetch($mysqli, $rss_arr, $site_names_arr, $insert_suffix) {
	$feed = new Feed;
	$counter = 0;
	$start = time();

	foreach($rss_arr as $num => $line) {
		if($num === 0) { continue; }
		$rss = explode(",", $line);
		try {
			$rss_result = $feed->load($rss[1]);
			if (empty($rss_result)) {
				die("feed is empty.");
			}
			if ($rss_result->item) {
				foreach ($rss_result->item as $item) {
					$buffer = "";
					$url = get_redirected_url($item->link);
					if ($url) {
						$buffer .= '("' .
							         escape_text($mysqli, $item->title) . '","' .
							         $url . '","' .
							         $item->timestamp . '","' .
							         escape_text($mysqli, $item->description) . '","' .
							         $rss[2] . '","' .
							         get_sitename($url, $site_names_arr) .
							         $insert_suffix .
							       ')';
					}
					if ($buffer) {
						$counter += store_data($mysqli, $buffer);
					}
				}
			} elseif ($rss_result->entry) {
				foreach ($rss_result->entry as $entry) {
					$buffer = "";
					$url = get_redirected_url($entry->link->attributes()["href"]);
					if ($url) {
						$buffer .= '("' .
							         escape_text($mysqli, $entry->title) . '","' .
							         $url . '","' .
							         $entry->timestamp . '","' .
							         escape_text($mysqli, $entry->description) . '","' .
							         $rss[2] . '","' .
							         get_sitename($url, $site_names_arr) .
							         $insert_suffix .
							       ')';
					}
					if ($buffer) {
						$counter += store_data($mysqli, $buffer);
					}
				}
			}
		} catch(Exception $e) {
			//echo '捕捉した例外 : ' . $e->getMessage() . " : " . $rss[1] . "<br>";
		}
	}

	$timedistance = time() - $start;
	$minutes = intdiv($timedistance, 60);
	$seconds = $timedistance % 60;
	$result = "Finished, " . strval($counter) . " lines inserted. " . strval($minutes) . " min " . strval($seconds) . " sec.";
	return $result;
}

function get_sitename($url, $site_names_arr) {
	foreach($site_names_arr as $line) {
		$items = explode(",", $line);
		if ($items[0] && strpos($url, $items[0]) !== false) {
			return $items[1];
		}
	}
}

function store_data($mysqli, $rss_data) {
	$query = 'INSERT INTO article_candidate (title, url, created, summary, category, site_name, company_id, news_id) VALUES ' . $rss_data;

	try {
		if($mysqli->query($query)) {
			return 1;
        } else {
            // Not inserted
            return 0;
        }
    } catch (mysqli_sql_exception $e) {
        throw $e;
        die();
    }
}

function escape_text($mysqli, $s) {
	$pattern = '/<("[^"]*"|\'[^\']*\'|[^\'">])*>/';
	$s = trim($s);
	$s = str_replace(array("\r\n", "\r", "\n"), '', $s);
	$s = preg_replace($pattern, '', $s);
	$s = $mysqli->real_escape_string($s);
	return $s;
}

function get_redirected_url($url) {
	if (extension_loaded('curl')) {
		$url_r = null;
		$_url = explode("url=", $url);
		if (count($_url) === 2) {
			$url_r = $_url[1];
		} else {
			$url_r = $url;
		}
		$url_r = explode("&", $url_r)[0];
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url_r);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_ENCODING , '');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // no echo, just return result
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($curl);
		$url_r = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
		if (curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200) {
			return $url_r;
		} else {
			//echo '>>>>>>リンク先に到達できない : ' . $url_r . ' <br>';
			return null;
		}

	} else {
		throw new FeedException('PHP extension CURL is not loaded.');
	}
}
/*  fetch_contents  */




function update_watson_res($mysqli, $url, $class_name, $confidence, $cid_alias, $cid, $news_id) {
	$query = 'UPDATE article_candidate SET class_name="' . $class_name . '", confidence=' . $confidence . ', cid_alias="' . $cid_alias . '", cid="' .  $cid . '" WHERE url="' . $url . '" AND news_id="' . $news_id . '"';
	$result = $mysqli->query($query);
	if (!$result) {
		//echo "watson judgement update was failed.<br>";
	}
}

function dummy_watson() {
	$rand_a = rand(1, 30);
	$rand_b = rand(1, 30);
	$class_name = "";
	$confidence = null;
	if ($rand_a > $rand_b) {
		$class_name = "採用";
		$confidence = round(($rand_b / $rand_a), 4);
	} else {
		$class_name = "非採用";
		$confidence = round(($rand_a / $rand_b), 4);
	}
	$response = array(
		"classes" => array(
			0 => array(
					"class_name" => $class_name,
					"confidence" => $confidence
				)
		)
	);
	return json_encode($response);
}

function get_news_id($mysqli, $company_id) {
	try {
		$query = "SELECT news_id from preference WHERE company_id = ?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s", $company_id);
		$stmt->execute();
		$news_id_list = $stmt->get_result();
		if ($news_id_list) {
			return $news_id_list;
		} else {
			return null;
		}
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}

function get_news_ts($mysqli, $news_id, $company_id) {
	try {
		$query = "SELECT ts FROM article_candidate WHERE news_id = ? AND company_id = ? ORDER BY ts DESC LIMIT 1";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss", $news_id, $company_id);
		$stmt->execute();
		$ts = $stmt->get_result()->fetch_array(MYSQLI_NUM)[0];
		if ($ts) {
			return $ts;
		} else {
			return null;
		}
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}

function get_preference($mysqli, $company_id, $news_id) {
	try {
		$query = 'SELECT news_id,cid_alias,company_id,category_id,rss_id,site_names_id,default_title,period_day,period_hour,fetch_num,signature ' .
		         'FROM preference WHERE company_id = ? AND news_id = ?';
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss", $company_id, $news_id);
		$stmt->execute();
		$preference = $stmt->get_result();
		if ($preference) {
			return $preference;
		} else {
			return null;
		}
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}

function get_classifier_list($mysqli, $company_id) {
	try {
		$query = "SELECT cid_alias,cid from classifier_list WHERE company_id = ? ORDER BY ts ASC";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("s", $company_id);
		$stmt->execute();
		$classifier_list = $stmt->get_result();
		if ($classifier_list) {
			return $classifier_list;
		} else {
			return null;
		}
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}

/**
 * ユーザーリストの取得
 * $company_idが null の場合は、rootユーザーが全ユーザーを取得することを想定。
 * @param  mysqliオブジェクト, company_id
 * @return 成功時は ユーザーリスト
 */
function get_users_list($mysqli, $company_id) {
	if (is_null($company_id)) {
		$query = "SELECT company_id,user_id,password_expires,role FROM users_list";
		try {
			$users_list = $mysqli->query($query);
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
	} else {
		try {
			$query = "SELECT user_id,password_expires,role FROM users_list WHERE company_id = ?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $company_id);
			$stmt->execute();
			$users_list = $stmt->get_result();
		} catch (mysqli_sql_exception $e) {
		    throw $e;
		    die();
		}
	}
	if ($users_list) {
		return $users_list;
	} else {
		return null;
	}
}

function get_user($mysqli, $company_id, $target) {
	try {
		$query = "SELECT user_id,password_expires,role  FROM users_list WHERE company_id = ? AND user_id = ?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("ss", $company_id, $target);
		$stmt->execute();
		$user = $stmt->get_result();
		if ($user) {
			return $user->fetch_assoc();
		} else {
			return null;
		}
	} catch (mysqli_sql_exception $e) {
	    throw $e;
	    die();
	}
}

function translate_role($role) {
	if ($role == 'admin') {
		return '管理者';
	} else if ($role == 'editor') {
		return '編集者';
	} else {
		return null;
	}
}


/**
* Returns a string with backslashes before characters that need to be escaped.
* As required by MySQL and suitable for multi-byte character sets
* Characters encoded are NUL (ASCII 0), \n, \r, \, ', ", and ctrl-Z.
* In addition, the special control characters % is also escaped,
* suitable for all statements, but especially suitable for `LIKE`.
*
* @param string $string String to add slashes to
* @return $string with `\` prepended to reserved characters
*
* @author Trevor Herselman
*/
if (function_exists('mb_ereg_replace'))
{
    function mb_escape(string $string)
    {
        return mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x25\x27\x5C]', '\\\0', $string);
    }
} else {
    function mb_escape(string $string)
    {
        return preg_replace('~[\x00\x0A\x0D\x1A\x22\x25\x27\x5C]~u', '\\\$0', $string);
    }
}

/*
 * ディレクトリと内部のファイルを再帰的に削除
*/
function delTree($dir) {
	$files = array_diff(scandir($dir), array('.','..'));
	foreach ($files as $file) {
		(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
   }
   return rmdir($dir);
}

/**
 * CSVの文中「"」で囲まれた範囲に出現する「,」をセパレータとして認識させないための関数
 */
function csvSplit($str) {
	$flag = false;
	$arr = preg_split('//u', $str, null, PREG_SPLIT_NO_EMPTY);
	$result = array();
	$buf = array();
	foreach($arr as $c) {
		if ( $c == '"' && $flag == false ) {
			$flag = true;
		} else if ( $c == '"' && $flag == true ) {
			$flag = false;
		} else if ( $c == ',' && $flag == true ) {
			$buf[] = $c;
		} else if ( $c == ',' && $flag == false ) {
			$result[] = implode($buf);
			$buf = array();
		} else {
			$buf[] = $c;
		}
	}
	$result[] = implode($buf);
	return $result;
}

function datetimeFormatter($str) {
	$date = new DateTime($str);
	return $date->format('Y-m-d H:m:s');
}
?>

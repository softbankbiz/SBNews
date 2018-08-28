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

function get_cid_alias_as_select($mysqli, $cid_alias) {
	try {
		$result = get_classifier_list($mysqli, $_SESSION["company_id"]);

		$config = get_configuration($mysqli, $_SESSION["company_id"]);
		$w_username = $config["w_username"];
		$w_password = $config["w_password"];

		$buf = '<select name="cid_alias" id="cid_alias">';
		if ($cid_alias === 'dummy_watson') {
			$buf .= '<option selected>dummy_watson</option>';
		} else {
			$buf .= '<option>dummy_watson</option>';
		}

		while( $row = $result->fetch_array(MYSQLI_ASSOC) ) {
			if ("利用可能" !== get_cid_status($w_username, $w_password, $row["cid"])) {
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


function get_cid_status($w_username, $w_password, $classifier_id) {
	require_once "./WatsonNLC.php";
	$wnlc = new WatsonNLC;
	$result = $wnlc->info_classifier($w_username, $w_password, $classifier_id);
	$res = json_decode($result);	
	if ($res->{"status"} === "Available") {
		return "利用可能";
	} else if ($res->{"status"} === "Training") {
		return "<span style='color:red;'>学習中につき、まだ利用できません</span>";
	} else {
		return $res->{"status"} . ' : ' . $res->{"status_description"};
	}
}

function get_configuration($mysqli, $company_id) {
	$query = "SELECT w_username,w_password FROM configuration WHERE company_id = ?";
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
	echo '			<div class="header_title">' . $title . '</div>';
	if ($session) {
	echo '			<div class="login_user">' . $session['user_id'] . '@' . $session['company_id'] . '</div>';
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
	echo '		<div class="footer">' . 'Powerd by SoftBankBiz@gmail.com' . '</div>';
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
echo $rss[2];
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
			echo '捕捉した例外 : ' . $e->getMessage() . " : " . $rss[1] . "<br>";
		}
	}

	$timedistance = time() - $start;
	$minutes = intdiv($timedistance, 60);
	$seconds = $timedistance % 60;
	echo "<br><br>Finished, " . $counter . " lines inserted. " . $minutes . " min " . $seconds . " sec.<br><br><br>";
	//$mysqli->close();
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
			echo '>>>>>>リンク先に到達できない : ' . $url_r . ' <br>';
			return null;
		}
		
	} else {
		throw new FeedException('PHP extension CURL is not loaded.');
	}
}
/*  fetch_contents  */




function update_watson_res($mysqli, $url, $class_name, $confidence) {
	$query = 'UPDATE article_candidate SET class_name="' . $class_name . '", confidence=' . $confidence . ' WHERE url="' . $url . '"';
	$result = $mysqli->query($query);
	if (!$result) {
		echo "watson judgement update was failed.<br>";
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
?>
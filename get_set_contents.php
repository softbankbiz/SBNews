<?php

require_once dirname(__FILE__) . "/functions.php";

session_start();

$mysqli = getConnection();

////////////////// user_id 認証
if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
    return;
}

// Excelへの書き出し用にCSVを出力
if ($_POST["period_day"] && $_POST["period_hour"] && $_POST["news_id"]) {
    $query = "SELECT title,class_name,confidence,url FROM article_candidate WHERE created >= ? AND company_id = ? AND news_id = ?";
    try {
        $period = gat_period($_POST["period_day"], $_POST["period_hour"]);
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $period, $_SESSION["company_id"], $_POST["news_id"]);
        $stmt->execute();
        $contents = $stmt->get_result();
        $buf = '';
        while ($row = $contents->fetch_row()) {
            $tmp = array();
            foreach ($row as $key => $value) {
                if (strpos($value, ',') !== false) {
                    $tmp[] = '"' . $value . '"';
                } else {
                    $tmp[] = $value;
                }
            }
            $buf .= implode(',', $tmp) . "\n";
        }
        if (isset($buf)) {
            echo $buf;
        } else {
            echo 'no data!';
        }
    } catch (mysqli_sql_exception $e) {
        throw $e;
        die("ng3");
    }

// Excelからの書き戻し
} else if ($_POST["import_file"] && $_POST["news_id"]) {
    $query = "UPDATE article_candidate SET title = ?, class_name = ?, confidence = ?, url = ?  WHERE url = ? AND company_id = \"" . $_SESSION["company_id"] . "\" AND news_id = \"" . $_POST["news_id"] . "\"";
    try {
        $stmt = $mysqli->prepare($query);
        $list = explode("\n", $_POST["import_file"]);
        $cnt = 0;
        foreach ($list as $key => $value) {
            $items = csvSplit($value);
            if (count($items) == 4) {
                $stmt->bind_param("ssdss", $items[0], $items[1], $items[2], $items[3], $items[3]);
                $stmt->execute();
                if ($stmt->errno == 0) { $cnt += 1; } 
            }
        }
        if ($cnt > 0) {
            echo $cnt;
        } else {
            echo 0;
        }
    } catch (mysqli_sql_exception $e) {
        throw $e;
        die("ng");
    }
} else {
    die("パラメータが足りません");
}
?>
<?php

require_once dirname(__FILE__) . "/functions.php";

session_start();

$mysqli = getConnection();

////////////////// user_id 認証
if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
    return;
}

if ($_POST["period_day"] && $_POST["period_hour"] && $_POST["fetch_num"] && $_POST["news_id"]) {
    $query_a = "SELECT category,title,url,site_name,created FROM article_candidate WHERE class_name = \"採用\" AND created >= ? AND company_id = ? AND news_id = ? ORDER BY confidence DESC LIMIT ?";
    $query_b = "SELECT category_data from category_list WHERE category_id = (SELECT category_id FROM preference WHERE company_id = ? AND news_id = ?)";
} else {
    die("パラメータが足りません");
}

try {
    $period = gat_period($_POST["period_day"], $_POST["period_hour"]);
    $fetch_num = (int) $_POST["fetch_num"];
    $stmt_a = $mysqli->prepare($query_a);
    $stmt_a->bind_param("sssi", $period, $_SESSION["company_id"], $_POST["news_id"], $fetch_num);
    $stmt_a->execute();
    $contents = $stmt_a->get_result();

    $stmt_b = $mysqli->prepare($query_b);
    $stmt_b->bind_param("ss", $_SESSION["company_id"], $_POST["news_id"]);
    $stmt_b->execute();
    $category_data = $stmt_b->get_result();

    if ($contents !== FALSE && $category_data !== FALSE) {
        $lines = $category_data->fetch_array(MYSQLI_NUM)[0];
        $arr = explode("\n", $lines);
        foreach($arr as $num => $line) {
            if ($num !== 0) {
                $items = explode(",", $line);
                $categoryArray[] = $items[0];
            }
        }
        while ($row = $contents->fetch_row()) {
            $dataArray[] = $row;
        }
        foreach ($categoryArray as $_category) {
            foreach ($dataArray as $_row) {
                if ($_category === $_row[0]) {
                    $orderedArray[] = $_row;
                }
            }
        }
        if (isset($orderedArray)) {
            echo json_encode($orderedArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            echo "ng1";
        }
    } else {
        echo "ng2";
    }
} catch (mysqli_sql_exception $e) {
    throw $e;
    die("ng3");
}

?>
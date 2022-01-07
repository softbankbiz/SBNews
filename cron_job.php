<?php

require_once dirname(__FILE__) . "/functions.php";
require_once dirname(__FILE__) . "/Feed.php";
require_once dirname(__FILE__) . "/WatsonNLU.php";

$mysqli = getConnection();

try {
	$query = "SELECT news_id,cid_alias,company_id,category_id,rss_id,site_names_id FROM preference";
	$result = $mysqli->query($query);
	if($result) {
		foreach ($result as $row) {
      $query_rss = 'SELECT rss_data FROM rss_list WHERE rss_id = "' . $row["rss_id"] . '"';
      $rss_data = $mysqli->query($query_rss);
			$rss_arr = explode("\n", $rss_data->fetch_array(MYSQLI_NUM)[0]);

			$query_site_names = 'SELECT site_names_data FROM site_names_list WHERE site_names_id = "' . $row["site_names_id"] . '"';
			$site_names_data = $mysqli->query($query_site_names);
			$site_names_arr = explode("\n", $site_names_data->fetch_array(MYSQLI_NUM)[0]);

			$insert_suffix = '","' . $row["company_id"] . '","' . $row["news_id"] . '"';

			/* Fetch RSS Job */
			do_fetch($mysqli, $rss_arr, $site_names_arr, $insert_suffix);


			/* Watson Judgement Job */
			// コンテンツの取得
	    $query_a = "SELECT url,title,summary from article_candidate WHERE class_name IS NULL AND company_id = ? AND news_id = ?";
	    $stmt = $mysqli->prepare($query_a);
	    $stmt->bind_param("ss", $row["company_id"], $row["news_id"]);
	    $stmt->execute();
			$contents_for_judgement = $stmt->get_result();

			// cid_alias の取得
			$cid_alias = $row["cid_alias"];

			if ($cid_alias == 'dummy_watson') {
				$counter = 0;
	    	foreach($contents_for_judgement as $content) {
		    	$watson_res = dummy_watson();
		    	if (!empty($watson_res)) {
						$res = json_decode($watson_res);
						update_watson_res($mysqli, $content["url"], $res->{"classes"}[0]->{"class_name"}, $res->{"classes"}[0]->{"confidence"}, 'dummy_watson', 'dummy_watson', $row["news_id"]);
						$counter++;
					} else {
						echo "No data!";
					}
				}
				echo "Dummy Watson Judgemant had " . $counter . " updated of " . $row["news_id"] . "／" . $row["company_id"] . ".<br>";
	    } else {
	    	$counter = 0;
	    	$result = get_configuration($mysqli, $row["company_id"]);
	    	$w_apikey = $result["w_apikey"];
				$w_url = $result["w_url"];

				$query_d = "SELECT cid FROM classifier_list WHERE company_id = ? AND cid_alias = ?";
				$stmt = $mysqli->prepare($query_d);
				$stmt->bind_param("ss", $row["company_id"], $cid_alias);
				$stmt->execute();

				$model_id = $stmt->get_result()->fetch_array(MYSQLI_NUM)[0];
				$wnlu = new WatsonNLU;

				foreach($contents_for_judgement as $content) {
					$text_to_judge = $content["title"] . $content["summary"];
					// $watson_res = $wnlc->classify_phrase($w_apikey, $w_url, $cid, $text_to_judge);
					$watson_res = $wnlu->analize_phrase($w_apikey, $w_url, $model_id, $text_to_judge);

					if (!empty($watson_res)) {
						$res = json_decode($watson_res);
						// update_watson_res($mysqli, $content["url"], $res->{"classes"}[0]->{"class_name"}, $res->{"classes"}[0]->{"confidence"}, $cid_alias, $cid, $row["news_id"]);
						update_watson_res($mysqli, $content["url"], $res->{"classifications"}[0]->{"class_name"}, $res->{"classifications"}[0]->{"confidence"}, $cid_alias, $model_id, $row["news_id"]);
						$counter++;
					} else {
						echo "No data!";
					}
				}
				echo "Watson Judgemant had " . $counter . " updated of " . $row["news_id"] . "／" . $row["company_id"] . ".<br>";
			}
    }
	} else {
		echo "NG: select FROM preference";
	}
	$mysqli->close();

} catch (mysqli_sql_exception $e) {
    throw $e;
    die();
}

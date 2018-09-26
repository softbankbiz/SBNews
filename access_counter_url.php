<?php

require_once dirname(__FILE__) . "/functions.php";

$mysqli = getConnection();

if ($_GET["company_id"] && $_GET["news_id"] && $_GET["issue"]) {
	$query = "INSERT INTO access_counter (company_id, news_id, issue) VALUES (?,?,?)";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sss", $_GET["company_id"], $_GET["news_id"], $_GET["issue"]);
	$result = $stmt->execute();
	if ($result) {
		$file = './images/counter.gif';
		header("Cache-Control: private, must-revalidate, max-age=28800");
		header('Content-Type: image/gif');
		readfile($file);
	} else {
		die();
	}
} else {
	die();
}
?>
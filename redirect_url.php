<?php

require_once dirname(__FILE__) . "/functions.php";

$mysqli = getConnection();

if (!$mysqli) {
	var_dump($mysql);
	die();
}

if ($_GET["company_id"] && $_GET["news_id"] && $_GET["url"] && $_GET["issue"] && $_GET["title"]) {
	$query = "INSERT INTO click_counter (company_id, news_id, url, issue, title) VALUES (?,?,?,?,?)";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("sssss", $_GET["company_id"], $_GET["news_id"], $_GET["url"], $_GET["issue"], $_GET["title"]);
	$result = $stmt->execute();
	if ($result) {
		header('Location: ' . $_GET["url"], true, 301);
		exit();
	} else {
		die();
	}
} else {
	die();
}

?>
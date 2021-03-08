<?php

$request = json_decode(file_get_contents('php://input'), true);

$rand_a = rand(1, 20);
$rand_b = rand(1, 20);

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

echo json_encode($response);
?>
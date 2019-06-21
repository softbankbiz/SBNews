<?php

define('W_GATEWAY',  'https://gateway-toc.watsonplatform.net/natural-language-classifier/api/v1/classifiers');

class WatsonNLC {

	private static function headers($w_username, $w_password) {
		return array(
	        "Content-type: text/plain",
	        "Authorization: Basic " . base64_encode($w_username . ':' . $w_password)
	    );
	}
	
	private static function headers_for_classify($w_username, $w_password) {
		return array(
	        "Content-type: application/json",
	        "Authorization: Basic " . base64_encode($w_username . ':' . $w_password)
	    );
	}

	private static function headers_for_create($w_username, $w_password) {
		return array(
	        "Content-type: multipart/form-data",
	        "Authorization: Basic " . base64_encode($w_username . ':' . $w_password)
	    );
	}

	private static function nlc_text($s) {
		$chars = array(
			chr(0x00),chr(0x01),chr(0x02),chr(0x03),chr(0x04),chr(0x05),chr(0x06),chr(0x07),chr(0x08),chr(0x09),
			chr(0x0a),chr(0x0b),chr(0x0c),chr(0x0d),chr(0x0e),chr(0x0f),chr(0x10),chr(0x11),chr(0x12),chr(0x13),
			chr(0x14),chr(0x15),chr(0x16),chr(0x17),chr(0x18),chr(0x19),chr(0x1a),chr(0x1b),chr(0x1c),chr(0x1d),
			chr(0x1e),chr(0x1f),chr(0x7f),chr(0x22),",","`","'"
		);
		$s = str_replace($chars, "", $s);
		return substr($s, 0, 1024);
	}

	public function create_classifier($w_username, $w_password, $training_data, $training_data_name) {
		$url = W_GATEWAY;
		$training_metadata = "{\"language\":\"ja\",\"name\":\"" . $training_data_name . "\"}";
		$data = array('training_data' => '@'.$training_data, 'training_metadata' => $training_metadata);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers_for_create($w_username, $w_password));
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$sts = curl_exec($curl);
		
		if (curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200) {
			return $sts;
			curl_close($curl);
		} else {
			return $sts;
			curl_close($curl);
		}
	}

	public function create_classifier_v2($w_username, $w_password, $training_data, $training_data_name) {
		$url = W_GATEWAY;
		$training_metadata = "{\"language\":\"ja\",\"name\":\"" . $training_data_name . "\"}";
		$data = array('training_data' => '@'.$training_data, 'training_metadata' => $training_metadata);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers_for_create($w_username, $w_password));
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$sts = curl_exec($curl);
		
		if (curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200) {
			return $sts;
			curl_close($curl);
		} else {
			return $sts;
			curl_close($curl);
		}
	}


	public function classify_phrase($w_username, $w_password, $classifier_id, $text_to_judge) {
		$url = W_GATEWAY . "/" . $classifier_id . "/classify";
	    $data_json = json_encode(array("text" => self::nlc_text($text_to_judge)));

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers_for_classify($w_username, $w_password));
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$sts = curl_exec($curl);

		if (curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200) {
			return $sts;
			curl_close($curl);
		} else {
			return null;
			curl_close($curl);
		}
	}

	public function list_classifiers($w_username, $w_password) {
		$url = W_GATEWAY;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers($w_username, $w_password));
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$sts = curl_exec($curl);

		if (curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200) {
			return $sts;
			curl_close($curl);
		} else {
			return null;
			curl_close($curl);
		}
	}

	public function info_classifier($w_username, $w_password, $classifier_id) {
		$url = W_GATEWAY . "/" . $classifier_id;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers($w_username, $w_password));
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$sts = curl_exec($curl);

		if (curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200) {
			return $sts;
			curl_close($curl);
		} else {
			return null;
			curl_close($curl);
		}
	}

	public function delete_classifier($w_username, $w_password, $classifier_id) {
		$url = W_GATEWAY . "/" . $classifier_id;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers($w_username, $w_password));
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_exec($curl);

		if (curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200) {
			return true;
			curl_close($curl);
		} else {
			return null;
			curl_close($curl);
		}
	}
}
?>

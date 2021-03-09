<?php

class WatsonNLC {

	private static function headers($w_apikey) {
		return array(
	        "Content-type: text/plain",
	        "Authorization: Basic " . base64_encode('apikey:' . $w_apikey)
	    );
	}

	private static function headers_for_classify($w_apikey) {
		return array(
	        "Content-type: application/json",
	        "Authorization: Basic " . base64_encode('apikey:' . $w_apikey)
	    );
	}

	private static function headers_for_create($w_apikey) {
		return array(
	        "Content-type: multipart/form-data",
	        "Authorization: Basic " . base64_encode('apikey:' . $w_apikey)
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

	public function create_classifier($w_apikey, $w_url, $training_data, $training_data_name) {
		$W_GATEWAY = $w_url . '/v1/classifiers';
		$training_metadata = "{\"language\":\"ja\",\"name\":\"" . $training_data_name . "\"}";
		$data = array('training_data' => '@'.$training_data, 'training_metadata' => $training_metadata);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $W_GATEWAY);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers_for_create($w_apikey));
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

	public function classify_phrase($w_apikey, $w_url, $classifier_id, $text_to_judge) {
		$W_GATEWAY =  $w_url . '/v1/classifiers/' . $classifier_id . "/classify";
	  $data_json = json_encode(array("text" => self::nlc_text($text_to_judge)));

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $W_GATEWAY);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers_for_classify($w_apikey));
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

	public function list_classifiers($w_apikey, $w_url) {
		$W_GATEWAY = $w_url . '/v1/classifiers';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $W_GATEWAY);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers($w_apikey));
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

	public function info_classifier($w_apikey, $w_url, $classifier_id) {
		$W_GATEWAY =  $w_url . '/v1/classifiers/' . $classifier_id;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $W_GATEWAY);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers($w_apikey));
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

	public function delete_classifier($w_apikey, $w_url, $classifier_id) {
		$W_GATEWAY =  $w_url . '/v1/classifiers/' . $classifier_id;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $W_GATEWAY);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers($w_apikey));
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

<?php

class WatsonNLU {

	private static function headers($w_apikey) {
		return array(
	        "Content-type: text/plain",
	        "Authorization: Basic " . base64_encode('apikey:' . $w_apikey)
	    );
	}

	// nlc_text -> nlu_text, max 1024 -> max 2000,
	private static function nlu_text($s) {
		$chars = array(
			chr(0x00),chr(0x01),chr(0x02),chr(0x03),chr(0x04),chr(0x05),chr(0x06),chr(0x07),chr(0x08),chr(0x09),
			chr(0x0a),chr(0x0b),chr(0x0c),chr(0x0d),chr(0x0e),chr(0x0f),chr(0x10),chr(0x11),chr(0x12),chr(0x13),
			chr(0x14),chr(0x15),chr(0x16),chr(0x17),chr(0x18),chr(0x19),chr(0x1a),chr(0x1b),chr(0x1c),chr(0x1d),
			chr(0x1e),chr(0x1f),chr(0x7f),chr(0x22),",","`","'"
		);
		$s = str_replace($chars, "", $s);
		return substr($s, 0, 2000);
	}

	public function create_model($w_apikey, $w_url, $training_data, $training_data_name) {
		$W_GATEWAY = $w_url . '/v1/models/classifications?version=2021-08-01';
		$command = 'curl -X POST -u "apikey:' . $w_apikey .
		           '" -H "Content-Type: multipart/form-data" -F "training_data=' . $training_data .
							 ';type=text/csv" -F "language=ja" -F "name=' . $training_data_name .
							 '" "' . $W_GATEWAY . '"';
		$res = exec($command, $output, $retval);
		if ($res && $retval == 0) {
			return $output;
		} else {
			return false;
		}
	}

	public function analize_phrase($w_apikey, $w_url, $model_id, $text_to_judge) {
		$W_GATEWAY =  $w_url . '/v1/analyze?version=2021-08-01';
		$command = 'curl -u "apikey:' . $w_apikey . '" "' . $W_GATEWAY . '&text=' . urlencode(self::nlu_text($text_to_judge)) .
		           '&language=ja&features=classifications&classifications.model=' . $model_id . '"';
		$res = exec($command, $output, $retval);
		if ($res && $retval == 0) {
			// WatsonからのレスポンスがStringの配列と認識されるので、全要素を連結したStringに組み立てる、何で？
			$buffer = "";
			foreach($output as $s) {
				$buffer .= $s;
			}
			return $buffer;
		} else {
			return false;
		}
	}

	public function list_model($w_apikey, $w_url) {
		$W_GATEWAY = $w_url . '/v1/models/classifications?version=2021-08-01';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $W_GATEWAY);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers($w_apikey));
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$sts = curl_exec($curl);

		if (curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200) {
			curl_close($curl);
			return $sts;
		} else {
			curl_close($curl);
			return null;
		}
	}

	public function info_model($w_apikey, $w_url, $model_id) {
		$W_GATEWAY =  $w_url . '/v1/models/classifications/' . $model_id . '?version=2021-08-01';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $W_GATEWAY);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers($w_apikey));
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$sts = curl_exec($curl);

		if (curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200) {
			curl_close($curl);
			return $sts;
		} else {
			curl_close($curl);
			return null;
		}
	}

	public function delete_model($w_apikey, $w_url, $model_id) {
		$W_GATEWAY =  $w_url . '/v1/models/classifications/' . $model_id . '?version=2021-08-01';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $W_GATEWAY);
		curl_setopt($curl, CURLOPT_HTTPHEADER, self::headers($w_apikey));
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_exec($curl);

		if (curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200) {
			curl_close($curl);
			return true;
		} else {
			curl_close($curl);
			return null;
		}
	}
}
?>

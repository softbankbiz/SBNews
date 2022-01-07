<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";
require_once dirname(__FILE__) . "/WatsonNLC.php";

session_start();

if (! isset($_SESSION['auth'])) {
  $_SESSION['auth'] = false;
}

$mysqli = getConnection();

if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	exit();
} else if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
	echo '<script>alert("あなたには編集権限がありません。"); location.href = "/' . BASE . '/";</script>';
	return;
} else {
	print_header("Watson NLU モデルの追加／削除", $_SESSION);
	print_mennu($_GET['page']);
?>
			<div class="main_area">
				<h3>Watson NLU モデルの追加／削除</h3>
				<h4>＜モデルを新規に追加＞</h4>
				<p class="ope_description">トレーニングデータ（CSVファイル、UTF-8）をアップロードすることでモデルを作成します。</p>
				<table class="conf_table">
					<tr>
						<td>
							<input type="file" name="training_data" id="training_data">
						</td>
						<td>
							<button type="button" id="create_cid" disabled>追加する</button>
						</td>
						<td>
							<img src="images/bx_loader.gif" class="bx_loader" style="display: none;" id="bx_loader_create">
						</td>
					</tr>
				</table>

			    <br><br>

				<h4>＜既存のモデルを削除＞</h4>
				<table class="conf_table">
					<p class="ope_description">削除したいモデルの右側の「削除」ボタンをクリックします。</p>
					<?php
					if (!two_step_auth($mysqli, $_SESSION["company_id"], $_SESSION["user_id"])) {
				        return;
				    }
					$result = get_classifier_list($mysqli, $_SESSION["company_id"]);
					if ($result->num_rows > 0) {
						for ($i = 0; $i < $result->num_rows; $i++) {
							echo '<tr>';
							echo '<td>' . ($i+1) . '</td>';
							$row = $result->fetch_array(MYSQLI_ASSOC);
							if (isset($row["cid_alias"]) && isset($row["cid"])) {
								echo '<td>' . $row["cid_alias"] . '</td>';
							}
							echo '<td><button onclick=\'delete_cid("' . $row["cid"] . '", "' . $row["cid_alias"] . '")\'>削除</button></td>';
							echo '</tr>';
						}
					} else {
						echo '<tr><td colspan="3">既存のモデルはありません。</td></tr>';
					}
					?>
				</table>
				<br><br>
				<div>
					<a href="/<?php echo BASE; ?>/?page=watson_conf"><button class="button_back">戻る</button></a>
				</div>
			</div>

			<script src="/<?php echo BASE; ?>/js/vendor/xlsx.full.min.js"></script>
			<script>
			var watson_management = "watson_management.php";

			function delete_cid(_cid, _cid_alias) {
				if(confirm("本当に削除しますか？　この操作は取り消しできません。") == false) {
				    return;
				}
				$.post(watson_management,
			    {
			        cmd:       "delete",
			        cid:       _cid,
			        cid_alias: _cid_alias
			    },
			    function(data, status){
			        if(status == 'success' && data.trim() == 'deleted') {
		        		alert("Watsonモデルを削除しました。このモデルは「dummy_watson」に差し替えられました。");
		        		location.href = "/<?php echo BASE; ?>/?page=watson_conf";
			        } else {
			        	alert("Watsonモデルの削除に失敗しました。");
			        	//alert(data.trim());
			        }
				});
			}

			var _training_data_name = "";
			var _training_data = "";

			document.getElementById('create_cid').addEventListener('click', function (evt) {
				$("#bx_loader_create").css("display","block");
				var escaped_training_data = han2zen(_training_data); // 半角カナを全角カナに変換するJavaScriptツール
				$.post(watson_management,
			    {
			        cmd:                "create",
			        training_data_name: _training_data_name,
			        training_data:      escaped_training_data
			    },
			    function(data, status){
			    	//alert(data);
			        if(status == 'success' && data.trim() == 'ok') {
			        	alert("Watsonモデルを作成しました。");
			        	//alert(data.trim());
			        	$("#bx_loader_create").css("display","none");
			        	location.href = "/<?php echo BASE; ?>/?page=watson_conf";
			        } else {
			        	alert("Watsonモデルの作成に失敗しました。\n" + data);
			        	//alert(data.trim());
			        	$("#bx_loader_create").css("display","none");
			        }
				});
			});

			document.getElementById('training_data').addEventListener('change', function (evt) {
				var file = evt.target.files[0];
				var reader = new FileReader();
				reader.readAsText( file );
				reader.addEventListener( 'load', function() {
			        var data = reader.result.trim();
			        var buf = "";
			        var arr = data.split("\n");
			        for (var i=0; i<arr.length; i++) {
			        	var line = arr[i].split(',');
			        	if(line[0].length < 1024) {
			        		buf += line[0] + ',' + line[1] + "\n";
			        	} else {
			        		buf += line[0].slice(0,1023) + ',' + line[1] + "\n";
			        	}
			        }
			        _training_data = buf.trim();
			    });
				_training_data_name = file.name.split('.')[0];
				$("#cid_alias").text(_training_data_name);
				$("#create_cid").prop('disabled', false);
			});

			function nlc_text(s) {
			  s.replace(/(0x00)/g, "")
			   .replace(/(0x01)/g, "")
			   .replace(/(0x02)/g, "")
			   .replace(/(0x03)/g, "")
			   .replace(/(0x04)/g, "")
			   .replace(/(0x05)/g, "")
			   .replace(/(0x06)/g, "")
			   .replace(/(0x07)/g, "")
			   .replace(/(0x08)/g, "")
			   .replace(/(0x09)/g, "")
			   .replace(/(0x0a)/g, "")
			   .replace(/(0x0b)/g, "")
			   .replace(/(0x0c)/g, "")
			   .replace(/(0x0d)/g, "")
			   .replace(/(0x0e)/g, "")
			   .replace(/(0x0f)/g, "")
			   .replace(/(0x10)/g, "")
			   .replace(/(0x11)/g, "")
			   .replace(/(0x12)/g, "")
			   .replace(/(0x13)/g, "")
			   .replace(/(0x14)/g, "")
			   .replace(/(0x15)/g, "")
			   .replace(/(0x16)/g, "")
			   .replace(/(0x17)/g, "")
			   .replace(/(0x18)/g, "")
			   .replace(/(0x19)/g, "")
			   .replace(/(0x1a)/g, "")
			   .replace(/(0x1b)/g, "")
			   .replace(/(0x1c)/g, "")
			   .replace(/(0x1d)/g, "")
			   .replace(/(0x1e)/g, "")
			   .replace(/(0x1f)/g, "")
			   .replace(/(0x7f)/g, "")
			   .replace(/(0x22)/g, "")
			   .replace(/","/g, "")
			   .replace(/"`"/g, "")
			   .replace(/"'"/g, "");
			  return s.slice(0,1023);
			}
			</script>

<?php
	print_javascript("others");
	print_footer();
}

exit();
?>

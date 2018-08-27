<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";

session_start();

$mysqli = getConnection();

if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	exit();
} else if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
	echo '<script>alert("あなたには編集権限がありません。"); location.href = "/' . BASE . '/";</script>';
	return;
} else if ($_GET['rss_id'] && two_step_auth($mysqli, $_SESSION['company_id'], $_SESSION['user_id'])) {

	print_header("RSSリスト設定画面", $_SESSION);
	print_mennu($_GET['page']);
?>
			<div class="main_area">
				<h3>RSSリスト設定画面</h3>
				<?php
				echo '<table class="conf_table">';
				echo '<tr><th>RSSリスト ID</th><td>' . $_GET['rss_id'] . '</td></tr>';
				echo '<tr><th>RSSリストを差し替え</th><td><input type="file" name="' . $_GET['rss_id'] . '" id="update_rss_data"></td></tr>';
				echo '<tr><th>このRSSリストを削除</th><td><button id="delete_rss_data">削除する</button></td></tr>';
				echo '</table>';
				echo '<input type="hidden" id="_company_id" value="' . $_SESSION['company_id'] . '">';
				echo '<input type="hidden" id="_user_id" value="' . $_SESSION["user_id"] . '">';
				echo '<input type="hidden" id="_rss_id" value="' . $_GET['rss_id'] . '">';
				?>
			</div>
			<br><br>
			<div>
				<a href="/<?php echo BASE; ?>/?page=crawler_conf"><button>戻る</button></a>
			</div>
			<script src="js/vendor/xlsx.full.min.js"></script>
			<script>
var rss_manage = "rss_manage.php";
document.getElementById('delete_rss_data').addEventListener('click', function (evt) {
	$.post(rss_manage,
    {
        rss_id:     $("#_rss_id").val(),
        cmd:        "delete",
    },
    function(data, status){
        if(status == 'success') {
        	if (data == 1) {
        		alert("RSSリストを削除しました。");
        		location.href = "/<?php echo BASE; ?>/?page=crawler_conf";
        	} else {
        		alert(data + "RSSリストの削除に失敗しました。");
        		location.href = "/<?php echo BASE; ?>/?page=crawler_conf";
        	}
        } else {
        	alert("error.");
        }
	});
});

document.getElementById('update_rss_data').addEventListener('change', function (evt) {
	var file = evt.target.files[0];
	var er = new ExcelJs.Reader(file, function (e, xlsx) {
	    var _rss_data =  xlsx.toCsv();
	    var _rss_data_name = file.name.split('.')[0];
	    $.post(rss_manage,
	    {
	        rss_id:     _rss_data_name, // ファイル名を使うこと
	        cmd:        "update",
	        rss_data:   _rss_data
	    },
	    function(data, status){
	        if(status == 'success') {
	        	if (data == 1) {
	        		alert("RSSリストを差し替えました。");
        			location.href = "/<?php echo BASE; ?>/?page=crawler_conf";
        		} else {
        		alert(data + "RSSリストの差し替えに失敗しました。");
        		location.href = "/<?php echo BASE; ?>/?page=crawler_conf";
        	}
	        } else {
	        	alert("error.");
	        }
		});
	}, false);
});

(function (window, document) {
  window.ExcelJs = {};
  ExcelJs.File = function (_file, _workbook) {
    var file = _file;
    var workbook = _workbook;
    return {
      toCsv() {
        var result = [];
        workbook.SheetNames.forEach(function(sheetName) {
          var csv = XLSX.utils.sheet_to_csv(workbook.Sheets[sheetName]);
          if(csv.length > 0){
            result.push(csv);
          }
        });
        return result.join("\n");
      }
    };
  };

  ExcelJs.Reader = function (_file, onload) {
    var file = _file;
    var reader = new FileReader();
    reader.onload = function(e) {
      var data = e.target.result;
      var arr = handleCodePoints(new Uint8Array(data));
      if (typeof onload == 'function') {
        onload(e, new ExcelJs.File(file, XLSX.read(btoa(arr), {type: 'base64'})));
      }
    };
    reader.readAsArrayBuffer(file);
  };
})(window, window.document);

function handleCodePoints(array) {
  var CHUNK_SIZE = 0x8000;
  var index = 0;
  var length = array.length;
  var result = '';
  var slice;
  while (index < length) {
    slice = array.slice(index, Math.min(index + CHUNK_SIZE, length));
    result += String.fromCharCode.apply(null, slice);
    index += CHUNK_SIZE;
  }
  return result;
}
		</script>

<?php
		print_javascript("others");
		print_footer();
} else {
	echo 'パラメータが不正です。';
}
exit();
?>
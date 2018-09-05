<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";

session_start();

$mysqli = getConnection();

if ($_SESSION['auth'] !== true) {
	echo '閲覧権限が不足しています。';
	return;
} else if ($_SESSION['role'] == 'editor' || is_null($_SESSION['role'])) {
	echo '<script>alert("あなたには編集権限がありません。"); location.href = "/' . BASE . '/";</script>';
	return;
} else {
	print_header("サイト名リスト追加画面", $_SESSION);
	print_mennu($_GET['page']);
?>
			<div class="main_area">
				<h3>サイト名リスト追加画面</h3>
				<p class="ope_description">
					サイト名リストデータはExcelファイルのままアップロードできます。
					ファイルを選択すると、即座にアップロードが始まります。
					Excelファイル名がサイト名リスト IDになります。
				</p>
				<table class="conf_table">
					<tr>
						<td><input type="file" id="add_site_names_data"></td>
					</tr>
				</table>
			</div>
			<br><br>
			<div>
				<a href="/<?php echo BASE; ?>/?page=crawler_conf"><button>戻る</button></a>
			</div>
			<script src="js/vendor/xlsx.full.min.js"></script>
			<script>
var site_names_manage = "site_names_manage.php";
document.getElementById('add_site_names_data').addEventListener('change', function (evt) {
	var file = evt.target.files[0];
	var er = new ExcelJs.Reader(file, function (e, xlsx) {
	  	var _site_names_data_name = file.name.split('.')[0];
	    var _site_names_data =  xlsx.toCsv();
	    
	    $.post(site_names_manage,
	    {
	        site_names_id:     _site_names_data_name,
	        site_names_data:   _site_names_data,
	        cmd:               "insert"
	    },
	    function(data, status){
	        if(status == 'success') {
	        	if (data == 1) {
	        		alert("サイト名リストを新規追加しました。");
	        		location.href = "/<?php echo BASE; ?>/?page=crawler_conf";
	        	} else {
	        		alert(data + "サイト名リストの新規追加に失敗しました。");
	        		location.href = "/<?php echo BASE; ?>/site_names_add.php?page=crawler_conf";
	        	}
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
}

exit();
?>
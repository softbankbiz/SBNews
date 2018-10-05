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
} else if ($_GET['site_names_id'] && two_step_auth($mysqli, $_SESSION['company_id'], $_SESSION['user_id'])) {

  print_header("サイト名リスト設定画面", $_SESSION);
  print_mennu($_GET['page']);
?>
      <div class="main_area">
        <h3>サイト名リスト設定画面</h3>
        <p class="ope_description">
          サイト名リストを差し替える場合は、同一のファイル名である必要があります。
        </p>
        <?php
        echo '<table class="conf_table">';
        echo '<tr><th>サイト名リスト ID</th><td>' . $_GET['site_names_id'] . '</td></tr>';
        echo '<tr><th>サイト名リストを差し替え</th><td><input type="file" name="' . $_GET['site_names_id'] . '" id="update_site_names_data"></td></tr>';
        echo '<tr><th>このサイト名リストを削除</th><td><button id="delete_site_names_data">削除する</button></td></tr>';
        echo '</table>';
        echo '<input type="hidden" id="_company_id" value="' . $_SESSION['company_id'] . '">';
        echo '<input type="hidden" id="_user_id" value="' . $_SESSION["user_id"] . '">';
        echo '<input type="hidden" id="_site_names_id" value="' . $_GET['site_names_id'] . '">';
        ?>
      </div>
      <br><br>
      <div>
        <a href="/<?php echo BASE; ?>/?page=crawler_conf"><button>戻る</button></a>
      </div>
      <script src="js/vendor/xlsx.full.min.js"></script>
      <script>
var site_names_manage = "site_names_manage.php";
document.getElementById('delete_site_names_data').addEventListener('click', function (evt) {
  $.post(site_names_manage,
    {
        site_names_id: $("#_site_names_id").val(),
        cmd:           "delete",
    },
    function(data, status){
        if(status == 'success') {
          if (data == 1) {
            alert("サイト名リストを削除しました。");
            location.href = "/<?php echo BASE; ?>/?page=crawler_conf";
          } else {
            alert(data + "サイト名リストの削除に失敗しました。");
            location.href = "/<?php echo BASE; ?>/?page=crawler_conf";
          }
        } else {
          alert("error.");
        }
  });
});

document.getElementById('update_site_names_data').addEventListener('change', function (evt) {
  var file = evt.target.files[0];
  var er = new ExcelJs.Reader(file, function (e, xlsx) {
      var _site_names_data_name = file.name.split('.')[0];
      var _site_names_data =  xlsx.toCsv();
      $.post(site_names_manage,
      {
          site_names_id:   _site_names_data_name, // ファイル名を使うこと
          cmd:             "update",
          site_names_data: _site_names_data
      },
      function(data, status){
          if(status == 'success') {
            if (data == 1) {
              alert("サイト名リストを差し替えました。");
              location.href = "/<?php echo BASE; ?>/?page=crawler_conf";
            } else {
              alert(data + "サイト名リストの差し替えに失敗しました。");
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
      toCsv: function() {
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
      var arr = handleCodePoints2(new Uint8Array(data));
      if (typeof onload == 'function') {
        onload(e, new ExcelJs.File(file, XLSX.read(btoa(arr), {type: 'base64'})));
      }
    };
    reader.readAsArrayBuffer(file);
  };
})(window, window.document);

function handleCodePoints2(byteArray) {
  var binStr = '';
  for (var p = 0; p < byteArray.length; p++) {
    binStr += String.fromCharCode(byteArray[p]);
  }
  return binStr;
}
/*
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
}*/
      </script>

<?php
    print_javascript("others");
    print_footer();
} else {
  echo 'パラメータが不正です。';
}
exit();
?>
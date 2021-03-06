<?php

require_once dirname(__FILE__) . "/functions.php";

session_start();

# 認証済みか?
if (! isset($_SESSION['auth'])) {
  die("認証されていません。");
} else if ( isset($_SESSION['company_id']) && isset($_SESSION['user_id']) && isset($_GET['news_id']) ) {
	print_header("SBNews ページ制作", $_SESSION);
	print_javascript("newsmaker");
	print_mennu($_GET['page']);
?>
		<div class="main_area">
			<div class="newsmaker_wrapper">
				<div>
					<input type="hidden" name="_company_id" id="_company_id" value="<?php echo $_SESSION['company_id'] ?>">
					<input type="hidden" name="_news_id" id="_news_id" value="<?php echo $_GET['news_id'] ?>">
				</div>

				<div class="operation">
		        	<h2>Step_1　メール件名を編集</h2>
		            <ul class="memo_box">
		              <li>
		                <div>
							<input type="text" name="subject" id="subject" value="">
							メール件名編集欄（必須）
						</div>
		              </li>
		            </ul>
		        </div>

				<div class="operation">
					<h2>Step_2　コンテンツ候補を編集</h2>
					<div class="readbutton_area">
						<select class="period_day" id="period_day_pre"></select> の
		            	<select class="period_hour" id="period_hour_pre"></select> 時から現在までに更新されたコンテンツ候補を手動で編集する <a id="targetLink" style="display: none;">.</a>　＞　
						<button class="read_data_btn" id="default_ex" onclick="read_data_export()">CSVで書き出す</button>
						<button class="read_data_btn" id="default_im" onclick="read_data_import()">Excelから書き戻す</button>
						<input type="file" id="import_file" style="display:none;">
					</div>
					<div class="circle_icon_area" id="circle_icon_area_2">
		                <img src="images/circle_icon.gif" class="circle_icon">
		            </div>
				</div>

				<div class="operation">
					<h2>Step_3　コンテンツ候補を取得</h2>
		            <div class="readbutton_area">
						<div class="readbutton_area_upper">
							<select class="period_day" id="period_day"></select> の
							<select class="period_hour" id="period_hour"></select> 時から現在までに更新されたコンテンツを上位
							<select class="fetch_num" id="fetch_num"></select> 件 
						</div>
						<div  class="readbutton_area_lower">
							<button class="read_data_btn" id="default_SS" onclick="read_data()">取り込む</button>
						</div>
		            </div>
		            <div class="circle_icon_area" id="circle_icon_area">
		                <img src="images/circle_icon.gif" class="circle_icon">
		            </div>
				</div>

				<div class="operation">
					<h2>Step_4　ニュース本文を作成</h2>
		            <ul class="memo_box">
		              <li>
		                <div>
							<input type="text" name="memo" id="memo" value="">
							連絡メモ欄（オプション）
						</div>
		              </li>
		            </ul>
					<ul class="add_button">
						<li>
							<button onclick="create_category()">カテゴリーを新規作成</button>
							<button onclick="add_category()">カテゴリーを追加</button>
							<button onclick="add_cassette()">記事を追加</button>
						</li>
					</ul>
					<ul id="sortable">
						<li id="tmpl_category" class="invisible" style="background-color: #FFC;">
							<div style="background-color: #FFC;">
								<select id="category_list">
									<option>-- カテゴリを選ぶ --</option>
								</select>
								<span class="delete_button">
									<button onclick="remove_cassette(this)">削除</button>
								</span>
							</div>
						</li>
						<li id="tmpl_cassette" class="invisible">
							<div>
								<div>
									<input type="checkbox" name="checked">
									注目記事！
								</div>
								<div>
									<input type="text" name="article_url" value="">
									記事URLを入力
								</div>
								<div>
									<input type="text" name="article_title" value="">
									記事タイトルを入力
								</div>
								<div>
									<input type="text" name="article_media" value="">
									メディアを入力
								</div>
		                        <div>
									<input type="checkbox" name="req_login">
									要ログイン
								</div>
								<div>
									<input type="checkbox" name="add_fyi_0" onclick="add_fyi(this)">
									参考リンク追加
									<div class="add_fyi">
										<input type="text" name="fyi_url_0" value="">
										参考リンク_1のURLを入力
									</div>
									<div class="add_fyi">
										<input type="text" name="fyi_title_0" value="">
										参考リンク_1のタイトルを入力
									</div>
								</div>
								<div class="add_fyi">
									<input type="checkbox" name="add_fyi_1" onclick="add_fyi(this)">
									参考リンク追加
									<div class="add_fyi">
										<input type="text" name="fyi_url_1" value="">
										参考リンク_2のURLを入力
									</div>
									<div class="add_fyi">
										<input type="text" name="fyi_title_1" value="">
										参考リンク_2のタイトルを入力
									</div>
								</div>
								<div class="add_fyi">
									<input type="checkbox" name="add_fyi_2" onclick="add_fyi(this)">
									参考リンク追加
									<div class="add_fyi">
										<input type="text" name="fyi_url_2" value="">
										参考リンク_3のURLを入力
									</div>
									<div class="add_fyi">
										<input type="text" name="fyi_title_2" value="">
										参考リンク_3のタイトルを入力
									</div>
								</div>
								<span class="delete_button">
									<button onclick="remove_cassette(this)">削除</button>
								</span>
							</div>
						</li>
					</ul>
					<ul class="add_button">
						<li>
							<button onclick="create_category()">カテゴリーを新規作成</button>
		                    <button onclick="add_category()">カテゴリーを追加</button>
		                    <button onclick="add_cassette()">記事を追加</button>
		                </li>
					</ul>
				</div>
		        
				<div class="operation">
					<h2>Step_5　ランキングを作成</h2>
					<div id="Step_2" class="edit_area">
						<div class="operation">▼発行号を指定する▼</div>
						<select id="target_issue" style="margin-right:20px;">
						</select>
						<button onclick="gen_ranking()">この発行号でランキングを生成</button>
						<div id="ranking_area"></div>
					</div>
				</div>
		        
				<div class="operation">
					<h2>Step_6　プレビューを確認</h2>
					<div id="Step_3" class="edit_area">
						<div>
							<button onclick="preview()">プレビュー</button>
							<select id="pv_style" style="margin-left:20px;">
								<option value="kr">並び順：記事→ランキング</option>
								<option value="rk">並び順：ランキング→記事</option>
							</select>
						</div>
						<div class="small_cap">件名</div>
						<div id="preview_subject"></div>
						<div class="small_cap">本文</div>
						<div id="preview_area"></div>
					</div>
				</div>

				<div class="operation">
					<h2>Step_7　コンテンツを利用</h2>
					<div id="Step_X" class="edit_area">
						<p>件名および本文をそれぞれコピーして、お使いのメーラーなどにペーストしてご利用ください。<br>お疲れさまでした。</p>
					</div>
				</div>

				<div class="operation">
					<a href="/<?php echo BASE; ?>/?page=news_make"><button class="button_back">戻る</button></a>
				</div>
	        </div>
	    </div>
	    <script src="js/vendor/xlsx.full.min.js"></script>
	    <script>
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
			</script>
<?php
	print_footer();
} else {
	die ("ページを表示できません。");
}
?>

/**********************************************
 メルマガのトップに表示させるタイトル画像のURL。
 **********************************************/
var title_image_url = "";

/**********************************************
 カテゴリ用のアイコン画像が複数格納されているディレクトリのURL。
 画像ファイル名「robot.png」などは、MySQLの「category_list」で
 指定。
 **********************************************/
var category_icon_url = "";

/**********************************************
 メルマガの最下部に表示させるタイトル画像のURL。
 **********************************************/
var bottom_image_url = "";

/**********************************************
 注目記事に付けるアイコン画像のURL。
 **********************************************/
var check_icon_url = "images/common_icon/checkmark.png";

/**********************************************
 要ログイン記事に付けるアイコン画像のURL。
 **********************************************/
var req_login_url = "images/common_icon/req_login.png";

/**********************************************
 クリックカウント用のURL。
***********************************************/
var redirect_url = 'redirect_url.php';

/**********************************************
 初期化データ取得のURL。
***********************************************/
var get_init_data_url = "get_init_data_url.php";

/**********************************************
 ランキングデータを生成するための日付リスト、および
 ランキングデータのリストを取得するためのURL。
 日付かリストかは、パラメータで切り分けている。
 AJAXでURLを叩いてJSONで受け取る。
***********************************************/
var get_ranking_issue_url = "get_ranking_issue_url.php";

/**********************************************
 開封カウント用のURL。画像ファイルとしてアクセス。
 サーバー側では、このURLが来たら、開封カウンターを+1して
 1ピクセル×1ピクセルの透明な画像を返す。
***********************************************/
var access_counter_url = "access_counter_url.php";


/**********************************************
 コンテンツ取得のURL。
***********************************************/
var get_contents_data_url = "get_contents_data_url.php";


/**********************************************
 コンテンツを取り込む前に編集するためのURL。
***********************************************/
var get_set_contents = "get_set_contents.php";


/**********************************************
 グローバル変数。
***********************************************/
//var default_title = "";
var issue = get_issue();
var category_icon = {};
var signature = '';
var period_day_list = [["-1 day","1日前"], ["-2 day","2日前"], ["-3 day","3日前"], ["-4 day","4日前"], ["-5 day","5日前"], ["-6 day","6日前"], ["-7 day","7日前"]];
var period_hour_list = ["00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23"];
var fetch_num_list = ["10","20","30","40","50"];
var ranking_length = 0;


/**********************************************
 初期化処理。
***********************************************/
$( function() {
    // set sortable
    $( "#sortable" ).sortable({
        disabled: false,
        update: function( event, ui ) {}
    });

    // 画像のURL
    var company_id = $("#_company_id").val();
    var news_id = $("#_news_id").val();
    title_image_url   = "images/" + company_id + "/" + news_id + "/title_image.png";
    category_icon_url = "images/" + company_id + "/" + news_id + "/";
    bottom_image_url  = "images/" + company_id + "/" + news_id + "/bottom_image.png";

    // オリジナルのトップ画像がなければ、デフォルトを表示させる
    var title_image = new Image();
    title_image.src = title_image_url;
    title_image.onerror = function() {
        title_image_url = "images/common_icon/title_image.png";
    }

    // オリジナルのボトム画像がなければ、仮リンク
    var bottom_image = new Image();
    bottom_image.src = bottom_image_url;
    bottom_image.onerror = function() {
        bottom_image_url = "#";
    }


    /**********************************************
     AJAXを使ってサーバーから設定情報を取得。
    ***********************************************/
    $.post(get_init_data_url,
    {
        news_id:    $("#_news_id").val()
    },
    function(data, status){
        if(status == 'success') {
            //alert(data);
            var args = JSON.parse(data);

            // デフォルトのメール件名
            $("#subject").val(args["default_title"]);

            // 署名をセット
            signature = '<div>' + convert_br(escape_html(args["signature"])) + '</div>';

            // カテゴリのリストをセット
            for(var i=0; i<args["category_name"].length; i++) {
                var node = document.createElement("OPTION");
                var textnode = document.createTextNode(args["category_name"][i]);
                node.appendChild(textnode);
                document.getElementById("category_list").appendChild(node);
            }

            // カテゴリアイコンをセット
            category_icon = args["category_icon"];
            
            // period_day
            var buf_p = "";
            for(var j=0; j<period_day_list.length; j++) {
                if (period_day_list[j][0] === args["period_day"]) {
                    buf_p += '<option value="' + period_day_list[j][0] + '" selected>' + period_day_list[j][1] + '</option>';
                } else {
                    buf_p += '<option value="' + period_day_list[j][0] + '">' + period_day_list[j][1] + '</option>';
                }
             }
             $(".period_day").html(buf_p);

            // period_hour
            var buf_h = "";
            for(var k=0; k<period_hour_list.length; k++) {
                if (period_hour_list[k] === args["period_hour"]) {
                    buf_h += "<option selected>" + period_hour_list[k] + "</option>";
                } else {
                    buf_h += "<option>" + period_hour_list[k] + "</option>";
                }
             }
             $(".period_hour").html(buf_h);

            // fetch_num
            var buf_n = "";
            for(var l=0; l<fetch_num_list.length; l++) {
                if (fetch_num_list[l] === args["fetch_num"]) {
                    buf_n += "<option selected>" + fetch_num_list[l] + "</option>";
                } else {
                    buf_n += "<option>" + fetch_num_list[l] + "</option>";
                }
             }
             $(".fetch_num").html(buf_n);
        } else {
            alert("ng");
        }
    });

    /**********************************************
     AJAXを使ってサーバーからランキングリストを生成するための
     日付リストをJSONで取得し、ドロップダウンリストを生成。
    ***********************************************/
    $.post(get_ranking_issue_url,
    {
        ranking_issue_list: 'exec',
        news_id:    $("#_news_id").val()
    },
    function(data, status){
        if(status == 'success') {
            ///////////
            //alert(data);
            //alert(data.length);
            ///////////
            var node = null;
            var textnode = null;
            if(data.trim() === 'null') {
                node = document.createElement("OPTION");
                textnode = document.createTextNode('ランキングはありません');
                node.appendChild(textnode);
                node.setAttribute("value", "no-data");
                document.getElementById("target_issue").appendChild(node);
            } else {
                var issue_list = JSON.parse(data);
                for(var i=0; i<issue_list.length; i++) {
                    node = document.createElement("OPTION");
                    textnode = document.createTextNode(issue_list[i]);
                    node.appendChild(textnode);
                    node.setAttribute("value", issue_list[i]);
                    document.getElementById("target_issue").appendChild(node);
                }
            }
        } else {
            alert("Error at get_ranking_issue_url.");
        }
    });
});


function get_issue() {
    var date = new Date();
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();
    if ( m < 10 ) { m = '0' + m; }
    if ( d < 10 ) { d = '0' + d; }
    return y + '-' + m + '-' + d;
}

/**********************************************
 AJAXを使ってサーバーからデータベースからコンテンツを取得。
***********************************************/
function read_data() {
    $("#circle_icon_area").css("display","block");

    $.post(get_contents_data_url,
    {
        period_day:  $("#period_day").val(),
        period_hour: $("#period_hour").val(),
        fetch_num:   $("#fetch_num").val(),
        news_id:     $("#_news_id").val()
    },
    function(data, status){
        if(status == 'success') {
            /////////
            //alert(data.trim());
            /////////
            if(data.trim().length === 0) {
                alert("指定した期間にはコンテンツがありません。");
                $("#circle_icon_area").css("display","none");
            } else {
                var contents = JSON.parse(data);
                $("#circle_icon_area").css("display","none");
                resetElements();
                setElements(contents);
            }
        }
    });
}


/**********************************************
 AJAXを使ってサーバーからコンテンツを取得して、
 いったんCSVに書き出して、Excelで編集
***********************************************/
function read_data_export() {
    //alert('read_data_export()');
    $("#circle_icon_area").css("display","block");

    $.post(get_set_contents,
    {
        period_day:  $("#period_day_pre").val(),
        period_hour: $("#period_hour_pre").val(),
        news_id:     $("#_news_id").val()
    },
    function(data, status){
        if(status == 'success') {
            /////////
            //alert(data.trim());
            /////////
            if(data.trim().length === 0) {
                alert("指定した期間にはコンテンツがありません。");
                $("#circle_icon_area").css("display","none");
            } else {
                $("#circle_icon_area").css("display","none");
                var filename = get_issue() + "_article_candidate.csv";
                var bom = new Uint8Array([0xEF, 0xBB, 0xBF]);
                var blob = new Blob([ bom, data.trim() ], { "type" : "text/csv" });
                window.URL = window.URL || window.webkitURL;
                $('#targetLink').attr("href", window.URL.createObjectURL(blob));
                $('#targetLink').attr("download", filename);
                $('#targetLink').click(function(){
                    if(window.navigator.msSaveBlob){
                        //console.log('IE11?');
                        window.navigator.msSaveBlob(blob, filename);
                    }
                });
                $('#targetLink')[0].click();
            }
        }
    });
}


/**********************************************
 Excelで編集したコンテンツをサーバにアップロードして書き戻す
***********************************************/
function read_data_import() {
    $("#circle_icon_area").css("display","block");
    //$('#import_file').click();
    $('#import_file').change(function(evt) {
        var file = evt.target.files[0];
        var extension = file.name.split('.')[1];
        if (extension === 'csv') {
            var reader = new FileReader();
            reader.readAsText( file );
            reader.addEventListener( 'load', function() {
                var _import_file = reader.result.trim();
                $.post(get_set_contents,
                {
                    import_file:      _import_file,
                    news_id:          $("#_news_id").val()
                },
                function(data, status){
                    if(status == 'success') {
                        //alert(data);
                        if (parseInt(data) > 0) {
                            alert(data + " 行のコンテンツ候補を書き戻しました。いったんリロードします。");
                            location.reload();
                        } else {
                            alert("コンテンツ候補の書き戻しに失敗しました。リロードします。");
                            location.reload();
                        }
                    } else {
                        alert("error.");
                        $("#circle_icon_area").css("display","none");
                    }
                });
            });
        } else {
            var er = new ExcelJs.Reader(file, function (e, xlsx) {
                var _import_file =  xlsx.toCsv();
                $.post(get_set_contents,
                {
                    import_file:      _import_file,
                    news_id:          $("#_news_id").val()
                },
                function(data, status){
                    if(status == 'success') {
                        //alert(data);
                        if (parseInt(data) > 0) {
                            alert(data + " 行のコンテンツ候補を書き戻しました。いったんリロードします。");
                            location.reload();
                        } else {
                            alert("コンテンツ候補の書き戻しに失敗しました。リロードします。");
                            location.reload();
                        }
                    } else {
                        alert("error.");
                        $("#circle_icon_area").css("display","none");
                    }
                });
            }, false);
        }
    });
    $('#import_file').click();
}
/*
function read_data_import() {
    $("#circle_icon_area").css("display","block");
    //$('#import_file').click();
    document.getElementById('import_file').addEventListener('change', function (evt) {
        var file = evt.target.files[0];
        var er = new ExcelJs.Reader(file, function (e, xlsx) {
            var _import_file = xlsx.toCsv();
            var fd = new FormData();
            fd.append("import_file", _import_file);
            fd.append("news_id", $("#_news_id").val());
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    if(Number(this.responseText) > 0) {
                        alert(this.responseText + " 行のコンテンツ候補を書き戻しました。いったんリロードしますので、改めてコンテンツを取り込んでください。");
                        location.reload();
                    } else {
                        alert("コンテンツ候補の書き戻しに失敗しました。リロードします。");
                        location.reload();
                    }
               }
            };
            xhttp.open("POST", get_set_contents, true);
            xhttp.send(fd);
        });
    });
    $('#import_file').click();
}
*/

function resetElements() {
    $(".ui-sortable-handle").each(function(index, element){
        if(index > 1) {
            $(element).remove();
        }
    });
}

function setElements(arr) {
    var categoryName = '';
    for(var i=0; i<arr.length; i++) {
        if(categoryName != arr[i][0]) {
            add_category(arr[i][0]);
            categoryName = arr[i][0];
        }
        add_cassette(arr[i]);
    }
}

function create_category() {
    //alert("here");
    var new_category = prompt("新規追加したいカテゴリ名を入力してください", "");
    //alert(new_category);
    $("#category_list").append('<option>' + new_category + '</option>');
    add_category(new_category);
}

function add_category(arg) {
    var cont_category = $("#tmpl_category").clone(false).removeAttr("id");
    $(cont_category).removeClass("invisible");
    $("#sortable").append(cont_category);
    if(arg) {
        $(cont_category).find("option").each(function(index,element){
            if($(element).text() == arg) {
                $(element).attr("selected","selected");
            }
        });
    }
}

function add_cassette(args) {
    var cont_cassette = $("#tmpl_cassette").clone(false).removeAttr("id");
    $(cont_cassette).removeClass("invisible");
    $("#sortable").append(cont_cassette);
    if(args) {
        var fyi_flag = [false, false, false];
        $(cont_cassette).find("input").each(function(index,element){
            if($(element).attr("name") == "article_url") {
                $(element).val(args[2]);
            } else if($(element).attr("name") == "article_title") {
                $(element).val(args[1]);
            } else if($(element).attr("name") == "article_media") {
                $(element).val(args[3] + '（' + args[4].split(' ')[0] + '）');
            }
        });
    }
}

function remove_cassette(elm) {
    $(elm).parent().parent().parent().remove();
}


/**********************************************
 AJAXを使ってサーバーからランキングリストをJSONで取得し、
 HTMLテーブルを生成。
 ***********************************************/
function gen_ranking() {
    $.post(get_ranking_issue_url,
    {
        issue:      $("#target_issue").val(),
        news_id:    $("#_news_id").val()
    },
    function(data, status){
        if(status == 'success') {
            //alert(data.trim());
            if(data.trim() === 'null') {
                alert("コンテンツがありません");
            } else {
                /////////////
                //alert(data);
                /////////////
                var ranking = JSON.parse(data);
                ranking_length = ranking.length;
                var buf = '<div class="operation">▼タイトルや順位を確認・編集▼</div>';
                buf += '<table id="rankingtable" class="rankingtable">';
                buf += '<tr><th>No</th><th>URL／記事名</th><th>PV</th><th>削除</th></tr>';
                for(var i=0; i<ranking.length; i++) {
                    buf += '<tr>';
                    buf += '<td rowspan="2" align="center">' + (i+1) + '</td>';
                    buf += '<td><a href="' + ranking[i][0] + '" target="_blank" id="url_' + (i+1) + '">' + ranking[i][0] + '</a></td>';
                    buf += '<td rowspan="2" align="right" id="num_' + (i+1) + '">' + ranking[i][2] + '</td>';
                    buf += '<td rowspan="2" align="center"><button class="deleteButton">×</button></td>';
                    buf += '</tr>';
                    buf += '<tr>';
                    buf += '<td>';
                    buf += '<input id="title_' + (i+1) + '" type="text" value="' + ranking[i][1] + '" size="70"></td>';
                    buf += '</tr>';
                }
                buf += '</table>';
                $( "#ranking_area" ).html(buf);
                rebuilt();
            }
        }
    });
}

/**********************************************
 直前の gen_ranking() の最後に呼ぶ関数。
 ランキングにふさわしくないタイトルがあった場合、
 そのタイトルを削除できるのが、削除後に
 ランキングの次点だったタイトルをテーブルに
 追加する必要がある。サーバからは10件のタイトルを
 取得しておく。
 ***********************************************/

function rebuilt() {
    var current_num = ($("#rankingtable tr").length);
    ranking_length = (current_num - 1) / 2;
    $("#rankingtable tr").each(function(i, val) {
        // 2行で1レコード、1行目はタイトル行、なので1〜10行が対象となる
        if(i > 0 && i < current_num) {
            // 奇数行にはURLとPV数のIDを振る
            if (i % 2 === 1) {
                val.children[1].children[0].setAttribute("id", "url_" + (Math.floor(i/2)+1));
                val.children[2].setAttribute("id", "num_" + (Math.floor(i/2)+1));
            // 偶数行にはタイトルのIDを振る
            } else if (i % 2 === 0) {
                val.children[0].children[0].setAttribute("id", "title_" + (i/2));
            }
        }
    });
    $(".deleteButton").click(function () {
        $(this).parent().parent().next().remove();
        $(this).parent().parent().remove();
        // 順位のレコードを削除したら、IDを振り直す。じゃないとプレビューの時に困る
        rebuilt();
    });
    $(".deleteButton").hover(function () {
        $(this).parent().parent().addClass("hilite");
        $(this).parent().parent().next().addClass("hilite");
    }, function () {
        $(this).parent().parent().removeClass("hilite");
        $(this).parent().parent().next().removeClass("hilite");
    });
}

function setErrorColor(elm) {
    $(elm).css("background-color","#FAF");
}

function setDefaultColor(elm, opt) {
    if(opt) {
        $(elm).css("background-color", opt);
    } else {
        $(elm).css("background-color","#FFF");
    }
}

function set_fyi(elem, label, error_collection) {
    if($(elem).css('display') != 'none' &&
       $(elem.firstElementChild).attr('name') == ('add_fyi_' + label) &&
       $(elem.firstElementChild).attr('checked') == 'checked') {
        if($(elem.firstElementChild.nextElementSibling.nextElementSibling.firstElementChild).attr('name') == ('fyi_title_' + label) &&
           $(elem.firstElementChild.nextElementSibling.nextElementSibling.firstElementChild).val() === '') {
            error_collection['fyi_title_' + label] += 1;
            setErrorColor(elem.firstElementChild.nextElementSibling.nextElementSibling);
        } else {
            setDefaultColor(elem.firstElementChild.nextElementSibling.nextElementSibling);
        }
        if($(elem.firstElementChild.nextElementSibling.firstElementChild).attr('name') == ('fyi_url_' + label)) {
            if($(elem.firstElementChild.nextElementSibling.firstElementChild).val() === '') {
                error_collection['fyi_url_missing_' + label] += 1;
                setErrorColor(elem.firstElementChild.nextElementSibling);
            } else if(!$(elem.firstElementChild.nextElementSibling.firstElementChild).val().match("^https?://")) {
                error_collection['fyi_url_reg_' + label] += 1;
                setErrorColor(elem.firstElementChild.nextElementSibling);
            } else {
                setDefaultColor(elem.firstElementChild.nextElementSibling);
            }
        }
        return '<div name="fyi" style="margin-top:3px;"><a href="' + redirect_url + '?url=' +
               $(elem.firstElementChild.nextElementSibling.firstElementChild).val() +
               '" style="font-size:9pt;color:rgb(70,70,70);color:blue;text-decoration:underline;" target="_blank">' +
               $(elem.firstElementChild.nextElementSibling.nextElementSibling.firstElementChild).val() +
               '</a></div>';
    } else {
        return '';
    }
}

function add_fyi(elem) {
    if($(elem).next().css("display") === "none") {
        $(elem).next().css("display", "block");
        $(elem).next().next().css("display", "block");
        $(elem).parent().next().css("display", "block");
        // 以下の判定がjQueryでは動かないのでDOMしてます（propで行けることを思い出した、でも直さない）
        if(elem.parentElement.nextElementSibling.firstElementChild.getAttribute("checked") == 'checked') {
          $(elem).parent().next().first().next().css("display", "block");
          $(elem).parent().next().first().next().next().css("display", "block");
          $(elem).parent().next().next().css("display", "block");
          if(elem.parentElement.nextElementSibling.nextElementSibling.firstElementChild.getAttribute("checked") == 'checked') {
            $(elem).parent().next().next().first().next().css("display", "block");
            $(elem).parent().next().next().first().next().next().css("display", "block");
          }
        }
    } else {
        $(elem).next().css("display", "none");
        $(elem).next().next().css("display", "none");
        // 削除ボタンを消さないために判定してます
        if($(elem).parent().next().attr("class")==="add_fyi") {
          $(elem).parent().next().css("display", "none");
        }
        if($(elem).parent().next().next().attr("class")==="add_fyi") {
          $(elem).parent().next().next().css("display", "none");
        }
    }
}

function preview() {
    $("#preview_area").html('');
    var error_collection = {
        'body': 0,'subject': 0,
        'category': 0, 'url_missing': 0, 'url_reg': 0, 'title': 0, 'media': 0,
        'fyi_url_missing_0': 0, 'fyi_title_0': 0, 'fyi_url_reg_0': 0,
        'fyi_url_missing_1': 0, 'fyi_title_1': 0, 'fyi_url_reg_1': 0,
        'fyi_url_missing_2': 0, 'fyi_title_2': 0, 'fyi_url_reg_2': 0, 'ranking_area': 0
    };
    var buffer = '';
    var category = '';
    var isSameCategory = true;
    var isTop = 0;
    if($("#sortable > li").length === 2) {
        error_collection['body'] = 1;
    } else {
        if($("#subject").val() === '') {
            error_collection['subject'] = 1;
        }
        $("#sortable > li").each(function (index, domEle) {
            
            if(!$(domEle).attr("id")) {
                var level2 = domEle.firstElementChild.firstElementChild;
                if(level2.tagName == "SELECT") {
                    if($(level2).val() == "-- カテゴリを選ぶ --") {
                        error_collection['category'] += 1;
                        setErrorColor(domEle);
                    } else {
                        category = $(level2).val();
                        isSameCategory = false;
                    }
                } else if(level2.tagName == "DIV") {
                    isTop += 1;
                    var divs = domEle.firstElementChild.children;
                    var main_url = '';
                    
                    // article_url
                    if($(divs[1].firstElementChild).attr('name') == 'article_url') {
                        if($(divs[1].firstElementChild).val() === '') {
                            error_collection['url_missing'] += 1;
                            setErrorColor(divs[1]);
                        } else if (!$(divs[1].firstElementChild).val().match("^https?://")) {
                            error_collection['url_reg'] += 1;
                            setErrorColor(divs[1]);
                        } else {
                            main_url = '<a href="' + redirect_url + '?url=' + $(divs[1].firstElementChild).val() +
                                       '&company_id=' + encodeURI($("#_company_id").val()) + '&news_id=' + encodeURI($("#_news_id").val()) + '&issue=' + issue +
                                       '&title=' + encodeURI($(divs[2].firstElementChild).val()) +
                                       '" target="_blank" style="color:blue;text-decoration:underline;" name="url">';
                            setDefaultColor(divs[1]);
                        }
                    }

                    buffer += '<div name="elem" style="width:100%">';

                    // set category icon 
                    if(!isSameCategory) {
                        if(isTop > 1) {
                            buffer += '<hr style="border-top:1px solid #ccc;margin: 10px 40px;width:520px"></hr>';
                        }
                        buffer += '<div name="category" value="' + category  + '" style="float:none;width:150px;height:30px;">';
                        if(category_icon[category] === "base_icon.png" || category_icon[category] !== null) {
                            buffer += '<div alt="' + category + '" style=\'width:500px;height:30px;background-image:url("images/common_icon/base_icon.png");background-repeat:no-repeat;background-position: left center;\'><span style="padding-left:26px;font-size:large;font-weight:bold;">' + category + '</span></div></div>';
                        } else {
                            buffer += '<img src="' + category_icon_url + category_icon[category] + '" alt="' + category + '" style="width:100%;height:100%;" /></div>';
                        }
                        isSameCategory = true;
                    }
                    buffer += '<div name="contents" style="float:none;width:100%;margin-top:10px;">';
                    
                    // check！
                    if($(divs[0].firstElementChild).prop('checked')) {
                        buffer += '<div name="attention" style="width:10%;height:20px;float:left;">';
                        buffer += '<img src="' + check_icon_url + '" alt="注目！" style="width:100%;" /></div>';
                    } else {
                        buffer += '<div name="attention" style="width:10%;height:20px;float:left;"></div>';
                    }
                                        
                    // article_title
                    if($(divs[2].firstElementChild).attr('name') == 'article_title') {
                        if($(divs[2].firstElementChild).val() === '') {
                            error_collection['title'] += 1;
                            setErrorColor(divs[2]);
                        } else {
                            buffer += '<div name="wrapper" style="margin-top:0px;width:90%;float:left">';
                            buffer += '<div name="title" style="font-size:13pt;line-height:1.1;font-weight:700;">' + main_url + escape_html($(divs[2].firstElementChild).val()) + '</a></div>';
                            setDefaultColor(divs[2]);
                        }
                    }
                    
                    // article_media
                    if($(divs[3].firstElementChild).attr('name') == 'article_media') {
                        if($(divs[3].firstElementChild).val() === '') {
                            error_collection['media'] += 1;
                            setErrorColor(divs[3]);
                        } else {
                            // 要ログイン
                            buffer += '<div name="media" style="font-size:9pt;color:rgb(70,70,70);text-align:right;margin-top:0px;font-weight:bold;">';
                            buffer += '<span style="margin-right:6px;">' + escape_html($(divs[3].firstElementChild).val()) + '</span>';
                            if($(divs[4].firstElementChild).prop('checked')) {
                                buffer += '<img src="' + req_login_url + '" style="vertical-align:bottom;height:26px;" alt="要ログイン" />';
                            }
                            buffer += '</div>';
                            setDefaultColor(divs[3]);
                        }
                    }
                    
                    // fyi_0_url
                    if(divs[5]) { buffer += set_fyi(divs[5], '0', error_collection); }
                    // fyi_1_url
                    if(divs[6]) { buffer += set_fyi(divs[6], '1', error_collection); }
                    // fyi_2_url
                    if(divs[7]) { buffer += set_fyi(divs[7], '2', error_collection); }
                    
                    buffer += '</div><div style="clear:both;"></div></div></div>';
                }
            }
        });
    }

    if($("#ranking_area").html() === '') {
        if($("#target_issue").val() !== 'no-data') {
            error_collection['ranking_area'] += 1;
        }
    }
    show_results(error_collection, buffer);
}

function show_results(e_col, buffer) {
    var flag = false;
    var buff = '';
    if(e_col['body'] > 0)              { buff += "本文がありません\n"; flag = true; }
    if(e_col['subject'] > 0)           { buff += "メール件名がありません\n"; flag = true; }
    if(e_col['category'] > 0)          { buff += "未選択のカテゴリーが " + e_col['category'] + " つあります\n"; flag = true; }
    if(e_col['url_missing'] > 0)       { buff += "URLの未入力が " + e_col['url_missing'] + " つあります\n"; flag = true; }
    if(e_col['url_reg'] > 0)           { buff += "URLの不正が " + e_col['url_reg'] + " つあります\n"; flag = true; }
    if(e_col['title'] > 0)             { buff += "タイトルの未入力が " + e_col['title'] + " つあります\n"; flag = true; }
    //if(e_col['media'] > 0)             { buff += "メディアの未入力が " + e_col['media'] + " つあります\n"; flag = true; }
    if(e_col['fyi_url_missing_0'] > 0) { buff += "参考リンク_1のURLの未入力が " + e_col['fyi_url_missing_0'] + " つあります\n"; flag = true; }
    if(e_col['fyi_url_reg_0'] > 0)     { buff += "参考リンク_1のURLの不正が " + e_col['fyi_url_reg_0'] + " つあります\n"; flag = true; }
    if(e_col['fyi_title_0'] > 0)       { buff += "参考リンク_1のタイトルの未入力が " + e_col['fyi_title_0'] + " つあります\n"; flag = true; }
    if(e_col['fyi_url_missing_1'] > 0) { buff += "参考リンク_2のURLの未入力が " + e_col['fyi_url_missing_1'] + " つあります\n"; flag = true; }
    if(e_col['fyi_url_reg_1'] > 0)     { buff += "参考リンク_2のURLの不正が " + e_col['fyi_url_reg_1'] + " つあります\n"; flag = true; }
    if(e_col['fyi_title_1'] > 0)       { buff += "参考リンク_2のタイトルの未入力が " + e_col['fyi_title_1'] + " つあります\n"; flag = true; }
    if(e_col['fyi_url_missing_2'] > 0) { buff += "参考リンク_3のURLの未入力が " + e_col['fyi_url_missing_2'] + " つあります\n"; flag = true; }
    if(e_col['fyi_url_reg_2'] > 0)     { buff += "参考リンク_3のURLの不正が " + e_col['fyi_url_reg_2'] + " つあります\n"; flag = true; }
    if(e_col['fyi_title_2'] > 0)       { buff += "参考リンク_3のタイトルの未入力が " + e_col['fyi_title_2'] + " つあります\n"; flag = true; }
    if(e_col['ranking_area'] > 0)      { buff += "ランキングが作成されていません\n"; flag = true; }
    if(flag) {
        alert(buff + '\n修正してください！');
    } else {
        $("#preview_subject").html(getSubject());
        if($("#target_issue").val() !== 'no-data') {
            if($("#pv_style").val() === 'kr') {
                $("#preview_area").html(
                     getMailheader() + getMemo() + buffer + headofranking_a + getRankingDate() + headofranking_b + getRanking() + getFooter() );   
            } else if($("#pv_style").val() === 'rk') {
                $("#preview_area").html(
                     getMailheader() + getMemo() + headofranking_a + getRankingDate() + headofranking_b + getRanking() + buffer + getFooter() );
            }
        } else {
            $("#preview_area").html(
                getMailheader() + getMemo() + buffer + getFooter() );
        }
    }
}

function getMailheader() {
    var root = '<div name="201704" style="font-size:10pt;color:rgb(0,0,0);font-family:Avenir,Helvetica,Arial,Verdana,Roboto,YuGothic,Meiryo,sans-serif;line-height:1.2;width:600px;">';
    var head_a = '<div style="width:100%;margin:0 0 1em 0;padding:0;"><img src="' + title_image_url + '" style="width:100%;margin:0;padding:0;" alt="Title Image" /></div>';
    return root + head_a;
}

function getSubject() {
    var subject = escape_html($("#subject").val());
    var y = new Date().getFullYear();
    var m = new Date().getMonth() + 1;
    var d = new Date().getDate();
    m = ('0' + m).slice(-2);
    d = ('0' + d).slice(-2);
    var _today = y + '/' + m + '/' + d;
    return subject.replace('20XX/XX/XX', _today);
}

function getMemo() {
  var memo = escape_html($("#memo").val());
  if(memo) {
    return '<div style="color:rgb(0,0,0);font-family:Avenir,Helvetica,Arial,Verdana,Roboto,YuGothic,Meiryo,sans-serif;font-size:13.3333px;width:600px;margin:0px 0px 1em;padding:0px"><b style="color:rgb(34,34,34);font-family:arial,sans-serif;font-size:14px">' +
           memo + '</b><br></div>';
  } else {
    return '';
  }
}

var headofranking_a = '<div style="width:98.4%;font-size:9pt;margin-top:30px;color:white;font-weight:bold;padding:0.8%;background-color:black;">';

function getRankingDate() {
    var y_m_d = $("#target_issue").val().split('-');
    var m;
    var d;
    if(y_m_d[1][0] === '0') {
        m = y_m_d[1][1];
    } else {
        m = y_m_d[1];
    }
    if(y_m_d[2][0] === '0') {
        d = y_m_d[2][1];
    } else {
        d = y_m_d[2];
    }
    return '【' + m + '月' + d + '日のアクセスランキング】';
}


var headofranking_b = '</div>' +
                        '<table style="width:100%;font-size:9pt;margin-top:6px;margin-bottom:30px;border-collapse: collapse;line-height:1.2;">' +
                        '<tr style="text-align:center;margin:1px 0 1px 0;background-color:#ccc;">' +
                        '<td style="width:6%;padding:0.5%;">順位</td>' +
                        '<td style="border-left:1px solid #fff;border-right:1px solid #fff;width:86%;padding:0.5%;">記事</td>' +
                        '<td style="width:8%;padding:0.5%;">Click</td>' +
                        '</tr>';

function getRanking() {
    var buffer = '';
    var ranking = 0;
    for(var i=1; i<(ranking_length+1); i++) {
        if(i % 2 === 0) {
            buffer += '<tr style="margin:1px 0 1px 0;background-color:#eee;">';
        } else {
            buffer += '<tr style=""margin:1px 0 1px 0;background-color:#fff;">';
        }
        buffer += '<td style="width:6%;padding:0.5%;font-weight:bold;text-align:center;">' + i + '</td>';
        buffer += '<td style="border-left:1px solid #fff;border-right:1px solid #fff;width:86%;padding:0.5%;text-align:left;">';
        if($("#title_" + i).length !== 0) {
            buffer += '<a href="' + redirect_url + '?url=' + $("#url_" + i).text() +
                      '&company_id=' + encodeURI($("#_company_id").val()) +
                      '&news_id=' + encodeURI($("#_news_id").val()) +'&issue=' + $("#target_issue").val() +
                      '&title=' + encodeURI($("#title_" + i).val()) +
                      '" target="_blank" style="text-decoration:none;">';
            buffer += $("#title_" + i).val() + '</a></td><td style="width:8%;padding:0.5%;text-align:right;">' + $("#num_" + i).text() + '</td></tr>';
        } else {
            buffer += ' -- </td><td style="width:8%;padding:0.5%;text-align:right;"> -- </td></tr>';
        }
    }
    buffer += '</table>';

    return buffer;
}


function getFooter() {
    /**********************************************
     開封カウンタ用の処理。サーバーに対して、company_id、api_key、
     issueをパラメータで渡し、get_issue
     1ピクセル×1ピクセルの透明な画像を受け取る。
     ***********************************************/
    y = new Date().getFullYear().toString().substr(2,3);
    m = new Date().getMonth() + 1;
    d = new Date().getDate();
    if ( m < 10 ) { m = '0' + m; }
    if ( d < 10 ) { d = '0' + d; }

    var acImageUrl = '<img src="' + access_counter_url +
                           '?company_id=' + encodeURI($("#_company_id").val()) +
                           '&news_id=' + encodeURI($("#_news_id").val()) +
                           '&issue=' + issue + '" style="display:none;">';

    var buffer = '';
    buffer += '<div style="margin:2em 0 0.5em 0;clear:both;">';
    buffer += acImageUrl;
    buffer += '<div style="margin-bottom:1em;">※<span style="color:red;font-weight:bold;">要ログイン</span>とは掲載元のID登録により記事全文を読むことが出来るニュースです</div>';
    buffer += signature;
    if(bottom_image_url !== '#') {
        buffer += '<div style="width:100%;margin:1em 0 0 0;padding:0;"><img src="' + bottom_image_url + '" style="width:100%;margin:0;padding:0;" alt="Bottom Image" /></div>';
    }
    buffer += '</div>';
    return buffer;
}

function copy_subject() {
    return;
}

function copy_body() {
    return;
}


function escape_html(string) {
  if(typeof string !== 'string') {
    return string;
  }
  return string.replace(/[&'`"<>]/g, function(match) {
    return {
      '&': '&amp;',
      "'": '&#x27;',
      '`': '&#x60;',
      '"': '&quot;',
      '<': '&lt;',
      '>': '&gt;',
    }[match];
  });
}

function convert_br(string) {
    if(typeof string !== 'string') {
        return string;
    }
    return string.replace(/&lt;br&gt;\n/g, '<br>').replace(/&lt;br&gt;/g, '<br>').replace(/\n/g, '<br>');
}

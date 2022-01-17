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
} else if ($_GET['news_id'] && two_step_auth($mysqli, $_SESSION['company_id'], $_SESSION['user_id'])) {
    print_header("ニュース設定画面", $_SESSION);
    print_mennu($_GET['page'])
?>
            <div class="main_area">
                <h3>ニュースの設定変更</h3>

                <?php
                    $result = get_preference($mysqli, $_SESSION['company_id'], $_GET['news_id']);
                    $row = $result->fetch_assoc();
                ?>
                <table class="conf_table">
                    <tr><th>企業 ID</th><td><?php echo $_SESSION['company_id']; ?></td></tr>
                    <tr><th>ニュース ID</th><td><?php echo $_GET['news_id']; ?></td></tr>
                    <tr><th>モデルエイリアス</th><td><?php echo get_cid_alias_as_select($mysqli, $row["cid_alias"]); ?></td></tr>
                    <tr><th>RSSリスト ID</th><td><?php echo get_rss_id_as_select($mysqli, $row["rss_id"]); ?></td></tr>
                    <tr><th>カテゴリ リスト ID</th><td><?php echo get_category_id_as_select($mysqli, $row["category_id"]); ?></td></tr>
                    <tr><th>サイト名リスト ID</th><td><?php echo get_site_names_id_as_select($mysqli, $row["site_names_id"]); ?></td></tr>
                    <tr><th>メール件名</th><td><input name="default_title" id="default_title" type="text" value="<?php echo $row["default_title"]; ?>" size="50"></td></tr>
                    <tr><th>ニュース取得開始日</th><td><?php echo get_period_day_as_select($mysqli, $row["period_day"]); ?></td></tr>
                    <tr><th>ニュース取得開始時刻</th><td><?php echo get_period_hour_as_select($mysqli, $row["period_hour"]); ?></td></tr>
                    <tr><th>ニュース取得数</th><td><?php echo get_fetch_num_as_select($mysqli, $row["fetch_num"]); ?></td></tr>
                    <tr><th>署名</th><td><textarea rows="5" cols="40" name="signature" id="signature"><?php echo $row["signature"]; ?></textarea></td></tr>
                    <tr>
                        <th>トップ画像<span class="help_icon" onmouseover="help('top_image')">？</span></th>
                        <td>
                            <input type="file" id="add_top_image" name="add_top_image" accept="image/png">
                            <button type="button" onclick="image_upload('top')">送信</button>
                            <button type="button" onclick="image_delete('top')">削除</button>
                        </td>
                    </tr>
                    <tr>
                        <th>カテゴリー画像<span class="help_icon" onmouseover="help('category_image')">？</span></th>
                        <td>
                            <input type="file" id="add_category_icons" name="add_category_icons[]" accept="image/*,.png,.jpg,.jpeg,.gif" multiple>
                            <button type="button" onclick="category_icons_upload()">送信</button>
                            <button type="button" onclick="category_icons_delete()">削除</button>
                        </td>
                    </tr>
                    <tr>
                        <th>ボトム画像<span class="help_icon" onmouseover="help('bottom_image')">？</span></th>
                        <td>
                            <input type="file" id="add_bottom_image" name="add_bottom_image" accept="image/*,.png,.jpg,.jpeg,.gif">
                            <button type="button" onclick="image_upload('bottom')">送信</button>
                            <button type="button" onclick="image_delete('bottom')">削除</button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <button id="update_news">データを更新する</button>
                            <button id="delete_news" style="margin: 0 20px;">このニュースを削除する</button>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="news_id" id="news_id" value="<?php echo $row["news_id"]; ?>">
            </div>
            <br><br>
            <a href="/<?php echo BASE; ?>/?page=news_conf"><button class="button_back">戻る</button></a>
            <script>
function image_delete(arg) {
    var msg = "";
    if (arg === "top") { msg = "トップ画像を削除しますか？"}
    else if (arg === "bottom") { msg = "ボトム画像を削除しますか？"}
    if (! confirm(msg)) {
        return;
    }
    $.post("image_manage.php",
    {
        news_id: $("#news_id").val(),
        cmd:     "delete",
        place:   arg,
    },
    function(data, status){
        if(status == 'success') {
            if (data == 1) {
                alert("この画像を削除しました。");
            } else if (data == 0) {
                alert("この画像の削除に失敗しました。");
            } else if (data == 2) {
                alert("この画像は存在しません。");
            }
        } else {
            alert("error.");
        }
    });
}
function category_icons_delete() {
    if (! confirm("カテゴリー画像を一括削除しますか？（個別の削除はできません）")) {
        return;
    }
    $.post("image_manage.php",
    {
        news_id: $("#news_id").val(),
        cmd:     "delete",
        place:   "icons",
    },
    function(data, status){
        if(status == 'success') {
            //alert(data);
            if (data > 0) {
                alert(data + "個のカテゴリー画像を削除しました。");
            } else if (data == 0) {
                alert("カテゴリー画像は存在しません。");
            }
        } else {
            alert("error.");
        }
    });
}
function image_upload(arg) {
    if($("#add_" + arg + "_image").prop("files")[0] === undefined) {
        alert("画像ファイルを選んでください。");
        return;
    }
    var fd = new FormData();
    fd.append("file", $("#add_" + arg + "_image").prop("files")[0]);
    fd.append("news_id", $("#news_id").val());
    fd.append("place", arg);
    fd.append("cmd", "upload");
    $.ajax({
        url  : "image_manage.php",
        type : "POST",
        data : fd,
        contentType : false,
        processData : false,
    })
    .done(function(data, textStatus, jqXHR){
        alert(data);
    })
    .fail(function(jqXHR, textStatus, errorThrown){
        alert("fail " + textStatus);
    });
}
function category_icons_upload() {
    if($("#add_category_icons").prop("files")[0] === undefined) {
        alert("画像ファイルを選んでください。");
        return;
    }
    var fd = new FormData();
    var files = $("#add_category_icons").prop("files");
    for (var i=0; i<files.length; i++) {
        fd.append("file[]", files[i]);
    }
    fd.append("news_id", $("#news_id").val());
    fd.append("cmd", "upload");
    $.ajax({
        url  : "image_manage.php",
        type : "POST",
        data : fd,
        contentType : false,
        processData : false,
    })
    .done(function(data, textStatus, jqXHR){
        alert(data);
    })
    .fail(function(jqXHR, textStatus, errorThrown){
        alert("fail " + textStatus);
    });
}


var news_manage = "news_manage.php";
document.getElementById('delete_news').addEventListener('click', function (evt) {
    if(confirm("本当に削除しますか？　この操作は取り消しできません。") == false) {
        return;
    }
    $.post(news_manage,
    {
        news_id: $("#news_id").val(),
        cmd:     "delete",
    },
    function(data, status){
        if(status == 'success') {
            if (data == 1) {
                alert("このニュースを削除しました。");
                location.href = "/<?php echo BASE; ?>/?page=news_conf";
            } else {
                alert("このニュースの削除に失敗しました。");
                location.href = "/<?php echo BASE; ?>/?page=news_conf";
            }
        } else {
            alert("error.");
        }
    });
});

document.getElementById('update_news').addEventListener('click', function (evt) {
    $.post(news_manage,
    {
        news_id:       $("#news_id").val(),
        cid_alias:     $("#cid_alias").val(),
        rss_id:        $("#rss_id").val(),
        category_id:   $("#category_id").val(),
        site_names_id: $("#site_names_id").val(),
        default_title: $("#default_title").val(),
        period_day:    $("#period_day").val(),
        period_hour:   $("#period_hour").val(),
        fetch_num:     $("#fetch_num").val(),
        signature:     $("#signature").val(),
        cmd:           "update",
    },
    function(data, status){
        if(status == 'success') {
            if (data == 1) {
                alert("データを更新しました。");
                location.href = "/<?php echo BASE; ?>/?page=news_conf";
            } else {
                alert("データの更新に失敗しました。");
                location.href = "/<?php echo BASE; ?>/?page=news_conf";
            }
        } else {
            alert("error.");
        }
    });
});
            </script>
<?php
        print_javascript("others");
        print_footer();
} else {
    echo 'パラメータが不正です。';
}

exit();
?>

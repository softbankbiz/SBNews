function help(arg) {
  var msg = '';
  if (arg === 'top_image') {
    msg = 'トップ画像\nプレビュー画面の上部に表示される画像をアップロードします。ファイル名は何であってもかまいません。\
ファイルタイプは「PNG」のみ利用可能です。\
画像サイズは、幅：600ピクセル（固定）、高さ：120ピクセル（任意）です。\
ファイルを選択したら、必ず「送信」ボタンを押してください。';
  } else if (arg === 'category_image') {
    msg = 'カテゴリー画像\nプレビュー画面のカテゴリ見出しに表示させる画像をアップロードします。\
ファイル名は、該当する「カテゴリ リスト ID」に登録したファイル名と整合させてください。\
画像サイズは、幅：360ピクセル（任意）、高さ：70ピクセル（固定）です。\
カテゴリー画像を用意しない場合は、デフォルトの「画像＋テキスト」が使用されます。\
ファイルを選択したら、必ず「送信」ボタンを押してください。';
  } else if (arg === 'bottom_image') {
    msg = 'ボトム画像\nプレビュー画面の下部に表示させる画像（任意）をアップロードします。ファイル名は何であってもかまいません。\
ファイルタイプは「PNG」のみ利用可能です。\
画像サイズは、幅：600ピクセル（固定）、高さ：120ピクセル（任意）です。\
ファイルを選択したら、必ず「送信」ボタンを押してください。';
  }
  alert(msg);
}

function delete_confirm() {
  if (! confirm('本当に削除しますか？')) { return; }
}


function check_date_format(id) {
    //return true;
    var year_s = $("#" + id + "_year_s").val();
    var month_s = $("#" + id + "_month_s").val();
    var date_s = $("#" + id + "_date_s").val();
    var year_e = $("#" + id + "_year_e").val();
    var month_e = $("#" + id + "_month_e").val();
    var date_e = $("#" + id + "_date_e").val();
    if ( year_s === "" || month_s === "" || date_s === "" || year_e === "" || month_e === "" || date_e === "") {
        alert("未入力項目があります");
        return false;
    } else if (! year_s.match(/20[0-9][0-9]/) || ! year_e.match(/20[0-9][0-9]/)) {
        alert("年の書式が違います");
        return false;
    } else if (! month_s.match(/0[1-9]/) && ! month_s.match(/1[0-2]/)) {
        alert("月の書式が違います");
        return false;
    } else if (! month_e.match(/0[1-9]/) && ! month_e.match(/1[0-2]/)) {
        alert("月の書式が違います");
        return false;
    } else if (! date_s.match(/0[1-9]/) && ! date_s.match(/1[0-9]/) && ! date_s.match(/2[0-9]/) && ! date_s.match(/3[0-1]/)) {
        alert("日の書式が違います");
        return false;
    } else if (! date_e.match(/0[1-9]/) && ! date_e.match(/1[0-9]/) && ! date_e.match(/2[0-9]/) && ! date_e.match(/3[0-1]/)) {
        alert("日の書式が違います");
        return false;
    } else {
        return true;
    }
}

function check_change_password() {
  if (document.change_password_form.password_01.value != document.change_password_form.password_02.value){
    alert("パスワードが一致しません。");
    return false;
  } else if (document.change_password_form.password_01.value.length < 6) {
    alert("パスワードは6文字以上にしてください。");
    return false;
  } else if (document.change_password_form.password_01.value.length > 12) {
    alert("パスワードは12文字以内にしてください。");
    return false;
  } else if (! document.change_password_form.password_01.value.match(/^[A-Za-z0-9]*$/)) {
    alert("パスワードは英数半角文字にしてください。");
    return false;
  } else {
    return true;
  }
}

function check_create_user_info() {
  //return true;

  if (!document.create_user_form.company_id.value || !document.create_user_form.user_id.value) {
    alert("入力が不足しています。");
    return false;
  } else if (document.create_user_form.company_id.value == 'root') {
    alert("その企業IDは使用できません。");
    return false;
  } else if (document.create_user_form.company_id.value.length > 20) {
    alert("企業IDは20文字以内にしてください。");
    return false;
  } else if (document.create_user_form.user_id.value.length > 20) {
    alert("ユーザーIDは20文字以内にしてください。");
    return false;
  } else if (! document.create_user_form.company_id.value.match(/^[A-Za-z0-9\._]*$/)) {
    alert("企業IDは英数半角文字にしてください。");
    return false;
  } else if (! document.create_user_form.user_id.value.match(/^[A-Za-z0-9\._]*$/)) {
    alert("ユーザーIDは英数半角文字にしてください。");
    return false;
  } else if (! document.create_user_form.password_expires.value.match(/^(2[0-9][0-9]{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/)) {
    alert("パスワード有効期限の書式が違います。");
    return false;
  } else if (! document.create_user_form.role.value.match(/admin|editor/)) {
    alert("存在しない役割です。");
    return false;
  }  else {
    return true;
  }
}

function check_update_user_info() {
  if (!document.update_user_form.company_id.value || !document.update_user_form.user_id.value) {
    alert("入力が不足しています。");
    return false;
  } else if (document.create_user_form.company_id.value == 'root') {
    alert("その企業IDは使用できません。");
    return false;
  } else if (document.update_user_form.company_id.value.length > 20) {
    alert("企業IDは20文字以内にしてください。");
    return false;
  } else if (document.update_user_form.user_id.value.length > 20) {
    alert("ユーザーIDは20文字以内にしてください。");
    return false;
  } else if (! document.update_user_form.company_id.value.match(/^[A-Za-z0-9\._]*$/)) {
    alert("企業IDは英数半角文字にしてください。");
    return false;
  } else if (! document.update_user_form.user_id.value.match(/^[A-Za-z0-9\._]*$/)) {
    alert("ユーザーIDは英数半角文字にしてください。");
    return false;
  } else if (! document.update_user_form.password_expires.value.match(/^(2[0-9][0-9]{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/)) {
    alert("パスワード有効期限の書式が違います。");
    return false;
  } else if (! document.update_user_form.role.value.match(/admin|editor/)) {
    alert("存在しない役割です。");
    return false;
  }  else {
    return true;
  }
}

/**
 * Get the URL parameter value
 *
 * @param  name {string} パラメータのキー文字列
 * @return  url {url} 対象のURL文字列（任意）
 */
function getParam(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}



/*
 全角カナを半角カナに変換するJavaScriptツール
 https://gist.github.com/iwbjp/fe10220ee461e05eb4c17a509555fccb
 */
function inverseObject(obj, keyIsNumber) {
  return Object.keys(obj).reduceRight(function (ret, k) {
    return (ret[obj[k]] = keyIsNumber ? parseInt(k, 10) : k, ret);
  }, {});
}
var henkanObj = {
  'ア': 'ｱ',
  'イ': 'ｲ',
  'ウ': 'ｳ',
  'エ': 'ｴ',
  'オ': 'ｵ',
  'カ': 'ｶ',
  'キ': 'ｷ',
  'ク': 'ｸ',
  'ケ': 'ｹ',
  'コ': 'ｺ',
  'サ': 'ｻ',
  'シ': 'ｼ',
  'ス': 'ｽ',
  'セ': 'ｾ',
  'ソ': 'ｿ',
  'タ': 'ﾀ',
  'チ': 'ﾁ',
  'ツ': 'ﾂ',
  'テ': 'ﾃ',
  'ト': 'ﾄ',
  'ナ': 'ﾅ',
  'ニ': 'ﾆ',
  'ヌ': 'ﾇ',
  'ネ': 'ﾈ',
  'ノ': 'ﾉ',
  'ハ': 'ﾊ',
  'ヒ': 'ﾋ',
  'フ': 'ﾌ',
  'ヘ': 'ﾍ',
  'ホ': 'ﾎ',
  'マ': 'ﾏ',
  'ミ': 'ﾐ',
  'ム': 'ﾑ',
  'メ': 'ﾒ',
  'モ': 'ﾓ',
  'ヤ': 'ﾔ',
  'ユ': 'ﾕ',
  'ヨ': 'ﾖ',
  'ラ': 'ﾗ',
  'リ': 'ﾘ',
  'ル': 'ﾙ',
  'レ': 'ﾚ',
  'ロ': 'ﾛ',
  'ワ': 'ﾜ',
  'ヲ': 'ｦ',
  'ン': 'ﾝ',
  'ァ': 'ｧ',
  'ィ': 'ｨ',
  'ゥ': 'ｩ',
  'ェ': 'ｪ',
  'ォ': 'ｫ',
  'ッ': 'ｯ',
  'ャ': 'ｬ',
  'ュ': 'ｭ',
  'ョ': 'ｮ',
  'ガ': 'ｶﾞ',
  'ギ': 'ｷﾞ',
  'グ': 'ｸﾞ',
  'ゲ': 'ｹﾞ',
  'ゴ': 'ｺﾞ',
  'ザ': 'ｻﾞ',
  'ジ': 'ｼﾞ',
  'ズ': 'ｽﾞ',
  'ゼ': 'ｾﾞ',
  'ゾ': 'ｿﾞ',
  'ダ': 'ﾀﾞ',
  'ヂ': 'ﾁﾞ',
  'ヅ': 'ﾂﾞ',
  'デ': 'ﾃﾞ',
  'ド': 'ﾄﾞ',
  'バ': 'ﾊﾞ',
  'ビ': 'ﾋﾞ',
  'ブ': 'ﾌﾞ',
  'ベ': 'ﾍﾞ',
  'ボ': 'ﾎﾞ',
  'パ': 'ﾊﾟ',
  'ピ': 'ﾋﾟ',
  'プ': 'ﾌﾟ',
  'ペ': 'ﾍﾟ',
  'ポ': 'ﾎﾟ',
  'ヴ': 'ｳﾞ',
  'ヷ': 'ﾜﾞ',
  'ヺ': 'ｦﾞ',
  '。': '｡',
  '、': '､',
  'ー': 'ｰ',
  '「': '｢',
  '」': '｣',
  '・': '･',
  '゛': 'ﾞ',
  '゜': 'ﾟ'
};
var zen2han = function(str) {
  var reg = new RegExp('(' + Object.keys(henkanObj).join('|') + ')', 'g');
  return str.replace(reg, function(match) {
    return henkanObj[match];
  });
};
var han2zen = function(str) {
  var k = inverseObject(henkanObj);
  var reg = new RegExp('(' + Object.keys(k).join('|') + ')', 'g');
  return str.replace(reg, function(match) {
    return k[match];
  });
};
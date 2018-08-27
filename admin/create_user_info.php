<?php

define('BASE', basename(dirname(dirname(__FILE__))));
require_once dirname(dirname(__FILE__)) . "/functions.php";

# DB Connection
$mysqli = getConnection();

session_start();

if ($_SESSION['role'] != 'su') {
  echo '<script>alert("あなたに編集権限はありません。"); location.href = "/' . BASE . '/";</script>';
  return;
}

if (empty($_POST)) {
    print_header("ユーザ情報の登録", $_SESSION);
    print_mennu($_GET['page']);
?>
        <div class="login">
            <div class="reader-text">ユーザ情報の登録</div>
            <form action="/<?php echo BASE; ?>/admin/create_user_info.php" method="post" name="create_user_form">
                <p>新規にユーザーを作成します。
                    <br>企業IDおよびユーザーIDは20文字以内の半角英数文字およびピリオド「.」アンダーバー「_」でお願いします。
                    <br>パスワード有効期限は「YYYY-MM-DD」形式で入力してください（デフォルト値は1年後です）。</p>
                <table class="form-table">
                    <tr><th>企業ID：</th><td><input type="text" name="company_id" id="company_id"></td></tr>
                    <tr><th>ユーザーID：</th><td><input type="text" name="user_id" id="user_id"></td></tr>
                    <tr><th>パスワード有効期限：</th><td><input type="text" name="password_expires" id="password_expires" value="<?php echo gat_Ymd("+1 year"); ?>"></td></tr>
                    <tr><th>役割：</th><td><select name="role" id="role"><option selected>admin</option><option>editor</option></select></td></tr>
                    <tr><td colspan="2"><input type="submit" name="submit" value="登録" onclick="return check_create_user_info();"></td></tr>
                </table>
            </form>
        </div>
        <?php 
        print_javascript("others");
        print_footer();
        ?>
    </body>
</html>


<?php

} else {
    // テスト時に複数アカウントで動かすと、古い方で入れてしまうから。
    $_SESSION['auth'] = false;
    
    if ($_POST['company_id'] && $_POST['user_id'] && $_POST['password_expires'] && $_POST['role']) {
        // 初期パスワードはユーザーIDと同一。
        $hash_pass = password_hash($_POST['user_id'], PASSWORD_DEFAULT);
        try {
            ////////////////// user_id の重複チェック
            if (two_step_auth($mysqli, $_POST["company_id"], $_POST["user_id"])) {
                print_header("ユーザ情報の登録", null);
                echo '<script>alert("ユーザーIDが重複しています。"); location.href = "/' . BASE . '/admin/create_user_info.php";</script>';
                return;
            }
            $query = "INSERT INTO users_list (company_id,user_id,password,password_expires,role) VALUES (?,?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sssss", $_POST['company_id'], $_POST['user_id'], $hash_pass, $_POST['password_expires'], $_POST['role']);
            $rusult = $stmt->execute();
            $stmt->close();

            print_header("ユーザ情報の登録", null);
            if ($rusult) {
                echo '<script>alert("' . $_POST['user_id'] . ' さんを登録しました。"); location.href = "/' . BASE . '/";</script>';
            } else {
                echo '<script>alert("' . $_POST['user_id'] . ' さんの登録に失敗しました。"); location.href = "/' . BASE . '/";</script>';
            }
        } catch (PDOException $e) {
            print($e->getMessage());
            die();
        }
    } else {
        print_header("ユーザ情報の登録", null);
        echo "入力に誤りがあります。";
    }
    print_javascript("others");
    print_footer();
}
exit();
?>
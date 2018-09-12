<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";

# DB Connection
$mysqli = getConnection();

session_start();

if ($_SESSION['role'] == 'editor') {
  echo '<script>alert("あなたに編集権限はありません。"); location.href = "/' . BASE . '/";</script>';
  return;
}

if (empty($_POST)) {
    print_header("ユーザーの追加", $_SESSION);
    print_mennu($_GET['page']);
?>
        <div class="main_area">
            <h3>SBNewsの管理者または編集者の追加</h3>
            <form action="/<?php echo BASE; ?>/user_add.php" method="post" name="create_user_form">
                <?php
                if ($_SESSION['role'] == 'su') {
                    echo '<p class="ope_description">企業IDを指定して新規にユーザーを作成します。';
                    echo '企業IDおよびユーザーIDは20文字以内の半角英数文字およびピリオド「.」アンダーバー「_」でお願いします。';
                    echo 'パスワード有効期限は「YYYY-MM-DD」形式で入力してください（デフォルト値は1年後です）。</p>';
                } else if ($_SESSION['role'] == 'admin') {
                    echo '<p class="ope_description">新規にユーザーを作成します。';
                    echo 'ユーザーIDは20文字以内の半角英数文字およびピリオド「.」アンダーバー「_」でお願いします。';
                    echo 'パスワード有効期限は「YYYY-MM-DD」形式で入力してください（デフォルト値は1年後です）。</p>';
                } else {
                    die();
                }
                ?>
                <table class="conf_table">
                    <?php
                    if ($_SESSION['role'] == 'su') {
                        echo '<tr><th>企業ID：</th><td><input type="text" name="company_id" id="company_id"></td></tr>';
                    } else if ($_SESSION['role'] == 'admin') {
                        echo '<input type="hidden" name="company_id" id="company_id" value="' . $_SESSION['company_id'] . '"></td></tr>';
                    } else {
                        die();
                    }
                    ?>
                    <tr><th>ユーザーID：</th><td><input type="text" name="user_id" id="user_id"></td></tr>
                    <tr><th>パスワード有効期限：</th><td><input type="text" name="password_expires" id="password_expires" value="<?php echo gat_Ymd("+1 year"); ?>"></td></tr>
                    <tr><th>役割：</th><td><select name="role" id="role"><option value="admin" selected>管理者</option><option value="editor">編集者</option></select></td></tr>
                    <tr><td></td><td><input type="submit" name="submit" value="登録する" onclick="return check_create_user_info();"></td></tr>
                </table>
            </form>
        </div>
        <br><br>
        <div>
            <a href="/<?php echo BASE; ?>/?page=admin_menu"><button>戻る</button></a>
        </div>
        <?php 
        print_javascript("others");
        print_footer();
        ?>
    </body>
</html>


<?php

} else {
    if ($_POST['company_id'] && $_POST['user_id'] && $_POST['password_expires'] && $_POST['role']) {
        ////////////////// user_id の重複チェック
        if (two_step_auth($mysqli, $_POST["company_id"], $_POST["user_id"])) {
            print_header("ユーザ情報の登録", null);
            echo '<script>alert("ユーザーIDが重複しています。"); location.href = "/' . BASE . '/?page=admin_menu";</script>';
            exit;
        }
        if (! yyyymmdd_db($_POST['password_expires'])) {
            print_header("ユーザ情報の登録", null);
            echo '<script>alert("パスワード有効期限の日付フォーマットが違います。"); location.href = "/' . BASE . '/?page=admin_menu";</script>';
            exit;
        }

        // 初期パスワードはユーザーIDと同一。
        $hash_pass = password_hash($_POST['user_id'], PASSWORD_DEFAULT);
        try {
            $query = "INSERT INTO users_list (company_id,user_id,password,password_expires,role) VALUES (?,?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sssss", $_POST['company_id'], $_POST['user_id'], $hash_pass, $_POST['password_expires'], $_POST['role']);
            $rusult = $stmt->execute();
            $stmt->close();

            print_header("ユーザ情報の登録", null);
            if ($rusult) {
                echo '<script>alert("' . $_POST['user_id'] . ' さんを登録しました。"); location.href = "/' . BASE . '/?page=admin_menu";</script>';
            } else {
                echo '<script>alert("' . $_POST['user_id'] . ' さんの登録に失敗しました。"); location.href = "/' . BASE . '/?page=admin_menu";</script>';
            }
        } catch (PDOException $e) {
            print($e->getMessage());
            die();
        }
    } else {
        print_header("ユーザ情報の登録", null);
        echo '<script>alert("入力に誤りがあります。"); location.href = "/' . BASE . '/?page=admin_menu";</script>';
    }
    print_javascript("others");
    print_footer();
}
exit();
?>
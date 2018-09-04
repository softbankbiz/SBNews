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
    print_header("ユーザの修正／削除", $_SESSION);
    print_mennu($_GET['page']);

    if($_GET["task"] == "user_edit" && $_GET["target"] != '') {
        $user = get_user($mysqli, $_GET['company_id'], $_GET["target"]);
?>
            <div class="main_area">
                <h3>ユーザー情報の修正</h3>
                <form action="/<?php echo BASE; ?>/user_set.php" method="post" name="update_user_form">
                    <?php
                    if ($_SESSION['role'] == 'su' || $_SESSION['role'] == 'admin' ) {
                        echo '<p class="ope_description">企業IDおよびユーザーIDは変更できません。変更が必要な場合は、いったんユーザーを削除し、新規に作成してください。';
                        echo 'パスワード有効期限は「YYYY-MM-DD」形式で入力してください（デフォルト値は1年後です）。';
                        echo 'パスワードをリセットすると、ユーザーIDと同一の初期パスワードに設定され、初回アクセス時にパスワードの変更を求められます。</p>';
                    } else {
                        die();
                    }
                    ?>
                    <input type="hidden" name="company_id" id="company_id" value="<?php echo $_GET['company_id']; ?>">
                    <input type="hidden" name="user_id" id="user_id" value="<?php echo $user['user_id']; ?>">
                    <table class="conf_table">
                        <tr><th>企業ID：</th><td><input type="text"value="<?php echo $_GET['company_id']; ?>" disabled> <font color="red">※変更不可</font></td></tr>
                        <tr><th>ユーザーID：</th><td><input type="text" value="<?php echo $user['user_id']; ?>" disabled> <font color="red">※変更不可</font></td></tr>
                        <tr><th>パスワード有効期限：</th><td><input type="text" name="password_expires" id="password_expires" value="<?php echo explode(' ', $user['password_expires'])[0]; ?>"></td></tr>
                        <tr><th>役割：</th>
                        <td>
                            <select name="role" id="role">
                                <?php
                                if ($user['role'] == 'admin') {
                                    echo '<option value="admin" selected>管理者</option>';
                                    echo '<option value="editor">編集者</option>';
                                } else {
                                    echo '<option value="admin">管理者</option>';
                                    echo '<option value="editor" selected>編集者</option>';
                                }
                                ?>
                            </select>
                        </td></tr>
                        <tr><th>パスワードリセット</th>
                            <td>
                                <input type="radio" name="password_reset" value="yes"> する　　　
                                <input type="radio" name="password_reset" value="no" checked> しない
                            </td></tr>
                        <tr><td></td><td><input type="submit" name="submit" value="修正する" onclick="return check_update_user_info();"></td></tr>
                    </table>
                </form>
                <br><br>
                <div>
                    <a href="/<?php echo BASE; ?>/?page=admin_menu"><button>戻る</button></a>
                </div>
            </div>

<?php
    } else if($_GET["task"] == "user_delete" && $_GET["target"] && $_GET["company_id"]) {
        try {
            ////////////////// user_id の重複チェック
            if (! two_step_auth($mysqli, $_GET["company_id"], $_GET["target"])) {
                print_header("ユーザの修正／削除", null);
                echo '<script>alert("そのユーザーIDは存在しません。"); location.href = "/' . BASE . '/?page=admin_menu";</script>';
                return;
            }
            $query = "DELETE FROM users_list WHERE company_id = ? AND user_id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ss", $_GET["company_id"], $_GET["target"]);
            $rusult = $stmt->execute();
            $stmt->close();

            print_header("ユーザの修正／削除", null);
            if ($rusult) {
                echo '<script>alert("' . $_GET["target"] . ' さんを削除しました。"); location.href = "/' . BASE . '/?page=admin_menu";</script>';
            } else {
                echo '<script>alert("' . $_GET["target"] . ' さんの削除に失敗しました。"); location.href = "/' . BASE . '/?page=admin_menu";</script>';
            }
        } catch (PDOException $e) {
            print($e->getMessage());
            die();
        }
    }
} else {
    if ($_POST['company_id'] && $_POST['user_id'] && $_POST['password_expires'] && $_POST['role'] && $_POST['password_reset']) {
        try {
            ////////////////// user_id の重複チェック
            if (! two_step_auth($mysqli, $_POST["company_id"], $_POST["user_id"])) {
                print_header("ユーザの修正／削除", null);
                echo '<script>alert("そのユーザーIDは存在しません。"); location.href = "/' . BASE . '/?page=admin_menu";</script>';
                return;
            }
            if ($_POST['password_reset'] == 'yes') {
                // パスワードをユーザーIDにリセット。
                $hash_pass = password_hash($_POST['user_id'], PASSWORD_DEFAULT);
                $query = "UPDATE users_list SET password = ?, password_expires = ?, role = ? WHERE company_id = ? AND user_id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("sssss", $hash_pass, $_POST['password_expires'], $_POST['role'], $_POST['company_id'], $_POST['user_id']);
            } else if ($_POST['password_reset'] == 'no') {
                $query = "UPDATE users_list SET password_expires = ?, role = ? WHERE company_id = ? AND  user_id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("ssss", $_POST['password_expires'], $_POST['role'], $_POST['company_id'], $_POST['user_id']);
            }
            $rusult = $stmt->execute();
            $stmt->close();

            print_header("ユーザの修正／削除", null);
            if ($rusult) {
                echo '<script>alert("' . $_POST['user_id'] . ' さんの情報を修正しました。"); location.href = "/' . BASE . '/?page=admin_menu";</script>';
            } else {
                echo '<script>alert("' . $_POST['user_id'] . ' さんの情報修正に失敗しました。"); location.href = "/' . BASE . '/?page=admin_menu";</script>';
            }
        } catch (PDOException $e) {
            print($e->getMessage());
            die();
        }
    } else {
        print_header("ユーザの修正／削除", null);
        echo "入力に誤りがあります。";
    }
}
print_javascript("others");
print_footer();
exit();
?>
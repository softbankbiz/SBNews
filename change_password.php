<?php

define('BASE', basename(dirname(__FILE__)));
require_once dirname(__FILE__) . "/functions.php";

# DB Connection
$mysqli = getConnection();

if (! empty($_POST)) {
    if ($_POST['company_id'] && $_POST['user_id'] && $_POST['password_01'] && $_POST['password_02']) {
    	if ($_POST['password_01'] != $_POST['password_02']) {
            echo '<script>alert("パスワードが一致しません。"); location.href = "/' . BASE . '/";</script>';
    		return;
    	} else if (mb_strwidth($_POST['password_01']) > 12 || mb_strwidth($_POST['password_02']) > 12) {
            echo '<script>alert("パスワードが不正です。"); location.href = "/' . BASE . '/";</script>';
            return;
        } else if (! two_step_auth($mysqli, $_POST["company_id"], $_POST["user_id"])) {
            echo '<script>alert("ユーザーとして認証できません。"); location.href = "/' . BASE . '/";</script>';
            return;
        }
    	$hash_pass = password_hash($_POST['password_01'], PASSWORD_DEFAULT);
    	try {
            $query = "UPDATE users_list SET password = ? WHERE company_id = ? AND user_id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sss", $hash_pass, $_POST['company_id'], $_POST['user_id']);
            $rusult = $stmt->execute();

            print_header("パスワード変更ページ", null);
            if ($rusult) {
                echo '<script>alert("パスワードを変更しました。"); location.href = "/' . BASE . '/?company_id=' . $_POST['company_id'] . '&user_id=' . $_POST['user_id'] . '";</script>';
            } else {
                echo '<script>alert("パスワードの変更に失敗しました"); location.href = "/' . BASE . '/";</script>';
            }
            print_footer();
            $stmt->close();
        } catch (PDOException $e) {
            print($e->getMessage());
            die();
        }
    }
}
exit();
?>
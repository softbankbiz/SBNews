<?php
    session_start();
    define('BASE', basename(dirname(__FILE__)));
?>
<!DOCTYPE html>
<html lang="ja">
<head><title>ログアウト</title></head>
<body>

<?php

    $_SESSION = array();

    if (isset($_COOKIE["PHPSESSID"])) {
        setcookie("PHPSESSID", '', time() - 1800, '/');
    }

    session_destroy();
?>
<script>
alert("ログアウトしました。");
location.href = "/<?php echo BASE; ?>/";
</script>

</body>
</html>
<?php
session_start();
require('dbconnect.php');

// ★ポイント1★
if (!empty($_POST)) {
    if (($_POST['mail'] != '') && ($_POST['password'] != '')) {
        $login = $db->prepare('SELECT * FROM members WHERE mail=?');
        $login->execute(array($_POST['mail']));
        $member=$login->fetch();

	// 認証
        if ($member != false && password_verify($_POST['password'],$member['password'])) {
            $_SESSION['id'] = $member['id'];
            $_SESSION['time'] =time();
            header('Location: post.php');

            exit();
        } else {
            $error['login']='failed';

        }
    } else {
        $error['login'] ='blank';

    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<link rel="stylesheet" href="style.css">
<head>
	<title>ログイン画面</title>

	<style>
		.error { color: red;font-size:0.8em; }
	</style>
</head>
<body>
	<h1>ログイン画面</h1>
	<form action='' method="post">

		<label>
			email
			<input type="text" name="mail" style="width:200px"
			value="<?php echo htmlspecialchars($_POST['mail']??"", ENT_QUOTES); ?>">
			<?php if (isset($error['login']) && ($error['login'] =='blank')): ?>
			<p class="error">メールとパスワードを入力してください</p>
			<?php endif; ?>

			<?php if (isset($error['login']) && $error['login'] =='failed'): ?>
			<p class="error">メールかパスワードが間違っています</p>
			<?php endif; ?>
		</label>
		<br />
		<label>
			パスワード
			<input type="password" name="password" style="width:200px"
			value="<?php echo htmlspecialchars($_POST['password']??"", ENT_QUOTES); ?>">
		</label>

		<div class="login2">
			<input type="submit" value="ログインする" class="button">
		</div>

	</form>
	<br />
	<div style="text-align: center;">
    <a href="signup_mail.php">ユーザ登録する</a>
	</div>

</body>
</html>

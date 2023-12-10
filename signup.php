<link rel="stylesheet" href="style.css">
<?php
session_start();
require('dbconnect.php');
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];

header('X-FRAME-OPTIONS: SAMEORIGIN');

$errors = array();

if (empty($_GET)) {
  header("Location: signup_mail.php");

  exit();
} else {

  $urltoken = isset($_GET["urltoken"]) ? $_GET["urltoken"] : NULL;

  if ($urltoken == '') {
    $errors['urltoken'] = "トークンがありません。";
  } else {
    try {
      $sql = "SELECT mail FROM pre_users WHERE urltoken=(:urltoken) AND flag =0 AND date > now() - interval 24 hour";
      $stm = $db->prepare($sql);
      $stm->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
      $stm->execute();

      $row_count = $stm->rowCount();

      if ($row_count == 1) {

        $mail_array = $stm->fetch();
        $mail = $mail_array["mail"];
        $_SESSION['mail'] = $mail;
      } else {


        $errors['urltoken_timeover'] = "このURLはご利用できません。利用期限が過ぎたかurlが間違えている可能性がございます。もう一度やり直して下さい。";
      }
      $stm = null;
    } catch (PDOException $e) {
      print('Error:' . $e->getMessage());
      die();
    }
  }
}

if (isset($_POST['btn_confirm'])) {
  if (empty($_POST)) {
    header("Location: signup_mail.php");

    exit();
  } else {
    $name = isset($_POST['name']) ? $_POST['name'] : NULL;
    $password = isset($_POST['password']) ? $_POST['password'] : NULL;

    $_SESSION['name'] = $name;
    $_SESSION['password'] = $password;

    if ($password == ''):
      $errors['password'] = "パスワードが入力されていません。";
    else:
      $password_hide = str_repeat('*', strlen($password));
    endif;

    if ($name == ''):
      $errors['name'] = "氏名が入力されていません。";
    endif;
  }
}

if (isset($_POST['btn_submit'])) {
  $password_hash = password_hash($_SESSION['password'], PASSWORD_DEFAULT);

  try {
    $sql = "INSERT INTO members (name,password,mail,status,created_at,updated_at) VALUES (:name,:password_hash,:mail,1,now(),now())";
    $stm = $db->prepare($sql);
    $stm->bindValue(':name', $_SESSION['name'], PDO::PARAM_STR);
    $stm->bindValue(':mail', $_SESSION['mail'], PDO::PARAM_STR);
    $stm->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
    $stm->execute();


    $sql = "UPDATE pre_users SET flag=1 WHERE mail=:mail";
    $stm = $db->prepare($sql);

    mb_language('ja');
    mb_internal_encoding('UTF-8');

    $to = $mail;
    $subject = "本登録";
    $message = "この度はご登録いただきありがとうございます。
               本登録致しました。";
    $headers = "From: from@example.com";

    mb_send_mail($to, $subject, $message, $headers);
    if (mb_send_mail($to, $subject, $message, $headers)) {
      //セッション変数を全て解除
      $_SESSION = array();
      //クッキーの削除
      if (isset($_COOKIE["PHPSESSID"])) {
        setcookie("PHPSESSID", '', time() - 1800, '/');
      } else {
        $errors['mail_error'] = "メールの送信に失敗しました。";
      }
      session_destroy();
    }
    $stm = null;

    $_SESSION = array();

    if (isset($_COOKIE["PHPSESSID"])) {
      setcookie("PHPSESSID", '', time() - 1800, '/');
    }


  } catch (PDOException $e) {

    $db->rollBack();
    $errors['error'] = "もう一度やりなおして下さい。";
    print('Error:' . $e->getMessage());
  }
}



?>

<h1>会員登録画面</h1>


<!-- page_3 完了画面-->
<?php if (isset($_POST['btn_submit']) && count($errors) === 0): ?>
   <?PHP if (empty($error)) {
        $_SESSION['join'] = $_POST;
        header('Location: login.php');
        exit();
    }
    ?>

  本登録されました。

  <!-- page_2 確認画面-->
<?php elseif (isset($_POST['btn_confirm']) && count($errors) === 0): ?>
  <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>?urltoken=<?php print $urltoken; ?>" method="post">
    <p>メールアドレス：
      <?= htmlspecialchars($_SESSION['mail'], ENT_QUOTES) ?>
    </p>
    <p>パスワード：
      <?= $password_hide ?>
    </p>
    <p>氏名：
      <?= htmlspecialchars($name, ENT_QUOTES) ?>
    </p>

    <input type="submit" name="btn_back" value="戻る">
    <input type="hidden" name="token" value="<?= $_POST['token'] ?>">
    <input type="submit" name="btn_submit" value="登録する">
  </form>

<?php else: ?>
  <!-- page_1 登録画面 -->
  <?php if (count($errors) > 0): ?>
    <?php
    foreach ($errors as $value) {
      echo "<p class='error'>" . $value . "</p>";
    }
    ?>

  <?php endif; ?>
  <?php if (!isset($errors['urltoken_timeover'])): ?>
    <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>?urltoken=<?php print $urltoken; ?>" method="post">
      <p>メールアドレス：
        <?= htmlspecialchars($mail, ENT_QUOTES, 'UTF-8') ?>
      </p>
      <p>パスワード：<input type="password" name="password"></p>
      <p>氏名：<input type="text" name="name" value="<?php if (!empty($_SESSION['name'])) {
        echo $_SESSION['name'];
      } ?>"></p>
      <input type="hidden" name="token" value="<?= $token ?>">
      <input type="submit" name="btn_confirm" value="確認する">
    </form>
  <?php endif ?>
<?php endif; ?>

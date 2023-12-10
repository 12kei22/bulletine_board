<link rel="stylesheet" href="style.css">
<?php
session_start();
require('dbconnect.php');
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];

header('X-FRAME-OPTIONS: SAMEORIGIN');

$errors = array();



if (isset($_POST['submit'])) {
    if(empty($_POST['mail'])){
      $errors['mail'] = 'メールアドレスが未入力です。';
    }else{
      $mail = isset($_POST['mail']) ? $_POST['mail'] : NULL;

      if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $mail)){
        $errors['mail_check'] = "メールアドレスの形式が正しくありません。";
      }

      $sql = "SELECT id FROM pre_users WHERE mail=:mail";
      $stm = $db->prepare($sql);
      $stm->bindValue(':mail', $mail, PDO::PARAM_STR);

      $stm->execute();
      $result = $stm->fetch(PDO::FETCH_ASSOC);

      if(isset($result["id"])){
         $errors['user_check'] = "このメールアドレスはすでに利用されております。";
      }
 }

 if (count($errors) === 0){
   $urltoken = hash('sha256',uniqid(rand(),1));
   $url = "http://localhost/signup.php?urltoken=".$urltoken;

   try{

      $sql = "INSERT INTO pre_users (urltoken, mail, date, flag) VALUES (:urltoken, :mail, now(), '0')";
      $stm = $db->prepare($sql);
      $stm->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
      $stm->bindValue(':mail', $mail, PDO::PARAM_STR);
      $stm->execute();
      $pdo = null;
      $message = "メールをお送りしました。24時間以内にメールに記載されたURLからご登録下さい。";
   }catch (PDOException $e){
      print('Error:'.$e->getMessage());
      die();
   }

   mb_language('ja');
   mb_internal_encoding('UTF-8');

   $to = $mail;
   $subject = "仮会員登録";
   $message = "この度はご登録いただきありがとうございます。
               24時間以内に下記のURLからご登録下さい。";
   $headers = "From: from@example.com";
   mb_send_mail($to, $subject, $message, $headers);
       if(mb_send_mail($to, $subject, $message, $headers)){
           //セッション変数を全て解除
           $_SESSION = array();
           //クッキーの削除
           if (isset($_COOKIE["PHPSESSID"])) {
               setcookie("PHPSESSID", '', time() - 1800, '/');
           }
           //セッションを破棄する
           session_destroy();

       }

  }
}

?>
<h1>仮会員登録画面</h1>
<?php if (isset($_POST['submit']) && count($errors) === 0): ?>
   <!-- 登録完了画面　-->
  <div class="regist">
      <p><?=$message?></p>
      <p class="regist">このURLが記載されたメールが届きます。</p>
      <a href="<?=$url?>"><?=$url?></a>
  </div>
<?php else: ?>
<!-- 登録画面　-->
   <?php if(count($errors) > 0): ?>
      <?php
      foreach($errors as $value){
          echo "<p class='error'>".$value."</p>";
      }
      ?>
   <?php endif; ?>
   <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">

      <p>メールアドレス:<input type="text" name="mail" size="50" value="<?php if( !empty($_POST['mail']) ){ echo $_POST['mail']; } ?>"></p>
      <input type="hidden" name="token" value="<?=$token?>">
      <input type="submit" name="submit" value="送信">
  </form>
<?php endif; ?>

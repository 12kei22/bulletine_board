<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  try {
  $db = new PDO ('mysql:dbname=bulletin_board;host=localhost; charset=utf8', 'root','root');
  }  catch (PDOException $e) {
  echo 'db接続エラー' . $e->getMessage();
  }
  ?>


</body>
</html>

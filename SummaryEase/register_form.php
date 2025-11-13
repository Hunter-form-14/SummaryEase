<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>Register form</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="page">
    <img src="logo3.png" alt="Logo" class="logo">
      <h1>新規登録</h1>
      <h3>ユーザー名とパスワードを入力してください</h3>

      <form action="register_submit.php" method="get">
        <p class="basic">ユーザー名</p>  
        <p class="bottom"><input type="text" name="username" required></p>

        <p class="basic">パスワード</p>  
        <p class="bottom"><input type="password" name="password" required></p>

        <p class="basic"><input type="submit" value="送信" class="button2"></p>
      </form>
    </div>
  </body>
</html>

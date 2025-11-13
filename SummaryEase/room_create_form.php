<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>Room Create Form</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="page">
    <img src="logo3.png" alt="Logo" class="logo">
      <h1>ルームを作成</h1>
      <h3>ルーム名とあいことばを入力してください</h3>

      <form action="room_create_submit.php" method="post">
        <!-- ルーム名入力 -->
        <p class="basic">ルーム名</p>  
        <p class="bottom">
          <input type="text" name="roomname" required>
        </p>

        <!-- パスワード入力 -->
        <p class="basic">あいことば</p>  
        <p class="bottom">
          <input type="password" name="password" required>
        </p>

        <!-- 作成ボタン -->
        <p class="basic">
          <input type="submit" value="作成" class="button2">
        </p>
      </form>
    </div>
  </body>
</html>
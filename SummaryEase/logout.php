<?php
  session_start();

  $_SESSION = array();
  session_destroy();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Logout</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="page">
      <h2>ログアウトしました</h2>
      <p style="margin-left: 7%;"><a href="index.html">ログインページに戻る</a></p>
    </div>
  </body>
</html>

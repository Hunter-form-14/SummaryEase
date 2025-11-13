<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Top Page</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="page">
  <img src="logo3.png" alt="Logo" class="logo">
    <h1>リアルタイム議事録</h1>
    <!-- ログアウトリンク -->
    <p class="logout"><a href="logout.php">ログアウト</a></p>
  
    <?php if (isset($_SESSION["username"])): ?>
      <p class="basic"><?= htmlspecialchars($_SESSION["username"]) ?> さん、ようこそ！！</p>
      <p class="basic">既存のルームでチャットを始めるか、新しくルームを作成しましょう！</p><br>
    <?php endif; ?>

    <div class="button-container">
    <!-- ルームへ参加 -->
    <form action="room_join_form.php" method="get">
      <p class="basic"><input type="submit" value="ルームに参加する" class="button"></p>
    </form>

    <!-- ルームを作成 -->
    <form action="room_create_form.php" method="get">
      <p class="basic"><input type="submit" value="ルームを作成する" class="button"></p>
    </form>
    </div>
  </div>
</body>
</html>

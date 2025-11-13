<?php
session_start();
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; // エラーメッセージ取得
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ルーム参加フォーム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page">
<img src="logo3.png" alt="Logo" class="logo">
    <h1>ルームに参加</h1>
    <h3>ルーム名とあいことばを入力してください</h3>

    <!-- エラーメッセージの表示 -->
    <?php if (!empty($error)): ?>
        <p style="color: red; font-weight: bold;"><?= $error ?></p>
    <?php endif; ?>

    <form action="room_join_submit.php" method="post">
        <p class="basic">ルーム名</p>
        <p class="bottom"><input type="text" name="roomname" required></p>

        <p class="basic">あいことば</p>
        <p class="bottom"><input type="password" name="password" required></p>

        <p class="basic"><input type="submit" value="参加" class="button2"></p>
    </form>
</div>
</body>
</html>
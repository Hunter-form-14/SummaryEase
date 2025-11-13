<?php
session_start();

if (isset($_GET["username"]) && isset($_GET["password"])) {
  $username = $_GET["username"];
  $password = $_GET["password"];

  $pdo = new PDO("sqlite:chat.sqlite");
  $st = $pdo->prepare("SELECT * FROM user WHERE username = ?");
  $st->execute([$username]);
  $user_on_db = $st->fetch();

  if (!$user_on_db) {
    // ユーザーが存在しない
    $error = "指定されたユーザーが存在しません。";
  } elseif ($password == $user_on_db["password"]) { // パスワード一致
    $_SESSION["username"] = $username;
    header("Location: top_page.php"); // トップページにリダイレクト
    exit;
  } else { // パスワード不一致
    $error = "パスワードが違います。";
  }

  // エラーがあればログインフォームにリダイレクト
  header("Location: login_form.php?error=" . urlencode($error));
  exit;
}
?>

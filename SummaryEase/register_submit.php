<?php
session_start();

if (isset($_GET["username"]) && isset($_GET["password"])) {
    $username = trim($_GET["username"]); // 空白を除去
    $password = trim($_GET["password"]); // 空白を除去

    // SQLiteデータベース接続
    $pdo = new PDO("sqlite:chat.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    // ユーザーの存在チェック
    $st = $pdo->prepare("SELECT * FROM user WHERE username = ?");
    $st->execute(array($username));
    $user = $st->fetch();

    if ($user) {
        // ユーザーがすでに存在する場合
        echo "そのユーザー名はすでに存在します。";
    } else {
        // 新規ユーザー登録
        $st = $pdo->prepare("INSERT INTO user(username, password) VALUES (?, ?)");
        $st->execute(array($username, $password));

        // セッションにユーザー名を保存
        $_SESSION["username"] = $username;
        
        // chat.phpにリダイレクト
        header("Location: top_page.php");
        exit;
    }
}
?>
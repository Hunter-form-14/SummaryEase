<?php
session_start();

// ユーザーがログインしていなければ登録フォームへリダイレクト
if (!isset($_SESSION["username"])) {
    header("Location: register_form.php");
    exit;
}

// POSTデータのroomnameとpasswordがセットされていなければエラーメッセージを表示
if (!isset($_POST["roomname"]) || trim($_POST["roomname"]) === '' || 
    !isset($_POST["password"]) || trim($_POST["password"]) === '') {
    echo "ルーム名とパスワードを入力してください。";
    exit;
}

$roomname = trim($_POST["roomname"]); // 入力されたルーム名を取得
$password = trim($_POST["password"]); // 入力されたパスワードを取得

try {
    // SQLiteデータベース接続
    $pdo = new PDO("sqlite:chat.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL: roomテーブルにデータを挿入
    $st = $pdo->prepare("INSERT INTO room (roomname, password) VALUES (?, ?)");
    $st->execute([$roomname, $password]);

    // 挿入したルームのIDを取得
    $room_id = $pdo->lastInsertId();

    // ルームIDをセッションに保存
    $_SESSION['room_id'] = $room_id;


    // 成功したらチャットページにリダイレクト
    header("Location: chat.html");
    exit;

} catch (PDOException $e) {
    // エラー発生時: メッセージをログに記録し、ユーザーには一般的なエラーを表示
    error_log("データベースエラー: " . $e->getMessage());
    echo "ルームの作成中にエラーが発生しました。もう一度やり直してください。";
    exit;
}
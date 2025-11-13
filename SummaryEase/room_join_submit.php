<?php
session_start();

// 必要なデータが POST で送信されているか確認
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['roomname']) && isset($_POST['password'])) {
    $roomname = trim($_POST['roomname']);
    $password = trim($_POST['password']);

    try {
        // SQLite データベース接続
        $pdo = new PDO("sqlite:chat.sqlite");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ルーム名とパスワードで検索
        $stmt = $pdo->prepare("SELECT id, roomname, password FROM room WHERE roomname = ? AND password = ?");
        $stmt->execute([$roomname, $password]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        var_dump($room);  // 取得した結果を確認
        var_dump($roomname);  // POSTされたroomnameを確認
        var_dump($password);
        
        // ルームが存在するかどうかを確認
        if ($room) {
            $_SESSION['room_id'] = $room['id'];  // ルームIDをセッションに保存
            header("Location: chat.html");  // チャットページへ移動
            exit;
        } else {
            $error = 'ルーム名またはあいことばが間違っています。';
        }
    } catch (PDOException $e) {
        // データベースエラー時の処理
        error_log("データベースエラー: " . $e->getMessage());
        $error = '内部エラーが発生しました。もう一度お試しください。';
    }

    // エラーがある場合はフォームにリダイレクト
    if (isset($error)) {
        header("Location: room_join_form.php?error=" . urlencode($error));
        exit;
    }
} else {
    // 必要なデータが送信されていない場合
    header("Location: room_join_form.php?error=" . urlencode("無効なリクエストです。"));
    exit;
}
?>
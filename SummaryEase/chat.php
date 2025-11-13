<?php
session_start();

require 'vendor/autoload.php';

use Dotenv\Dotenv;

// .envファイルを読み込む
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 必須環境変数のチェック
$dotenv->required(['OPENAI_API_KEY', 'DB_PATH'])->notEmpty();

// データベース接続
try {
    $db = new PDO("sqlite:" . $_ENV['DB_PATH']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'データベース接続に失敗しました: ' . $e->getMessage()]));
}

// OpenAI APIキーを環境変数から取得
define('OPENAI_API_KEY', $_ENV['OPENAI_API_KEY']);

// ユーザー情報
$username = $_SESSION['username'] ?? null;
$room_id = $_SESSION['room_id'] ?? null;

// セッション情報の検証
if (!$username || !$room_id) {
    die(json_encode(['success' => false, 'error' => 'セッション情報が不足しています']));
}

// ルーム情報を取得
$stmt = $db->prepare("SELECT id, roomname FROM room WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

// 要約を取得
$stmt = $db->prepare("SELECT summary_text FROM summaries WHERE room_id = ?");
$stmt->execute([$room_id]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

// AJAX処理用のエンドポイント
header('Content-Type: application/json');
$postData = json_decode(file_get_contents('php://input'), true);

// アクションが指定されていない場合
if (!isset($postData['action'])) {
    echo json_encode(['success' => false, 'error' => 'No action specified.']);
    exit;
}

// アクションに応じて処理を実行
switch ($postData['action']) {
    case 'get_username':
        echo json_encode(['success' => true, 'username' => $username]);
        break;

    case 'get_roomname':
        echo json_encode(['success' => true, 'roomname' => $room['roomname'] ?? '']);
        break;

    case 'get_summary':
        echo json_encode(['success' => true, 'summary' => $summary['summary_text'] ?? '']);
        break;

    case 'send_message':
        if (!empty($postData['message']) && !empty($postData['username'])) {
            $message = [
                'username' => htmlspecialchars($postData['username'], ENT_QUOTES, 'UTF-8'),
                'text' => htmlspecialchars($postData['message'], ENT_QUOTES, 'UTF-8')
            ];

            // メッセージをデータベースに保存（プリペアドステートメント使用）
            $stmt = $db->prepare("INSERT INTO messages (room_id, username, message_text) VALUES (?, ?, ?)");
            $stmt->execute([$room_id, $message['username'], $message['text']]);

            if (!isset($_SESSION['messages'])) {
                $_SESSION['messages'] = [];
            }
            $_SESSION['messages'][] = $message;
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid message or username.']);
        }
        break;

    case 'get_update':
        try {
            // 最新のメッセージを取得
            $stmt = $db->prepare("SELECT username, message_text FROM messages WHERE room_id = ? ORDER BY created_at ASC");
            $stmt->execute([$room_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // JSON形式でレスポンスを返す
            echo json_encode(['success' => true, 'messages' => $messages]);
        } catch (PDOException $e) {
            // エラーハンドリング
            echo json_encode(['success' => false, 'error' => 'メッセージの取得中にエラーが発生しました: ' . $e->getMessage()]);
        }
        break;

    case 'update_summary':
        if (!empty($_SESSION['messages'] ?? [])) {
            try {
                $conversation = implode("\n", array_map(function ($message) {
                    return htmlspecialchars($message['text'], ENT_QUOTES, 'UTF-8');
                }, $_SESSION['messages']));
                
                $summary = generateSummary($conversation, $db);
                if ($summary === false) {
                    throw new Exception('要約の生成に失敗しました。');
                }
                $_SESSION['summary'] = $summary;

                // 要約をデータベースに保存または更新
                $stmt = $db->prepare("INSERT INTO summaries (room_id, summary_text, created_at) 
                                        VALUES (?, ?, datetime('now')) 
                                        ON CONFLICT(room_id) 
                                        DO UPDATE SET summary_text = ?, created_at = datetime('now')");
                $stmt->execute([$room_id, $summary, $summary]);

                echo json_encode(['success' => true, 'summary' => $summary]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => '要約するメッセージがありません。']);
        }
        break;

    case 'reset_summary':
        try {
            // データベースからsummaryを削除
            $stmt = $db->prepare("DELETE FROM summaries WHERE room_id = ?");
            $stmt->execute([$room_id]);

            $_SESSION['summary'] = '';
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error deleting summary: ' . $e->getMessage()]);
        }
        break;

    case 'reset_conversation':
        try {
            // データベースからmessagesを削除
            $stmt = $db->prepare("DELETE FROM messages WHERE room_id = ?");
            $stmt->execute([$room_id]);

            $_SESSION['messages'] = [];
            $_SESSION['summary'] = '';
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error deleting conversation: ' . $e->getMessage()]);
        }
        break;

    case 'add_item':
        // 要約項目を追加
        if (!empty($postData['item'])) {
            $item = htmlspecialchars($postData['item'], ENT_QUOTES, 'UTF-8');
            if (!isset($_SESSION['items'])) {
                $_SESSION['items'] = [];
            }
            $_SESSION['items'][] = $item;
            echo json_encode(['success' => true, 'items' => $_SESSION['items']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid item.']);
        }
        break;

    case 'delete_item':
        // 要約項目を削除
        if (!empty($postData['item'])) {
            $itemToDelete = htmlspecialchars($postData['item'], ENT_QUOTES, 'UTF-8');
            $_SESSION['items'] = array_values(array_filter(
                $_SESSION['items'] ?? [],
                function ($item) use ($itemToDelete) {
                    return $item !== $itemToDelete;
                }
            ));
            echo json_encode(['success' => true, 'items' => $_SESSION['items']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid item.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
        break;
}

// 要約を生成
function generateSummary($conversation, $db)
{
    $url = 'https://api.openai.com/v1/chat/completions';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ];

    $itemsString = implode(", ", $_SESSION['items'] ?? []);

    $data = [
        'model' => 'gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => 'あなたは高度な要約を専門とするアシスタントです。必要な情報を抜き出し、要点を簡潔にまとめてください。'],
            ['role' => 'user', 'content' => "以下の会話を\n\n$itemsString\n\nごとに箇条書きで要約してください。\n$conversation"]
        ],
        'max_tokens' => 3000,
        'temperature' => 0.0,
        'top_p' => 1.0,
        'frequency_penalty' => 0.2,
        'presence_penalty' => 0.0
    ];

    $options = [
        'http' => [
            'header' => implode("\r\n", $headers),
            'method' => 'POST',
            'content' => json_encode($data),
            'timeout' => 30
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response === FALSE) {
        return 'エラーが発生しました。';
    }

    $responseData = json_decode($response, true);
    if (isset($responseData['choices'][0]['message']['content'])) {
        return $responseData['choices'][0]['message']['content'];
    } else {
        return '要約に失敗しました。';
    }
}

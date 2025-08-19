<?php
// PHPエラーを画面に表示（開発時のみ出すときは消すかコメントアウト）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース接続情報
$servername = "localhost";
$dbname = "iot_data";
$username = "root";
$password = ""; // パスワードを設定している場合は、ここに記述

// レスポンスヘッダーをJSONに設定
//サーバーからの応答がJSONデータであることをブラウザに伝えてる
header('Content-Type: application/json; charset=utf-8');

// データベースに接続。いつものpdo
try {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// setAttributeで属性をエラーモード(第１引数)、値を例外（第２引数）と設定することで
// エラー発生時にPDOExceptionがスローされる
//phpにはExceptionクラスがいっぱいある。PDOExceptionはそのうちのひとつ
} catch (PDOException $e) {
    // 接続失敗時はエラーメッセージをJSON形式で返す
    http_response_code(500); // サーバーエラーのステータスコードを送信
    echo json_encode(["error" => "データベース接続エラー: " . $e->getMessage()]);
    exit;
}

try {
    // 最新10件の温度データを取得するSQL文
    // ORDER BY created_at DESC で新しい順に並べ、LIMIT 10 で10件に制限
    //ascで昇順に表示(新しい順)
    // 修正後: descで古い順に10件取得
$query = "SELECT temp, device_id, created_at FROM temperatures ORDER BY created_at DESC LIMIT 10";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 取得したデータをJSON形式に変換して出力
    echo json_encode($data);

} catch (PDOException $e) {
    // データ取得失敗時はエラーメッセージをJSON形式で返す
    http_response_code(500);
    echo json_encode(["error" => "データ取得エラー: " . $e->getMessage()]);
}

// 接続を閉じる
$stmt = null;
$pdo = null;

?>

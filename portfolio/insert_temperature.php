<?php
// PHPエラーを画面に表示（開発時のみ推奨）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース接続情報
$servername = "localhost";
$dbname = "iot_data"; // 作成したデータベース名
$username = "root";
$password = ""; // パスワードを設定している場合は、ここに記述してください

// データベースに接続 (PDOを使用)
//try-catch文。最後がexitではなくdieなのはphpの慣習らしい？
//エラーが起きてるから意図的に「死なす(止める)」みたいな感じらしい
try {
    // DSN (Data Source Name) を定義
    //授業ではdsn定義はしなかったが最初にDB情報の変数を定義して
    //ここでDBハンドラーとしてdbhやdsnと変数定義しておくと
    //後々DB変更があったときにミスが出にくいというベストプラクティスらしいので書いた
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);

    // エラーモードを例外に設定することで、エラー発生時にPDOExceptionがスローされる
    //phpにはExceptionクラスがいっぱいある。PDOExceptionはそのうちのひとつ
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // 接続に失敗した場合、エラーメッセージを表示して処理を停止
    die("データベース接続エラー: " . $e->getMessage());
}

// POSTメソッドで送信された温度データとデバイスIDを受け取る
// 'temperature'と'device_id'というキーでデータが送られてくることを想定
//＄postで受け取るデータはすべて文字列なので(float)や(int)で一時的に型変換する
$posted_temperature = isset($_POST['temperature']) ? (float)$_POST['temperature'] : null;
$posted_device_id = isset($_POST['device_id']) ? (int)$_POST['device_id'] : null;

// 温度データとデバイスIDが正しく受け取れたかを確認
if ($posted_temperature === null || $posted_device_id === null) {
    // データが受け取れなかった場合、エラーメッセージを返す
    die("エラー: 温度データまたはデバイスIDが受信されませんでした。");
}

// データをデータベースに挿入するSQL文
// ? は後から値をバインドするためのプレースホルダー
$query = "INSERT INTO temperatures (temp, device_id) VALUES (?, ?)";

try {
    // プリペアドステートメントを準備
    // SQLインジェクション攻撃を防ぐためのセキュリティ対策
    $stmt = $pdo->prepare($query);

    // パラメータをバインド
    // PDO::PARAM_STRは文字列としてバインドするが、PDOは自動で型を判断するため通常は不要
    // 明示的に型を指定する場合は PDO::PARAM_INT, PDO::PARAM_STR などを使用
    $stmt->bindParam(1, $posted_temperature, PDO::PARAM_STR); // 1番目のプレースホルダーに温度をバインド
    $stmt->bindParam(2, $posted_device_id, PDO::PARAM_INT);  // 2番目のプレースホルダーにデバイスIDをバインド

    // SQLを実行
    $stmt->execute();
    // 挿入が成功した場合のメッセージ
    echo "新しいレコードが正常に作成されました。";

} catch (PDOException $e) {
    // 挿入に失敗した場合のエラーメッセージ
    echo "エラー: " . $e->getMessage();
}

// 接続を閉じる (PDOでは通常、スクリプト終了時に自動的に閉じられるが、明示的にnullを設定することも可能)
//なんかメモリ節約とかもあるみたいでベストプラクティスらしいので一応書く
$stmt = null; // ステートメントを閉じる
$pdo = null;  // データベース接続を閉じる
?>

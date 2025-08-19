import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URI;
import java.nio.charset.StandardCharsets;

public class PostData {

    public static void main(String[] args) {
        // 無限ループでデータを継続的に送信
        while (true) {
            try {
                //URLをより安全に扱うために一時的にURIとして扱っている。
                // 送信先のPHPファイルをURIで一回構造をチェックしてURLに変換
                URI uri = new URI("http://localhost/portfolio/insert_temperature.php");
                URL url = uri.toURL();
                //url.openConnection();はURLConnectionというオブジェクトを返す。
                //HttpURLConnection型のconnにURLConnectionをHttpURLConnectionにキャスト(型変換)したものを代入する。
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                
                //デフォルトだとGET通信になるのでpostと明示
                // POSTメソッドで接続
                conn.setRequestMethod("POST");

                //デフォだとgetで受信するだけなので、post送信のために通信許可を明示
                conn.setDoOutput(true);
                
                // 送信するデータを定義
                double randomTemp = Math.random() * 10 + 20; // 24.0〜27.0のランダムな値
                int deviceId = 1; // デバイスIDを1に固定
                
                // 送信するデータ（温度とデバイスIDの両方を含める）
                //"%.1f": これは「書式指定子」と呼ばれ、数値をどのように整形するかを定義。
                // %　これがここから書式指定が始まります、という目印。
                // .1　小数点以下1桁まで表示するという指定。
                // f　浮動小数点数（floatやdouble）を対象とする、という指定。
                //double：倍精度浮動小数点数、小数点以下約15〜17桁までの精度を持つ。
                //float：単精度浮動小数点数と呼ばれ、小数点以下約6〜7桁までの精度しかない。
                //double：8バイト（64ビット）、float：4バイト（32ビット）
                String postData = "temperature=" + String.format("%.1f", randomTemp) + "&device_id=" + deviceId;

                // データの送信、OutputStreamはデータ送信のためにつなげるパイプのようなもの
                //変数名osはパイプに名前をつけている
                //conn.getOutputStream()でHttpURLConnectionオブジェクト=connから
                //データを送信するための「パイプ」のような役割を持つOutputStreamを入手してる。
                try (OutputStream os = conn.getOutputStream()) {
                    //postDataには"temperature=25.7&device_id=1"のような、人間が読める文字が入っている。
                    //それをUTF_8の文字コード表にならって文字を１０進数の数字に変換
                    //その後２進数の0,1で変換する。
                    byte[] input = postData.getBytes(StandardCharsets.UTF_8);
                    //ここでパイプにデータを流し込む,inputが流したいデータ(２進数に変換された文字列)
                    //0: データの送信を始める位置。ここでは、input配列の先頭（インデックス0）から送信することを指定している。
                    //input.length: 送信するデータの長さです。input配列の最後まで送信することを指定している。
                    os.write(input, 0, input.length);
                }
                //またここのtry{}のブロックはtry-with-resources文といわれ
                //PCのリソースの解放処理を自動化し、プログラマーのミスを防いでくれる便利な機能
                //パイプ（OutputStream）をつなげておくにはメモリなどのコンピュータ資源が必要で
                //それを使いっぱなしにしておくと非効率的だから、通信が終わった時点で解放する、という目的があります




                // レスポンスコードの確認
                int responseCode = conn.getResponseCode();
                
                // 応答コードが200番台（成功）かどうかで判断
                //２００番台はとりあえずhttp通信が成功したかどうかがわかる
                //サーバーとphpとのやりとりなど、細かいエラーに関してはjava側では不明なので
                //そちらはphp側に任せここは200番台だけで判断
                if (responseCode >= 200 && responseCode < 300) {
                    System.out.println("データを正常に送信しました。");
                } else {
                    System.out.println("データ送信に失敗しました。レスポンスコード: " + responseCode);
                }

                System.out.println("送信データ: " + postData);
                
                conn.disconnect();

                // 10秒待機
                Thread.sleep(10000); // 10000ミリ秒 = 10秒

            } catch (Exception e) {
                e.printStackTrace();
            }
        }
    }
}

//printStackTrace()メソッドは単にエラー文だけをプリントするのではなく
//「スタックトレース」と呼ばれる、より詳細なエラー情報をすべて出力する。
//エラーが、なぜ、どこで、どのように発生したかを知るためのもの。

//スタックトレースに含まれる情報として
// e.printStackTrace()がコンソールに出力する内容は、主に以下の3つ。

//1.エラーの種類: 発生した例外のクラス名（例: java.io.IOException）。
//2.エラーメッセージ: 例外に付随する簡潔な説明文。
//3.メソッドの呼び出し履歴（スタック）: エラーが発生した時点までに
//どのクラスのどのメソッドが何行目で呼び出されたかの記録。

//この履歴を見ることで、例えば「main()メソッドがsendData()メソッドを呼び出し、
//そのsendData()メソッドの10行目でIOExceptionが発生した」といったように、エラーの原因を正確に追跡することが可能。


//最後に最初のimport文の説明
//javaに元々標準搭載されているクラスを召喚するのがimport

// import java.io.OutputStream;データをバイト単位で出力するための**OutputStreamクラス**を呼び込む。
// プログラムがデータをネットワークに送信する際、データを流し込むための「パイプ」機能を提供。

// import java.net.HttpURLConnection;
// HTTP通信を行うためのHttpURLConnectionクラスを呼び込む。
// ウェブサーバーへの接続、リクエストメソッド（GETやPOSTなど）の設定、ヘッダーの追加など、
//HTTP通信のすべてをこのクラスが担当。

// import java.net.URL;
// これはURL（Uniform Resource Locator）を表現するためのURLクラスを呼び込む。
// "http://localhost/..."のような文字列を、Javaが認識できるURLオブジェクトに変換するために使われます。

// import java.net.URI;
// URI（Uniform Resource Identifier）を表現するためのURIクラスを呼び込んでいる。
// URIはURLよりも広い概念で、今回のコードではURLをより安全に扱うために一時的にURIとして扱っています。

// import java.nio.charset.StandardCharsets;
// 文字コードを扱うためのStandardCharsetsクラスを呼び込む。
// "UTF-8"のような標準的な文字コードを安全に指定するために使われます。
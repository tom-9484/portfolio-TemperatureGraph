<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
    <!-- Chart.jsのCDNを読み込む -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>リアルタイム温度データ</h1>

    <!-- データを表示するテーブル -->
    <table>
        <thead>
            <tr>
                <th>デバイスID</th>
                <th>温度(℃)</th>
                <th>測定日時</th>
            </tr>
        </thead>
        <tbody id="data-table-body">
            <!-- データはJavaScriptで動的に追加されます -->
        </tbody>
    </table>

    <!-- グラフを表示するキャンバス -->
    <div id="chart-container">
        <canvas id="myChart"></canvas>
    </div>
</div>

<script>
    // Chart.jsの初期設定
    const ctx = document.getElementById('myChart');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [], // 時刻が入る
            datasets: [{
                label: '温度(℃)',
                data: [],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.2)',
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 0 // アニメーションを無効にする
            },
            scales: {
                y: {
                    beginAtZero: false,
                    suggestedMin: 20,
                    suggestedMax: 30,
                    title: {
                        display: true,
                        text: '温度 (℃)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: '時間'
                    }
                }
            }
        }
    });

    function fetchData() {
        fetch('get_data.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('ネットワーク応答が正常ではありませんでした。');
                }
                return response.json();
            })
            .then(data => {
                // テーブルを更新
                const tableBody = document.getElementById('data-table-body');
                tableBody.innerHTML = ''; // テーブルの中身をクリア

                // グラフのデータをクリア
                chart.data.labels = [];
                chart.data.datasets[0].data = [];


                data.forEach(row => {
                    // テーブルに新しい行を追加
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${row.device_id}</td><td>${row.temp}</td><td>${row.created_at}</td>`;
                    tableBody.appendChild(tr);

                    // グラフにデータを追加
                    // created_atの日付部分を削除して時刻だけを表示
                    //php側はデータをdesc（新しい順）で取得しているので
                    //グラフに新しいデータをpush(末尾)にいれるとグラフの表示が
                    //新しいものから古いものになってしまうのでunshiftを使って先頭に収納
                    const time = row.created_at.split(' ')[1];
                    chart.data.labels.unshift(time);
                    chart.data.datasets[0].data.unshift(row.temp);
                });

                // グラフを更新
                chart.update();
            })
            .catch(error => {
                console.error('データ取得エラー:', error);
            });
    }

    // ページロード時に一度データを取得し、その後10秒ごとに更新
    fetchData();
    setInterval(fetchData, 10000);
</script>
</body>
</html>

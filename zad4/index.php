<?php
$host = 'localhost';
$dbname = 'chart';
$username = 'int_baz';
$password = '1nt3rn3t0w3_b4zy';
$port = 3306;

// Stała definiująca maksymalną liczbę wykresów
define('CHART_LIMIT', 5);

try {
    $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SHOW COLUMNS FROM history");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    echo "Wystąpił błąd połączenia z bazą danych SQL";
    $errormsg= "[" . date('Y-m-d H:i:s') . "] " . (string)$e . PHP_EOL;
    error_log($errormsg, 3, 'error_log.log');
}

$chartData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['charts'])) {
    $charts = $_POST['charts'];
    foreach ($charts as $chartIndex => $selection) {
        $xColumn = $selection['x'] ?? null;
        $yColumns = $selection['y'] ?? [];

        if ($xColumn && !empty($yColumns)) {
            $columnsList = implode(", ", array_merge([$xColumn], $yColumns));
            $query = "SELECT $columnsList FROM history";
            $stmt = $pdo->query($query);
            $chartData[$chartIndex] = [
                'x' => $xColumn,
                'y' => $yColumns,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Konfiguracja wykresów</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', { packages: ['corechart'] });

        function drawCharts() {
            <?php if (!empty($chartData)): ?>
                const chartData = <?php echo json_encode($chartData); ?>;

                chartData.forEach((chart, index) => {
                    const data = new google.visualization.DataTable();

                    // Dodanie kolumn X i Y
                    data.addColumn('string', chart.x);
                    chart.y.forEach(column => {
                        data.addColumn('number', column);
                    });

                    // Dodanie wierszy danych
                    chart.data.forEach(row => {
                        const rowData = [row[chart.x]];
                        chart.y.forEach(column => {
                            rowData.push(parseFloat(row[column]) || 0);
                        });
                        data.addRow(rowData);
                    });

                    // Konfiguracja wykresu
                    const options = {
                        title: `Wykres ${index + 1}`,
                        hAxis: { title: chart.x },
                        vAxis: { title: 'Wartość' },
                    };

                    const chartElement = new google.visualization.LineChart(document.getElementById(`chart_${index + 1}`));
                    chartElement.draw(data, options);
                });
            <?php endif; ?>
        }

        google.charts.setOnLoadCallback(drawCharts);
    </script>
</head>
<body>
    <h1>Wybierz dane do wykresów</h1>
    <form method="POST" action="">
        <label for="num_charts">Ile wykresów chcesz wygenerować?</label>
        <select name="num_charts" id="num_charts" onchange="generateChartSelectors()">
            <?php for ($i = 1; $i <= CHART_LIMIT; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        <br /><br />

        <div id="chart-selectors"></div>

        <br />
        <button type="submit">Generuj wykresy</button>
    </form>

    <div id="charts">
        <?php for ($i = 1; $i <= CHART_LIMIT; $i++): ?>
            <div id="chart_<?php echo $i; ?>" style="width: 100%; height: 500px; margin-top: 20px;"></div>
        <?php endfor; ?>
    </div>

    <script>
        const columns = <?php echo json_encode($columns); ?>;

        function generateChartSelectors() {
            const numCharts = document.getElementById('num_charts').value;
            const chartSelectors = document.getElementById('chart-selectors');
            chartSelectors.innerHTML = '';

            for (let i = 1; i <= numCharts; i++) {
                const chartDiv = document.createElement('div');
                chartDiv.innerHTML = `<h4>Wykres ${i}: Wybierz dane</h4>`;

                const xSelector = document.createElement('div');
                xSelector.innerHTML = '<label>Oś X: </label>';
                const xSelect = document.createElement('select');
                xSelect.name = `charts[${i - 1}][x]`;
                columns.forEach(column => {
                    const option = document.createElement('option');
                    option.value = column;
                    option.textContent = column;
                    xSelect.appendChild(option);
                });
                xSelector.appendChild(xSelect);
                chartDiv.appendChild(xSelector);

                const ySelector = document.createElement('div');
                ySelector.innerHTML = '<label>Oś Y: </label>';
                columns.forEach(column => {
                    const label = document.createElement('label');
                    label.innerHTML = `<input type="checkbox" name="charts[${i - 1}][y][]" value="${column}"> ${column}`;
                    ySelector.appendChild(label);
                });
                chartDiv.appendChild(ySelector);

                chartDiv.appendChild(document.createElement('br'));
                chartSelectors.appendChild(chartDiv);
            }
        }

        document.addEventListener('DOMContentLoaded', generateChartSelectors);
    </script>
</body>
</html>
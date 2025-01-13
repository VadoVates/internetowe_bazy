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
    echo "Wystąpił błąd";
    $errormsg= "[" . date('Y-m-d H:i:s') . "] " . (string)$e . PHP_EOL;
    error_log($errormsg, 3, 'error_log.log');
}

$chartData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['columns']) && isset($_POST['num_charts'])) {
    $selectedColumns = $_POST['columns'];
    $numCharts = (int)$_POST['num_charts'];

    if (!empty($selectedColumns)) {
        $columnsList = implode(", ", $selectedColumns);
        $query = "SELECT $columnsList FROM history";
        $stmt = $pdo->query($query);
        $chartData = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                const selectedColumns = <?php echo json_encode($selectedColumns); ?>;

                for (let i = 0; i < selectedColumns.length; i++) {
                    const column = selectedColumns[i];

                    // 1. Tworzenie tabeli danych
                    const data = new google.visualization.DataTable();
                    data.addColumn('string', 'X'); // Oś X
                    data.addColumn('number', column); // Wybrana kolumna

                    // 2. Dodawanie wierszy do tabeli
                    for (let j = 0; j < chartData.length; j++) {
                        const row = chartData[j];
                        const xValue = row[column];
                        const yValue = parseFloat(row[column]) || 0;
                        data.addRow([xValue, yValue]);
                    }

                    // 3. Ustawianie opcji wykresu
                    const options = {
                        title: `Wykres ${i + 1} (${column})`,
                        hAxis: { title: 'X' },
                        vAxis: { title: column },
                    };

                    // 4. Rysowanie wykresu
                    const chart = new google.visualization.LineChart(document.getElementById(`chart_${i + 1}`));
                    chart.draw(data, options);
                }
            <?php endif; ?>
        }

        google.charts.setOnLoadCallback(drawCharts);
    </script>
</head>
<body>
    <h1>Wybierz dane do wykresów</h1>
    <form method="POST" action="">
        <label for="num_charts">Ile wykresów chcesz wygenerować?</label>
        <select name="num_charts" id="num_charts">
            <?php for ($i = 1; $i <= CHART_LIMIT; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        <br />

        <h4>Wybierz dane dla wykresów:</h4>
        <?php foreach ($columns as $column): ?>
            <div>
                <label>
                    <input type="checkbox" name="columns[]" value="<?php echo $column; ?>">
                    <?php echo $column; ?>
                </label>
            </div>
        <?php endforeach; ?>

        <br />
        <button type="submit">Generuj wykresy</button>
    </form>

    <div id="charts">
        <?php for ($i = 1; $i <= CHART_LIMIT; $i++): ?>
            <div id="chart_<?php echo $i; ?>" style="width: 100%; height: 500px; margin-top: 20px;"></div>
        <?php endfor; ?>
    </div>
</body>
</html>

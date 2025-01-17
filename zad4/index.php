<?php
$host = 'localhost';
$dbname = 'chart';
$username = 'int_baz';
$password = '1nt3rn3t0w3_b4zy';
$port = 3306;

// Stała definiująca maksymalną liczbę wykresów
define('CHART_LIMIT', 5);
$error = '';
try {
    $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SHOW COLUMNS FROM history");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $xColumn = $columns[0]; // Pierwsza kolumna jako domyślna oś X
    unset($columns[0]); // Usuń pierwszą kolumnę z listy, by nie była wybierana na Y

    // $rangeStmt = $pdo->query("SELECT MIN($xColumn) AS min_date, MAX($xColumn) AS max_date FROM history");
    // $rangeResult = $rangeStmt->fetch(PDO::FETCH_ASSOC);
    //     $minDate = $rangeResult['min_date'];
    // rozdzielenie zapytania bo 'MAX($xColumn) AS max_date FROM history' ostatni element posortowanej tablicy po date_time
    // wiec na sam dół spada nazwa kolumny czyli string 'date_time' który był wpychany w pole daty kalendarza
    $minDateStmt = $pdo->query("SELECT MIN($xColumn) AS min_date FROM history");
    $minDateResult = $minDateStmt->fetch(PDO::FETCH_ASSOC);
    $minDate = $minDateResult['min_date'];

    $maxDateStmt = $pdo->query("SELECT MAX($xColumn) AS max_date FROM history"); 
        // WHERE $xColumn < (SELECT MAX($xColumn) FROM history)
    // "); // pobieranie przed ostaniej zawartosci czyli daty anie stinga 'date_time'
    $maxDateResult = $maxDateStmt->fetch(PDO::FETCH_ASSOC);

    $maxDate = $maxDateResult['max_date'];

    $dateTimeMin = new DateTime($minDate);
    $minDateCalendar = $dateTimeMin->format('Y-m-d'); // formatujemy daty dla kalendarza
    $DateTimeMax = new DateTime($maxDate);
    $maxDateCalendar = $DateTimeMax->format('Y-m-d');

} catch (PDOException $e) {
    echo "Wystąpił błąd połączenia z bazą danych SQL";
    $errormsg = "[" . date('Y-m-d H:i:s') . "] " . (string)$e . PHP_EOL;
    error_log($errormsg, 3, 'error_log.log');
}

$chartData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['charts'])) { // sprawdzamy czy w post poszedł charts
        $error = "Brak niezbędnych danych charts";
    } else {
        $error = '';

        $charts = $_POST['charts'];
        $postStartDateTime = new DateTime($_POST['start_date']); // formatujemy date z kalendarza na typ date
        $postStartDateTimeString = $postStartDateTime->format('Y-m-d H:i:s'); // formatujemy date na fomat date dal bazy danych
        $startDate = $postStartDateTimeString ?? $minDate;

        $postEndDateTime = new DateTime($_POST['end_date']);
        $postEndDateTimeString = $postEndDateTime->format('Y-m-d H:i:s');
        $endDate = $postEndDateTimeString ?? $maxDate;

        echo $endDate;

        if ($startDate > $endDate) {
            list($startDate, $endDate) = [$endDate, $startDate];
        }

        // Dopasowanie dat do najbliższych dostępnych w bazie
        $adjustDateStmt = $pdo->prepare("SELECT MIN($xColumn) AS adjusted_start FROM history WHERE $xColumn >= :start_date");
        $adjustDateStmt->bindParam(':start_date', $startDate);
        $adjustDateStmt->execute();
        $adjustedStart = $adjustDateStmt->fetch(PDO::FETCH_ASSOC)['adjusted_start'] ?? $minDate;

        $adjustDateStmt = $pdo->prepare("SELECT MAX($xColumn) AS adjusted_end FROM history WHERE $xColumn <= :end_date");
        $adjustDateStmt->bindParam(':end_date', $endDate);
        $adjustDateStmt->execute();
        $adjustedEnd = $adjustDateStmt->fetch(PDO::FETCH_ASSOC)['adjusted_end'] ?? $maxDate;

        foreach ($charts as $chartIndex => $selection) {
            $yColumns = $selection['y'] ?? [];

            if (!empty($yColumns)) {
                $columnsList = implode(", ", array_merge([$xColumn], $yColumns));
                $query = "SELECT $columnsList FROM history WHERE $xColumn BETWEEN :start_date AND :end_date";

                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':start_date', $adjustedStart);
                $stmt->bindParam(':end_date', $adjustedEnd);
                $stmt->execute();

                $chartData[$chartIndex] = [
                    'x' => $xColumn,
                    'y' => $yColumns,
                    'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
                ];
            }
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
        google.charts.load('current', {
            packages: ['corechart']
        });

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
                        hAxis: {
                            title: chart.x
                        },
                        vAxis: {
                            title: 'Wartość'
                        },
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
        <div>
            <!-- zakresy zależne od dateCalendar -->
            <label for="start_date">Początek zakresu (<?php echo $xColumn; ?>):</label> 
            <input type="date" id="start_date" name="start_date" value="<?php echo $minDateCalendar; ?>" min="<?php echo $minDateCalendar; ?>" max="<?php echo $maxDateCalendar; ?>">
        </div>
        <div>
            <label for="end_date">Koniec zakresu (<?php echo $xColumn; ?>):</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo $maxDateCalendar; ?>" min="<?php echo $minDateCalendar; ?>" max="<?php echo $maxDateCalendar; ?>">
        </div>
        <br />

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
                <!-- pole na errory -->
    <div>
        <p style="color:red"><?php echo $error; ?></p>
    </div>

    <div id="charts">
        <?php for ($i = 1; $i <= CHART_LIMIT; $i++): ?>
            <div id="chart_<?php echo $i; ?>" style="width: 100%; height: 500px; margin-top: 20px;"></div>
        <?php endfor; ?>
    </div>

    <script>
        const columns = <?php echo json_encode(array_values($columns)); ?>;
        const xColumn = <?php echo json_encode($xColumn); ?>;

        function generateChartSelectors() {
            const numCharts = document.getElementById('num_charts').value;
            const chartSelectors = document.getElementById('chart-selectors');
            chartSelectors.innerHTML = '';

            for (let i = 1; i <= numCharts; i++) {
                const chartDiv = document.createElement('div');
                chartDiv.innerHTML = `<h4>Wykres ${i}: Wybierz dane dla osi Y</h4>`;

                const ySelector = document.createElement('div');
                ySelector.innerHTML = `<p>Oś X: <strong>${xColumn}</strong></p>`;
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
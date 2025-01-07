<?php
session_start();
/*
<!-- Autor: Marek Górski -->
<!-- nr indeksu: 155647 -->
<!-- grupa D1 -->
<!-- rok akademicki 2024/2025 -->
<!-- semestr V -->
*/


$headers = [];
$data = [];
$filteredData = [];
$minDate = $maxDate = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $filePath = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ",");

        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[] = $row;
        }
        fclose($handle);
        $_SESSION['headers'] = $headers;
        $_SESSION['csv_data'] = $data;

        $dates = array_column($data, 0);
        $minDate = min($dates);
        $maxDate = max($dates);

        // echo "minimalna data: " . $minDate . ", maksymalna data: " . $maxDate;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_date'], $_POST['end_date'])) {
    $data = $_SESSION['csv_data'];
    $headers = $_SESSION['headers'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    foreach ($data as $row) {
        $rowDate = $row[0];
        if ($rowDate >= $startDate && $rowDate <= $endDate) {
            $filteredData[] = $row;
        }
    }

    foreach ($filteredData as $filteredRow) {
        foreach ($filteredRow as $atomic) {
            echo $atomic;
        }
    }
}
?>

<!DOCTYPE html>
<html> 
    <head>
        <title>Internetowe Bazy Danych</title>
        <?php if (!empty($filteredData)): ?>
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <script type="text/javascript">
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = new google.visualization.DataTable();
                <?php
                    echo "data.addColumn('datetime', '" . $headers[0]. "');\n";
                    echo "data.addColumn('number', '" . $headers[1]. "');\n";
                    echo "data.addColumn('number', '" . $headers[2]. "');\n";
                    echo "data.addColumn('number', '" . $headers[3]. "');\n";
                    echo "data.addColumn('number', '" . $headers[4]. "');\n";
                ?>

                data.addRows([
                    <?php
                    foreach ($filteredData as $row) {
                        echo "[new Date('" . $row[0] . "'), ";
                        echo (float)$row[1] . ", " . (float)$row[2] . ", " . (float)$row[3] . ", " . (float)$row[4];
                        echo "],\n";
                    }
                    ?>
                ]);

                var options = {
                    title: 'Historia kursów wymiany',
                    // curveType: 'function',
                    legend: { position: 'bottom' },
                    hAxis: { title: '<?php echo $headers[0]; ?>' },
                    vAxis: { title: 'wartość' }
                };

                var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

                chart.draw(data, options);
            }
            </script>
        <?php endif; ?>

        <style>
            h1 {
                text-align: center;
            }
        </style>
    </head>

    <body>
        <h1>Wykresy kursów walut</h1>

        <?php if (!empty($filteredData)): ?>
            <!--Div that will hold the pie chart-->
            <div id="curve_chart" style="width: 100%; height: 500px"></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit">Prześlij plik CSV w celu wygenerowania wykresu</button>
        </form>

    </body>
</html>
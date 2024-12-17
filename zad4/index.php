<?php

$headers = [];
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $filePath = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ",");

        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[] = $row;
        }
        fclose($handle);
    }
}
?>

<!DOCTYPE html>
<html> 
    <head>
        <title>Internetowe Bazy Danych</title>
        <?php if (!empty($data)): ?>
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
                $dataLength = count($data);
                foreach ($data as $index => $row) {
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
        <?php if (!empty($data)): ?>
        <!--Div that will hold the pie chart-->
        <div id="curve_chart" style="width: 100%; height: 500px"></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit">Prześlij plik CSV w celu wygenerowania wykresu</button>
        </form>
    </body>
</html>
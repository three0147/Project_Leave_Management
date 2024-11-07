<?php
session_start();
require('./config/configDB.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
}

$query1 = "
    SELECT l.leave_name, SUM(DATEDIFF(leavehis_end, leavehis_start)) AS total_days 
    FROM leave_his lh
    JOIN `Leaves` l ON lh.leave_id = l.leave_id
    GROUP BY l.leave_name
";
$result1 = $conn->query($query1);
$leave_data = [];
$leave_labels = [];
foreach ($result1 as $row) {
    $leave_labels[] = $row['leave_name'];
    $leave_data[] = $row['total_days'];
}

$query2 = "
    SELECT l.leave_name, MONTH(lh.leavehis_start) AS month, SUM(DATEDIFF(leavehis_end, leavehis_start)) AS total_days 
    FROM leave_his lh
    JOIN `Leaves` l ON lh.leave_id = l.leave_id
    GROUP BY l.leave_name, MONTH(lh.leavehis_start)
";
$result2 = $conn->query($query2);
$trend_data = [];
while ($row = $result2->fetch(PDO::FETCH_ASSOC)) {
    $trend_data[$row['leave_name']][$row['month']] = $row['total_days'];
}

$query3 = "
    SELECT SUM(DATEDIFF(leavehis_end, leavehis_start)) AS total_days 
    FROM leave_his
";
$result3 = $conn->query($query3);
$total_days = $result3->fetchColumn();

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <?php include('./include/cdn.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include('./include/sidebar.php'); ?>
    <div class="container mt-5">
        <h1 class="mb-4">Dashboard</h1>
        
 
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">จำนวนวันลาทั้งหมด</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_days; ?> Days</h5>
                        <p class="card-text">รวมจำนวนวันลาของพนักงานทุกคน</p>
                    </div>
                </div>
            </div>
        </div>

     
        <div class="row mb-4">
            <div class="col-md-12">
                <h3>Leave Summary by Type</h3>
                <canvas id="leaveSummaryChart"></canvas>
            </div>
        </div>

       
        <div class="row mb-4">
            <div class="col-md-12">
                <h3>Leave Trends by Month</h3>
                <canvas id="leaveTrendChart"></canvas>
            </div>
        </div>
    </div>
    <script>
        const leaveSummaryCtx = document.getElementById('leaveSummaryChart').getContext('2d');
        const leaveSummaryChart = new Chart(leaveSummaryCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($leave_labels); ?>,
                datasets: [{
                    label: 'Total Days',
                    data: <?php echo json_encode($leave_data); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

  
        const leaveTrendCtx = document.getElementById('leaveTrendChart').getContext('2d');
        const trendData = <?php echo json_encode($trend_data); ?>;
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        const datasets = Object.keys(trendData).map(leaveType => {
            return {
                label: leaveType,
                data: months.map((_, i) => trendData[leaveType][i + 1] || 0),
                fill: false,
                borderColor: getRandomColor(),
                tension: 0.1
            };
        });

        const leaveTrendChart = new Chart(leaveTrendCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: datasets
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });


        function getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }
    </script>
    <?php include('./include/footer.php'); ?>
</body>

</html>

<?php
session_start();
require('./config/configDB.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$query_leave_report = "SELECT 
    L.leave_name,
    L.leave_Max_date, 
    IF(L.leave_name = 'ลาพักร้อน', 4.5, 
        IF(L.leave_name = 'ลาป่วย', 1, 
            IFNULL(SUM(DATEDIFF(lh.leavehis_end, lh.leavehis_start) + 1), 0)
        )
    ) AS used_leave_days,
    (L.leave_Max_date - 
        IF(L.leave_name = 'ลาพักร้อน', 4.5, 
            IF(L.leave_name = 'ลาป่วย', 1,
                IFNULL(SUM(DATEDIFF(lh.leavehis_end, lh.leavehis_start) + 1), 0)
            )
        )
    ) AS remaining_leave_days
FROM Leaves L
LEFT JOIN leave_his lh ON L.leave_id = lh.leave_id AND lh.leave_status_id = 1 AND lh.emp_id = :user_id
GROUP BY L.leave_id, L.leave_name, L.leave_Max_date";

$stmt_leave_report = $conn->prepare($query_leave_report);
$stmt_leave_report->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_leave_report->execute();
$leave_report = $stmt_leave_report->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Report</title>
    <?php include('./include/cdn.php'); ?>
</head>
<body>
    <?php include('./include/sidebar.php'); ?>
    <div class="container mt-4">
        <h2>Leave Report</h2>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Max Leave Days</th>
                            <th>Used Leave Days</th>
                            <th>Remaining Leave Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 

                        foreach ($leave_report as $leave) { ?>
                            <tr>
                                <td><?php echo $leave['leave_name']; ?></td>
                                <td><?php echo $leave['leave_Max_date']; ?></td>
                                <td><?php echo $leave['used_leave_days']; ?></td>
                                <td><?php echo $leave['remaining_leave_days']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include('./include/footer.php'); ?>
</body>
</html>

<?php
session_start();
require('./config/configDB.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['leavehis_id'])) {
    $leavehis_id = $_POST['leavehis_id'];

    // ตรวจสอบว่าค่า leavehis_id ถูกส่งมาถูกต้องหรือไม่
    echo "Leave ID: " . $leavehis_id;

    // ลบข้อมูลการลา
    $delete_query = "DELETE FROM leave_his WHERE leavehis_id = :leavehis_id AND leave_status_id = 0";
    $stmt_delete = $conn->prepare($delete_query);
    $stmt_delete->bindParam(':leavehis_id', $leavehis_id, PDO::PARAM_INT);

    // ตรวจสอบการทำงานของ execute()
    if ($stmt_delete->execute()) {
        echo "Delete success";
    } else {
        echo "Delete failed";
    }

    // รีเฟรชหน้าเพื่อแสดงผลการลบ
    header("Location: index.php");
    exit;
}




$user_id = $_SESSION['user_id'];

$query_leave_history = "SELECT 
    L.leave_name, 
    lh.leavehis_start, 
    lh.leavehis_end, 
    DATEDIFF(lh.leavehis_end, lh.leavehis_start) AS total_days, 
    lh.leave_status_id, 
    lh.leavehis_id
FROM leave_his lh
JOIN Leaves L ON lh.leave_id = L.leave_id
WHERE lh.emp_id = :user_id
ORDER BY lh.leavehis_start DESC";
$user_id = $_SESSION['user_id'];
$stmt_leave_history = $conn->prepare($query_leave_history);
$stmt_leave_history->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_leave_history->execute();
$leave_history = $stmt_leave_history->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าแรก</title>
    <?php include('./include/cdn.php'); ?>
</head>

<body>
    <?php include('./include/sidebar.php'); ?>
    <div class="container mt-4">
        <div class="row">
            <?php
            $query_total_remaining_leave = "SELECT 
    SUM(L.leave_Max_date - IFNULL(lh.total_leave_days, 0)) AS total_remaining_leave_days
FROM Leaves L
LEFT JOIN (
    SELECT leave_id, emp_id, SUM(DATEDIFF(lh.leavehis_end, lh.leavehis_start)) AS total_leave_days
    FROM leave_his lh
    WHERE lh.leave_status_id = 1 AND lh.emp_id = :user_id
    GROUP BY leave_id, emp_id
) lh ON L.leave_id = lh.leave_id;


        ";

            $stmt_total_remaining_leave = $conn->prepare($query_total_remaining_leave);
            $stmt_total_remaining_leave->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_total_remaining_leave->execute();
            $total_remaining_leave = $stmt_total_remaining_leave->fetch(PDO::FETCH_ASSOC);

            $query_pending_leave = "SELECT IFNULL(SUM(DATEDIFF(leavehis_end, leavehis_start)),0) AS pending_leave_days
                FROM leave_his
                WHERE leave_status_id = 0 AND emp_id = $user_id";

            $stmt_pending_leave = $conn->prepare($query_pending_leave);
            $stmt_pending_leave->execute();
            $pending_leave = $stmt_pending_leave->fetch(PDO::FETCH_ASSOC);

            $query_approved_leave = "SELECT IFNULL(SUM(DATEDIFF(leavehis_end, leavehis_start)),0) AS approved_leave_days
                 FROM leave_his
                 WHERE leave_status_id = 1 AND  emp_id = $user_id";

            $stmt_approved_leave = $conn->prepare($query_approved_leave);
            $stmt_approved_leave->execute();
            $approved_leave = $stmt_approved_leave->fetch(PDO::FETCH_ASSOC);

            $query_rejected_leave = "SELECT IFNULL(SUM(DATEDIFF(leavehis_end, leavehis_start)),0) AS rejected_leave_days
                 FROM leave_his
                 WHERE leave_status_id = 2 AND emp_id = $user_id";

            $stmt_rejected_leave = $conn->prepare($query_rejected_leave);
            $stmt_rejected_leave->execute();
            $rejected_leave = $stmt_rejected_leave->fetch(PDO::FETCH_ASSOC);

            ?>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_remaining_leave["total_remaining_leave_days"]; ?></h5>
                        <p class="card-text">วันลาคงเหลือ <br> ประจำปี <?php echo date("Y") + 543; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $pending_leave['pending_leave_days']; ?></h5>
                        <p class="card-text">วันลาที่รออนุมัติ <br> ประจำปี <?php echo date("Y") + 543; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $approved_leave['approved_leave_days']; ?></h5>
                        <p class="card-text">วันลาที่อนุมัติแล้ว <br> ประจำปี <?php echo date("Y") + 543; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $rejected_leave['rejected_leave_days']; ?></h5>
                        <p class="card-text">จำนวนวันลาที่ไม่อนุมัติ <br> ประจำปี <?php echo date("Y") + 543; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <h4>การลางานของฉัน ล่าสุด</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>การลา</th>
                            <th>วันที่ลา</th>
                            <th>จำนวนวัน</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leave_history as $leave): ?>
                            <tr>
                                <td><?php echo $leave['leave_name']; ?></td>
                                <td><?php echo $leave['leavehis_start'] . ' - ' . $leave['leavehis_end']; ?></td>
                                <td><?php echo $leave['total_days']; ?></td>
                                <td>
                                    <?php if ($leave['leave_status_id'] == 1): ?>
                                        <span class="badge bg-success">อนุมัติแล้ว</span>
                                    <?php elseif ($leave['leave_status_id'] == 2): ?>
                                        <span class="badge bg-danger">ไม่อนุมัติ</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">รออนุมัติ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($leave['leave_status_id'] == 0): ?>
                                        <form id="delete-form-<?php echo $leave['leavehis_id']; ?>" method="POST" action="index.php" style="display:inline;">
                                            <input type="hidden" name="leavehis_id" value="<?php echo $leave['leavehis_id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $leave['leavehis_id']; ?>); return false;">ลบ</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        function confirmDelete(leavehis_id) {
            console.log("Confirm delete for leavehis_id:", leavehis_id);

            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: "คุณจะไม่สามารถย้อนกลับการลบนี้ได้!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log("Confirmed delete for leavehis_id:", leavehis_id);
                    document.getElementById('delete-form-' + leavehis_id).submit();
                }
            })
        }
    </script>
    <?php include('./include/footer.php'); ?>

</body>

</html>
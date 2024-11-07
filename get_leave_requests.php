<?php
require('./config/configDB.php');


$department = $_POST['department'];
$leaveType = $_POST['leaveType'];
$searchTerm = $_POST['searchTerm'];


$conditions = [];
$params = [];

if ($department != "ทั้งหมด") {
    $conditions[] = "d.department_name = :department";
    $params[':department'] = $department;
}

if ($leaveType != "ทั้งหมด") {
    $conditions[] = "l.leave_name = :leaveType";
    $params[':leaveType'] = $leaveType;
}

if (!empty($searchTerm)) {
    $conditions[] = "(e.emp_fname LIKE :search OR e.emp_lname LIKE :search)";
    $params[':search'] = '%' . $searchTerm . '%';
}

$where = '';
if (count($conditions) > 0) {
    $where = 'WHERE ' . implode(' AND ', $conditions);
}


$query = "
    SELECT e.emp_fname, e.emp_lname, d.department_name, l.leave_name, lh.leavehis_start, 
           DATEDIFF(lh.leavehis_end, lh.leavehis_start) AS total_days, lh.leavehis_file, ls.status_name
    FROM leave_his lh
    JOIN employees e ON lh.emp_id = e.emp_id
    JOIN department d ON e.department_id = d.department_id
    JOIN Leave l ON lh.leave_id = l.leave_id
    JOIN Leave_status ls ON lh.leave_status_id = ls.leave_status_id
    $where
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$leave_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (count($leave_requests) > 0) {
    foreach ($leave_requests as $leave) {
        echo "<tr>";
        echo "<td>" . $leave['emp_fname'] . " " . $leave['emp_lname'] . "</td>";
        echo "<td>" . $leave['department_name'] . "</td>";
        echo "<td>" . $leave['leave_name'] . "</td>";
        echo "<td><span class='badge bg-success'>ลาเต็มวัน</span></td>";
        echo "<td>" . date('d/m/Y', strtotime($leave['leavehis_start'])) . "</td>";
        echo "<td>" . $leave['total_days'] . "</td>";
        echo "<td>";
        if ($leave['leavehis_file']) {
            echo "<button class='btn btn-outline-info btn-sm'>เปิดดู</button>";
        } else {
            echo "-";
        }
        echo "</td>";
        echo "<td><span class='badge bg-primary'>" . $leave['status_name'] . "</span></td>";
        echo "<td><button class='btn btn-outline-warning btn-sm'>การอนุมัติ</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>ไม่มีข้อมูล</td></tr>";
}

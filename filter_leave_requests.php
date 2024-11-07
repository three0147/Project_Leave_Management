<?php
session_start();
require('./config/configDB.php');

// ตรวจสอบการส่งข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department_id = $_POST['department_id'];
    $leave_type_id = $_POST['leave_type_id'];

    // สร้าง SQL ตามค่า filter ที่เลือก
    $sql = "
        SELECT e.emp_fname, e.emp_lname, d.department_name, l.leave_name, lh.leavehis_start, 
            DATEDIFF(lh.leavehis_end, lh.leavehis_start) + 1 AS total_days, 
            lh.leavehis_file, ls.status_name, lh.leavehis_des, lh.leavehis_id
        FROM leave_his lh
        JOIN employees e ON lh.emp_id = e.emp_id
        JOIN department d ON e.department_id = d.department_id
        JOIN Leaves l ON lh.leave_id = l.leave_id
        JOIN Leave_status ls ON lh.leave_status_id = ls.leave_status_id
        WHERE lh.leave_status_id = 0
    ";

    // กรองตามแผนก
    if ($department_id !== 'ทั้งหมด') {
        $sql .= " AND d.department_id = :department_id";
    }

    // กรองตามประเภทการลา
    if ($leave_type_id !== 'ทั้งหมด') {
        $sql .= " AND l.leave_id = :leave_id";
    }

    $sql .= " ORDER BY lh.leavehis_start DESC";

    $stmt = $conn->prepare($sql);

    // ผูกค่า parameter หากจำเป็น
    if ($department_id !== 'ทั้งหมด') {
        $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
    }
    if ($leave_type_id !== 'ทั้งหมด') {
        $stmt->bindParam(':leave_id', $leave_type_id, PDO::PARAM_INT);
    }

    $stmt->execute();
    $leave_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // แสดงผลข้อมูลในรูปแบบของ HTML (แถวในตาราง)
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
                echo "<a href='uploads/" . $leave['leavehis_file'] . "' target='_blank' class='btn btn-outline-info btn-sm'>เปิดดู</a>";
            } else {
                echo "-";
            }
            echo "</td>";
            echo "<td>" . $leave['leavehis_des'] . "</td>";
            echo "<td><span class='badge bg-primary'>" . $leave['status_name'] . "</span></td>";
            echo "<td>
                    <button class='btn btn-success btn-sm' onclick='handleAction(" . $leave['leavehis_id'] . ", \"approve\")'>อนุมัติ</button>
                    <button class='btn btn-danger btn-sm' onclick='handleAction(" . $leave['leavehis_id'] . ", \"reject\")'>ไม่อนุมัติ</button>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='10'>ไม่มีข้อมูล</td></tr>";
    }
}
?>

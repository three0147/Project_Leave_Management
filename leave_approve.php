<?php
session_start();
require('./config/configDB.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leavehis_id = $_POST['leavehis_id'];
    $action = $_POST['action'];

    $leave_status_id = ($action === 'approve') ? 1 : 2;


    $sql_update_status = "UPDATE leave_his SET leave_status_id = :leave_status_id WHERE leavehis_id = :leavehis_id";
    $stmt_update = $conn->prepare($sql_update_status);
    $stmt_update->bindParam(':leave_status_id', $leave_status_id, PDO::PARAM_INT);
    $stmt_update->bindParam(':leavehis_id', $leavehis_id, PDO::PARAM_INT);
    $stmt_update->execute();
}


$sql_departments = "SELECT * FROM department";
$departments_stmt = $conn->prepare($sql_departments);
$departments_stmt->execute();
$departments = $departments_stmt->fetchAll(PDO::FETCH_ASSOC);


$sql_leaves = "SELECT * FROM Leaves";
$leaves_stmt = $conn->prepare($sql_leaves);
$leaves_stmt->execute();
$leave_types = $leaves_stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT 
        e.emp_fname, e.emp_lname, d.department_name, l.leave_name, lh.leavehis_start, 
        DATEDIFF(lh.leavehis_end, lh.leavehis_start) + 1 AS total_days, 
        lh.leavehis_file, ls.status_name, lh.leavehis_des, lh.leavehis_id
    FROM leave_his lh
    JOIN employees e ON lh.emp_id = e.emp_id
    JOIN department d ON e.department_id = d.department_id
    JOIN Leaves l ON lh.leave_id = l.leave_id
    JOIN Leave_status ls ON lh.leave_status_id = ls.leave_status_id
    WHERE lh.leave_status_id = 0  
    ORDER BY lh.leavehis_start DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$leave_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dashboard</title>
    <?php include('./include/cdn.php'); ?>
</head>

<body>
    <?php include('./include/sidebar.php'); ?>

    <div class="container mt-5">
        <h1 class="mb-4">รายการลา</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <select class="form-select d-inline w-auto" id="department-select" onchange="filterData()">
            <option value="ทั้งหมด">แผนกทั้งหมด</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?php echo $department['department_id']; ?>">
                    <?php echo $department['department_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select class="form-select d-inline w-auto" id="leave-type-select" onchange="filterData()">
            <option value="ทั้งหมด">ประเภทการลาทั้งหมด</option>
            <?php foreach ($leave_types as $leave_type): ?>
                <option value="<?php echo $leave_type['leave_id']; ?>">
                    <?php echo $leave_type['leave_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

        <script>
            function handleAction(leavehis_id, action) {
                let actionText = action === 'approve' ? 'อนุมัติ' : 'ไม่อนุมัติ';
                let confirmButtonText = action === 'approve' ? 'อนุมัติ' : 'ไม่อนุมัติ';
                let confirmButtonColor = action === 'approve' ? '#28a745' : '#dc3545'; // สีเขียวสำหรับอนุมัติ สีแดงสำหรับไม่อนุมัติ

                Swal.fire({
                    title: `คุณต้องการ ${actionText} การลาหรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: 'ยกเลิก',
                    reverseButtons: false, // เรียงลำดับให้ถูกต้อง
                    customClass: {
                        confirmButton: action === 'approve' ? 'btn btn-success me-2' : 'btn btn-danger me-2',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false // ปิดการใช้ปุ่มสไตล์เริ่มต้นของ SweetAlert เพื่อใช้ Bootstrap classes
                }).then((result) => {
                    if (result.isConfirmed) {
                        // ส่งข้อมูลไปยังเซิร์ฟเวอร์ผ่านฟอร์มแบบ POST
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '';

                        let leaveIdInput = document.createElement('input');
                        leaveIdInput.type = 'hidden';
                        leaveIdInput.name = 'leavehis_id';
                        leaveIdInput.value = leavehis_id;
                        form.appendChild(leaveIdInput);

                        let actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = action;
                        form.appendChild(actionInput);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        </script>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">ชื่อพนักงาน</th>
                    <th scope="col">แผนก</th>
                    <th scope="col">การลา</th>
                    <th scope="col">ประเภท</th>
                    <th scope="col">วันที่ลา</th>
                    <th scope="col">จำนวนวัน</th>
                    <th scope="col">ไฟล์แนบ</th>
                    <th scope="col">เหตุผลการลา</th>
                    <th scope="col">สถานะ</th>
                    <th scope="col">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
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
                ?>
            </tbody>
        </table>


        <nav>
            <ul class="pagination">
                <li class="page-item disabled"><a class="page-link" href="#">ก่อนหน้า</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">ถัดไป</a></li>
            </ul>
        </nav>
    </div>
    <script>
    function filterData() {
        let departmentId = document.getElementById('department-select').value;
        let leaveTypeId = document.getElementById('leave-type-select').value;

        // ส่งค่าไปยังเซิร์ฟเวอร์ด้วย Ajax
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'filter_leave_requests.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
            if (xhr.status === 200) {
                // อัปเดตตารางข้อมูลด้วยผลลัพธ์ที่ได้จากเซิร์ฟเวอร์
                document.querySelector('tbody').innerHTML = xhr.responseText;
            }
        };

        // ส่งค่าไปยังเซิร์ฟเวอร์
        xhr.send('department_id=' + departmentId + '&leave_type_id=' + leaveTypeId);
    }
</script>

    <?php include('./include/footer.php'); ?>
</body>

</html>
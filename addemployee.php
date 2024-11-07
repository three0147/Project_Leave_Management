<?php
session_start();
require('./config/configDB.php');

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emp_fname = $_POST['emp_fname'];
    $emp_lname = $_POST['emp_lname'];
    $emp_email = $_POST['emp_email'];
    $phonenumber = $_POST['phonenumber'];
    $username = $_POST['username'];
    $password = md5($_POST['password'], PASSWORD_DEFAULT);
    $department_id = $_POST['department_id'];
    $role_id = $_POST['role_id'];
    $time_start = $_POST['time_start'];

    // เพิ่มข้อมูลผู้ใช้ลงในฐานข้อมูล
    $sql = "INSERT INTO employees (emp_fname, emp_lname, emp_email, phonenumber, username, password, department_id, role_id, time_start) 
            VALUES (:emp_fname, :emp_lname, :emp_email, :phonenumber, :username, :password, :department_id, :role_id, :time_start)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':emp_fname', $emp_fname);
    $stmt->bindParam(':emp_lname', $emp_lname);
    $stmt->bindParam(':emp_email', $emp_email);
    $stmt->bindParam(':phonenumber', $phonenumber);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':department_id', $department_id);
    $stmt->bindParam(':role_id', $role_id);
    $stmt->bindParam(':time_start', $time_start);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: 'เพิ่มผู้ใช้สำเร็จ!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'somepage.php'; // เปลี่ยน URL หน้าที่คุณต้องการเปลี่ยนหลังจากเพิ่มผู้ใช้
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'เกิดข้อผิดพลาดในการเพิ่มผู้ใช้!',
            });
        </script>";
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มผู้ใช้ใหม่</title>
    <?php include('./include/cdn.php'); ?>
</head>

<body>
    <?php include('./include/sidebar.php'); ?>

    <div class="container mt-5">
        <h1 class="mb-4">เพิ่มผู้ใช้ใหม่</h1>

        <form method="POST" action="">

            <div class="mb-3">
                <label for="emp_fname" class="form-label">ชื่อ</label>
                <input type="text" class="form-control" id="emp_fname" name="emp_fname" required>
            </div>

            <div class="mb-3">
                <label for="emp_lname" class="form-label">นามสกุล</label>
                <input type="text" class="form-control" id="emp_lname" name="emp_lname" required>
            </div>

            <div class="mb-3">
                <label for="emp_email" class="form-label">อีเมล์</label>
                <input type="email" class="form-control" id="emp_email" name="emp_email" required>
            </div>

            <div class="mb-3">
                <label for="phonenumber" class="form-label">เบอร์โทร</label>
                <input type="text" class="form-control" id="phonenumber" name="phonenumber" required>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <label for="department_id" class="form-label">แผนก</label>
                <select class="form-select" id="department_id" name="department_id" required>
                    <option value="">เลือกแผนก</option>
                    <?php
                    // ดึงข้อมูลแผนกทั้งหมดจากฐานข้อมูล
                    $sql_departments = "SELECT * FROM department";
                    $stmt_departments = $conn->prepare($sql_departments);
                    $stmt_departments->execute();
                    $departments = $stmt_departments->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($departments as $department) {
                        echo "<option value='" . $department['department_id'] . "'>" . $department['department_name'] . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="role_id" class="form-label">ตำแหน่ง</label>
                <select class="form-select" id="role_id" name="role_id" required>
                    <option value="">เลือกตำแหน่ง</option>
                    <?php
                    // ดึงข้อมูลตำแหน่งจากฐานข้อมูล
                    $sql_roles = "SELECT * FROM role";
                    $stmt_roles = $conn->prepare($sql_roles);
                    $stmt_roles->execute();
                    $roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($roles as $role) {
                        echo "<option value='" . $role['role_id'] . "'>" . $role['role_name'] . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="time_start" class="form-label">วันที่เริ่มงาน</label>
                <input type="date" class="form-control" id="time_start" name="time_start" required>
            </div>

            <button type="submit" class="btn btn-primary">เพิ่มผู้ใช้</button>
        </form>
    </div>
</body>

</html>

<?php
session_start();
require('./config/configDB.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$emp_id = $_SESSION['user_id'];

$sql_leaves = "SELECT * FROM Leaves";
$leaves_stmt = $conn->prepare($sql_leaves);
$leaves_stmt->execute();
$leave_types = $leaves_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leave_type = $_POST['leaveType'];
    $start_date = $_POST['startDate'];
    $end_date = $_POST['endDate'];
    $leave_description = $_POST['description'];
    $file = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];

    if ($file) {
        $upload_dir = './uploads/';
        $file_path = $upload_dir . basename($file);
        move_uploaded_file($file_tmp, $file_path);
    }

    $sql_insert_leave = "
        INSERT INTO leave_his (leave_id, emp_id, leavehis_des, leavehis_file, leave_status_id, leavehis_start, leavehis_end) 
        VALUES (:leave_id, :emp_id, :leavehis_des, :leavehis_file, 0, :leavehis_start, :leavehis_end)";
    
    $stmt_insert = $conn->prepare($sql_insert_leave);
    $stmt_insert->bindParam(':leave_id', $leave_type, PDO::PARAM_INT);
    $stmt_insert->bindParam(':emp_id', $emp_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':leavehis_des', $leave_description, PDO::PARAM_STR);
    $stmt_insert->bindParam(':leavehis_file', $file, PDO::PARAM_STR);
    $stmt_insert->bindParam(':leavehis_start', $start_date, PDO::PARAM_STR);
    $stmt_insert->bindParam(':leavehis_end', $end_date, PDO::PARAM_STR);

    if ($stmt_insert->execute()) {
        echo "<script>
            Swal.fire({
                title: 'สำเร็จ!',
                text: 'คำขอลาของคุณได้รับการยืนยันแล้ว',
                icon: 'success'
            }).then(function() {
                window.location.href = 'leave_history.php'; // เปลี่ยนเส้นทางไปหน้าประวัติการลา
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถบันทึกคำขอลาได้', 'error');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยื่นคำร้องการลา</title>
    <style>
        .error-message {
            color: red;
            font-size: 14px;
        }
    </style>
    <?php include('./include/cdn.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const leaveTypes = <?php echo json_encode($leave_types); ?>;

        function updateLeaveDescription() {
            const leaveTypeSelect = document.getElementById('leaveType');
            const selectedLeaveType = leaveTypeSelect.value;
            const descriptionElement = document.getElementById('leaveDescription');

            const leaveTypeData = leaveTypes.find(type => type.leave_id == selectedLeaveType);

            if (leaveTypeData) {
                descriptionElement.innerText = leaveTypeData.leave_des; 
            } else {
                descriptionElement.innerText = '';
            }
        }

        function validateDates() {
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            const today = new Date();

            if (startDate > endDate) {
                document.getElementById('error-message').innerText = 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มต้น';
                return false;
            }

            const leaveType = document.getElementById('leaveType').value;

            if (leaveType === 'religious_leave') {
                const noticeDays = Math.ceil((startDate - today) / (1000 * 60 * 60 * 24));
                if (noticeDays < 60) {
                    document.getElementById('error-message').innerText = 'คุณต้องทำเรื่องลาล่วงหน้าไม่น้อยกว่า 60 วันสำหรับการลาทำบุญทางศาสนา';
                    return false;
                }
            }

            if (leaveType === 'military_leave') {
                const noticeHours = Math.ceil((startDate - today) / (1000 * 60 * 60));
                if (noticeHours < 48) {
                    document.getElementById('error-message').innerText = 'คุณต้องทำเรื่องลาล่วงหน้าไม่น้อยกว่า 48 ชั่วโมงสำหรับการลาเข้ารับการตรวจเลือก';
                    return false;
                }
            }

            return true;
        }

        function validateFile() {
            const fileInput = document.getElementById('file');
            const file = fileInput.files[0];

            if (!file) {
                document.getElementById('error-message').innerText = 'กรุณาแนบไฟล์หลักฐาน';
                return false;
            }

            const allowedExtensions = /(\.pdf|\.jpeg|\.png)$/i;
            const maxSize = 5 * 1024 * 1024; 

            if (!allowedExtensions.exec(file.name)) {
                document.getElementById('error-message').innerText = 'ไฟล์ที่อัปโหลดต้องเป็น PDF, JPEG, หรือ PNG เท่านั้น';
                return false;
            }

            if (file.size > maxSize) {
                document.getElementById('error-message').innerText = 'ไฟล์ต้องมีขนาดไม่เกิน 5 MB';
                return false;
            }

            return true;
        }

        function handleSubmit(event) {
            event.preventDefault();
            document.getElementById('error-message').innerText = ''; 

            if (!validateDates()) {
                return;
            }

            if (!validateFile()) {
                return;
            }

            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: 'คุณต้องการส่งคำขอนี้หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('leaveForm').submit();
                } else {
                    Swal.fire('ยกเลิกแล้ว', 'คำขอการลาของคุณถูกยกเลิก', 'error');
                }
            });
        }
    </script>
</head>

<body class="bg-light">
<?php include('./include/sidebar.php'); ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center text-warning mb-4">ยื่นคำร้องการลา</h2>
                        <form id="leaveForm" action="" method="POST" enctype="multipart/form-data" onsubmit="handleSubmit(event)">
                            <div class="mb-3">
                                <label for="leaveType" class="form-label">เลือกประเภทการลา:</label>
                                <select id="leaveType" class="form-select" name="leaveType" onchange="updateLeaveDescription()" required>
                                    <option value="">-- เลือกประเภทการลา --</option>
                                    <?php foreach ($leave_types as $type): ?>
                                        <option value="<?php echo $type['leave_id']; ?>"><?php echo $type['leave_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <p id="leaveDescription" class="alert alert-info"></p> 
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">เหตุผลในการลา:</label>
                                <textarea id="description" class="form-control" name="description" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="startDate" class="form-label">วันที่เริ่มต้น:</label>
                                <input type="date" id="startDate" class="form-control" name="startDate" required>
                            </div>

                            <div class="mb-3">
                                <label for="endDate" class="form-label">วันที่สิ้นสุด:</label>
                                <input type="date" id="endDate" class="form-control" name="endDate" required>
                            </div>

                            <div class="mb-3">
                                <label for="file" class="form-label">แนบไฟล์หลักฐาน:</label>
                                <input type="file" id="file" class="form-control" name="file" accept=".pdf,.jpeg,.png">
                            </div>

                            <p id="error-message" class="error-message"></p> 

                            <div class="text-center">
                                <button type="submit" class="btn btn-warning btn-lg">ส่งคำขอการลา</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br><br>
    <?php include('./include/footer.php'); ?>
</body>

</html>

<?php
session_start();
require('./config/configDB.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
}
$sql = "SELECT * FROM Leaves";
$stmt = $conn->prepare($sql);
$stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TypeLeave</title>
    <?php include('./include/cdn.php'); ?>

</head>

<body>
    <?php include('./include/sidebar.php'); ?>
    <div class="container mt-5">
        <h1 class="mb-4">ประเภทการลา</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">ชื่อประเภท</th>
                    <th scope="col">จำนวนวันลา</th>
                    <th scope="col">รายละเอียด</th>
                    <th scope="col">แก้ไข</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($result) > 0) {
                    foreach ($result as $row) {
                        echo "<tr>";
                        echo "<td>" . $row['leave_name'] . "</td>";
                        echo "<td>" . $row['leave_Max_date'] . " วัน</td>";
                        echo "<td>" . $row['leave_des'] . "</td>";
                        echo "<td><button class='btn btn-warning btn-edit' data-id='" . $row['leave_id'] . "' data-name='" . $row['leave_name'] . "' data-max-date='" . $row['leave_Max_date'] . "' data-des='" . $row['leave_des'] . "'>แก้ไข</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>ไม่มีข้อมูล</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">แก้ไขข้อมูลประเภทการลา</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <div class="mb-3">
                            <label for="leave_name" class="form-label">ชื่อประเภทการลา</label>
                            <input type="text" class="form-control" id="leave_name" name="leave_name">
                        </div>
                        <div class="mb-3">
                            <label for="leave_Max_date" class="form-label">จำนวนวันลา</label>
                            <input type="number" class="form-control" id="leave_Max_date" name="leave_Max_date">
                        </div>
                        <div class="mb-3">
                            <label for="leave_des" class="form-label">รายละเอียด</label>
                            <input type="text" class="form-control" id="leave_des" name="leave_des">
                        </div>
                        <input type="hidden" id="leave_id" name="leave_id">
                        <button type="submit" class="btn btn-success">บันทึก</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const leaveId = this.getAttribute('data-id');
                const leaveName = this.getAttribute('data-name');
                const leaveMaxDate = this.getAttribute('data-max-date');
                const leaveDes = this.getAttribute('data-des');

                document.getElementById('leave_id').value = leaveId;
                document.getElementById('leave_name').value = leaveName;
                document.getElementById('leave_Max_date').value = leaveMaxDate;
                document.getElementById('leave_des').value = leaveDes;

                var myModal = new bootstrap.Modal(document.getElementById('editModal'));
                myModal.show();
            });
        });

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const leaveId = document.getElementById('leave_id').value;
            const leaveName = document.getElementById('leave_name').value;
            const leaveMaxDate = document.getElementById('leave_Max_date').value;
            const leaveDes = document.getElementById('leave_des').value;

            Swal.fire({
                title: 'ยืนยันการบันทึก?',
                text: "คุณต้องการบันทึกการเปลี่ยนแปลงหรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่, บันทึกเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('update_leave.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                leave_id: leaveId,
                                leave_name: leaveName,
                                leave_Max_date: leaveMaxDate,
                                leave_des: leaveDes
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                var myModalEl = document.getElementById('editModal');
                                var modal = bootstrap.Modal.getInstance(myModalEl);
                                modal.hide();

                                const row = document.querySelector(`button[data-id='${leaveId}']`).closest('tr');
                                row.querySelectorAll('td')[0].innerText = leaveName;
                                row.querySelectorAll('td')[1].innerText = leaveMaxDate + " วัน";
                                row.querySelectorAll('td')[2].innerText = leaveDes;

                                Swal.fire({
                                    icon: 'success',
                                    title: 'บันทึกสำเร็จ',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'เกิดข้อผิดพลาด',
                                    text: data.message,
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: 'Failed to save the changes',
                            });
                        });
                }
            });
        });
    </script>
<?php include('./include/footer.php'); ?>
</body>

</html>
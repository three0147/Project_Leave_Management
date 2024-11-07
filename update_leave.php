<?php
session_start();
require('./config/configDB.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['leave_id']) || !isset($data['leave_name']) || !isset($data['leave_Max_date']) || !isset($data['leave_des'])) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit;
    }

    $leave_id = $data['leave_id'];
    $leave_name = $data['leave_name'];
    $leave_Max_date = $data['leave_Max_date'];
    $leave_des = $data['leave_des'];


    $sql = "UPDATE Leaves SET leave_name = :leave_name, leave_Max_date = :leave_Max_date, leave_des = :leave_des WHERE leave_id = :leave_id";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':leave_name', $leave_name);
    $stmt->bindParam(':leave_Max_date', $leave_Max_date);
    $stmt->bindParam(':leave_des', $leave_des);
    $stmt->bindParam(':leave_id', $leave_id);


    try {
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่มีการเปลี่ยนแปลงข้อมูล']);
        }
    } catch (PDOException $e) {

        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $e->getMessage()]);
    }
} else {

    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

<?php
require('./config/configDB.php');

if (isset($_GET['leavehis_id'])) {
    $leavehis_id = $_GET['leavehis_id'];


    $query = "SELECT leavehis_id, leave_id, leavehis_start, leavehis_end, leavehis_des, leavehis_file FROM leave_his WHERE leavehis_id = :leavehis_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':leavehis_id', $leavehis_id, PDO::PARAM_INT);
    $stmt->execute();
    $leave_data = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($leave_data);
}

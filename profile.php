<?php
session_start();
require('./config/configDB.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT 
            e.emp_fname, 
            e.emp_lname, 
            e.emp_email, 
            e.phonenumber, 
            e.username, 
            e.time_start, 
            r.role_name
          FROM employees e
          JOIN role r ON e.role_id = r.role_id
          WHERE e.emp_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);




$sql = "SELECT profile_image FROM employees WHERE emp_id = :emp_id";

$stmt = $conn->prepare($sql);

$stmt->bindParam(':emp_id', $user_id, PDO::PARAM_INT);

$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของฉัน</title>
    <?php include('./include/cdn.php'); ?>
</head>

<body>
    <?php include('./include/sidebar.php'); ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="profile-section">
                    <?php
                    if (!empty($result['profile_image'])) {
                    ?>
                        <img src="./picture/<?php echo htmlspecialchars($result['profile_image']); ?>" alt="profile" class="img-fluid rounded-circle" style="width: 50%; height: auto; margin-left: 5%;">
                    <?php
                    } else {
                    ?>
                        <img src="./picture/user.png" alt="default profile" class="img-fluid rounded-circle" style="width: 50%; height: auto; margin-left: 5%;">
                    <?php
                    }
                    ?>
                    <h3><?php echo $user['emp_fname'] . ' ' . $user['emp_lname']; ?></h3>
                </div>
            </div>

            <div class="col-md-9">

                <form method="POST" action="update_profile.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="emp_fname" class="form-label">ชื่อ</label>
                        <input type="text" class="form-control" id="emp_fname" name="emp_fname" value="<?php echo $user['emp_fname']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="emp_lname" class="form-label">นามสกุล</label>
                        <input type="text" class="form-control" id="emp_lname" name="emp_lname" value="<?php echo $user['emp_lname']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="emp_email" class="form-label">อีเมล์</label>
                        <input type="email" class="form-control" id="emp_email" name="emp_email" value="<?php echo $user['emp_email']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="phonenumber" class="form-label">เบอร์โทร</label>
                        <input type="text" class="form-control" id="phonenumber" name="phonenumber" value="<?php echo $user['phonenumber']; ?>">
                    </div>

                </form>
            </div>
        </div>
    </div>
</body>

</html>
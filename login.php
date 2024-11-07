<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave - Login</title>
    <?php include('./include/cdn.php'); ?>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .form-container {
            margin-top: 100px;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }

        .form-container .btn-primary {
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .form-container .btn-primary:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }

        .form-container .form-control:focus {
            border-color: #ffc107;
            box-shadow: none;
        }

        .form-container a {
            color: #ffc107;
        }

        .form-container a:hover {
            color: #e0a800;
        }
    </style>
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="form-container col-md-4">
            <div class="text-center mb-4">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSC-QINJxLvDdX4ZWonlEK9Gx_Z8ktv2kbHIA&s" alt="Logo" class="img-fluid mb-3" style="width: 100px;">

                <h3>Login</h3>
            </div>
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Log In</button>
            </form>
        </div>
    </div>

    <?php
    session_start();
    require('./config/configDB.php');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM employees WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && md5($password) === $user['password']) {
            $_SESSION['user_id'] = $user['emp_id'];
            $_SESSION['username'] = $user['emp_fname'];
            $_SESSION['role'] = $user['role_id'];
            $_SESSION['profile'] = $user['profile_image'];

            echo '<script>
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "เข้าสู่ระบบสำเร็จ",
                    showConfirmButton: false,
                    timer: 1500
                });
                setTimeout(function() {
                    window.location.href = "./index.php";
                }, 1500); 
                </script>';
        } else {
            echo '<script>
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "เข้าสู่ระบบไม่สำเร็จ",
                    showConfirmButton: false,
                    timer: 1500
                });
                setTimeout(function() {
                    window.location.href = "./login.php";
                }, 1500); 
                </script>';
        }
    }
    ?>
</body>

</html>

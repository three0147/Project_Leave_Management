<?php
session_start();
require('./config/configDB.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];


    try {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);


        $sql = "INSERT INTO users (username, password, email) VALUES (:username, :password, :email)";
        $stmt = $conn->prepare($sql);


        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);


        $stmt->execute();

        echo '<script>
            Swal.fire({
                icon: "success",
                title: "ลงทะเบียนสำเร็จ",
                showConfirmButton: false,
                timer: 1500
            });
            setTimeout(function() {
                window.location.href = "login.php";
            }, 1500);
        </script>';
    } catch (PDOException $e) {
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "ลงทะเบียนสำเร็จ",
                showConfirmButton: false,
                timer: 1500
            });
            setTimeout(function() {
                window.location.href = "login.php";
            }, 1500);
        </script>';
    }

    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
                <h3>Register</h3>
            </div>
            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>

</html>
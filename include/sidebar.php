<div class="container-fluid">

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container p-2 my-2" style="background-color: #ffffe0; font-size: 18px; font-family: 'NTFont', sans-serif;">

            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSC-QINJxLvDdX4ZWonlEK9Gx_Z8ktv2kbHIA&s" alt="โลโก้บริษัท NT" width="100" style="margin-right: 10px;">
            <a class="navbar-brand" href="index.php" style="font-size: 28px; margin-right: 10px;">ระบบลาบริษัท NT</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">หน้าแรก</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="leave_request.php">ยื่นคำร้องการลา</a>
                    </li>

                    <?php
                    if ($_SESSION['role'] == 1) {
                    ?>
                        <li class="nav-item">
                            <a class="nav-link" href="leave_approve.php">ตรวจสอบคำขอลา</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="typeleave.php">ประเภทการลา</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="leave_report.php">รายงานการลา</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                    <?php
                    }
                    ?>

                </ul>

                <?php if (isset($_SESSION['user_id'])) { ?>
                    <div class="dropdown me-3">
                        <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" id="dropdownProfile" data-bs-toggle="dropdown" aria-expanded="false">

                            <?php
                            $profileImage = !empty($_SESSION['profile']) ? $_SESSION['profile'] : 'user.png';
                            ?>
                            <img src="./picture/<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="rounded-circle" width="40" height="40">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownProfile">
                            <li><a class="dropdown-item" href="profile.php">ข้อมูลส่วนตัว</a></li>
                            <?php
                            if ($_SESSION['role'] == 1) {
                            ?>
                                <li><a class="dropdown-item" href="addemployee.php">เพิ่มพนักงาน</a></li>
                            <?php } ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>


                            <?php
                            if (!isset($_SESSION['user_id'])) {
                            ?>
                                <li><a class="dropdown-item" href="login.php">เข้าสู่ระบบ</a></li>
                            <?php
                            } else {
                            ?>
                                <li><a class="dropdown-item" href="logout.php">ออกจากระบบ</a></li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                <?php } ?>



            </div>
        </div>
    </nav>
</div>
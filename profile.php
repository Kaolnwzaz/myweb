<?php
session_start();
require "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$sql = "SELECT * FROM tbl_user WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
?>

<link rel="stylesheet" href="style.css">
<?php include 'navbar.php'; ?>

<div class="form-container">
    <h2>โปรไฟล์ของฉัน</h2>

    <?php if (!empty($user["img"])): ?>
        <img src="uploads/<?php echo htmlspecialchars($user["img"]); ?>" alt="profile" style="max-width: 160px; border-radius: 50%; margin-bottom: 15px;">
    <?php endif; ?>

    <p><strong>รหัสผู้ใช้:</strong> <?php echo htmlspecialchars($user["id"]); ?></p>
    <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($user["name"]); ?></p>
    <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($user["email"]); ?></p>
    <p><strong>โทรศัพท์:</strong> <?php echo htmlspecialchars($user["phone"] ?? "-"); ?></p>

    <p><a href="profile_update.php" class="btn">แก้ไขโปรไฟล์</a></p>
    <p><a href="logout.php">ออกจากระบบ</a></p>
</div>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="navbar">
    <a href="index.php">หน้าแรก</a>
    <a href="about.php">เกี่ยวกับเรา</a>

    <?php if (isset($_SESSION["user_id"])) { ?>
        <a href="upload.php">อัปโหลดรูป</a>
        <a href="files.php">อัปโหลด PDF</a>
        <a href="profile.php"><?php echo htmlspecialchars($_SESSION["name"] ?? "โปรไฟล์"); ?></a>
        <a href="logout.php">ออกจากระบบ</a>
    <?php } else { ?>
        <a href="login.php">เข้าสู่ระบบ</a>
        <a href="register.php">สมัครสมาชิก</a>
    <?php } ?>
</div>
<?php
session_start();
require "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$message = "";
$name = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $imageName = null;

    if ($name === "") {
        $message = "กรุณากรอกชื่อให้ครบ";
    } else {
        if (!empty($_FILES["image"]["name"])) {
            $targetDir = "uploads/";

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = time() . "_" . basename($_FILES["image"]["name"]);
            $targetFile = $targetDir . $fileName;

            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $allowTypes = ["jpg", "jpeg", "png", "gif", "webp"];

            if (in_array($fileType, $allowTypes, true) && move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $imageName = $fileName;
            } else {
                $message = "ไฟล์รูปภาพไม่ถูกต้อง";
            }
        }

        if ($message === "") {
            if ($imageName !== null) {
                $sql = "UPDATE tbl_user SET name = ?, phone = ?, img = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $imageName, $user_id);
            } else {
                $sql = "UPDATE tbl_user SET name = ?, phone = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $name, $phone, $user_id);
            }

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: profile.php?success=1");
                exit;
            }

            $message = "ไม่สามารถอัปเดตโปรไฟล์ได้";
            mysqli_stmt_close($stmt);
        }
    }
}

$sql = "SELECT id, name, phone, img FROM tbl_user WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($user) {
    $name = $user["name"] ?? $name;
    $phone = $user["phone"] ?? $phone;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "navbar.php"; ?>

    <div class="form-container">
        <h2>แก้ไขโปรไฟล์</h2>

        <?php if ($message !== ""): ?>
            <p style="color: #b91c1c;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form action="profile_update.php" method="post" enctype="multipart/form-data">
            <label for="name" class="label">ชื่อ:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required class="input-field">

            <label for="phone" class="label">โทรศัพท์:</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" class="input-field">

            <label for="image" class="label">รูปภาพโปรไฟล์:</label>
            <input type="file" id="image" name="image" accept="image/*" class="input-field">

            <input type="submit" value="บันทึก" class="btn">
        </form>

        <p><a href="profile.php">กลับไปหน้ารายละเอียดโปรไฟล์</a></p>
    </div>
</body>
</html>
<?php
session_start();
include "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$user_id = $_SESSION["user_id"];

if (isset($_POST["upload"])) {

    $file_name = $_FILES["image"]["name"];
    $file_tmp  = $_FILES["image"]["tmp_name"];
    $file_size = $_FILES["image"]["size"];
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed_images = ["jpg", "jpeg", "png", "gif"];
    $allowed_files = ["pdf"];
    $allowed = array_merge($allowed_images, $allowed_files);

    if (!in_array($file_ext, $allowed)) {
        $message = "อนุญาตเฉพาะ JPG, JPEG, PNG, GIF, PDF";
    } elseif ($file_size > 5 * 1024 * 1024) {
        $message = "ไฟล์ต้องไม่เกิน 5MB";
    } else {
        $new_name = uniqid("FILE_", true) . "." . $file_ext;
        $upload_path = in_array($file_ext, $allowed_images) ? "uploads/" . $new_name : "files/" . $new_name;

        if (move_uploaded_file($file_tmp, $upload_path)) {

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO tbl_upload (user_id, image_name) VALUES (?, ?)"
            );

            mysqli_stmt_bind_param($stmt, "is", $user_id, $new_name);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Upload สำเร็จ";
            } else {
                $message = "บันทึกฐานข้อมูลไม่สำเร็จ";
            }

        } else {
            $message = "Upload ไม่สำเร็จ";
        }
    }
}

echo "<link rel='stylesheet' href='style.css'>";
include 'navbar.php';

?>

<h2>Upload ไฟล์</h2>

<p>
    ผู้ใช้งาน: <?php echo htmlspecialchars($_SESSION["name"] ?? ""); ?>
</p>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.pdf" required>
    <button type="submit" name="upload">Upload</button>
</form>

<p><?php echo htmlspecialchars($message); ?></p>

<hr>

<h2>ไฟล์ของฉัน</h2>

<div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px;">
<?php
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM tbl_upload WHERE user_id = ? ORDER BY id DESC"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $file_name = htmlspecialchars($row["image_name"]);
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    echo "<div style='text-align: center; padding: 10px; border: 1px solid #ddd; border-radius: 8px;'>";

    if ($file_ext === "pdf") {
        echo "<div style='font-size: 40px; margin-bottom: 10px;'>📄</div>";
        echo "<a href='files/" . $file_name . "' target='_blank' download style='display: inline-block; color: #007bff; text-decoration: none;'>";
        echo "<div style='font-size: 14px; word-break: break-all; margin-bottom: 8px;'>" . $file_name . "</div>";
        echo "</a>";
    } else {
        echo "<img src='uploads/" . $file_name . "' style='max-width: 150px; width: 100%; height: auto; display: block; margin: 0 auto 10px;'>";
        echo "<div style='font-size: 14px; word-break: break-all; margin-bottom: 8px;'>" . $file_name . "</div>";
    }

    echo "<a href='delete_image.php?id=" . htmlspecialchars($row["id"]) . "' onclick='return confirm(\"คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์นี้?\")' style='color: #c00;'>ลบ</a>";
    echo "</div>";
}

?>
</div>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myweb";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS tbl_user (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL,
        email VARCHAR(50) NOT NULL,
        password TEXT NOT NULL,
        img VARCHAR(255) NOT NULL DEFAULT '',
        phone VARCHAR(20) NOT NULL DEFAULT '',
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS tbl_upload (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        image_name VARCHAR(255) NOT NULL,
        uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        CONSTRAINT tbl_upload_ibfk_1 FOREIGN KEY (user_id) REFERENCES tbl_user (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS tbl_files (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        files_name VARCHAR(255) NOT NULL,
        uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        CONSTRAINT tbl_files_ibfk_1 FOREIGN KEY (user_id) REFERENCES tbl_user (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS relay_states (
        relay_id TINYINT NOT NULL,
        state TINYINT NOT NULL DEFAULT 0,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (relay_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS switch_events (
        id INT(11) NOT NULL AUTO_INCREMENT,
        switch_id TINYINT NOT NULL,
        state TINYINT NOT NULL,
        event_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY switch_id (switch_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS sensor_ldr (
        id INT(11) NOT NULL AUTO_INCREMENT,
        value INT NOT NULL,
        recorded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS api_keys (
        id INT(11) NOT NULL AUTO_INCREMENT,
        api_key VARCHAR(128) NOT NULL,
        description VARCHAR(255) NOT NULL DEFAULT '',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY (api_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// insert default API key if not exists (change value if you prefer)
$default_api_key = 'CYVCitIJfpgxHWUkA8xV3q7fJmyufbIB5Te5Rabv';
mysqli_query($conn, "INSERT INTO api_keys (api_key, description)
    SELECT '" . mysqli_real_escape_string($conn, $default_api_key) . "', 'default esp32 key'
    FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM api_keys WHERE api_key = '" . mysqli_real_escape_string($conn, $default_api_key) . "')");
?>
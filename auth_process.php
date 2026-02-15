<?php
session_start();
include 'config/database.php';

// --- LOGIKA REGISTRASI ---
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Password konfirmasi tidak cocok!";
        header("Location: register.php");
        exit();
    }

    $check_query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Username sudah dipakai.";
        header("Location: register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user';

    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Pendaftaran berhasil! Silakan login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        header("Location: register.php");
        exit();
    }
}

// --- LOGIKA LOGIN (UPGRADE V3: SESSION MANAGEMENT) ---
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $row['password'])) {
            // Login Berhasil
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            // FITUR BARU: GENERATE SESSION ID UNIK
            // Format: user_id_timestamp_random
            // Ini membuat setiap login baru dianggap sesi chat baru
            $new_session_id = $row['id'] . '_' . time() . '_' . rand(1000, 9999);
            $_SESSION['current_chat_session'] = $new_session_id;

            if ($row['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user/chat.php");
            }
            exit();
        }
    }

    $_SESSION['error'] = "Username atau Password salah!";
    
    // LOGIKA REDIRECT PINTAR
    // Jika login dilakukan dari Modal di halaman Chat, kembalikan ke Chat (bukan ke login.php)
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'chat.php') !== false) {
        header("Location: user/chat.php?login_error=1");
    } else {
        header("Location: login.php");
    }
    exit();
}
?>
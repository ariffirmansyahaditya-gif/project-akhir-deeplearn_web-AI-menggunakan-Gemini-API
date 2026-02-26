<?php
session_start();
include '../config/database.php';

// Keamanan: Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Proses Hapus
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Query hapus (karena ada ON DELETE CASCADE di database, riwayat chat juga otomatis terhapus)
    $query = "DELETE FROM users WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: dashboard.php?msg=deleted");
    } else {
        echo "Gagal menghapus: " . mysqli_error($conn);
    }
} else {
    header("Location: dashboard.php");
}
?>
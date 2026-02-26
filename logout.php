<?php
session_start();

// Hapus semua data sesi
session_unset();
session_destroy();

// Kembalikan ke halaman awal
header("Location: index.php");
exit();
?>
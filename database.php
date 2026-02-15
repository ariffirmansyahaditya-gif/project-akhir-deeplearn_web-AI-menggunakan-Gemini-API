<?php
// Konfigurasi Database untuk XAMPP
$host = "localhost";
$username = "root";     // Default XAMPP username
$password = "";         // Default XAMPP password (kosong)
$database = "medigemini_db";

// Membuat koneksi ke MySQL
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// (Opsional) Uncomment baris di bawah ini untuk tes koneksi saat coding awal
// echo "Koneksi Berhasil!";
?>
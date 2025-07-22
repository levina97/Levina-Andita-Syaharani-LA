<?php
include 'config/config.php';

$nama = $_POST['nama'];
$email = $_POST['email'];
$password = md5($_POST['password']);
$role = 'pelanggan'; // default role

$query = mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', '$role')");

if ($query) {
    echo "<script>alert('Pendaftaran berhasil! Silakan login.');window.location='login.php';</script>";
} else {
    echo "<script>alert('Gagal mendaftar! Email mungkin sudah digunakan.');window.location='register.php';</script>";
}
?>

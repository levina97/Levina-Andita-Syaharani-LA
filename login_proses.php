<?php
session_start();
include 'config/config.php';

$email = $_POST['email'];
$password_input = $_POST['password'];
$password_md5 = md5($password_input);

// Ambil data user berdasarkan email
$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    $password_db = $data['password'];

    // Cek apakah password di database adalah hash bcrypt (panjang > 30 dan dimulai dengan $2y$ atau $argon2)
    if (password_verify($password_input, $password_db)) {
        // Cocok dengan password_hash()
        $_SESSION['user'] = $data;
    } elseif ($password_db === $password_md5) {
        // Cocok dengan md5 lama
        $_SESSION['user'] = $data;

        // Opsional: upgrade password ke password_hash setelah login berhasil
        $new_hash = password_hash($password_input, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$new_hash' WHERE email='$email'");
    } else {
        // Password salah
        echo "<script>alert('Login gagal! Password salah.');window.location='login.php';</script>";
        exit;
    }

    // Arahkan ke halaman berdasarkan role
    if ($data['role'] == 'admin') {
        header("Location: pages/admin/index.php");
    } elseif ($data['role'] == 'staff') {
        header("Location: pages/staff/index.php");
    } else {
        header("Location: pages/pelanggan/index.php");
    }
    exit;
} else {
    echo "<script>alert('Login gagal! Email tidak ditemukan.');window.location='login.php';</script>";
}
?>

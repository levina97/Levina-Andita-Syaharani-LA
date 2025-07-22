<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

// Ambil id inspeksi dari URL
$id_inspeksi = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_inspeksi <= 0) {
    $_SESSION['error'] = "ID inspeksi tidak valid.";
    header("Location: index.php");
    exit;
}

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // 1. Hapus sertifikat terkait jika ada
    $query_delete_sertifikat = "DELETE FROM sertifikat WHERE id_inspeksi = ?";
    $stmt_sertifikat = mysqli_prepare($conn, $query_delete_sertifikat);
    mysqli_stmt_bind_param($stmt_sertifikat, 'i', $id_inspeksi);
    mysqli_stmt_execute($stmt_sertifikat);
    
    // 2. Hapus data inspeksi
    $query_delete_inspeksi = "DELETE FROM inspeksi WHERE id_inspeksi = ? AND petugas_id = ?";
    $stmt_inspeksi = mysqli_prepare($conn, $query_delete_inspeksi);
    mysqli_stmt_bind_param($stmt_inspeksi, 'ii', $id_inspeksi, $_SESSION['user']['id_user']);
    mysqli_stmt_execute($stmt_inspeksi);
    
    // Commit transaksi jika semua berhasil
    mysqli_commit($conn);
    
    if (mysqli_stmt_affected_rows($stmt_inspeksi) > 0) {
        $_SESSION['success'] = "Data inspeksi berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Data tidak ditemukan atau tidak dapat dihapus.";
    }
} catch (Exception $e) {
    // Rollback transaksi jika ada error
    mysqli_rollback($conn);
    $_SESSION['error'] = "Terjadi kesalahan saat menghapus data: " . $e->getMessage();
}

mysqli_stmt_close($stmt_sertifikat);
mysqli_stmt_close($stmt_inspeksi);
header("Location: inspeksi.php");
exit;
?>
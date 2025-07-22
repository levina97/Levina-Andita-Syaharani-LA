<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        $status = 'approved';
    } else if ($action == 'reject') {
        $status = 'rejected';
    } else {
        header("Location: kelola_pengajuan.php");
        exit;
    }
    
    $query = "UPDATE pengajuan SET status = '$status' WHERE id_pengajuan = $id";
    
    if (mysqli_query($conn, $query)) {
        header("Location: detail_pengajuan.php?id=$id");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: kelola_pengajuan.php");
}
?>

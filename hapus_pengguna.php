<?php
include '../../config/config.php';

// Process only if ID is provided via POST
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Validate ID
    if ($id <= 0) {
        $_SESSION['error_message'] = "ID pengguna tidak valid";
        header("Location: kelola_pengguna.php");
        exit;
    }
    
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Hapus data inspeksi terkait melalui kontrak
        $query1 = mysqli_query($conn, "DELETE i FROM inspeksi i
                                      INNER JOIN kontrak k ON i.id_kontrak = k.id_kontrak
                                      WHERE k.id_user = $id");
        if (!$query1) {
            throw new Exception("Gagal menghapus data inspeksi: " . mysqli_error($conn));
        }
        
        // 2. Hapus kontrak terkait pengguna
        $query2 = mysqli_query($conn, "DELETE FROM kontrak WHERE id_user = $id");
        if (!$query2) {
            throw new Exception("Gagal menghapus kontrak: " . mysqli_error($conn));
        }
        
        // 3. Hapus pengguna
        $query3 = mysqli_query($conn, "DELETE FROM users WHERE id_user = $id");
        if (!$query3) {
            throw new Exception("Gagal menghapus pengguna: " . mysqli_error($conn));
        }
        
        // Commit transaksi jika semua berhasil
        mysqli_commit($conn);
        $_SESSION['success_message'] = "Pengguna dan data terkait berhasil dihapus";
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        $_SESSION['error_message'] = $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Permintaan tidak valid";
}

// Redirect back to user management page
header("Location: kelola_pengguna.php");
exit;
?>
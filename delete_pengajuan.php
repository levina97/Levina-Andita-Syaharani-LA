<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

// Verify id parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID pengajuan tidak valid";
    header("Location: kelola_pengajuan.php");
    exit;
}

$id = intval($_GET['id']);

// Get pengajuan details before deletion (for logging or notification purposes)
$query = "SELECT * FROM pengajuan WHERE id_pengajuan = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Pengajuan tidak ditemukan";
    header("Location: kelola_pengajuan.php");
    exit;
}

$pengajuan = mysqli_fetch_assoc($result);

// Begin transaction
mysqli_begin_transaction($conn);

try {
    // Check for dependent data
    
    // 1. Check for objek_inspeksi records and delete files
    $objek_query = "SELECT * FROM objek_inspeksi WHERE id_pengajuan = $id";
    $objek_result = mysqli_query($conn, $objek_query);
    
    while ($objek = mysqli_fetch_assoc($objek_result)) {
        // Delete physical files if they exist
        $file_fields = [
            'spesifikasi_teknis', 'manual_operasi', 'gambar_teknis', 
            'laporan_hasil', 'pengesahan_pemakaian', 'catatan_pemeliharaan', 
            'surat_izin_operator'
        ];
        
        foreach ($file_fields as $field) {
            if (!empty($objek[$field]) && file_exists('../../uploads/objek/' . $objek[$field])) {
                unlink('../../uploads/objek/' . $objek[$field]);
            }
        }
    }
    
    // 2. Check for contracts and related inspections/certificates
    $kontrak_query = "SELECT id_kontrak, file_kontrak FROM kontrak WHERE id_pengajuan = $id";
    $kontrak_result = mysqli_query($conn, $kontrak_query);
    
    while ($kontrak = mysqli_fetch_assoc($kontrak_result)) {
        $kontrak_id = $kontrak['id_kontrak'];
        
        // Find inspections related to this contract
        $inspeksi_query = "SELECT id_inspeksi FROM inspeksi WHERE id_kontrak = $kontrak_id";
        $inspeksi_result = mysqli_query($conn, $inspeksi_query);
        
        while ($inspeksi = mysqli_fetch_assoc($inspeksi_result)) {
            $inspeksi_id = $inspeksi['id_inspeksi'];
            
            // Delete certificates related to this inspection
            $sertifikat_query = "SELECT file_sertifikat FROM sertifikat WHERE id_inspeksi = $inspeksi_id";
            $sertifikat_result = mysqli_query($conn, $sertifikat_query);
            
            while ($sertifikat = mysqli_fetch_assoc($sertifikat_result)) {
                if (!empty($sertifikat['file_sertifikat']) && file_exists('../../uploads/sertifikat/' . $sertifikat['file_sertifikat'])) {
                    unlink('../../uploads/sertifikat/' . $sertifikat['file_sertifikat']);
                }
            }
            
            // Delete certificates records
            $delete_sertifikat = "DELETE FROM sertifikat WHERE id_inspeksi = $inspeksi_id";
            mysqli_query($conn, $delete_sertifikat);
        }
        
        // Delete inspections related to this contract
        $delete_inspeksi = "DELETE FROM inspeksi WHERE id_kontrak = $kontrak_id";
        mysqli_query($conn, $delete_inspeksi);
        
        // Delete contract file
        if (!empty($kontrak['file_kontrak']) && file_exists('../../uploads/kontrak/' . $kontrak['file_kontrak'])) {
            unlink('../../uploads/kontrak/' . $kontrak['file_kontrak']);
        }
    }
    
    // Delete in order based on foreign key constraints
    
    // 1. Delete objek_inspeksi records (has CASCADE on id_pengajuan)
    $delete_objek = "DELETE FROM objek_inspeksi WHERE id_pengajuan = $id";
    mysqli_query($conn, $delete_objek);
    
    // 2. Delete kontrak records (has CASCADE on id_pengajuan)
    $delete_kontrak = "DELETE FROM kontrak WHERE id_pengajuan = $id";
    mysqli_query($conn, $delete_kontrak);
    
    // 3. Finally delete the pengajuan
    $delete_pengajuan = "DELETE FROM pengajuan WHERE id_pengajuan = $id";
    $delete_result = mysqli_query($conn, $delete_pengajuan);
    
    if (!$delete_result) {
        throw new Exception("Gagal menghapus pengajuan");
    }
    
    // Commit if everything is successful
    mysqli_commit($conn);
    
    // Log activity if desired
    // logActivity($_SESSION['user']['id_user'], "Menghapus pengajuan #$id - {$pengajuan['nama_client']}");
    
    $_SESSION['success'] = "Pengajuan berhasil dihapus";
} catch (Exception $e) {
    // Rollback changes if there's an error
    mysqli_rollback($conn);
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

// Redirect back to the management page
header("Location: kelola_pengajuan.php");
exit;
?>

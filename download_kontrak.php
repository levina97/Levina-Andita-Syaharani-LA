<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pelanggan') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

if (isset($_GET['id'])) {
    $kontrak_id = $_GET['id'];
    $user_id = $_SESSION['user']['id_user'];
    
    // Verifikasi bahwa kontrak ini milik pelanggan yang sedang login
    $query = "SELECT k.*, p.id_user FROM kontrak k 
              JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan 
              WHERE k.id_kontrak = $kontrak_id AND p.id_user = $user_id";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $kontrak = mysqli_fetch_assoc($result);
        
        // Periksa apakah kontrak masih berlaku
        if (!isKontrakExpired($kontrak['tanggal_berakhir'])) {
            $file_path = '../../uploads/kontrak/' . $kontrak['file_kontrak'];
            
            if (file_exists($file_path)) {
                // Set header untuk download
                header('Content-Description: File Transfer');
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($kontrak['file_kontrak']) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_path));
                readfile($file_path);
                exit;
            } else {
                echo "File tidak ditemukan.";
            }
        } else {
            echo "Kontrak sudah kedaluwarsa.";
        }
    } else {
        echo "Anda tidak memiliki akses ke kontrak ini.";
    }
} else {
    echo "ID kontrak tidak valid.";
}
?>

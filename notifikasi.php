<?php
require_once '../config/config.php';

$sertifikat = mysqli_query($conn, 
    "SELECT s.*, p.email 
     FROM sertifikat s
     JOIN kontrak k ON s.kontrak_id = k.id
     JOIN pengajuan p ON k.pengajuan_id = p.id
     WHERE DATEDIFF(s.tanggal_expired, NOW()) BETWEEN 1 AND 30");

while($s = mysqli_fetch_assoc($sertifikat)) {
    mail($s['email'], "Pengingat Sertifikat", 
        "Sertifikat Anda akan kadaluarsa pada ".$s['tanggal_expired']);
}
?>
<?php
function uploadFile($file, $targetDir) {
    $fileName = basename($file["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Buat nama file unik
    $fileName = uniqid() . '_' . $fileName;
    $targetFilePath = $targetDir . $fileName;
    
    // Periksa ukuran file (maksimal 10MB)
    if ($file["size"] > 10000000) {
        return false;
    }
    
    // Periksa format file (hanya PDF)
    if ($fileType != "pdf") {
        return false;
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
        return $fileName;
    } else {
        return false;
    }
}

function formatDate($date) {
    return date('d-m-Y', strtotime($date));
}

function isKontrakExpired($tanggal_berakhir) {
    $today = new DateTime();
    $expiry = new DateTime($tanggal_berakhir);
    return $today > $expiry;
}

function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}


?>

<?php
function createPengajuan($data, $files) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO pengajuan (user_id, nama_client, perusahaan, alamat, npwp, kegiatan, wilayah, kontak, jenis_objek) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("issssssss", 
        $_SESSION['user']['id'],
        $data['nama_client'],
        $data['perusahaan'],
        $data['alamat'],
        $data['npwp'],
        $data['kegiatan'],
        $data['wilayah'],
        $data['kontak'],
        $data['jenis_objek']
    );
    
    if($stmt->execute()) {
        $pengajuan_id = $stmt->insert_id;
        handleFileUpload($pengajuan_id, $files);
        return true;
    }
    return false;
}

function handleFileUpload($pengajuan_id, $files) {
    global $conn;

    $upload_dir = "../uploads/dokumen_pengajuan/";

    // Daftar dokumen dan nama kolomnya di database
    $dokumen = [
        'spesifikasi_teknis' => $files['spesifikasi_teknis'],
        'manual_operasi' => $files['manual_operasi'],
        'gambar_teknis' => $files['gambar_teknis'],
        'laporan_uji' => $files['laporan_uji'],
        'pengesahan_pemerintah' => $files['pengesahan_pemerintah'],
        'catatan_pemeliharaan' => $files['catatan_pemeliharaan'],
        'surat_izin' => $files['surat_izin']
    ];

    $file_paths = [];

    foreach ($dokumen as $key => $file) {
        if ($file['size'] > 0) {
            $filename = uniqid() . '_' . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $upload_dir . $filename);
            $file_paths[$key] = $filename;
        } else {
            $file_paths[$key] = null; // Jika file tidak diupload
        }
    }

    // Siapkan query insert
    $stmt = $conn->prepare("
        INSERT INTO dokumen_pengajuan (
            pengajuan_id,
            spesifikasi_teknis,
            manual_operasi,
            gambar_teknis,
            laporan_uji,
            pengesahan_pemerintah,
            catatan_pemeliharaan,
            surat_izin
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isssssss",
        $pengajuan_id,
        $file_paths['spesifikasi_teknis'],
        $file_paths['manual_operasi'],
        $file_paths['gambar_teknis'],
        $file_paths['laporan_uji'],
        $file_paths['pengesahan_pemerintah'],
        $file_paths['catatan_pemeliharaan'],
        $file_paths['surat_izin']
    );

    $stmt->execute();
}
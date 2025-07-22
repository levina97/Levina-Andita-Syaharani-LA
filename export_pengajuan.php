<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';

// Ambil semua data pengajuan
$query = "SELECT p.*, u.nama, u.email as user_email FROM pengajuan p 
          JOIN users u ON p.id_user = u.id_user 
          ORDER BY p.tanggal_pengajuan DESC";
$result = mysqli_query($conn, $query);

// Set header untuk download CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="data_pengajuan_' . date('Y-m-d') . '.csv"');

// Buat file pointer untuk output
$output = fopen('php://output', 'w');

// Tambahkan header CSV
fputcsv($output, [
    'ID', 'Nama Client', 'Nama Perusahaan', 'Alamat', 'NPWP', 
    'Kegiatan TIC', 'Wilayah', 'Telepon', 'Email', 'Status', 
    'Tanggal Pengajuan', 'Nama Pengguna', 'Email Pengguna'
]);

// Tambahkan data ke CSV
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['id_pengajuan'],
        $row['nama_client'],
        $row['nama_perusahaan'],
        $row['alamat'],
        $row['npwp'],
        $row['kegiatan_tic'],
        $row['wilayah'],
        $row['telp'],
        $row['email'],
        $row['status'],
        $row['tanggal_pengajuan'],
        $row['nama'],
        $row['user_email']
    ]);
}

fclose($output);
exit;
?>

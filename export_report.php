<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

// Query untuk mengambil data lengkap
$report_query = "SELECT 
    p.id_pengajuan,
    p.nama_perusahaan,
    p.nama_client,
    p.alamat,
    p.npwp,
    p.kegiatan_tic,
    p.wilayah,
    p.telp,
    p.email,
    p.status as status_pengajuan,
    p.tanggal_pengajuan,
    k.id_kontrak,
    k.tanggal_berakhir as kontrak_berakhir,
    k.status as status_kontrak,
    i.id_inspeksi,
    i.tanggal_inspeksi,
    i.lokasi,
    i.objek_diperiksa,
    i.kondisi,
    i.status_perbaikan,
    u.nama as petugas_nama,
    s.id_sertifikat,
    s.tanggal_terbit,
    s.tanggal_berakhir as sertifikat_berakhir,
    s.status as status_sertifikat
FROM pengajuan p
LEFT JOIN kontrak k ON p.id_pengajuan = k.id_pengajuan
LEFT JOIN inspeksi i ON k.id_kontrak = i.id_kontrak
LEFT JOIN users u ON i.petugas_id = u.id_user
LEFT JOIN sertifikat s ON i.id_inspeksi = s.id_inspeksi
ORDER BY p.tanggal_pengajuan DESC";

$report_result = mysqli_query($conn, $report_query);

// Hitung statistik untuk summary
$stats_query = "SELECT 
    COUNT(DISTINCT p.id_pengajuan) as total_pengajuan,
    COUNT(DISTINCT CASE WHEN p.status = 'approved' THEN p.id_pengajuan END) as pengajuan_approved,
    COUNT(DISTINCT CASE WHEN p.status = 'pending' THEN p.id_pengajuan END) as pengajuan_pending,
    COUNT(DISTINCT CASE WHEN p.status = 'rejected' THEN p.id_pengajuan END) as pengajuan_rejected,
    COUNT(DISTINCT k.id_kontrak) as total_kontrak,
    COUNT(DISTINCT CASE WHEN k.status = 'aktif' THEN k.id_kontrak END) as kontrak_aktif,
    COUNT(DISTINCT i.id_inspeksi) as total_inspeksi,
    COUNT(DISTINCT CASE WHEN i.kondisi = 'baik' THEN i.id_inspeksi END) as inspeksi_baik,
    COUNT(DISTINCT s.id_sertifikat) as total_sertifikat,
    COUNT(DISTINCT CASE WHEN s.status = 'active' THEN s.id_sertifikat END) as sertifikat_aktif
FROM pengajuan p
LEFT JOIN kontrak k ON p.id_pengajuan = k.id_pengajuan
LEFT JOIN inspeksi i ON k.id_kontrak = i.id_kontrak
LEFT JOIN sertifikat s ON i.id_inspeksi = s.id_inspeksi";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Jika request untuk download/print, generate file
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'print') {
        generatePrintReport($report_result, $stats);
    } elseif ($_GET['action'] == 'excel') {
        generateExcelReport($report_result, $stats);
    }
    exit;
}

function generatePrintReport($data, $stats) {
    // Reset pointer hasil query
    mysqli_data_seek($data, 0);
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Laporan Monitoring K3</title>
        <style>
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
                .print-break { page-break-after: always; }
            }
            
            body { 
                font-family: Arial, sans-serif; 
                font-size: 12px; 
                margin: 20px;
            }
            
            .header { 
                text-align: center; 
                margin-bottom: 30px; 
                border-bottom: 2px solid #333;
                padding-bottom: 20px;
            }
            
            .header h1 {
                font-size: 18px;
                margin: 10px 0;
                color: #333;
            }
            
            .header h2 {
                font-size: 16px;
                margin: 5px 0;
                color: #666;
            }
            
            .summary { 
                margin-bottom: 20px; 
                background-color: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
            }
            
            .summary h3 {
                margin-top: 0;
                color: #333;
                border-bottom: 1px solid #ddd;
                padding-bottom: 5px;
            }
            
            .summary table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 10px;
            }
            
            .summary th, .summary td { 
                border: 1px solid #ddd; 
                padding: 8px; 
                text-align: left; 
            }
            
            .summary th { 
                background-color: #e9ecef; 
                font-weight: bold;
            }
            
            .data-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 20px; 
            }
            
            .data-table th, .data-table td { 
                border: 1px solid #ddd; 
                padding: 6px; 
                font-size: 10px; 
            }
            
            .data-table th { 
                background-color: #4e73df; 
                color: white; 
                font-weight: bold;
                text-align: center;
            }
            
            .data-table tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            
            .status-approved { color: #28a745; font-weight: bold; }
            .status-pending { color: #ffc107; font-weight: bold; }
            .status-rejected { color: #dc3545; font-weight: bold; }
            
            .footer {
                margin-top: 30px;
                text-align: right;
                font-size: 10px;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 10px;
            }
            
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                background-color: #007bff;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
            }
            
            .print-button:hover {
                background-color: #0056b3;
            }
        </style>
        <script>
            window.onload = function() {
                // Auto print ketika halaman selesai dimuat
                setTimeout(function() {
                    window.print();
                }, 500);
            }
            
            function printReport() {
                window.print();
            }
            
            function closeWindow() {
                window.close();
            }
        </script>
    </head>
    <body>
        <button class="print-button no-print" onclick="printReport()">
            🖨️ Print Ulang
        </button>
        
        <div class="header">
            <h1>LAPORAN MONITORING INSPEKSI, SERTIFIKASI, DAN RESERTIFIKASI K3</h1>
            <h2>PT SURVEYOR INDONESIA</h2>
            <p>Tanggal Laporan: ' . date('d F Y') . '</p>
        </div>
        
        <div class="summary">
            <h3>RINGKASAN DATA</h3>
            <table>
                <tr>
                    <th width="25%">Kategori</th>
                    <th width="15%">Total</th>
                    <th width="60%">Detail</th>
                </tr>
                <tr>
                    <td>Pengajuan</td>
                    <td>' . $stats['total_pengajuan'] . '</td>
                    <td>Approved: ' . $stats['pengajuan_approved'] . ' | Pending: ' . $stats['pengajuan_pending'] . ' | Rejected: ' . $stats['pengajuan_rejected'] . '</td>
                </tr>
                <tr>
                    <td>Kontrak</td>
                    <td>' . $stats['total_kontrak'] . '</td>
                    <td>Aktif: ' . $stats['kontrak_aktif'] . '</td>
                </tr>
                <tr>
                    <td>Inspeksi</td>
                    <td>' . $stats['total_inspeksi'] . '</td>
                    <td>Kondisi Baik: ' . $stats['inspeksi_baik'] . '</td>
                </tr>
                <tr>
                    <td>Sertifikat</td>
                    <td>' . $stats['total_sertifikat'] . '</td>
                    <td>Aktif: ' . $stats['sertifikat_aktif'] . '</td>
                </tr>
            </table>
        </div>
        
        <h3>DATA DETAIL</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="15%">Perusahaan</th>
                    <th width="12%">Client</th>
                    <th width="10%">Tgl Pengajuan</th>
                    <th width="8%">Status</th>
                    <th width="10%">Tgl Inspeksi</th>
                    <th width="15%">Objek Inspeksi</th>
                    <th width="8%">Kondisi</th>
                    <th width="12%">Petugas</th>
                    <th width="7%">Sertifikat</th>
                </tr>
            </thead>
            <tbody>';
    
    $no = 1;
    while ($row = mysqli_fetch_assoc($data)) {
        $status_class = '';
        if ($row['status_pengajuan'] == 'approved') $status_class = 'status-approved';
        elseif ($row['status_pengajuan'] == 'pending') $status_class = 'status-pending';
        elseif ($row['status_pengajuan'] == 'rejected') $status_class = 'status-rejected';
        
        $html .= '
                <tr>
                    <td style="text-align: center;">' . $no++ . '</td>
                    <td>' . htmlspecialchars($row['nama_perusahaan']) . '</td>
                    <td>' . htmlspecialchars($row['nama_client']) . '</td>
                    <td>' . formatDate($row['tanggal_pengajuan']) . '</td>
                    <td class="' . $status_class . '">' . ucfirst($row['status_pengajuan']) . '</td>
                    <td>' . ($row['tanggal_inspeksi'] ? formatDate($row['tanggal_inspeksi']) : '-') . '</td>
                    <td>' . ($row['objek_diperiksa'] ? htmlspecialchars($row['objek_diperiksa']) : '-') . '</td>
                    <td>' . ($row['kondisi'] ? ucfirst($row['kondisi']) : '-') . '</td>
                    <td>' . ($row['petugas_nama'] ? htmlspecialchars($row['petugas_nama']) : '-') . '</td>
                    <td>' . ($row['status_sertifikat'] ? ucfirst($row['status_sertifikat']) : '-') . '</td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p><strong>Digenerate pada:</strong> ' . date('d F Y H:i:s') . '</p>
            <p><strong>Oleh:</strong> ' . $_SESSION['user']['nama'] . '</p>
            <p><strong>PT SURVEYOR INDONESIA</strong></p>
        </div>
        
        <script>
            // Tutup window setelah print dialog ditutup
            window.onafterprint = function() {
                setTimeout(function() {
                    if (confirm("Tutup jendela ini?")) {
                        window.close();
                    }
                }, 1000);
            }
        </script>
    </body>
    </html>';
    
    echo $html;
}

function generateExcelReport($data, $stats) {
    // Reset pointer hasil query
    mysqli_data_seek($data, 0);
    
    // Set header untuk Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="laporan_monitoring_k3_' . date('Y-m-d') . '.xls"');
    
    echo '<table border="1">
            <tr>
                <td colspan="10" style="text-align: center; font-weight: bold; font-size: 16px;">
                    LAPORAN MONITORING INSPEKSI, SERTIFIKASI, DAN RESERTIFIKASI K3<br>
                    PT SURVEYOR INDONESIA<br>
                    Tanggal: ' . date('d F Y') . '
                </td>
            </tr>
            <tr><td colspan="10"></td></tr>
            <tr>
                <td colspan="2" style="font-weight: bold;">RINGKASAN DATA</td>
            </tr>
            <tr>
                <td>Total Pengajuan:</td>
                <td>' . $stats['total_pengajuan'] . '</td>
            </tr>
            <tr>
                <td>Pengajuan Approved:</td>
                <td>' . $stats['pengajuan_approved'] . '</td>
            </tr>
            <tr>
                <td>Pengajuan Pending:</td>
                <td>' . $stats['pengajuan_pending'] . '</td>
            </tr>
            <tr>
                <td>Pengajuan Rejected:</td>
                <td>' . $stats['pengajuan_rejected'] . '</td>
            </tr>
            <tr>
                <td>Total Kontrak:</td>
                <td>' . $stats['total_kontrak'] . '</td>
            </tr>
            <tr>
                <td>Kontrak Aktif:</td>
                <td>' . $stats['kontrak_aktif'] . '</td>
            </tr>
            <tr>
                <td>Total Inspeksi:</td>
                <td>' . $stats['total_inspeksi'] . '</td>
            </tr>
            <tr>
                <td>Inspeksi Kondisi Baik:</td>
                <td>' . $stats['inspeksi_baik'] . '</td>
            </tr>
            <tr>
                <td>Total Sertifikat:</td>
                <td>' . $stats['total_sertifikat'] . '</td>
            </tr>
            <tr>
                <td>Sertifikat Aktif:</td>
                <td>' . $stats['sertifikat_aktif'] . '</td>
            </tr>
            <tr><td colspan="10"></td></tr>
            <tr style="background-color: #4e73df; color: white; font-weight: bold;">
                <td>No</td>
                <td>Perusahaan</td>
                <td>Client</td>
                <td>Tanggal Pengajuan</td>
                <td>Status Pengajuan</td>
                <td>Tanggal Inspeksi</td>
                <td>Objek Inspeksi</td>
                <td>Kondisi</td>
                <td>Petugas</td>
                <td>Status Sertifikat</td>
            </tr>';
    
    $no = 1;
    while ($row = mysqli_fetch_assoc($data)) {
        echo '<tr>
                <td>' . $no++ . '</td>
                <td>' . htmlspecialchars($row['nama_perusahaan']) . '</td>
                <td>' . htmlspecialchars($row['nama_client']) . '</td>
                <td>' . formatDate($row['tanggal_pengajuan']) . '</td>
                <td>' . ucfirst($row['status_pengajuan']) . '</td>
                <td>' . ($row['tanggal_inspeksi'] ? formatDate($row['tanggal_inspeksi']) : '-') . '</td>
                <td>' . ($row['objek_diperiksa'] ? htmlspecialchars($row['objek_diperiksa']) : '-') . '</td>
                <td>' . ($row['kondisi'] ? ucfirst($row['kondisi']) : '-') . '</td>
                <td>' . ($row['petugas_nama'] ? htmlspecialchars($row['petugas_nama']) : '-') . '</td>
                <td>' . ($row['status_sertifikat'] ? ucfirst($row['status_sertifikat']) : '-') . '</td>
            </tr>';
    }
    
    echo '</table>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report - Monitoring K3</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include '../../includes/topbar.php'; ?>
                
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Generate Report</h1>
                        <a href="index.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Pilih Format Laporan</h6>
                                </div>
                                <div class="card-body">
                                    <p>Pilih format laporan yang ingin Anda cetak atau download:</p>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-danger">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                                Print Report</div>
                                                            <div class="small text-gray-500">
                                                                Cetak langsung ke printer
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <a href="?action=print" class="btn btn-danger" target="_blank">
                                                                <i class="fas fa-print"></i> Print Laporan
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-success">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                                Excel Report</div>
                                                            <div class="small text-gray-500">
                                                                Format Excel untuk analisis data
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <a href="?action=excel" class="btn btn-success">
                                                                <i class="fas fa-file-excel"></i> Download Excel
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Informasi:</strong> 
                                        <ul class="mb-0 mt-2">
                                            <li><strong>Print Laporan:</strong> Akan membuka jendela baru dan otomatis menampilkan dialog print browser</li>
                                            <li><strong>Download Excel:</strong> Akan mendownload file Excel untuk analisis data lebih lanjut</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Statistik Data</h6>
                                </div>
                                <div class="card-body">
                                    <div class="small mb-2">
                                        <strong>Total Pengajuan:</strong> <?= $stats['total_pengajuan'] ?>
                                    </div>
                                    <div class="small mb-2">
                                        <strong>Pengajuan Approved:</strong> 
                                        <span class="text-success"><?= $stats['pengajuan_approved'] ?></span>
                                    </div>
                                    <div class="small mb-2">
                                        <strong>Pengajuan Pending:</strong> 
                                        <span class="text-warning"><?= $stats['pengajuan_pending'] ?></span>
                                    </div>
                                    <div class="small mb-2">
                                        <strong>Pengajuan Rejected:</strong> 
                                        <span class="text-danger"><?= $stats['pengajuan_rejected'] ?></span>
                                    </div>
                                    <div class="small mb-2">
                                        <strong>Total Kontrak:</strong> <?= $stats['total_kontrak'] ?>
                                    </div>
                                    <div class="small mb-2">
                                        <strong>Kontrak Aktif:</strong> <?= $stats['kontrak_aktif'] ?>
                                    </div>
                                    <div class="small mb-2">
                                        <strong>Total Inspeksi:</strong> <?= $stats['total_inspeksi'] ?>
                                    </div>
                                    <div class="small mb-2">
                                        <strong>Inspeksi Baik:</strong> <?= $stats['inspeksi_baik'] ?>
                                    </div>
                                    <div class="small mb-2">
                                        <strong>Total Sertifikat:</strong> <?= $stats['total_sertifikat'] ?>
                                    </div>
                                    <div class="small mb-2">
                                        <strong>Sertifikat Aktif:</strong> <?= $stats['sertifikat_aktif'] ?>
                                    </div>
                                    <hr>
                                    <div class="small text-muted">
                                        Data per tanggal: <?= date('d F Y') ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-warning">Tips Print</h6>
                                </div>
                                <div class="card-body">
                                    <div class="small">
                                        <p><i class="fas fa-lightbulb text-warning"></i> <strong>Tips untuk hasil print terbaik:</strong></p>
                                        <ul class="small">
                                            <li>Gunakan kertas A4 untuk hasil optimal</li>
                                            <li>Set orientasi ke Portrait/Potrait</li>
                                            <li>Pastikan printer sudah siap dan ada kertas</li>
                                            <li>Untuk tabel yang panjang, akan otomatis terbagi ke halaman berikutnya</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/sb-admin-2.min.js"></script>
</body>
</html>
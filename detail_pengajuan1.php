<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pelanggan') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

$user_id = $_SESSION['user']['id_user'];
$pengajuan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validasi pengajuan milik user
$query_validasi = "SELECT * FROM pengajuan WHERE id_pengajuan = $pengajuan_id AND id_user = $user_id";
$result_validasi = mysqli_query($conn, $query_validasi);

if (mysqli_num_rows($result_validasi) == 0) {
    header("Location: index.php");
    exit;
}

$pengajuan = mysqli_fetch_assoc($result_validasi);

// Query untuk mengambil objek inspeksi
$query_objek = "SELECT * FROM objek_inspeksi WHERE id_pengajuan = $pengajuan_id";
$result_objek = mysqli_query($conn, $query_objek);

// Query untuk mengambil data kontrak jika ada
$query_kontrak = "SELECT * FROM kontrak WHERE id_pengajuan = $pengajuan_id";
$result_kontrak = mysqli_query($conn, $query_kontrak);
$kontrak = mysqli_fetch_assoc($result_kontrak);

// Query untuk mengambil data inspeksi jika ada
$inspeksi = null;
$sertifikat = null;
if ($kontrak) {
    $query_inspeksi = "SELECT i.*, u.nama as petugas_nama 
                      FROM inspeksi i 
                      LEFT JOIN users u ON i.petugas_id = u.id_user 
                      WHERE i.id_kontrak = {$kontrak['id_kontrak']}";
    $result_inspeksi = mysqli_query($conn, $query_inspeksi);
    $inspeksi = mysqli_fetch_assoc($result_inspeksi);
    
    // Query untuk mengambil data sertifikat jika ada
    if ($inspeksi) {
        $query_sertifikat = "SELECT * FROM sertifikat WHERE id_inspeksi = {$inspeksi['id_inspeksi']}";
        $result_sertifikat = mysqli_query($conn, $query_sertifikat);
        $sertifikat = mysqli_fetch_assoc($result_sertifikat);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan - Sistem Monitoring K3</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .timeline {
            position: relative;
            padding: 0;
            list-style: none;
        }
        .timeline:before {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 40px;
            width: 2px;
            margin-left: -1.5px;
            content: '';
            background-color: #e9ecef;
        }
        .timeline > li {
            position: relative;
            margin-bottom: 50px;
            min-height: 50px;
        }
        .timeline > li:before,
        .timeline > li:after {
            content: ' ';
            display: table;
        }
        .timeline > li:after {
            clear: both;
        }
        .timeline > li .timeline-panel {
            position: relative;
            float: right;
            width: calc(100% - 90px);
            padding: 20px;
            border: 1px solid #d4edda;
            border-radius: 2px;
            background: #f8f9fa;
        }
        .timeline > li .timeline-badge {
            position: absolute;
            top: 16px;
            left: 28px;
            z-index: 100;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            text-align: center;
            font-size: 1.4em;
            line-height: 25px;
            color: #fff;
        }
        .timeline-badge.success { background-color: #28a745; }
        .timeline-badge.warning { background-color: #ffc107; }
        .timeline-badge.info { background-color: #17a2b8; }
        .timeline-badge.danger { background-color: #dc3545; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include '../../includes/topbar.php'; ?>
                
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Detail Pengajuan</h1>
                        <a href="index.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    
                    <!-- Informasi Pengajuan -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Informasi Pengajuan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Nama Client:</strong></td>
                                                    <td><?= htmlspecialchars($pengajuan['nama_client']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Nama Perusahaan:</strong></td>
                                                    <td><?= htmlspecialchars($pengajuan['nama_perusahaan']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>NPWP:</strong></td>
                                                    <td><?= htmlspecialchars($pengajuan['npwp']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Kegiatan TIC:</strong></td>
                                                    <td><?= htmlspecialchars($pengajuan['kegiatan_tic']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Wilayah:</strong></td>
                                                    <td><?= htmlspecialchars($pengajuan['wilayah']) ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Alamat:</strong></td>
                                                    <td><?= htmlspecialchars($pengajuan['alamat']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Telepon:</strong></td>
                                                    <td><?= htmlspecialchars($pengajuan['telp']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td><?= htmlspecialchars($pengajuan['email']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Pengajuan:</strong></td>
                                                    <td><?= formatDate($pengajuan['tanggal_pengajuan']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td>
                                                        <span class="badge badge-<?= 
                                                            $pengajuan['status'] == 'approved' ? 'success' : 
                                                            ($pengajuan['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                                            <?= ucfirst($pengajuan['status']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Objek Inspeksi -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Objek Inspeksi</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (mysqli_num_rows($result_objek) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Jenis Objek</th>
                                                        <th>Spesifikasi Teknis</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $no = 1;
                                                    while($objek = mysqli_fetch_assoc($result_objek)): 
                                                    ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= htmlspecialchars($objek['jenis_objek']) ?></td>
                                                            <td><?= htmlspecialchars($objek['spesifikasi_teknis']) ?: '-' ?></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">Belum ada objek inspeksi yang didaftarkan.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Detail Kontrak -->
                            <?php if ($kontrak): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">Detail Kontrak</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Tanggal Upload:</strong> <?= formatDate($kontrak['tanggal_upload']) ?></p>
                                            <p><strong>Tanggal Berakhir:</strong> <?= formatDate($kontrak['tanggal_berakhir']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Status:</strong> 
                                                <span class="badge badge-<?= $kontrak['status'] == 'aktif' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($kontrak['status']) ?>
                                                </span>
                                            </p>
                                            <p><strong>File Kontrak:</strong> 
                                                <a href="../../uploads/kontrak/<?= $kontrak['file_kontrak'] ?>" 
                                                   target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-file-contract"></i> Lihat Kontrak
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Detail Inspeksi -->
                            <?php if ($inspeksi): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-info">Detail Inspeksi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Tanggal Inspeksi:</strong> <?= formatDate($inspeksi['tanggal_inspeksi']) ?></p>
                                            <p><strong>Lokasi:</strong> <?= htmlspecialchars($inspeksi['lokasi']) ?></p>
                                            <p><strong>Objek Diperiksa:</strong> <?= htmlspecialchars($inspeksi['objek_diperiksa']) ?></p>
                                            <p><strong>Petugas:</strong> <?= htmlspecialchars($inspeksi['petugas_nama']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Kondisi:</strong> 
                                                <span class="badge badge-<?= 
                                                    $inspeksi['kondisi'] == 'baik' ? 'success' : 
                                                    ($inspeksi['kondisi'] == 'kurang baik' ? 'warning' : 'danger') ?>">
                                                    <?= ucfirst($inspeksi['kondisi']) ?>
                                                </span>
                                            </p>
                                            <p><strong>Status Perbaikan:</strong> 
                                                <span class="badge badge-<?= $inspeksi['status_perbaikan'] == 'selesai' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($inspeksi['status_perbaikan']) ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <?php if ($inspeksi['temuan']): ?>
                                    <div class="mt-3">
                                        <p><strong>Temuan:</strong></p>
                                        <div class="alert alert-warning">
                                            <?= nl2br(htmlspecialchars($inspeksi['temuan'])) ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($inspeksi['tindakan_rekomendasi']): ?>
                                    <div class="mt-3">
                                        <p><strong>Tindakan Rekomendasi:</strong></p>
                                        <div class="alert alert-info">
                                            <?= nl2br(htmlspecialchars($inspeksi['tindakan_rekomendasi'])) ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Detail Sertifikat -->
                            <?php if ($sertifikat): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-warning">Detail Sertifikat</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Tanggal Terbit:</strong> <?= formatDate($sertifikat['tanggal_terbit']) ?></p>
                                            <p><strong>Tanggal Berakhir:</strong> <?= formatDate($sertifikat['tanggal_berakhir']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Status:</strong> 
                                                <span class="badge badge-<?= $sertifikat['status'] == 'active' ? 'success' : 'danger' ?>">
                                                    <?= $sertifikat['status'] == 'active' ? 'Aktif' : 'Kedaluwarsa' ?>
                                                </span>
                                            </p>
                                            <p><strong>File Sertifikat:</strong> 
                                                <a href="../../uploads/sertifikat/<?= $sertifikat['file_sertifikat'] ?>" 
                                                   target="_blank" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-certificate"></i> Lihat Sertifikat
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Timeline Progress -->
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Timeline Progress</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="timeline">
                                        <li>
                                            <div class="timeline-badge success">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                    <h6 class="timeline-title">Pengajuan Dibuat</h6>
                                                    <p><small class="text-muted">
                                                        <?= formatDate($pengajuan['tanggal_pengajuan']) ?>
                                                    </small></p>
                                                </div>
                                                <div class="timeline-body">
                                                    <p>Pengajuan inspeksi telah berhasil dibuat dan menunggu persetujuan admin.</p>
                                                </div>
                                            </div>
                                        </li>
                                        
                                        <?php if ($pengajuan['status'] == 'approved'): ?>
                                        <li>
                                            <div class="timeline-badge success">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                    <h6 class="timeline-title">Pengajuan Disetujui</h6>
                                                </div>
                                                <div class="timeline-body">
                                                    <p>Pengajuan telah disetujui oleh admin dan akan diproses lebih lanjut.</p>
                                                </div>
                                            </div>
                                        </li>
                                        <?php elseif ($pengajuan['status'] == 'rejected'): ?>
                                        <li>
                                            <div class="timeline-badge danger">
                                                <i class="fas fa-times"></i>
                                            </div>
                                            <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                    <h6 class="timeline-title">Pengajuan Ditolak</h6>
                                                </div>
                                                <div class="timeline-body">
                                                    <p>Pengajuan ditolak. Silakan hubungi admin untuk informasi lebih lanjut.</p>
                                                </div>
                                            </div>
                                        </li>
                                        <?php else: ?>
                                        <li>
                                            <div class="timeline-badge warning">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                    <h6 class="timeline-title">Menunggu Persetujuan</h6>
                                                </div>
                                                <div class="timeline-body">
                                                    <p>Pengajuan sedang menunggu persetujuan dari admin.</p>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php if ($kontrak): ?>
                                        <li>
                                            <div class="timeline-badge success">
                                                <i class="fas fa-file-contract"></i>
                                            </div>
                                            <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                    <h6 class="timeline-title">Kontrak Dibuat</h6>
                                                    <p><small class="text-muted">
                                                        <?= formatDate($kontrak['tanggal_upload']) ?>
                                                    </small></p>
                                                </div>
                                                <div class="timeline-body">
                                                    <p>Kontrak telah dibuat dan dapat diunduh.</p>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php if ($inspeksi): ?>
                                        <li>
                                            <div class="timeline-badge info">
                                                <i class="fas fa-search"></i>
                                            </div>
                                            <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                    <h6 class="timeline-title">Inspeksi Dilakukan</h6>
                                                    <p><small class="text-muted">
                                                        <?= formatDate($inspeksi['tanggal_inspeksi']) ?>
                                                    </small></p>
                                                </div>
                                                <div class="timeline-body">
                                                    <p>Inspeksi telah dilakukan oleh <?= htmlspecialchars($inspeksi['petugas_nama']) ?>.</p>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php if ($sertifikat): ?>
                                        <li>
                                            <div class="timeline-badge success">
                                                <i class="fas fa-certificate"></i>
                                            </div>
                                            <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                    <h6 class="timeline-title">Sertifikat Diterbitkan</h6>
                                                    <p><small class="text-muted">
                                                        <?= formatDate($sertifikat['tanggal_terbit']) ?>
                                                    </small></p>
                                                </div>
                                                <div class="timeline-body">
                                                    <p>Sertifikat K3 telah diterbitkan dan dapat diunduh.</p>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
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

<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

// Ambil id inspeksi dari URL
$id_inspeksi = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query untuk mengambil data inspeksi, kontrak, pengajuan, dan sertifikat
$query = "SELECT i.*, k.tanggal_berakhir, p.nama_perusahaan, p.nama_client, 
                 s.file_sertifikat, s.tanggal_terbit, s.tanggal_berakhir AS berakhir_sertifikat, s.status AS status_sertifikat
          FROM inspeksi i
          JOIN kontrak k ON i.id_kontrak = k.id_kontrak
          JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
          LEFT JOIN sertifikat s ON i.id_inspeksi = s.id_inspeksi
          WHERE i.id_inspeksi = ? AND i.petugas_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $id_inspeksi, $_SESSION['user']['id_user']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$inspeksi = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan, redirect atau tampilkan pesan
if (!$inspeksi) {
    $_SESSION['error'] = "Data inspeksi tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$kondisi_badge = [
    'baik' => 'success',
    'kurang baik' => 'warning',
    'tidak layak' => 'danger'
];

$status_perbaikan_badge = [
    'perlu' => 'warning',
    'selesai' => 'success'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Inspeksi - K3 Monitoring</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <style>
        .detail-card {
            border-left: 4px solid #4e73df;
            border-radius: 0.35rem;
            margin-bottom: 1.5rem;
        }
        .badge-lg {
            padding: 0.6rem 1rem;
            font-size: 1rem;
            border-radius: 0.3rem;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        .info-label {
            font-weight: 600;
            color: #5a5c69;
        }
        .info-value {
            font-size: 1.1rem;
            color: #4e73df;
        }
        .file-icon {
            font-size: 3rem;
            color: #dddfeb;
            margin-bottom: 0.5rem;
        }
        .sertifikat-card {
            background: linear-gradient(to bottom right, #f8f9fc, #e2e8f0);
            border: 1px solid #e3e6f0;
        }
    </style>
</head>
<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

    <?php include '../../includes/sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <?php include '../../includes/topbar.php'; ?>

            <!-- Begin Page Content -->
            <div class="container-fluid">
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Detail Inspeksi</h1>
                    <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
                    </a>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4 detail-card">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Data Inspeksi</h6>
                                <span class="badge badge-<?= $kondisi_badge[$inspeksi['kondisi']] ?> badge-lg">
                                    <?= ucfirst($inspeksi['kondisi']) ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="info-label">Tanggal Inspeksi</div>
                                            <div class="info-value"><?= formatDate($inspeksi['tanggal_inspeksi']) ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="info-label">Perusahaan</div>
                                            <div class="info-value"><?= htmlspecialchars($inspeksi['nama_perusahaan']) ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="info-label">Lokasi</div>
                                            <div class="info-value"><?= htmlspecialchars($inspeksi['lokasi']) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="info-label">Objek Diperiksa</div>
                                            <div class="info-value"><?= htmlspecialchars($inspeksi['objek_diperiksa']) ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="info-label">Tanggal Berakhir Kontrak</div>
                                            <div class="info-value"><?= formatDate($inspeksi['tanggal_berakhir']) ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="info-label">Status Perbaikan</div>
                                            <div>
                                                <span class="badge badge-<?= $status_perbaikan_badge[$inspeksi['status_perbaikan']] ?> status-badge">
                                                    <?= ucfirst($inspeksi['status_perbaikan']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-gray-800 mb-3">Temuan</h5>
                                        <div class="card border-left-info shadow py-2 mb-4">
                                            <div class="card-body">
                                                <?= nl2br(htmlspecialchars($inspeksi['temuan'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-gray-800 mb-3">Tindakan/Rekomendasi</h5>
                                        <div class="card border-left-success shadow py-2">
                                            <div class="card-body">
                                                <?= nl2br(htmlspecialchars($inspeksi['tindakan_rekomendasi'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Sertifikat Section -->
                        <div class="card shadow mb-4 sertifikat-card">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Sertifikat Inspeksi</h6>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($inspeksi['file_sertifikat']): ?>
                                    <div class="file-icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <h5 class="text-primary mb-3">Sertifikat Tersedia</h5>
                                    
                                    <div class="text-left mb-4">
                                        <div class="mb-2">
                                            <span class="info-label">Tanggal Terbit:</span>
                                            <span class="d-block"><?= formatDate($inspeksi['tanggal_terbit']) ?></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="info-label">Berlaku Sampai:</span>
                                            <span class="d-block"><?= formatDate($inspeksi['berakhir_sertifikat']) ?></span>
                                        </div>
                                        <div>
                                            <span class="info-label">Status:</span>
                                            <span class="badge badge-<?= $inspeksi['status_sertifikat'] == 'active' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($inspeksi['status_sertifikat']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <a href="../../uploads/sertifikat/<?= htmlspecialchars($inspeksi['file_sertifikat']) ?>" 
                                       target="_blank" class="btn btn-primary btn-icon-split">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-download"></i>
                                        </span>
                                        <span class="text">Unduh Sertifikat</span>
                                    </a>
                                <?php else: ?>
                                    <div class="file-icon">
                                        <i class="fas fa-file-exclamation"></i>
                                    </div>
                                    <h5 class="text-gray-600 mb-3">Sertifikat Belum Tersedia</h5>
                                    <p class="text-gray-600">Sertifikat inspeksi belum diunggah untuk inspeksi ini.</p>
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-ban mr-2"></i>Belum Tersedia
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Informasi Petugas -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Informasi Petugas</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <img class="img-profile rounded-circle mb-3" 
                                         src="../../img/<?= $_SESSION['user']['avatar'] ?>" 
                                         style="width: 100px; height: 100px;">
                                    <h5 class="text-gray-800"><?= $_SESSION['user']['nama'] ?></h5>
                                    <p class="text-gray-600">Staff Inspeksi K3</p>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <div class="info-label">Tanggal Inspeksi Dibuat</div>
                                            <div class="text-gray-800"><?= formatDate($inspeksi['created_at']) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-body text-right">
                                <a href="edit_inspeksi.php?id=<?= $id_inspeksi ?>" class="btn btn-info btn-icon-split">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-edit"></i>
                                    </span>
                                    <span class="text">Edit Inspeksi</span>
                                </a>
                                <a href="cetak_inspeksi.php?id=<?= $id_inspeksi ?>" target="_blank" class="btn btn-primary btn-icon-split ml-2">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-print"></i>
                                    </span>
                                    <span class="text">Cetak Laporan</span>
                                </a>
                                <a href="#" class="btn btn-danger btn-icon-split ml-2" data-toggle="modal" data-target="#deleteModal">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                    <span class="text">Hapus</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Delete Modal-->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Hapus Data Inspeksi</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus data inspeksi ini? Data yang dihapus tidak dapat dikembalikan.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <a class="btn btn-danger" href="hapus_inspeksi.php?id=<?= $id_inspeksi ?>">Hapus</a>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- SB Admin 2 Scripts -->
<script src="../../js/sb-admin-2.min.js"></script>

</body>
</html>
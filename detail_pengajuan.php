<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

if (!isset($_GET['id'])) {
    header("Location: kelola_pengajuan.php");
    exit;
}

$id = $_GET['id'];

// Ambil data pengajuan
$query = "SELECT p.*, u.nama FROM pengajuan p 
          JOIN users u ON p.id_user = u.id_user 
          WHERE p.id_pengajuan = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: kelola_pengajuan.php");
    exit;
}

$pengajuan = mysqli_fetch_assoc($result);

// Ambil data objek inspeksi
$objek_query = "SELECT * FROM objek_inspeksi WHERE id_pengajuan = $id";
$objek_result = mysqli_query($conn, $objek_query);

// Cek apakah ada kontrak
$kontrak_query = "SELECT * FROM kontrak WHERE id_pengajuan = $id ORDER BY tanggal_upload DESC";
$kontrak_result = mysqli_query($conn, $kontrak_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detail Pengajuan Inspeksi</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
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
                    <h1 class="h3 mb-0 text-gray-800">Detail Pengajuan Inspeksi</h1>
                    <a href="kelola_pengajuan.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
                    </a>
                </div>

                <!-- Informasi Pengajuan Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Informasi Pengajuan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="30%"><strong>Nama Client</strong></td>
                                        <td>: <?= htmlspecialchars($pengajuan['nama_client']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Perusahaan</strong></td>
                                        <td>: <?= htmlspecialchars($pengajuan['nama_perusahaan']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Alamat</strong></td>
                                        <td>: <?= htmlspecialchars($pengajuan['alamat']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>NPWP</strong></td>
                                        <td>: <?= htmlspecialchars($pengajuan['npwp']) ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="30%"><strong>Wilayah</strong></td>
                                        <td>: <?= htmlspecialchars($pengajuan['wilayah']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Telepon</strong></td>
                                        <td>: <?= htmlspecialchars($pengajuan['telp']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email</strong></td>
                                        <td>: <?= htmlspecialchars($pengajuan['email']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status</strong></td>
                                        <td>: <span class="badge badge-<?= 
                                            $pengajuan['status'] == 'approved' ? 'success' : 
                                            ($pengajuan['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst($pengajuan['status']) ?>
                                        </span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Objek Inspeksi Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Objek Inspeksi</h6>
                    </div>
                    <div class="card-body">
                        <?php 
                        $no = 1;
                        mysqli_data_seek($objek_result, 0);
                        while ($objek = mysqli_fetch_assoc($objek_result)): 
                        ?>
                            <div class="card mb-4 border-left-primary">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Objek <?= $no++ ?>: <?= htmlspecialchars($objek['jenis_objek']) ?></h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach([
                                            'spesifikasi_teknis' => 'Spesifikasi Teknis',
                                            'manual_operasi' => 'Manual Operasi',
                                            'gambar_teknis' => 'Gambar Teknis',
                                            'laporan_hasil' => 'Laporan Hasil',
                                            'pengesahan_pemakaian' => 'Pengesahan Pemakaian',
                                            'catatan_pemeliharaan' => 'Catatan Pemeliharaan',
                                            'surat_izin_operator' => 'Surat Izin Operator'
                                        ] as $field => $label): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?= $label ?>
                                                <?php if ($objek[$field]): ?>
                                                    <a href="../../uploads/dokumen/<?= $objek[$field] ?>" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-outline-primary">
                                                       <i class="fas fa-external-link-alt"></i> Lihat Dokumen
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Tidak ada file</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Kontrak Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Dokumen Kontrak</h6>
                        <?php if ($pengajuan['status'] == 'approved'): ?>
                            <a href="upload_kontrak.php?id=<?= $id ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload fa-sm"></i> Upload Kontrak Baru
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($kontrak_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal Upload</th>
                                            <th>Tanggal Berakhir</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        mysqli_data_seek($kontrak_result, 0);
                                        while ($kontrak = mysqli_fetch_assoc($kontrak_result)): 
                                            $expired = isKontrakExpired($kontrak['tanggal_berakhir']);
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= formatDate($kontrak['tanggal_upload']) ?></td>
                                                <td><?= formatDate($kontrak['tanggal_berakhir']) ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $expired ? 'danger' : 'success' ?>">
                                                        <?= $expired ? 'Kedaluwarsa' : 'Aktif' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../../uploads/kontrak/<?= $kontrak['file_kontrak'] ?>" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-info">
                                                       <i class="fas fa-eye"></i> Lihat
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Belum ada kontrak yang diupload</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <?php if ($pengajuan['status'] == 'pending'): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Proses Pengajuan</h6>
                        </div>
                        <div class="card-body text-center">
                            <a href="proses_pengajuan.php?id=<?= $id ?>&action=approve" 
                               class="btn btn-success btn-icon-split mx-2">
                                <span class="icon text-white-50">
                                    <i class="fas fa-check"></i>
                                </span>
                                <span class="text">Setujui</span>
                            </a>
                            <a href="proses_pengajuan.php?id=<?= $id ?>&action=reject" 
                               class="btn btn-danger btn-icon-split mx-2">
                                <span class="icon text-white-50">
                                    <i class="fas fa-times"></i>
                                </span>
                                <span class="text">Tolak</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- SB Admin 2 Scripts -->
<script src="../../js/sb-admin-2.min.js"></script>

</body>
</html>

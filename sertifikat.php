<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pelanggan') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

$user_id = $_SESSION['user']['id_user'];
$query = "SELECT s.*, p.nama_perusahaan 
          FROM sertifikat s
          JOIN inspeksi i ON s.id_inspeksi = i.id_inspeksi
          JOIN kontrak k ON i.id_kontrak = k.id_kontrak
          JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
          WHERE p.id_user = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Saya</title>
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
                    <h1 class="h3 mb-0 text-gray-800">Daftar Sertifikat</h1>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Sertifikat Milik Anda</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Perusahaan</th>
                                                <th>Tanggal Terbit</th>
                                                <th>Tanggal Berakhir</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($result)): 
                                                $today = date('Y-m-d');
                                                $status = ($row['tanggal_berakhir'] < $today) ? 'Kedaluwarsa' : 'Aktif';
                                                $badgeClass = ($status == 'Aktif') ? 'success' : 'danger';
                                            ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['nama_perusahaan']) ?></td>
                                                    <td><?= formatDate($row['tanggal_terbit']) ?></td>
                                                    <td><?= formatDate($row['tanggal_berakhir']) ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= $badgeClass ?>">
                                                            <?= $status ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="../../uploads/sertifikat/<?= $row['file_sertifikat'] ?>" 
                                                           class="btn btn-success btn-sm"
                                                           download
                                                           title="Unduh Sertifikat">
                                                            <i class="fas fa-download"></i> Unduh
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- SB Admin 2 Scripts -->
<script src="../../js/sb-admin-2.min.js"></script>

</body>
</html>

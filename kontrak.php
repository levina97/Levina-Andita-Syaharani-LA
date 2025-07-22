<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pelanggan') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';

$user_id = $_SESSION['user']['id_user'];

// Perbaikan query: ganti ORDER BY created_at -> tanggal_upload
$query = "SELECT k.*, p.nama_client, p.nama_perusahaan 
          FROM kontrak k 
          JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan 
          WHERE p.id_user = '$user_id' 
          ORDER BY k.tanggal_upload DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kontrak</title>
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
                    <h1 class="h3 mb-0 text-gray-800">Daftar Kontrak</h1>
                </div>

                <!-- Data Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Dokumen Kontrak Anda</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Client</th>
                                        <th>Perusahaan</th>
                                        <th>Mulai Berlaku</th>
                                        <th>Berakhir Pada</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $counter = 1;
                                    while ($row = mysqli_fetch_assoc($result)) : 
                                        $today = date('Y-m-d');
                                        $is_active = $today <= $row['tanggal_berakhir'];
                                        $status = $is_active ? 'Aktif' : 'Kadaluarsa';
                                    ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td><?= htmlspecialchars($row['nama_client']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_perusahaan']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal_upload'])) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal_berakhir'])) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $is_active ? 'success' : 'danger' ?>">
                                                <?= $status ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($is_active) : ?>
                                                <a href="download.php?id=<?= $row['id_kontrak'] ?>" 
                                                   class="btn btn-success btn-sm btn-circle"
                                                   title="Download Kontrak">
                                                   <i class="fas fa-download"></i>
                                                </a>
                                            <?php else : ?>
                                                <span class="text-muted small">Kontrak telah kadaluarsa</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    
                                    <?php if (mysqli_num_rows($result) == 0) : ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-file-contract fa-2x text-gray-300 mb-3"></i><br>
                                            Belum ada kontrak
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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

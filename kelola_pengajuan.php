<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

// Filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = $status_filter ? "WHERE status = '$status_filter'" : "";

// Tambahkan parameter pencarian
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

// Bangun klausa WHERE
$where = [];
if ($status_filter) {
    $where[] = "p.status = '$status_filter'";
}
if ($search) {
    $search_term = "%$search%";
    $where[] = "(p.nama_client LIKE '$search_term' 
                OR p.nama_perusahaan LIKE '$search_term' 
                OR p.wilayah LIKE '$search_term'
                OR u.nama LIKE '$search_term')";
}
$where_clause = $where ? "WHERE " . implode(' AND ', $where) : "";

// Modifikasi query untuk include pencarian
$query = "SELECT p.*, u.nama FROM pengajuan p 
          JOIN users u ON p.id_user = u.id_user 
          $where_clause
          ORDER BY p.tanggal_pengajuan DESC";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengajuan Inspeksi</title>
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
                    <h1 class="h3 mb-0 text-gray-800">Kelola Pengajuan Inspeksi</h1>
                    <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Dashboard
                    </a>
                </div>
                    
                <!-- Filter Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="kelola_pengajuan.php" 
                               class="btn btn-<?= empty($status_filter) ? 'primary' : 'outline-primary' ?>">
                                Semua
                            </a>
                            <a href="kelola_pengajuan.php?status=pending" 
                               class="btn btn-<?= $status_filter == 'pending' ? 'warning' : 'outline-warning' ?>">
                                Pending
                            </a>
                            <a href="kelola_pengajuan.php?status=approved" 
                               class="btn btn-<?= $status_filter == 'approved' ? 'success' : 'outline-success' ?>">
                                Disetujui
                            </a>
                            <a href="kelola_pengajuan.php?status=rejected" 
                               class="btn btn-<?= $status_filter == 'rejected' ? 'danger' : 'outline-danger' ?>">
                                Ditolak
                            </a>
                        </div>
                        <!-- Baris Kedua: Search dan Export -->
        
                        <a href="export_pengajuan.php" class="btn btn-info btn-icon-split float-right">
                            <span class="icon text-white-50">
                                <i class="fas fa-file-export"></i>
                            </span>
                            <span class="text">Export Data</span>
                        </a>
                        <div class="row">
                            <div class="col-md-8">
                                <form method="GET" class="form-inline">
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control bg-light border-0 small" 
                                               placeholder="Cari pengajuan..." 
                                               name="search"
                                               value="<?= htmlspecialchars($search) ?>"
                                               aria-label="Search">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Daftar Pengajuan</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Client</th>
                                        <th>Perusahaan</th>
                                        <th>Wilayah</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    if (mysqli_num_rows($result) > 0): 
                                        while($row = mysqli_fetch_assoc($result)): 
                                    ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama_client']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_perusahaan']) ?></td>
                                            <td><?= htmlspecialchars($row['wilayah']) ?></td>
                                            <td><?= formatDate($row['tanggal_pengajuan']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= 
                                                    $row['status'] == 'approved' ? 'success' : 
                                                    ($row['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                                    <?= ucfirst($row['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="detail_pengajuan.php?id=<?= $row['id_pengajuan'] ?>" 
                                                   class="btn btn-info btn-sm btn-circle"
                                                   title="Detail">
                                                   <i class="fas fa-info-circle"></i>
                                                </a>
                                                
                                                <?php if ($row['status'] == 'pending'): ?>
                                                    <a href="proses_pengajuan.php?id=<?= $row['id_pengajuan'] ?>&action=approve" 
                                                       class="btn btn-success btn-sm btn-circle"
                                                       title="Setujui">
                                                       <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="proses_pengajuan.php?id=<?= $row['id_pengajuan'] ?>&action=reject" 
                                                       class="btn btn-danger btn-sm btn-circle"
                                                       title="Tolak">
                                                       <i class="fas fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($row['status'] == 'approved'): ?>
                                                    <a href="upload_kontrak.php?id=<?= $row['id_pengajuan'] ?>" 
                                                       class="btn btn-primary btn-sm btn-circle"
                                                       title="Upload Kontrak">
                                                       <i class="fas fa-upload"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <!-- Tombol Delete untuk semua status -->
                                                <a href="#" 
                                                   class="btn btn-danger btn-sm btn-circle delete-btn"
                                                   title="Hapus"
                                                   data-toggle="modal"
                                                   data-target="#deleteModal"
                                                   data-id="<?= $row['id_pengajuan'] ?>"
                                                   data-client="<?= htmlspecialchars($row['nama_client']) ?>">
                                                   <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                        endwhile; 
                                    else: 
                                    ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data pengajuan</td>
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

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Apakah Anda yakin ingin menghapus pengajuan dari <span id="clientName"></span>?</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                <a id="confirmDelete" class="btn btn-danger" href="#">Hapus</a>
            </div>
        </div>
    </div>
</div>

<!-- SB Admin 2 Scripts -->
<script src="../../js/sb-admin-2.min.js"></script>

<script>
    // Script untuk mengatur modal delete
    $('.delete-btn').on('click', function() {
        var id = $(this).data('id');
        var client = $(this).data('client');
        $('#clientName').text(client);
        $('#confirmDelete').attr('href', 'delete_pengajuan.php?id=' + id);
    });
</script>

</body>
</html>

<?php
session_start();
require '../../config/config.php';

// Cek hak akses admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$error = '';
$success = '';

// Handle delete inspeksi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id_inspeksi = $_GET['delete'];
    
    try {
        // Hapus sertifikat terkait terlebih dahulu
        $stmt = $conn->prepare("DELETE FROM sertifikat WHERE id_inspeksi = ?");
        $stmt->bind_param('i', $id_inspeksi);
        $stmt->execute();
        
        // Hapus inspeksi
        $stmt = $conn->prepare("DELETE FROM inspeksi WHERE id_inspeksi = ?");
        $stmt->bind_param('i', $id_inspeksi);
        
        if ($stmt->execute()) {
            $success = 'Data inspeksi berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus data inspeksi.';
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Handle update status perbaikan
if (isset($_POST['update_status'])) {
    $id_inspeksi = $_POST['id_inspeksi'];
    $status_perbaikan = $_POST['status_perbaikan'];
    
    try {
        $stmt = $conn->prepare("UPDATE inspeksi SET status_perbaikan = ? WHERE id_inspeksi = ?");
        $stmt->bind_param('si', $status_perbaikan, $id_inspeksi);
        
        if ($stmt->execute()) {
            $success = 'Status perbaikan berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui status perbaikan.';
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';
$params = [];
$types = '';

if (!empty($search)) {
    $where_clause = "AND (i.lokasi LIKE ? OR i.objek_diperiksa LIKE ? OR p.nama_perusahaan LIKE ? OR u.nama LIKE ?)";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param, $search_param];
    $types = 'ssss';
}

// Query untuk mengambil inspeksi terbaru per kontrak (menghindari duplikasi)
$base_sql = "FROM (
    SELECT i.*, 
           ROW_NUMBER() OVER (PARTITION BY i.id_kontrak ORDER BY i.tanggal_inspeksi DESC, i.id_inspeksi DESC) as rn
    FROM inspeksi i
) i_ranked
JOIN inspeksi i ON i.id_inspeksi = i_ranked.id_inspeksi AND i_ranked.rn = 1
JOIN kontrak k ON i.id_kontrak = k.id_kontrak
JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
JOIN users u ON i.petugas_id = u.id_user
WHERE 1=1 $where_clause";

// Get total records for pagination
$count_sql = "SELECT COUNT(*) as total $base_sql";

if (!empty($params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_records = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $limit);

// Get inspeksi data dengan join yang sesuai
$sql = "SELECT i.*, p.nama_perusahaan, p.nama_client, u.nama as petugas_nama,
               k.tanggal_berakhir as kontrak_berakhir,
               (SELECT COUNT(*) FROM sertifikat s WHERE s.id_inspeksi = i.id_inspeksi) as has_sertifikat
        $base_sql
        ORDER BY i.created_at DESC 
        LIMIT ? OFFSET ?";

$final_params = array_merge($params, [$limit, $offset]);
$final_types = $types . 'ii';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($final_types, ...$final_params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$inspeksi_data = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Daftar Inspeksi - SI-MONIK3</title>
    
    <!-- SB Admin 2 CSS -->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        .badge-kondisi-baik { background-color: #28a745; }
        .badge-kondisi-kurang { background-color: #ffc107; color: #212529; }
        .badge-kondisi-tidak { background-color: #dc3545; }
        .badge-kondisi-diperbaiki { background-color: #17a2b8; }
        .badge-status-perlu { background-color: #fd7e14; }
        .badge-status-selesai { background-color: #20c997; }
        .table-responsive { border-radius: 0.35rem; }
        .btn-group-sm > .btn { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
        .card {
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
            border: none;
        }
        .card-header {
            background: linear-gradient(120deg, #f8f9fc, #e2e8f0);
            border-bottom: 1px solid #e3e6f0;
            border-radius: 12px 12px 0 0 !important;
        }
        .latest-inspection {
            border-left: 4px solid #4e73df;
            background-color: rgba(78, 115, 223, 0.05);
        }
    </style>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include '../../includes/topbar.php'; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Daftar Inspeksi</h1>
                        <div class="d-none d-sm-block">
                            <span class="badge badge-primary">Admin Panel</span>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <?php if($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php elseif($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $success ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Data Inspeksi Terbaru per Kontrak</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Aksi:</div>
                                    <a class="dropdown-item" href="export_inspeksi.php">
                                        <i class="fas fa-file-excel fa-sm fa-fw mr-2 text-success"></i>
                                        Export ke Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Search Form -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <form method="GET" class="d-flex">
                                        <input type="text" name="search" class="form-control form-control-sm" 
                                               placeholder="Cari berdasarkan lokasi, objek, perusahaan, atau petugas..." 
                                               value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm ml-2">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <?php if($search): ?>
                                        <a href="daftar_inspeksi.php" class="btn btn-secondary btn-sm ml-1">
                                            <i class="fas fa-times"></i>
                                        </a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                                <div class="col-md-6 text-right">
                                    <small class="text-muted">
                                        Menampilkan <?= $offset + 1 ?> - <?= min($offset + $limit, $total_records) ?> dari <?= $total_records ?> data
                                    </small>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover" width="100%" cellspacing="0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Perusahaan</th>
                                            <th>Lokasi</th>
                                            <th>Objek</th>
                                            <th>Kondisi</th>
                                            <th>Petugas</th>
                                            <th>Status Perbaikan</th>
                                            <th>Sertifikat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = $offset + 1;
                                        while($row = $inspeksi_data->fetch_assoc()): 
                                            // Cek apakah ini inspeksi terbaru untuk kontrak ini
                                            $is_latest = true; // Karena query sudah memfilter yang terbaru
                                        ?>
                                        <tr class="<?= $is_latest ? 'latest-inspection' : '' ?>">
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($row['tanggal_inspeksi'])) ?>
                                                <?php if($is_latest): ?>
                                                <small class="d-block text-primary"><i class="fas fa-star"></i> Terbaru</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['nama_perusahaan']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($row['nama_client']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($row['lokasi']) ?></td>
                                            <td><?= htmlspecialchars($row['objek_diperiksa']) ?></td>
                                            <td>
                                                <?php
                                                $kondisi_class = '';
                                                switch($row['kondisi']) {
                                                    case 'baik': $kondisi_class = 'badge-kondisi-baik'; break;
                                                    case 'kurang baik': $kondisi_class = 'badge-kondisi-kurang'; break;
                                                    case 'tidak layak': $kondisi_class = 'badge-kondisi-tidak'; break;
                                                    case 'diperbaiki': $kondisi_class = 'badge-kondisi-diperbaiki'; break;
                                                    default: $kondisi_class = 'badge-secondary'; break;
                                                }
                                                ?>
                                                <span class="badge <?= $kondisi_class ?>"><?= ucfirst($row['kondisi']) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($row['petugas_nama']) ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id_inspeksi" value="<?= $row['id_inspeksi'] ?>">
                                                    <select name="status_perbaikan" class="form-control form-control-sm" onchange="this.form.submit()">
                                                        <option value="perlu" <?= $row['status_perbaikan'] == 'perlu' ? 'selected' : '' ?>>Perlu</option>
                                                        <option value="selesai" <?= $row['status_perbaikan'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                <?php if($row['has_sertifikat'] > 0): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Ada
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-times"></i> Belum
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="detail_inspeksi.php?id=<?= $row['id_inspeksi'] ?>" 
                                                       class="btn btn-info btn-sm" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_inspeksi.php?id=<?= $row['id_inspeksi'] ?>" 
                                                       class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if($row['has_sertifikat'] == 0): ?>
                                                    <a href="upload_sertifikat.php?inspeksi=<?= $row['id_inspeksi'] ?>" 
                                                       class="btn btn-success btn-sm" title="Buat Sertifikat">
                                                        <i class="fas fa-certificate"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <a href="?delete=<?= $row['id_inspeksi'] ?>" 
                                                       class="btn btn-danger btn-sm" 
                                                       onclick="return confirm('Yakin ingin menghapus data inspeksi ini?')" 
                                                       title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        
                                        <?php if($inspeksi_data->num_rows == 0): ?>
                                        <tr>
                                            <td colspan="10" class="text-center py-4">
                                                <i class="fas fa-search fa-2x text-gray-300 mb-2"></i>
                                                <p class="text-gray-500">Tidak ada data inspeksi yang ditemukan</p>
                                                <?php if($search): ?>
                                                <a href="daftar_inspeksi.php" class="btn btn-sm btn-outline-primary">Tampilkan Semua Data</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page-1 ?><?= $search ? '&search='.urlencode($search) : '' ?>">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    </li>
                                    
                                    <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?>"><?= $i ?></a>
                                    </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page+1 ?><?= $search ? '&search='.urlencode($search) : '' ?>">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span><?= date("Y"); ?> Copyright &copy; Aplikasi Monitoring Inspeksi, Sertifikasi, dan Resertifikasi Keselamatan dan Kesehatan Kerja (K3) | PT Surveyor Indonesia</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    });
    </script>

</body>
</html>

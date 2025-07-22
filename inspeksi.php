<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

$user_id = $_SESSION['user']['id_user'];

// Query yang diperbaiki untuk menghindari duplikasi - ambil inspeksi terbaru per kontrak
$query = "SELECT i.*, p.nama_perusahaan, k.tanggal_berakhir,
                 ROW_NUMBER() OVER (PARTITION BY i.id_kontrak ORDER BY i.tanggal_inspeksi DESC) as rn
          FROM inspeksi i
          JOIN kontrak k ON i.id_kontrak = k.id_kontrak
          JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
          WHERE i.petugas_id = ?
          ORDER BY i.tanggal_inspeksi DESC";

// Jika MySQL tidak mendukung ROW_NUMBER(), gunakan query alternatif
$query_alternative = "SELECT i.*, p.nama_perusahaan, k.tanggal_berakhir 
          FROM inspeksi i
          JOIN kontrak k ON i.id_kontrak = k.id_kontrak
          JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
          WHERE i.petugas_id = ?
          AND i.id_inspeksi IN (
              SELECT MAX(i2.id_inspeksi)
              FROM inspeksi i2
              WHERE i2.id_kontrak = i.id_kontrak
              AND i2.petugas_id = ?
          )
          ORDER BY i.tanggal_inspeksi DESC";

$stmt = mysqli_prepare($conn, $query_alternative);
mysqli_stmt_bind_param($stmt, 'ii', $user_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Inspeksi</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <style>
      .badge-diperbaiki {
          background-color: #d1ecf1;
          color: #0c5460;
          border: 1px solid #bee5eb;
      }
      .card {
          border-radius: 12px;
          box-shadow: 0 6px 15px rgba(0,0,0,0.08);
          border: none;
      }
      .card-header {
          background: linear-gradient(120deg, #f8f9fc, #e2e8f0);
          border-bottom: 1px solid #e3e6f0;
          padding: 20px 25px;
          border-radius: 12px 12px 0 0 !important;
      }
      .table th {
          vertical-align: middle;
          font-size: 0.9rem;
          border-color: #e3e6f0;
      }
      .table td {
          font-size: 0.85rem;
          border-color: #e3e6f0;
      }
      .btn-circle {
          width: 30px;
          height: 30px;
          padding: 6px 0px;
          border-radius: 15px;
          text-align: center;
          font-size: 12px;
          line-height: 1.428571429;
      }
      .badge {
          font-size: 0.85rem;
          padding: 0.4em 0.6em;
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
                    <h1 class="h3 mb-0 text-gray-800">Daftar Inspeksi</h1>
                    <a href="form_tambah_inspeksi.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Baru
                    </a>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Riwayat Inspeksi</h6>
                            </div>
                            <div class="card-body">
                                <?php if(isset($_SESSION['success'])): ?>
                                    <div class="alert alert-success alert-dismissible fade show">
                                        <?= $_SESSION['success']; ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?php unset($_SESSION['success']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if(isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        <?= $_SESSION['error']; ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?php unset($_SESSION['error']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Perusahaan</th>
                                                <th>Objek Diperiksa</th>
                                                <th>Lokasi</th>
                                                <th>Kondisi</th>
                                                <th>Status Perbaikan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $count = 0;
                                            while ($row = mysqli_fetch_assoc($result)): 
                                                $count++;
                                            ?>
                                            <tr>
                                                <td><?= date('d M Y', strtotime($row['tanggal_inspeksi'])) ?></td>
                                                <td>
                                                    <div class="font-weight-bold"><?= htmlspecialchars($row['nama_perusahaan']) ?></div>
                                                    <small class="text-muted">Kontrak berakhir: <?= date('d M Y', strtotime($row['tanggal_berakhir'])) ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($row['objek_diperiksa']) ?></td>
                                                <td><?= htmlspecialchars($row['lokasi']) ?></td>
                                                <td>
                                                    <?php
                                                        $kondisi = $row['kondisi'];
                                                        $badgeClass = 'secondary';
                                                        if ($kondisi === 'baik') $badgeClass = 'success';
                                                        elseif ($kondisi === 'kurang baik') $badgeClass = 'warning';
                                                        elseif ($kondisi === 'tidak layak') $badgeClass = 'danger';
                                                        elseif ($kondisi === 'diperbaiki') $badgeClass = 'diperbaiki';
                                                    ?>
                                                    <span class="badge badge-<?= $badgeClass ?>">
                                                        <?= ucfirst($kondisi) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                        $status_perbaikan = $row['status_perbaikan'];
                                                        if ($status_perbaikan === 'selesai') {
                                                            echo '<span class="badge badge-success">Selesai</span>';
                                                        } else {
                                                            echo '<span class="badge badge-warning">Perlu Perbaikan</span>';
                                                        }
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="detail_inspeksi.php?id=<?= $row['id_inspeksi'] ?>" 
                                                       class="btn btn-info btn-sm btn-circle"
                                                       title="Detail Inspeksi">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($row['kondisi'] === 'diperbaiki' && $row['status_perbaikan'] === 'perlu'): ?>
                                                    <a href="verifikasi_perbaikan.php?id=<?= $row['id_inspeksi'] ?>" 
                                                       class="btn btn-warning btn-sm btn-circle ml-1"
                                                       title="Verifikasi Perbaikan">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                            
                                            <?php if ($count === 0): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                                        <h5>Belum Ada Data Inspeksi</h5>
                                                        <p class="mb-0">Anda belum melakukan inspeksi apapun</p>
                                                        <p class="mt-3">
                                                            <a href="form_tambah_inspeksi.php" class="btn btn-primary">
                                                                <i class="fas fa-plus mr-2"></i> Buat Inspeksi Pertama
                                                            </a>
                                                        </p>
                                                    </div>
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
    </div>

</div>

<!-- SB Admin 2 Scripts -->
<script src="../../js/sb-admin-2.min.js"></script>
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

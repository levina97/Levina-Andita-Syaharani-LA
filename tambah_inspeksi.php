<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';


// Query untuk mendapatkan kontrak yang aktif dan inspeksi terbaru per kontrak
$base_query = "SELECT k.id_kontrak, k.tanggal_berakhir, k.status, 
                p.nama_perusahaan, p.wilayah, p.kegiatan_tic, 
                o.jenis_objek, i.id_inspeksi, i.status_perbaikan, i.kondisi,
                i.tanggal_inspeksi
             FROM kontrak k
             JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
             LEFT JOIN objek_inspeksi o ON k.id_objek = o.id_objek
             LEFT JOIN (
                 SELECT i1.* FROM inspeksi i1
                 INNER JOIN (
                     SELECT id_kontrak, MAX(tanggal_inspeksi) as max_tanggal,
                            MAX(id_inspeksi) as max_id
                     FROM inspeksi 
                     GROUP BY id_kontrak
                 ) i2 ON i1.id_kontrak = i2.id_kontrak 
                    AND i1.tanggal_inspeksi = i2.max_tanggal
                    AND i1.id_inspeksi = i2.max_id
             ) i ON k.id_kontrak = i.id_kontrak
             WHERE k.tanggal_berakhir > NOW() 
               AND k.status = 'aktif'
               AND (i.status_perbaikan IS NULL OR i.status_perbaikan = 'perlu')";

$conditions = [];
$params = [];
$types = '';

$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = cleanInput($_GET['search']);
    $conditions[] = "(p.nama_perusahaan LIKE ? OR p.wilayah LIKE ? OR o.jenis_objek LIKE ?)";
    $params = array_fill(0, 3, "%$search%");
    $types = 'sss';
}

if (!empty($conditions)) {
    $base_query .= " AND " . implode(' AND ', $conditions);
}

$base_query .= " ORDER BY k.tanggal_berakhir ASC";

$stmt = mysqli_prepare($conn, $base_query);

if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$kontrak_result = mysqli_stmt_get_result($stmt);

// Hitung jumlah hasil
$total_rows = mysqli_num_rows($kontrak_result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Inspeksi</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <style>
        .badge {
            font-size: 0.85rem;
            padding: 0.4em 0.6em;
        }
        .badge-diperbaiki {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .badge-kurangbaik {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .table th {
            vertical-align: middle;
            font-size: 0.9rem;
        }
        .table td {
            font-size: 0.85rem;
        }
        .btn-action {
            padding: 0.3rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 4px;
        }
        .urgent {
            background-color: #ffeded;
            border-left: 3px solid #ff6b6b;
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        .search-form {
            max-width: 300px;
        }
        .info-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .status-cell {
            min-width: 120px;
        }
        .action-cell {
            width: 80px;
        }
        .days-left {
            font-size: 0.75rem;
            display: block;
            margin-top: 3px;
        }
        .expired-soon {
            color: #e74a3b;
            font-weight: bold;
        }
        .expired-normal {
            color: #36b9cc;
        }
        .expired-past {
            color: #6c757d;
        }
        .expired-badge {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .active-badge {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
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
    </style>
</head>
<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include '../../includes/topbar.php'; ?>
            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Kelola Inspeksi</h1>
                    <div class="d-none d-sm-block">
                        <span class="badge badge-primary">Kontrak Perlu Inspeksi</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Daftar Kontrak yang Perlu Inspeksi</h6>
                                    <form class="search-form" method="GET">
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm" 
                                                   name="search" placeholder="Cari..." 
                                                   value="<?= htmlspecialchars($search) ?>">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary btn-sm" type="submit">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                                <?php if(!empty($search)): ?>
                                                    <a href="kelola.inspeksi.php" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <?php if(isset($_SESSION['success'])): ?>
                                    <div class="alert alert-success alert-dismissible fade show m-3">
                                        <?= $_SESSION['success']; ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?php unset($_SESSION['success']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if(isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show m-3">
                                        <?= $_SESSION['error']; ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <?php unset($_SESSION['error']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if(!empty($search)): ?>
                                    <div class="alert alert-info m-3">
                                        Menampilkan <strong><?= $total_rows ?></strong> hasil untuk pencarian: <strong>"<?= htmlspecialchars($search) ?>"</strong>
                                    </div>
                                <?php endif; ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Perusahaan</th>
                                                <th>Wilayah</th>
                                                <th>Jenis Objek</th>
                                                <th>Berakhir Kontrak</th>
                                                <th class="status-cell">Status</th>
                                                <th class="status-cell">Kondisi</th>
                                                <th class="status-cell">Inspeksi</th>
                                                <th class="action-cell">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if ($total_rows > 0) {
                                                $no = 1;
                                                while ($row = mysqli_fetch_assoc($kontrak_result)):
                                                    $id_kontrak = $row['id_kontrak'];
                                                    $isUrgent = strtotime($row['tanggal_berakhir']) < strtotime('+1 month');
                                                    
                                                    // Hitung hari tersisa
                                                    $today = new DateTime();
                                                    $expiry = new DateTime($row['tanggal_berakhir']);
                                                    $interval = $today->diff($expiry);
                                                    
                                                    $daysLeftText = '';
                                                    $daysLeftClass = '';
                                                    
                                                    if ($interval->invert) {
                                                        $daysLeftText = 'Kadaluarsa ' . $interval->days . ' hari lalu';
                                                        $daysLeftClass = 'expired-past';
                                                    } else {
                                                        if ($interval->days < 30) {
                                                            $daysLeftText = $interval->days . ' hari lagi';
                                                            $daysLeftClass = 'expired-soon';
                                                        } else {
                                                            $daysLeftText = $interval->days . ' hari lagi';
                                                            $daysLeftClass = 'expired-normal';
                                                        }
                                                    }
                                                    
                                                    $isExpired = isKontrakExpired($row['tanggal_berakhir']);
                                            ?>
                                            <tr class="<?= $isUrgent ? 'urgent' : '' ?>">
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td>
                                                    <div class="font-weight-bold"><?= htmlspecialchars($row['nama_perusahaan']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($row['kegiatan_tic']) ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($row['wilayah']) ?></td>
                                                <td><?= htmlspecialchars($row['jenis_objek'] ?: '-') ?></td>
                                                <td>
                                                    <span class="d-block"><?= date('d M Y', strtotime($row['tanggal_berakhir'])) ?></span>
                                                    <span class="days-left <?= $daysLeftClass ?>">
                                                        <?= $daysLeftText ?>
                                                    </span>
                                                </td>
                                                <td class="status-cell">
                                                    <?php if($isExpired): ?>
                                                        <span class="badge expired-badge">Kedaluwarsa</span>
                                                    <?php else: ?>
                                                        <span class="badge active-badge">Aktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="status-cell">
                                                    <?php
                                                    if ($row['kondisi'] == 'baik') {
                                                        echo '<span class="badge badge-success">Baik</span>';
                                                    } elseif ($row['kondisi'] == 'kurang baik') {
                                                        echo '<span class="badge badge-kurangbaik">Kurang Baik</span>';
                                                    } elseif ($row['kondisi'] == 'tidak layak') {
                                                        echo '<span class="badge badge-danger">Tidak Layak</span>';
                                                    } elseif ($row['kondisi'] == 'diperbaiki') {
                                                        echo '<span class="badge badge-diperbaiki">Diperbaiki</span>';
                                                    } else {
                                                        echo '<span class="badge badge-secondary">-</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="status-cell">
                                                    <?php if($row['id_inspeksi']): ?>
                                                        <?php
                                                        $status_perbaikan = $row['status_perbaikan'] ?? '';
                                                        if($status_perbaikan == 'perlu') {
                                                            echo '<span class="badge badge-danger">Perlu Perbaikan</span>';
                                                        } else {
                                                            echo '<span class="badge badge-success">Selesai</span>';
                                                        }
                                                        ?>
                                                    <?php else: ?>
                                                        <span class="badge badge-primary">Belum Diinspeksi</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center action-cell">
                                                    <?php if($row['id_inspeksi']): ?>
                                                        <!-- Jika sudah ada inspeksi, redirect ke edit untuk update kondisi -->
                                                        <a href="edit_inspeksi.php?id=<?= $row['id_inspeksi'] ?>" 
                                                           class="btn btn-warning btn-action" 
                                                           title="Update Inspeksi">
                                                            <i class="fas fa-edit"></i> Update
                                                        </a>
                                                    <?php else: ?>
                                                        <!-- Jika belum ada inspeksi, buat baru -->
                                                        <a href="form_tambah_inspeksi.php?id_kontrak=<?= $id_kontrak ?>" 
                                                           class="btn btn-primary btn-action" 
                                                           title="Buat Inspeksi">
                                                            <i class="fas fa-plus"></i> Inspeksi
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php 
                                                endwhile;
                                            } else {
                                            ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-5">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                                        <h5>Semua inspeksi telah selesai</h5>
                                                        <p class="mb-0">Tidak ada kontrak yang perlu diinspeksi saat ini</p>
                                                        <?php if(!empty($search)): ?>
                                                            <p class="mt-3">
                                                                <a href="kelola.inspeksi.php" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-undo mr-1"></i> Tampilkan Semua
                                                                </a>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php } ?>
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
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
});
</script>
</body>
</html>

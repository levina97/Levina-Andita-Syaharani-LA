<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';

// Definisi fungsi clean_input langsung di file ini
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Definisi fungsi formatDate jika belum ada
function formatDate($date) {
    return date('d-m-Y', strtotime($date));
}

$id_inspeksi = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data inspeksi yang akan diedit
$query = "SELECT i.*, k.id_pengajuan, p.nama_perusahaan 
          FROM inspeksi i
          JOIN kontrak k ON i.id_kontrak = k.id_kontrak
          JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
          WHERE i.id_inspeksi = ? AND i.petugas_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $id_inspeksi, $_SESSION['user']['id_user']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$inspeksi = mysqli_fetch_assoc($result);

if (!$inspeksi) {
    $_SESSION['error'] = "Data inspeksi tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// Ambil data kontrak yang terkait dengan pengajuan yang sama
$query_kontrak = "SELECT k.id_kontrak, k.tanggal_berakhir 
                  FROM kontrak k
                  JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
                  WHERE p.id_pengajuan = ?";
$stmt_kontrak = mysqli_prepare($conn, $query_kontrak);
mysqli_stmt_bind_param($stmt_kontrak, 'i', $inspeksi['id_pengajuan']);
mysqli_stmt_execute($stmt_kontrak);
$kontraks = mysqli_stmt_get_result($stmt_kontrak)->fetch_all(MYSQLI_ASSOC);

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kontrak = intval($_POST['id_kontrak']);
    $tanggal_inspeksi = $_POST['tanggal_inspeksi'];
    $lokasi = clean_input($_POST['lokasi']);
    $objek_diperiksa = clean_input($_POST['objek_diperiksa']);
    $kondisi = clean_input($_POST['kondisi']);
    $temuan = clean_input($_POST['temuan']);
    $tindakan_rekomendasi = clean_input($_POST['tindakan_rekomendasi']);
    $status_perbaikan = clean_input($_POST['status_perbaikan']);

    $update_query = "UPDATE inspeksi 
                     SET id_kontrak = ?, tanggal_inspeksi = ?, lokasi = ?, objek_diperiksa = ?, kondisi = ?, 
                         temuan = ?, tindakan_rekomendasi = ?, status_perbaikan = ?
                     WHERE id_inspeksi = ? AND petugas_id = ?";
    $stmt_update = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt_update, 'isssssssii', $id_kontrak, $tanggal_inspeksi, $lokasi, $objek_diperiksa, $kondisi, 
                          $temuan, $tindakan_rekomendasi, $status_perbaikan, $id_inspeksi, $_SESSION['user']['id_user']);
    
    if (mysqli_stmt_execute($stmt_update)) {
        $_SESSION['success'] = "Data inspeksi berhasil diperbarui.";
        header("Location: detail_inspeksi.php?id=$id_inspeksi");
        exit;
    } else {
        $_SESSION['error'] = "Terjadi kesalahan: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Inspeksi - K3 Monitoring</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <style>
        .card-header {
            background-color: #4e73df;
            color: white;
        }
        .form-label {
            font-weight: 600;
            color: #4e73df;
        }
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
                    <h1 class="h3 mb-0 text-gray-800">Edit Inspeksi</h1>
                    <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
                    </a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Form Edit Inspeksi</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_kontrak" class="form-label">Kontrak</label>
                                        <select class="form-control" id="id_kontrak" name="id_kontrak" required>
                                            <?php foreach ($kontraks as $kontrak): ?>
                                                <option value="<?= $kontrak['id_kontrak'] ?>" 
                                                    <?= $kontrak['id_kontrak'] == $inspeksi['id_kontrak'] ? 'selected' : '' ?>>
                                                    Kontrak <?= $kontrak['id_kontrak'] ?> (Berakhir: <?= formatDate($kontrak['tanggal_berakhir']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="tanggal_inspeksi" class="form-label">Tanggal Inspeksi</label>
                                        <input type="date" class="form-control" id="tanggal_inspeksi" name="tanggal_inspeksi" 
                                               value="<?= htmlspecialchars($inspeksi['tanggal_inspeksi']) ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="lokasi" class="form-label">Lokasi</label>
                                        <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                               value="<?= htmlspecialchars($inspeksi['lokasi']) ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="objek_diperiksa" class="form-label">Objek Diperiksa</label>
                                        <input type="text" class="form-control" id="objek_diperiksa" name="objek_diperiksa" 
                                               value="<?= htmlspecialchars($inspeksi['objek_diperiksa']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kondisi" class="form-label">Kondisi</label>
                                        <select class="form-control" id="kondisi" name="kondisi" required>
                                            <option value="baik" <?= $inspeksi['kondisi'] == 'baik' ? 'selected' : '' ?>>Baik</option>
                                            <option value="kurang baik" <?= $inspeksi['kondisi'] == 'kurang baik' ? 'selected' : '' ?>>Kurang Baik</option>
                                            <option value="tidak layak" <?= $inspeksi['kondisi'] == 'tidak layak' ? 'selected' : '' ?>>Tidak Layak</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="status_perbaikan" class="form-label">Status Perbaikan</label>
                                        <select class="form-control" id="status_perbaikan" name="status_perbaikan" required>
                                            <option value="perlu" <?= $inspeksi['status_perbaikan'] == 'perlu' ? 'selected' : '' ?>>Perlu</option>
                                            <option value="selesai" <?= $inspeksi['status_perbaikan'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="temuan" class="form-label">Temuan</label>
                                        <textarea class="form-control" id="temuan" name="temuan" rows="4" required><?= htmlspecialchars($inspeksi['temuan']) ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="tindakan_rekomendasi" class="form-label">Tindakan/Rekomendasi</label>
                                        <textarea class="form-control" id="tindakan_rekomendasi" name="tindakan_rekomendasi" rows="4" required><?= htmlspecialchars($inspeksi['tindakan_rekomendasi']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-icon-split">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-save"></i>
                                    </span>
                                    <span class="text">Simpan Perubahan</span>
                                </button>
                                
                                <a href="detail_inspeksi.php?id=<?= $id_inspeksi ?>" class="btn btn-secondary btn-icon-split ml-2">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-times"></i>
                                    </span>
                                    <span class="text">Batal</span>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../js/sb-admin-2.min.js"></script>
</body>
</html>
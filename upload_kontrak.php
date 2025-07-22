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

// Verifikasi pengajuan
$query = "SELECT * FROM pengajuan WHERE id_pengajuan = $id AND status = 'approved'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: kelola_pengajuan.php");
    exit;
}

$pengajuan = mysqli_fetch_assoc($result);

// Ambil data objek inspeksi yang terkait dengan pengajuan
$query_objek = "SELECT * FROM objek_inspeksi WHERE id_pengajuan = $id";
$result_objek = mysqli_query($conn, $query_objek);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Upload file kontrak
    if (isset($_FILES['kontrak']) && $_FILES['kontrak']['error'] == 0) {
        $file = $_FILES['kontrak'];
        $fileName = uploadFile($file, '../../uploads/kontrak/');
        
        if ($fileName) {
            // Hitung tanggal berakhir (3 bulan dari sekarang)
            $tanggal_berakhir = date('Y-m-d', strtotime('+3 months'));
            
            // Ambil id_objek yang dipilih
            $id_objek = isset($_POST['id_objek']) ? $_POST['id_objek'] : null;
            
            // Simpan data kontrak ke database dengan id_objek
            $query = "INSERT INTO kontrak (id_pengajuan, id_objek, file_kontrak, tanggal_berakhir) 
                      VALUES ($id, " . ($id_objek ? $id_objek : 'NULL') . ", '$fileName', '$tanggal_berakhir')";
            
            if (mysqli_query($conn, $query)) {
                header("Location: detail_pengajuan.php?id=$id");
                exit;
            } else {
                $error = "Gagal menyimpan data kontrak: " . mysqli_error($conn);
            }
        } else {
            $error = "Gagal mengupload file kontrak. Pastikan file dalam format PDF dan ukuran tidak lebih dari 10MB.";
        }
    } else {
        $error = "Silakan pilih file kontrak untuk diupload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Kontrak</title>
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
                    <h1 class="h3 mb-0 text-gray-800">Upload Kontrak</h1>
                    <a href="detail_pengajuan.php?id=<?= $id ?>" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Detail
                    </a>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Form Upload Kontrak</h6>
                            </div>
                            <div class="card-body">
                                <?php if(isset($error)): ?>
                                <div class="alert alert-danger mb-4">
                                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                                </div>
                                <?php endif; ?>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Nama Perusahaan</label>
                                        <input type="text" 
                                               class="form-control"
                                               value="<?= htmlspecialchars($pengajuan['nama_perusahaan']) ?>" 
                                               readonly>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold">Objek yang Diinspeksi</label>
                                        <select name="id_objek" class="form-control" required>
                                            <option value="">Pilih Objek Inspeksi</option>
                                            <?php while($objek = mysqli_fetch_assoc($result_objek)): ?>
                                            <option value="<?= $objek['id_objek'] ?>">
                                                <?= htmlspecialchars($objek['jenis_objek']) ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <small class="form-text text-muted">Pilih objek yang akan diinspeksi sesuai kontrak</small>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold">File Kontrak</label>
                                        <div class="custom-file">
                                            <input type="file" 
                                                   class="custom-file-input" 
                                                   name="kontrak" 
                                                   id="customFile"
                                                   accept=".pdf"
                                                   required>
                                            <label class="custom-file-label" for="customFile">Pilih file PDF (maks. 10MB)</label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold">Masa Berlaku Kontrak</label>
                                        <input type="text" 
                                               class="form-control"
                                               value="3 bulan dari tanggal upload" 
                                               readonly>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-icon-split">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-upload"></i>
                                        </span>
                                        <span class="text">Upload Kontrak</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Panduan Upload</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <i class="fas fa-file-pdf text-danger mr-2"></i>
                                        Format file harus PDF
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-weight-hanging text-warning mr-2"></i>
                                        Maksimal ukuran file 10MB
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-calendar-alt text-primary mr-2"></i>
                                        Masa berlaku otomatis 3 bulan
                                    </li>
                                    <li>
                                        <i class="fas fa-tools text-success mr-2"></i>
                                        Pilih objek yang akan diinspeksi
                                    </li>
                                </ul>
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
// Script untuk menampilkan nama file
$('.custom-file-input').on('change', function() {
    let fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').addClass("selected").html(fileName);
});
</script>

</body>
</html>
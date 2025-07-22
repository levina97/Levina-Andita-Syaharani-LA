<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inspeksi_id = cleanInput($_POST['inspeksi_id']);
    $tanggal_terbit = cleanInput($_POST['tanggal_terbit']);
    $file = $_FILES['file_sertifikat'];
    
    // Validasi file
    $allowed_ext = ['pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed_ext)) {
        $error = "Hanya file PDF yang diizinkan!";
    } elseif ($file['size'] > 5000000) {
        $error = "Ukuran file terlalu besar! Maksimal 5MB";
    } else {
        $filename = uniqid() . '.' . $ext;
        $target = '../../uploads/sertifikat/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $tanggal_berakhir = date('Y-m-d', strtotime($tanggal_terbit . ' +1 year'));
            
            $query = "INSERT INTO sertifikat (id_inspeksi, file_sertifikat, tanggal_terbit, tanggal_berakhir)
                      VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'isss', $inspeksi_id, $filename, $tanggal_terbit, $tanggal_berakhir);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Sertifikat berhasil diupload!";
            } else {
                $error = "Gagal menyimpan data: " . mysqli_error($conn);
            }
        } else {
            $error = "Gagal mengupload file!";
        }
    }
}

// Ambil inspeksi layak
$inspeksi_query = "SELECT i.id_inspeksi, p.nama_perusahaan 
                   FROM inspeksi i
                   JOIN kontrak k ON i.id_kontrak = k.id_kontrak
                   JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
                   WHERE i.kondisi = 'baik'";
$inspeksi_result = mysqli_query($conn, $inspeksi_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Sertifikat</title>
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
                    <h1 class="h3 mb-0 text-gray-800">Upload Sertifikat</h1>
                    <a href="kelola_sertifikat.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar
                    </a>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Form Upload Sertifikat</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?= $error ?></div>
                                <?php endif; ?>
                                <?php if ($success): ?>
                                    <div class="alert alert-success"><?= $success ?></div>
                                <?php endif; ?>
                                
                                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                    <div class="form-group">
                                        <label class="font-weight-bold text-gray-800">Inspeksi</label>
                                        <select name="inspeksi_id" class="form-control rounded-pill" required>
                                            <?php while ($row = mysqli_fetch_assoc($inspeksi_result)): ?>
                                                <option value="<?= $row['id_inspeksi'] ?>"><?= htmlspecialchars($row['nama_perusahaan']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div class="invalid-feedback">Silahkan pilih inspeksi</div>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold text-gray-800">Tanggal Terbit</label>
                                        <input type="date" name="tanggal_terbit" 
                                               class="form-control rounded-pill" 
                                               required 
                                               max="<?= date('Y-m-d') ?>">
                                        <div class="invalid-feedback">Tanggal tidak valid</div>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold text-gray-800">File Sertifikat</label>
                                        <div class="custom-file">
                                            <input type="file" name="file_sertifikat" 
                                                   class="custom-file-input" 
                                                   id="customFile"
                                                   accept=".pdf" 
                                                   required>
                                            <label class="custom-file-label rounded-pill" for="customFile">Pilih file PDF</label>
                                            <div class="invalid-feedback">File wajib diupload</div>
                                        </div>
                                        <small class="form-text text-muted">Maksimal ukuran file: 5MB</small>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-icon-split mt-4">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-upload"></i>
                                        </span>
                                        <span class="text">Upload Sertifikat</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <a href="../../logout.php" class="btn btn-danger float-right">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- SB Admin 2 Scripts -->
<script src="../../js/sb-admin-2.min.js"></script>
<script>
// Script untuk validasi form dan tampilan file
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var fileName = document.getElementById("customFile").files[0].name;
    var nextSibling = e.target.nextElementSibling;
    nextSibling.innerText = fileName;
});
</script>

</body>
</html>

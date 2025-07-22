<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pelanggan') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

// Pastikan direktori upload ada
$uploadDir = '../../uploads/dokumen/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$jenis_objek = [
    'Pesawat Uap dan Bejana Tekan',
    'Tangki Timbun',
    'Pesawat Tenaga Produksi',
    'Pesawat Angkat dan Pesawat Angkut',
    'Alat Berat',
    'Peralatan Bantu Angkat',
    'Instalasi Listrik dan Penyalur Petir',
    'Elevator dan Eskalator',
    'Tanur',
    'Instalasi Proteksi Kebakaran'
];

// Fungsi untuk mengubah struktur array $_FILES yang telah diperbaiki
function reArrayFiles($file_post) {
    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            if (isset($file_post[$key][$i])) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            } else {
                $file_ary[$i][$key] = null;
            }
        }
    }
    return $file_ary;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user']['id_user'];
    $nama_client = $_POST['nama_client'];
    $nama_perusahaan = $_POST['nama_perusahaan'];
    $alamat = $_POST['alamat'];
    $npwp = $_POST['npwp'];
    $kegiatan_tic = $_POST['kegiatan_tic'];
    $wilayah = $_POST['wilayah'];
    $telp = $_POST['telp'];
    $email = $_POST['email'];
    
    // Insert data pengajuan
    $query = "INSERT INTO pengajuan (id_user, nama_client, nama_perusahaan, alamat, npwp, kegiatan_tic, wilayah, telp, email) 
              VALUES ('$user_id', '$nama_client', '$nama_perusahaan', '$alamat', '$npwp', '$kegiatan_tic', '$wilayah', '$telp', '$email')";
    
    if (mysqli_query($conn, $query)) {
        $pengajuan_id = mysqli_insert_id($conn);
        $objek_count = isset($_POST['tambah_objek']) ? count($_POST['tambah_objek']) + 1 : 1;
        
        // Reorganisasi array file dengan pengecekan keberadaan data
        $spesifikasi_files = isset($_FILES['spesifikasi']) && !empty($_FILES['spesifikasi']['name'][0]) ? reArrayFiles($_FILES['spesifikasi']) : [];
        $manual_files = isset($_FILES['manual']) && !empty($_FILES['manual']['name'][0]) ? reArrayFiles($_FILES['manual']) : [];
        $gambar_files = isset($_FILES['gambar']) && !empty($_FILES['gambar']['name'][0]) ? reArrayFiles($_FILES['gambar']) : [];
        $laporan_files = isset($_FILES['laporan']) && !empty($_FILES['laporan']['name'][0]) ? reArrayFiles($_FILES['laporan']) : [];
        $pengesahan_files = isset($_FILES['pengesahan']) && !empty($_FILES['pengesahan']['name'][0]) ? reArrayFiles($_FILES['pengesahan']) : [];
        $catatan_files = isset($_FILES['catatan']) && !empty($_FILES['catatan']['name'][0]) ? reArrayFiles($_FILES['catatan']) : [];
        $surat_izin_files = isset($_FILES['surat_izin']) && !empty($_FILES['surat_izin']['name'][0]) ? reArrayFiles($_FILES['surat_izin']) : [];
        
        for ($i = 0; $i < $objek_count; $i++) {
            $jenis = $_POST['jenis_objek'][$i];
            
            // Upload file-file dengan pengecekan keberadaan data
            $spesifikasi = isset($spesifikasi_files[$i]) && isset($spesifikasi_files[$i]['name']) && !empty($spesifikasi_files[$i]['name']) ? 
                uploadFile($spesifikasi_files[$i], '../../uploads/dokumen/') : null;
            $manual = isset($manual_files[$i]) && isset($manual_files[$i]['name']) && !empty($manual_files[$i]['name']) ? 
                uploadFile($manual_files[$i], '../../uploads/dokumen/') : null;
            $gambar = isset($gambar_files[$i]) && isset($gambar_files[$i]['name']) && !empty($gambar_files[$i]['name']) ? 
                uploadFile($gambar_files[$i], '../../uploads/dokumen/') : null;
            $laporan = isset($laporan_files[$i]) && isset($laporan_files[$i]['name']) && !empty($laporan_files[$i]['name']) ? 
                uploadFile($laporan_files[$i], '../../uploads/dokumen/') : null;
            $pengesahan = isset($pengesahan_files[$i]) && isset($pengesahan_files[$i]['name']) && !empty($pengesahan_files[$i]['name']) ? 
                uploadFile($pengesahan_files[$i], '../../uploads/dokumen/') : null;
            $catatan = isset($catatan_files[$i]) && isset($catatan_files[$i]['name']) && !empty($catatan_files[$i]['name']) ? 
                uploadFile($catatan_files[$i], '../../uploads/dokumen/') : null;
            $surat_izin = isset($surat_izin_files[$i]) && isset($surat_izin_files[$i]['name']) && !empty($surat_izin_files[$i]['name']) ? 
                uploadFile($surat_izin_files[$i], '../../uploads/dokumen/') : null;
            
            // Insert data objek inspeksi
            $objek_query = "INSERT INTO objek_inspeksi (id_pengajuan, jenis_objek, spesifikasi_teknis, manual_operasi, gambar_teknis, laporan_hasil, pengesahan_pemakaian, catatan_pemeliharaan, surat_izin_operator) 
                          VALUES ('$pengajuan_id', '$jenis', '$spesifikasi', '$manual', '$gambar', '$laporan', '$pengesahan', '$catatan', '$surat_izin')";
            mysqli_query($conn, $objek_query);
        }
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Gagal mengajukan inspeksi: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ajukan Inspeksi</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <style>
        .objek-form.minimized .card-body {
            display: none;
        }
        .minimize-btn {
            cursor: pointer;
            float: right;
            margin-left: 10px;
        }
        .objek-summary {
            font-size: 0.9em;
            color: #6c757d;
            margin-left: 10px;
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
                    <h1 class="h3 mb-0 text-gray-800">Ajukan Inspeksi Baru</h1>
                    <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Dashboard
                    </a>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Formulir Pengajuan</h6>
                            </div>
                            <div class="card-body">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger mb-4"><?= $error ?></div>
                                <?php endif; ?>

                                <form method="POST" action="" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label>Nama Client</label>
                                        <input type="text" class="form-control" name="nama_client" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nama Perusahaan</label>
                                                <input type="text" class="form-control" name="nama_perusahaan" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>NPWP</label>
                                                <input type="text" class="form-control" name="npwp" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <textarea class="form-control" name="alamat" rows="3" required></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Kegiatan TIC</label>
                                                <select class="form-control" name="kegiatan_tic" required>
                                                    <option value="inspeksi">Inspeksi</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Wilayah</label>
                                                <input type="text" class="form-control" name="wilayah" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Telepon</label>
                                                <input type="text" class="form-control" name="telp" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>

                                    <hr class="my-5">

                                    <div id="objek-container">
                                        <div class="card mb-4 objek-form" data-objek="1">
                                            <div class="card-header">
                                                <h5 class="m-0 font-weight-bold text-primary">
                                                    Objek Inspeksi 1
                                                    <span class="objek-summary"></span>
                                                    <i class="fas fa-minus minimize-btn" onclick="toggleMinimize(1)" title="Minimize"></i>
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>Jenis Objek</label>
                                                    <select class="form-control jenis-objek-select" name="jenis_objek[]" required onchange="updateSummary(1)">
                                                        <option value="">-- Pilih Jenis Objek --</option>
                                                        <?php foreach ($jenis_objek as $jenis): ?>
                                                            <option value="<?= $jenis ?>"><?= $jenis ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label>Dokumen 1 - Manual Operasi <span class="text-danger">*</span> (PDF, maks 10MB)</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="spesifikasi[]" accept=".pdf" required>
                                                        <label class="custom-file-label">Pilih file...</label>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label>Dokumen 2 - Surat Izin Operator <span class="text-danger">*</span> (PDF, maks 10MB)</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="manual[]" accept=".pdf" required>
                                                        <label class="custom-file-label">Pilih file...</label>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label>Dokumen 3 - Spesifikasi Teknis <span class="text-muted">(Opsional)</span> (PDF, maks 10MB)</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="gambar[]" accept=".pdf">
                                                        <label class="custom-file-label">Pilih file...</label>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label>Dokumen 4 - Laporan Hasil <span class="text-muted">(Opsional)</span> (PDF, maks 10MB)</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="laporan[]" accept=".pdf">
                                                        <label class="custom-file-label">Pilih file...</label>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label>Dokumen 5 - Pengesahan Pemakaian <span class="text-muted">(Opsional)</span> (PDF, maks 10MB)</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="pengesahan[]" accept=".pdf">
                                                        <label class="custom-file-label">Pilih file...</label>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label>Dokumen 6 - Catatan Pemeliharaan <span class="text-muted">(Opsional)</span> (PDF, maks 10MB)</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="catatan[]" accept=".pdf">
                                                        <label class="custom-file-label">Pilih file...</label>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label>Dokumen 7 - Gambar Tenknis <span class="text-muted">(Opsional)</span> (PDF, maks 10MB)</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="surat_izin[]" accept=".pdf">
                                                        <label class="custom-file-label">Pilih file...</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" id="tambah-objek-btn" 
                                        class="btn btn-primary btn-icon-split mb-4"
                                        onclick="tambahObjek()">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-plus"></i>
                                        </span>
                                        <span class="text">Tambah Objek Inspeksi</span>
                                    </button>

                                    <div class="text-center mt-5">
                                        <button type="submit" class="btn btn-success btn-icon-split btn-lg">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-paper-plane"></i>
                                            </span>
                                            <span class="text">Ajukan Inspeksi</span>
                                        </button>
                                    </div>
                                </form>
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
// Dynamic Object Inspection
let objekCount = 1;
const maxObjek = 3;

function tambahObjek() {
    if (objekCount < maxObjek) {
        objekCount++;
        const objekContainer = document.getElementById('objek-container');
        
        const newObjek = document.createElement('div');
        newObjek.className = 'card mb-4 objek-form';
        newObjek.setAttribute('data-objek', objekCount);
        newObjek.innerHTML = `
            <div class="card-header">
                <h5 class="m-0 font-weight-bold text-primary">
                    Objek Inspeksi ${objekCount}
                    <span class="objek-summary"></span>
                    <i class="fas fa-minus minimize-btn" onclick="toggleMinimize(${objekCount})" title="Minimize"></i>
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Jenis Objek</label>
                    <select class="form-control jenis-objek-select" name="jenis_objek[]" required onchange="updateSummary(${objekCount})">
                        <option value="">-- Pilih Jenis Objek --</option>
                        <?php foreach ($jenis_objek as $jenis): ?>
                            <option value="<?= $jenis ?>"><?= $jenis ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Dokumen 1 - Manual Operasi <span class="text-danger">*</span> (PDF, maks 10MB)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="spesifikasi[]" accept=".pdf" required>
                        <label class="custom-file-label">Pilih file...</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Dokumen 2 - Surat Izin Operator <span class="text-danger">*</span> (PDF, maks 10MB)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="manual[]" accept=".pdf" required>
                        <label class="custom-file-label">Pilih file...</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Dokumen 3 - Gambar Teknis <span class="text-muted">(Opsional)</span> (PDF, maks 10MB)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="gambar[]" accept=".pdf">
                        <label class="custom-file-label">Pilih file...</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Dokumen 4 - Laporan Hasil <span class="text-muted">(Opsional)</span> (PDF, maks 10MB)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="laporan[]" accept=".pdf">
                        <label class="custom-file-label">Pilih file...</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Dokumen 5 - Pengesahan Pemakaian <span class="text-muted">(Opsional)</span> (PDF, maks 10MB)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="pengesahan[]" accept=".pdf">
                        <label class="custom-file-label">Pilih file...</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Dokumen 6 - Catatan Pemeliharaan <span class="text-muted">(Opsional)</span> (PDF, maks 10MB)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="catatan[]" accept=".pdf">
                        <label class="custom-file-label">Pilih file...</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Dokumen 7 - Spesifikasi Teknis <span class="text-muted">(Opsional)</span> (PDF, maks 10MB)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="surat_izin[]" accept=".pdf">
                        <label class="custom-file-label">Pilih file...</label>
                    </div>
                </div>
            </div>
        `;
        
        objekContainer.appendChild(newObjek);

        // Update button visibility
        if (objekCount === maxObjek) {
            document.getElementById('tambah-objek-btn').style.display = 'none';
        }

        // Re-initialize file input handlers for new form
        initializeFileInputs();
    }
}

function toggleMinimize(objekNumber) {
    const objekForm = document.querySelector(`[data-objek="${objekNumber}"]`);
    const minimizeBtn = objekForm.querySelector('.minimize-btn');
    
    if (objekForm.classList.contains('minimized')) {
        objekForm.classList.remove('minimized');
        minimizeBtn.classList.remove('fa-plus');
        minimizeBtn.classList.add('fa-minus');
        minimizeBtn.title = 'Minimize';
    } else {
        objekForm.classList.add('minimized');
        minimizeBtn.classList.remove('fa-minus');
        minimizeBtn.classList.add('fa-plus');
        minimizeBtn.title = 'Expand';
    }
}

function updateSummary(objekNumber) {
    const objekForm = document.querySelector(`[data-objek="${objekNumber}"]`);
    const select = objekForm.querySelector('.jenis-objek-select');
    const summary = objekForm.querySelector('.objek-summary');
    
    if (select.value) {
        summary.textContent = `- ${select.value}`;
    } else {
        summary.textContent = '';
    }
}

function initializeFileInputs() {
    $('.custom-file-input').off('change').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
}

// Initialize on document ready
$(document).ready(function () {
    initializeFileInputs();
});
</script>

</body>
</html>
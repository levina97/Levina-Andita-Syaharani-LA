<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

/* ----------------------------------------------------------
   AMBIL DATA KONTRAK YANG MASIH AKTIF
-----------------------------------------------------------*/
$kontrak_id = isset($_GET['id_kontrak']) ? cleanInput($_GET['id_kontrak']) : null;

$kontrak_query = "SELECT k.id_kontrak, p.nama_perusahaan, p.wilayah, o.jenis_objek
                  FROM kontrak k
                  JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
                  LEFT JOIN objek_inspeksi o ON k.id_objek = o.id_objek
                  WHERE k.tanggal_berakhir > NOW()
                    AND k.status = 'aktif'
                  ORDER BY k.tanggal_berakhir ASC";
$kontrak_result = mysqli_query($conn, $kontrak_query);

/* ----------------------------------------------------------
   AMBIL DETAIL KONTRAK JIKA ID DIKIRIM VIA URL
-----------------------------------------------------------*/
$kontrak_data = null;
$objek_data   = null;

if ($kontrak_id) {
    $kontrak_query_single = "SELECT k.id_kontrak, p.nama_perusahaan, p.wilayah, o.jenis_objek
                             FROM kontrak k
                             JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
                             LEFT JOIN objek_inspeksi o ON k.id_objek = o.id_objek
                             WHERE k.id_kontrak = $kontrak_id";
    $result_single = mysqli_query($conn, $kontrak_query_single);
    if ($row = mysqli_fetch_assoc($result_single)) {
        $kontrak_data = $row;
        $objek_data   = $row['jenis_objek'];
    }
}

/* ----------------------------------------------------------
   PROSES SUBMIT FORM
-----------------------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kontrak_id  = cleanInput($_POST['kontrak_id']);
    $tanggal     = cleanInput($_POST['tanggal']);
    $lokasi      = cleanInput($_POST['lokasi']);
    $objek       = cleanInput($_POST['objek']);
    $kondisi     = cleanInput($_POST['kondisi']);
    $temuan      = cleanInput($_POST['temuan']);
    $tindakan    = cleanInput($_POST['tindakan']);
    $petugas_id  = $_SESSION['user']['id_user'];

    /* ------------------------------------------------------
       STATUS_PERBAIKAN TIDAK LAGI OTOMATIS “SELESAI”  
       -> SELALU “perlu” SAMPAI DIVERIFIKASI (default DB) [1]
    -------------------------------------------------------*/
    $status_perbaikan = 'perlu';

    $sql  = "INSERT INTO inspeksi
             (id_kontrak, tanggal_inspeksi, lokasi, objek_diperiksa,
              kondisi, temuan, tindakan_rekomendasi,
              petugas_id, status_perbaikan)
             VALUES (?,?,?,?,?,?,?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        'issssssis',
        $kontrak_id,
        $tanggal,
        $lokasi,
        $objek,
        $kondisi,
        $temuan,
        $tindakan,
        $petugas_id,
        $status_perbaikan
    );

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'Data inspeksi berhasil disimpan!';
        header('Location: inspeksi.php');
        exit;
    }
    $_SESSION['error'] = 'Gagal menyimpan data inspeksi! '.mysqli_error($conn);
    header('Location: form_tambah_inspeksi.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tambah Inspeksi Baru</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <style>
        :root { --primary:#4e73df; --secondary:#6c757d; --success:#1cc88a;
                --warning:#f6c23e; --danger:#e74a3b; --light:#f8f9fc; --dark:#5a5c69; }
        .card{border-radius:12px;box-shadow:0 6px 15px rgba(0,0,0,.08);border:none;}
        .form-control,.custom-select{border-radius:8px;padding:12px 15px;border:1px solid #d1d3e2;transition:.3s;}
        .form-control:focus,.custom-select:focus{border-color:var(--primary);box-shadow:0 0 0 .2rem rgba(78,115,223,.25);}
        .btn{border-radius:8px;padding:12px 24px;font-weight:600;transition:.3s;box-shadow:0 2px 4px rgba(0,0,0,.1);}
        .btn-primary{background-color:var(--primary);border-color:var(--primary);}
        .btn-primary:hover{background-color:#2e59d9;border-color:#2653d4;transform:translateY(-2px);box-shadow:0 4px 8px rgba(78,115,223,.3);}
        .btn-secondary{background-color:var(--secondary);border-color:var(--secondary);}
        .btn-secondary:hover{background-color:#5a6268;border-color:#545b62;transform:translateY(-2px);box-shadow:0 4px 8px rgba(108,117,125,.3);}
        .info-box{background:#f0f5ff;border-left:4px solid var(--primary);padding:20px;border-radius:8px;margin-bottom:25px;}
        .info-box h5{color:var(--primary);margin-bottom:12px;font-weight:700;font-size:1.1rem;}
        .required-label:after{content:" *";color:var(--danger);}
        .condition-btn-group{display:flex;gap:15px;flex-wrap:wrap;}
        .condition-btn{flex:1;min-width:120px;text-align:center;padding:15px;border-radius:8px;background:#fff;border:2px solid #e3e6f0;cursor:pointer;transition:.3s;position:relative;}
        .condition-btn:hover{transform:translateY(-3px);box-shadow:0 4px 10px rgba(0,0,0,.08);}
        .condition-btn.selected{border-color:var(--primary);background:#f0f5ff;}
        .condition-btn input{position:absolute;opacity:0;width:0;height:0;}
        .condition-btn i{font-size:2rem;margin-bottom:10px;display:block;}
        .condition-btn.baik i{color:var(--success);}
        .condition-btn.baik.selected{border-color:var(--success);background:rgba(28,200,138,.1);}
        .condition-btn.kurang-baik i{color:var(--warning);}
        .condition-btn.kurang-baik.selected{border-color:var(--warning);background:rgba(246,194,62,.1);}
        .condition-btn.tidak-layak i{color:var(--danger);}
        .condition-btn.tidak-layak.selected{border-color:var(--danger);background:rgba(231,74,59,.1);}
        .input-group-icon{position:relative;}
        .form-icon{position:absolute;right:15px;top:50%;transform:translateY(-50%);color:#b7b9cc;}
        .btn-reset{background:#eaecf4;color:var(--dark);border:1px solid #d1d3e2;}
        .btn-reset:hover{background:#dde1e9;color:var(--dark);}
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
                    <h1 class="h3 mb-0 text-gray-800">Tambah Inspeksi Baru</h1>
                    <a href="inspeksi.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar
                    </a>
                </div>
                <div class="row"><div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Form Inspeksi</h6></div>
                        <div class="card-body p-4">
                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                </div>
                            <?php endif; ?>
                            <?php if($kontrak_data): ?>
                            <div class="info-box">
                                <h5>Informasi Kontrak</h5>
                                <div class="row">
                                    <div class="col-md-4"><p><strong>Perusahaan:</strong> <?= htmlspecialchars($kontrak_data['nama_perusahaan']) ?></p></div>
                                    <div class="col-md-4"><p><strong>Wilayah:</strong> <?= htmlspecialchars($kontrak_data['wilayah']) ?></p></div>
                                    <div class="col-md-4"><p><strong>Objek Inspeksi:</strong> <?= htmlspecialchars($kontrak_data['jenis_objek']) ?></p></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="form-group">
                                    <label class="font-weight-bold text-gray-800 required-label">Kontrak</label>
                                    <div class="input-group-icon">
                                        <select name="kontrak_id" class="form-control custom-select" required <?= $kontrak_data ? 'disabled' : '' ?>>
                                            <option value="">Pilih Kontrak</option>
                                            <?php mysqli_data_seek($kontrak_result,0);
                                            while($row = mysqli_fetch_assoc($kontrak_result)):
                                                $sel = ($kontrak_id && $row['id_kontrak']==$kontrak_id)?'selected':'';
                                            ?>
                                            <option value="<?= $row['id_kontrak'] ?>" <?= $sel ?>>
                                                <?= htmlspecialchars($row['nama_perusahaan']).' - '.htmlspecialchars($row['wilayah']).' - '.htmlspecialchars($row['jenis_objek']) ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <i class="fas fa-file-contract form-icon"></i>
                                    </div>
                                    <?php if($kontrak_data): ?>
                                        <input type="hidden" name="kontrak_id" value="<?= $kontrak_data['id_kontrak'] ?>">
                                    <?php endif; ?>
                                    <div class="invalid-feedback">Pilih kontrak terlebih dahulu</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800 required-label">Tanggal Inspeksi</label>
                                            <div class="input-group-icon">
                                                <input type="date" name="tanggal" class="form-control"
                                                       value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                                                <i class="fas fa-calendar-alt form-icon"></i>
                                            </div>
                                            <div class="invalid-feedback">Tanggal tidak valid</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800 required-label">Lokasi</label>
                                            <div class="input-group-icon">
                                                <input type="text" name="lokasi" class="form-control"
                                                       placeholder="Masukkan lokasi inspeksi"
                                                       value="<?= $kontrak_data?htmlspecialchars($kontrak_data['wilayah']):'' ?>" required>
                                                <i class="fas fa-map-marker-alt form-icon"></i>
                                            </div>
                                            <div class="invalid-feedback">Lokasi wajib diisi</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold text-gray-800 required-label">Objek yang Diperiksa</label>
                                    <div class="input-group-icon">
                                        <input type="text" name="objek" class="form-control"
                                               placeholder="Masukkan objek yang diperiksa"
                                               value="<?= $objek_data?htmlspecialchars($objek_data):'' ?>" required>
                                        <i class="fas fa-search form-icon"></i>
                                    </div>
                                    <div class="invalid-feedback">Objek wajib diisi</div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold text-gray-800 required-label mb-3">Kondisi Objek</label>
                                    <div class="condition-btn-group">
                                        <label class="condition-btn baik">
                                            <input type="radio" name="kondisi" value="baik" required>
                                            <i class="fas fa-check-circle"></i>
                                            <div>Baik</div><small>Objek berfungsi normal</small>
                                        </label>
                                        <label class="condition-btn kurang-baik">
                                            <input type="radio" name="kondisi" value="kurang baik" required>
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <div>Kurang Baik</div><small>Ada masalah kecil</small>
                                        </label>
                                        <label class="condition-btn tidak-layak">
                                            <input type="radio" name="kondisi" value="tidak layak" required>
                                            <i class="fas fa-times-circle"></i>
                                            <div>Tidak Layak</div><small>Perlu perbaikan segera</small>
                                        </label>
                                    </div>
                                    <div class="invalid-feedback d-block">Pilih kondisi objek</div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold text-gray-800">Temuan</label>
                                    <textarea name="temuan" class="form-control form-textarea" placeholder="Deskripsikan temuan selama inspeksi"></textarea>
                                    <small class="form-text text-muted">Kosongkan jika tidak ada temuan khusus</small>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold text-gray-800">Tindakan Rekomendasi</label>
                                    <textarea name="tindakan" class="form-control form-textarea" placeholder="Berikan rekomendasi tindakan perbaikan"></textarea>
                                    <small class="form-text text-muted">Kosongkan jika tidak diperlukan tindakan khusus</small>
                                </div>

                                <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                                    <button type="reset" class="btn btn-reset"><i class="fas fa-redo mr-2"></i> Reset Form</button>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i> Simpan Data</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div></div>
            </div>
        </div>
    </div>
</div>

<script src="../../js/sb-admin-2.min.js"></script>
<script>
(function(){
    'use strict';
    window.addEventListener('load',()=>{[...document.getElementsByClassName('needs-validation')].forEach(form=>{
        form.addEventListener('submit',e=>{
            if(!form.checkValidity()){e.preventDefault();e.stopPropagation();}
            form.classList.add('was-validated');
        },false);
    });});
})();

document.querySelector('select[name="kontrak_id"]').addEventListener('change',function(){
    let parts=this.options[this.selectedIndex].text.split(' - ');
    if(parts.length>=3){
        document.querySelector('input[name="lokasi"]').value=parts[1].trim();
        document.querySelector('input[name="objek"]').value =parts[2].trim();
    }
});

const btns=document.querySelectorAll('.condition-btn');
btns.forEach(btn=>{
    btn.addEventListener('click',function(){
        btns.forEach(b=>b.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input').checked=true;
    });
});
<?php if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['kondisi'])): ?>
document.querySelectorAll('.condition-btn').forEach(btn=>{
    if(btn.classList.contains("<?= cleanInput($_POST['kondisi']) ?>".replace(' ','-'))){
        btn.classList.add('selected');
        btn.querySelector('input').checked=true;
    }
});
<?php endif; ?>
</script>
</body>
</html>

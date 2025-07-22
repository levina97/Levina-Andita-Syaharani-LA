<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pelanggan') {
    exit('Unauthorized');
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

$user_id = $_SESSION['user']['id_user'];
$pengajuan_id = isset($_POST['pengajuan_id']) ? (int)$_POST['pengajuan_id'] : 0;

// Validasi pengajuan milik user
$query_validasi = "SELECT * FROM pengajuan WHERE id_pengajuan = $pengajuan_id AND id_user = $user_id";
$result_validasi = mysqli_query($conn, $query_validasi);

if (mysqli_num_rows($result_validasi) == 0) {
    echo '<li class="error text-center"><i class="fas fa-exclamation-circle text-danger fa-2x"></i><p>Data tidak ditemukan</p></li>';
    exit;
}

$pengajuan = mysqli_fetch_assoc($result_validasi);

// Query untuk mengambil data lengkap proses
$query_kontrak = "SELECT * FROM kontrak WHERE id_pengajuan = $pengajuan_id";
$result_kontrak = mysqli_query($conn, $query_kontrak);
$kontrak = mysqli_fetch_assoc($result_kontrak);

$inspeksi = null;
$sertifikat = null;
if ($kontrak) {
    $query_inspeksi = "SELECT i.*, u.nama as petugas_nama 
                      FROM inspeksi i 
                      LEFT JOIN users u ON i.petugas_id = u.id_user 
                      WHERE i.id_kontrak = {$kontrak['id_kontrak']}";
    $result_inspeksi = mysqli_query($conn, $query_inspeksi);
    $inspeksi = mysqli_fetch_assoc($result_inspeksi);
    
    if ($inspeksi) {
        $query_sertifikat = "SELECT * FROM sertifikat WHERE id_inspeksi = {$inspeksi['id_inspeksi']}";
        $result_sertifikat = mysqli_query($conn, $query_sertifikat);
        $sertifikat = mysqli_fetch_assoc($result_sertifikat);
    }
}
?>

<style>
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}
.timeline:before {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 40px;
    width: 2px;
    margin-left: -1.5px;
    content: '';
    background-color: #e9ecef;
}
.timeline > li {
    position: relative;
    margin-bottom: 30px;
    min-height: 50px;
}
.timeline > li .timeline-panel {
    position: relative;
    float: right;
    width: calc(100% - 90px);
    padding: 15px;
    border: 1px solid #d4edda;
    border-radius: 5px;
    background: #f8f9fa;
}
.timeline > li .timeline-badge {
    position: absolute;
    top: 16px;
    left: 28px;
    z-index: 100;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    text-align: center;
    font-size: 1.2em;
    line-height: 25px;
    color: #fff;
}
.timeline-badge.success { background-color: #28a745; }
.timeline-badge.warning { background-color: #ffc107; }
.timeline-badge.info { background-color: #17a2b8; }
.timeline-badge.danger { background-color: #dc3545; }
.timeline-badge.secondary { background-color: #6c757d; }
</style>

<li>
    <div class="timeline-badge success">
        <i class="fas fa-plus"></i>
    </div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h6 class="timeline-title">Pengajuan Dibuat</h6>
            <p><small class="text-muted"><?= formatDate($pengajuan['tanggal_pengajuan']) ?></small></p>
        </div>
        <div class="timeline-body">
            <p><strong>Perusahaan:</strong> <?= htmlspecialchars($pengajuan['nama_perusahaan']) ?></p>
            <p><strong>Client:</strong> <?= htmlspecialchars($pengajuan['nama_client']) ?></p>
            <p><strong>Kegiatan:</strong> <?= htmlspecialchars($pengajuan['kegiatan_tic']) ?></p>
        </div>
    </div>
</li>

<?php if ($pengajuan['status'] == 'approved'): ?>
<li>
    <div class="timeline-badge success">
        <i class="fas fa-check"></i>
    </div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h6 class="timeline-title">Pengajuan Disetujui</h6>
        </div>
        <div class="timeline-body">
            <p>Pengajuan telah disetujui oleh admin dan akan diproses lebih lanjut.</p>
            <span class="badge badge-success">Approved</span>
        </div>
    </div>
</li>
<?php elseif ($pengajuan['status'] == 'rejected'): ?>
<li>
    <div class="timeline-badge danger">
        <i class="fas fa-times"></i>
    </div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h6 class="timeline-title">Pengajuan Ditolak</h6>
        </div>
        <div class="timeline-body">
            <p>Pengajuan ditolak. Silakan hubungi admin untuk informasi lebih lanjut.</p>
            <span class="badge badge-danger">Rejected</span>
        </div>
    </div>
</li>
<?php else: ?>
<li>
    <div class="timeline-badge warning">
        <i class="fas fa-clock"></i>
    </div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h6 class="timeline-title">Menunggu Persetujuan</h6>
        </div>
        <div class="timeline-body">
            <p>Pengajuan sedang menunggu persetujuan dari admin.</p>
            <span class="badge badge-warning">Pending</span>
        </div>
    </div>
</li>
<?php endif; ?>

<?php if ($kontrak): ?>
<li>
    <div class="timeline-badge info">
        <i class="fas fa-file-contract"></i>
    </div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h6 class="timeline-title">Kontrak Dibuat</h6>
            <p><small class="text-muted"><?= formatDate($kontrak['tanggal_upload']) ?></small></p>
        </div>
        <div class="timeline-body">
            <p><strong>Tanggal Berakhir:</strong> <?= formatDate($kontrak['tanggal_berakhir']) ?></p>
            <p><strong>Status:</strong> 
                <span class="badge badge-<?= $kontrak['status'] == 'aktif' ? 'success' : 'danger' ?>">
                    <?= ucfirst($kontrak['status']) ?>
                </span>
            </p>
            <a href="../../uploads/kontrak/<?= $kontrak['file_kontrak'] ?>" 
               target="_blank" class="btn btn-sm btn-primary">
                <i class="fas fa-download"></i> Unduh Kontrak
            </a>
        </div>
    </div>
</li>
<?php endif; ?>

<?php if ($inspeksi): ?>
<li>
    <div class="timeline-badge info">
        <i class="fas fa-search"></i>
    </div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h6 class="timeline-title">Inspeksi Dilakukan</h6>
            <p><small class="text-muted"><?= formatDate($inspeksi['tanggal_inspeksi']) ?></small></p>
        </div>
        <div class="timeline-body">
            <p><strong>Lokasi:</strong> <?= htmlspecialchars($inspeksi['lokasi']) ?></p>
            <p><strong>Objek Diperiksa:</strong> <?= htmlspecialchars($inspeksi['objek_diperiksa']) ?></p>
            <p><strong>Petugas:</strong> <?= htmlspecialchars($inspeksi['petugas_nama']) ?></p>
            <p><strong>Kondisi:</strong> 
                <span class="badge badge-<?= 
                    $inspeksi['kondisi'] == 'baik' ? 'success' : 
                    ($inspeksi['kondisi'] == 'kurang baik' ? 'warning' : 'danger') ?>">
                    <?= ucfirst($inspeksi['kondisi']) ?>
                </span>
            </p>
            <p><strong>Status Perbaikan:</strong> 
                <span class="badge badge-<?= $inspeksi['status_perbaikan'] == 'selesai' ? 'success' : 'warning' ?>">
                    <?= ucfirst($inspeksi['status_perbaikan']) ?>
                </span>
            </p>
            
            <?php if ($inspeksi['temuan']): ?>
            <div class="mt-2">
                <p><strong>Temuan:</strong></p>
                <div class="alert alert-warning alert-sm">
                    <?= nl2br(htmlspecialchars($inspeksi['temuan'])) ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($inspeksi['tindakan_rekomendasi']): ?>
            <div class="mt-2">
                <p><strong>Rekomendasi:</strong></p>
                <div class="alert alert-info alert-sm">
                    <?= nl2br(htmlspecialchars($inspeksi['tindakan_rekomendasi'])) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</li>
<?php endif; ?>

<?php if ($sertifikat): ?>
<li>
    <div class="timeline-badge success">
        <i class="fas fa-certificate"></i>
    </div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h6 class="timeline-title">Sertifikat Diterbitkan</h6>
            <p><small class="text-muted"><?= formatDate($sertifikat['tanggal_terbit']) ?></small></p>
        </div>
        <div class="timeline-body">
            <p><strong>Tanggal Berakhir:</strong> <?= formatDate($sertifikat['tanggal_berakhir']) ?></p>
            <p><strong>Status:</strong> 
                <span class="badge badge-<?= $sertifikat['status'] == 'active' ? 'success' : 'danger' ?>">
                    <?= $sertifikat['status'] == 'active' ? 'Aktif' : 'Kedaluwarsa' ?>
                </span>
            </p>
            
            <?php 
            $days_remaining = round((strtotime($sertifikat['tanggal_berakhir']) - time()) / (60 * 60 * 24));
            if ($days_remaining <= 30 && $sertifikat['status'] == 'active'): 
            ?>
            <div class="alert alert-warning alert-sm">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Perhatian:</strong> Sertifikat akan berakhir dalam <?= $days_remaining ?> hari. 
                Segera ajukan resertifikasi.
            </div>
            <?php endif; ?>
            
            <a href="../../uploads/sertifikat/<?= $sertifikat['file_sertifikat'] ?>" 
               target="_blank" class="btn btn-sm btn-warning">
                <i class="fas fa-download"></i> Unduh Sertifikat
            </a>
        </div>
    </div>
</li>
<?php endif; ?>

<?php if (!$kontrak && $pengajuan['status'] == 'approved'): ?>
<li>
    <div class="timeline-badge secondary">
        <i class="fas fa-hourglass-half"></i>
    </div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h6 class="timeline-title">Menunggu Kontrak</h6>
        </div>
        <div class="timeline-body">
            <p>Menunggu admin untuk membuat dan mengunggah kontrak.</p>
            <span class="badge badge-secondary">In Progress</span>
        </div>
    </div>
</li>
<?php endif; ?>

<?php if ($kontrak && !$inspeksi): ?>
<li>
    <div class="timeline-badge secondary">
        <i class="fas fa-hourglass-half"></i>
    </div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h6 class="timeline-title">Menunggu Jadwal Inspeksi</h6>
        </div>
        <div class="timeline-body">
            <p>Kontrak telah dibuat. Menunggu penjadwalan inspeksi oleh staff.</p>
            <span class="badge badge-secondary">In Progress</span>
        </div>
    </div>
</li>
<?php endif; ?>

<?php if ($inspeksi && !$sertifikat): ?>
<li>
    <div class="timeline-badge secondary">
        <i class="fas fa-hourglass-half"></i>
    </div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h6 class="timeline-title">Menunggu Penerbitan Sertifikat</h6>
        </div>
        <div class="timeline-body">
            <p>Inspeksi telah selesai. Menunggu penerbitan sertifikat.</p>
            <span class="badge badge-secondary">In Progress</span>
        </div>
    </div>
</li>
<?php endif; ?>

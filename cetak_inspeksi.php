<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

$id_inspeksi = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT i.*, k.tanggal_berakhir, p.nama_perusahaan, p.nama_client, p.alamat, p.npwp, p.telp, p.email,
                 u.nama AS nama_petugas, s.file_sertifikat, s.tanggal_terbit, s.tanggal_berakhir AS berakhir_sertifikat
          FROM inspeksi i
          JOIN kontrak k ON i.id_kontrak = k.id_kontrak
          JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
          JOIN users u ON i.petugas_id = u.id_user
          LEFT JOIN sertifikat s ON i.id_inspeksi = s.id_inspeksi
          WHERE i.id_inspeksi = ? AND i.petugas_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $id_inspeksi, $_SESSION['user']['id_user']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$inspeksi = mysqli_fetch_assoc($result);

if (!$inspeksi) {
    die("Data inspeksi tidak ditemukan.");
}

$kondisi_text = [
    'baik' => 'Baik',
    'kurang baik' => 'Kurang Baik',
    'tidak layak' => 'Tidak Layak'
];

$status_perbaikan_text = [
    'perlu' => 'Perlu',
    'selesai' => 'Selesai'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Inspeksi - K3 Monitoring</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4e73df;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #4e73df;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 18px;
            color: #6c757d;
            font-weight: normal;
        }
        .logo {
            width: 120px;
            height: auto;
            margin-bottom: 10px;
        }
        .info-box {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
        }
        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-box th, .info-box td {
            padding: 8px 5px;
            vertical-align: top;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .info-box th {
            width: 25%;
            font-weight: 600;
            color: #4e73df;
        }
        .content-section {
            margin-top: 25px;
            margin-bottom: 25px;
        }
        .section-title {
            background-color: #4e73df;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .section-content {
            padding: 15px;
            border: 1px solid #e3e6f0;
            border-radius: 4px;
            background-color: #fff;
            min-height: 100px;
            white-space: pre-line;
        }
        .signature {
            margin-top: 50px;
            text-align: right;
        }
        .signature-placeholder {
            height: 100px;
            margin-top: 20px;
            position: relative;
        }
        .signature-line {
            display: inline-block;
            width: 300px;
            border-top: 1px solid #333;
            padding-top: 10px;
            text-align: center;
        }
        .print-controls {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        @media print {
            .print-controls {
                display: none;
            }
            body {
                margin: 0;
                padding: 20px;
            }
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-baik {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-kurang-baik {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-tidak-layak {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-perlu {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-selesai {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../../img/logo.png" alt="Logo Perusahaan" class="logo">
        <h1>LAPORAN HASIL INSPEKSI KESELAMATAN DAN KESEHATAN KERJA (K3)</h1>
        <h2>PT. Surveyor Indonesia - Divisi K3</h2>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <th>Perusahaan</th>
                <td><?= htmlspecialchars($inspeksi['nama_perusahaan']) ?></td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td><?= htmlspecialchars($inspeksi['alamat']) ?></td>
            </tr>
            <tr>
                <th>Tanggal Inspeksi</th>
                <td><?= formatDate($inspeksi['tanggal_inspeksi']) ?></td>
            </tr>
            <tr>
                <th>Lokasi</th>
                <td><?= htmlspecialchars($inspeksi['lokasi']) ?></td>
            </tr>
            <tr>
                <th>Objek Diperiksa</th>
                <td><?= htmlspecialchars($inspeksi['objek_diperiksa']) ?></td>
            </tr>
            <tr>
                <th>Petugas Inspeksi</th>
                <td><?= htmlspecialchars($inspeksi['nama_petugas']) ?></td>
            </tr>
            <tr>
                <th>Status Perbaikan</th>
                <td>
                    <span class="status-badge badge-<?= $inspeksi['status_perbaikan'] ?>">
                        <?= $status_perbaikan_text[$inspeksi['status_perbaikan']] ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="content-section">
        <div class="section-title">HASIL INSPEKSI</div>
        <div class="section-content">
            <p><strong>Kondisi: </strong>
                <span class="status-badge badge-<?= str_replace(' ', '-', $inspeksi['kondisi']) ?>">
                    <?= $kondisi_text[$inspeksi['kondisi']] ?>
                </span>
            </p>
            <p><strong>Temuan:</strong></p>
            <p><?= htmlspecialchars($inspeksi['temuan']) ?></p>
        </div>
    </div>

    <div class="content-section">
        <div class="section-title">TINDAKAN DAN REKOMENDASI</div>
        <div class="section-content">
            <?= htmlspecialchars($inspeksi['tindakan_rekomendasi']) ?>
        </div>
    </div>

    <?php if ($inspeksi['file_sertifikat']): ?>
    <div class="content-section">
        <div class="section-title">SERTIFIKAT INSPEKSI</div>
        <div class="section-content">
            <p>Sertifikat inspeksi telah diterbitkan dengan rincian sebagai berikut:</p>
            <table>
                <tr>
                    <td width="30%">Tanggal Terbit</td>
                    <td>: <?= formatDate($inspeksi['tanggal_terbit']) ?></td>
                </tr>
                <tr>
                    <td>Tanggal Berakhir</td>
                    <td>: <?= formatDate($inspeksi['berakhir_sertifikat']) ?></td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>: <span style="color: #28a745; font-weight: bold;">Aktif</span></td>
                </tr>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="signature">
        <p>Jakarta, <?= date('d F Y') ?></p>
        <div class="signature-placeholder">
            <div class="signature-line">
                <?= htmlspecialchars($inspeksi['nama_petugas']) ?><br>
                <em>Petugas Inspeksi K3</em>
            </div>
        </div>
    </div>

    <div class="print-controls">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Cetak Laporan
        </button>
        <button onclick="window.history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
    </div>

    <script>
        // Tambahkan kelas CSS untuk badge berdasarkan status
        document.querySelectorAll('.status-badge').forEach(badge => {
            const className = badge.className;
            
            if (className.includes('baik')) {
                badge.classList.add('badge-baik');
            } else if (className.includes('kurang-baik')) {
                badge.classList.add('badge-kurang-baik');
            } else if (className.includes('tidak-layak')) {
                badge.classList.add('badge-tidak-layak');
            } else if (className.includes('perlu')) {
                badge.classList.add('badge-perlu');
            } else if (className.includes('selesai')) {
                badge.classList.add('badge-selesai');
            }
        });
    </script>
</body>
</html>
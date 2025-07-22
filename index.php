<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../../config/functions.php';

// Dapatkan ID staff yang sedang login
$staff_id = $_SESSION['user']['id_user'];

// Hitung jumlah inspeksi yang ditugaskan kepada staff ini
$inspeksi_count_query = "SELECT 
                        COUNT(*) AS total,
                        SUM(CASE WHEN status_perbaikan = 'perlu' THEN 1 ELSE 0 END) AS perlu,
                        SUM(CASE WHEN status_perbaikan = 'selesai' THEN 1 ELSE 0 END) AS selesai
                        FROM inspeksi 
                        WHERE petugas_id = $staff_id";
$inspeksi_count_result = mysqli_query($conn, $inspeksi_count_query);
$inspeksi_counts = mysqli_fetch_assoc($inspeksi_count_result);

// Hitung jumlah sertifikat yang terkait dengan inspeksi staff ini
$sertifikat_count_query = "SELECT 
                          COUNT(*) AS total,
                          SUM(CASE WHEN s.status = 'active' THEN 1 ELSE 0 END) AS active,
                          SUM(CASE WHEN s.status = 'expired' THEN 1 ELSE 0 END) AS expired
                          FROM sertifikat s
                          JOIN inspeksi i ON s.id_inspeksi = i.id_inspeksi
                          WHERE i.petugas_id = $staff_id";
$sertifikat_count_result = mysqli_query($conn, $sertifikat_count_query);
$sertifikat_counts = mysqli_fetch_assoc($sertifikat_count_result);

// Ambil inspeksi terbaru yang ditugaskan kepada staff ini
$recent_inspeksi_query = "SELECT i.*, k.id_pengajuan, p.nama_perusahaan
                         FROM inspeksi i
                         JOIN kontrak k ON i.id_kontrak = k.id_kontrak
                         JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
                         WHERE i.petugas_id = $staff_id
                         ORDER BY i.tanggal_inspeksi DESC LIMIT 5";
$recent_inspeksi_result = mysqli_query($conn, $recent_inspeksi_query);

// Ambil sertifikat yang akan kedaluwarsa dalam 30 hari yang terkait dengan inspeksi staff ini
$expiring_sertifikat_query = "SELECT s.*, i.objek_diperiksa, p.nama_perusahaan
                             FROM sertifikat s
                             JOIN inspeksi i ON s.id_inspeksi = i.id_inspeksi
                             JOIN kontrak k ON i.id_kontrak = k.id_kontrak
                             JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
                             WHERE i.petugas_id = $staff_id
                             AND s.tanggal_berakhir > CURDATE() 
                             AND s.tanggal_berakhir <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                             ORDER BY s.tanggal_berakhir ASC";
$expiring_sertifikat_result = mysqli_query($conn, $expiring_sertifikat_query);

// Ambil kontrak yang akan berakhir dalam 30 hari yang terkait dengan inspeksi staff ini
$kontrak_expiring_query = "SELECT DISTINCT k.*, p.nama_perusahaan 
                          FROM kontrak k
                          JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
                          JOIN inspeksi i ON i.id_kontrak = k.id_kontrak
                          WHERE i.petugas_id = $staff_id
                          AND k.tanggal_berakhir > CURDATE() 
                          AND k.tanggal_berakhir <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                          ORDER BY k.tanggal_berakhir ASC";
$kontrak_expiring_result = mysqli_query($conn, $kontrak_expiring_query);

// Data untuk grafik inspeksi berdasarkan kondisi objek
$kondisi_query = "SELECT kondisi, COUNT(*) as jumlah 
                 FROM inspeksi 
                 WHERE petugas_id = $staff_id
                 GROUP BY kondisi";
$kondisi_result = mysqli_query($conn, $kondisi_query);
$kondisi_labels = [];
$kondisi_data = [];
$kondisi_colors = [
    'baik' => '#1cc88a',
    'kurang baik' => '#f6c23e',
    'tidak layak' => '#e74a3b'
];

if (mysqli_num_rows($kondisi_result) > 0) {
    while ($row = mysqli_fetch_assoc($kondisi_result)) {
        $kondisi_labels[] = ucfirst($row['kondisi']);
        $kondisi_data[] = $row['jumlah'];
    }
}

// Data untuk grafik trend inspeksi bulanan (6 bulan terakhir)
$months = [];
$monthly_inspeksi = [];

for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $months[] = date('M Y', strtotime("-$i months"));
    
    $start_date = $month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));
    
    $monthly_query = "SELECT COUNT(*) AS total
                     FROM inspeksi
                     WHERE petugas_id = $staff_id
                     AND tanggal_inspeksi BETWEEN '$start_date' AND '$end_date'";
    
    $monthly_result = mysqli_query($conn, $monthly_query);
    $monthly_data = mysqli_fetch_assoc($monthly_result);
    
    $monthly_inspeksi[] = (int)$monthly_data['total'];
}

// Hitung jumlah objek inspeksi berdasarkan jenis
$objek_query = "SELECT o.jenis_objek, COUNT(i.id_inspeksi) as jumlah
               FROM inspeksi i
               JOIN kontrak k ON i.id_kontrak = k.id_kontrak
               JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
               JOIN objek_inspeksi o ON o.id_pengajuan = p.id_pengajuan
               WHERE i.petugas_id = $staff_id
               GROUP BY o.jenis_objek";
$objek_result = mysqli_query($conn, $objek_query);
$objek_labels = [];
$objek_data = [];

if (mysqli_num_rows($objek_result) > 0) {
    while ($row = mysqli_fetch_assoc($objek_result)) {
        $objek_labels[] = $row['jenis_objek'];
        $objek_data[] = $row['jumlah'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard Staff - Monitoring K3</title>
    
    <!-- Custom fonts -->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles -->
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include '../../includes/sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <?php include '../../includes/topbar.php'; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard Staff</h1>
                        <div>
                            <a href="inspeksi.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-clipboard-check fa-sm text-white-50"></i> Kelola Inspeksi
                            </a>
                        </div>
                    </div>

                    <!-- Content Row - Summary Cards -->
                    <div class="row">
                        <!-- Inspeksi Card -->
                        <div class="col-xl-6 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Total Inspeksi</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $inspeksi_counts['total'] ?? 0 ?></div>
                                            <div class="small mt-2">
                                                <span class="text-warning"><?= $inspeksi_counts['perlu'] ?? 0 ?> Perlu Perbaikan</span> | 
                                                <span class="text-success"><?= $inspeksi_counts['selesai'] ?? 0 ?> Selesai</span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sertifikat Card -->
                        <div class="col-xl-6 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Sertifikat</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $sertifikat_counts['total'] ?? 0 ?></div>
                                            <div class="small mt-2">
                                                <span class="text-success"><?= $sertifikat_counts['active'] ?? 0 ?> Aktif</span> | 
                                                <span class="text-danger"><?= $sertifikat_counts['expired'] ?? 0 ?> Kedaluwarsa</span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-certificate fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row - Charts -->
                    <div class="row">
                        <!-- Trend Inspeksi Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Trend Inspeksi (6 Bulan Terakhir)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="inspeksiTrendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Distribusi Kondisi Inspeksi Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Distribusi Kondisi Objek</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="kondisiPieChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <?php if(count($kondisi_labels) > 0): ?>
                                            <?php foreach ($kondisi_labels as $index => $label): ?>
                                                <span class="mr-2">
                                                    <i class="fas fa-circle text-<?= $label == 'Baik' ? 'success' : ($label == 'Kurang baik' ? 'warning' : 'danger') ?>"></i> <?= $label ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span>Belum ada data kondisi</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row - Tables -->
                    <div class="row">
                        <!-- Inspeksi Terbaru -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Inspeksi Terbaru</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Perusahaan</th>
                                                    <th>Tanggal</th>
                                                    <th>Kondisi</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                if (mysqli_num_rows($recent_inspeksi_result) > 0): 
                                                    while($row = mysqli_fetch_assoc($recent_inspeksi_result)): 
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['nama_perusahaan']) ?></td>
                                                    <td><?= formatDate($row['tanggal_inspeksi']) ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= 
                                                            $row['kondisi'] == 'baik' ? 'success' : 
                                                            ($row['kondisi'] == 'tidak layak' ? 'danger' : 'warning') ?>">
                                                            <?= ucfirst($row['kondisi']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= $row['status_perbaikan'] == 'selesai' ? 'success' : 'warning' ?>">
                                                            <?= ucfirst($row['status_perbaikan']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    endwhile; 
                                                else: 
                                                ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Belum ada inspeksi</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-right mt-2">
                                        <a href="inspeksi.php" class="btn btn-sm btn-primary">Lihat Semua <i class="fas fa-arrow-right ml-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sertifikat Akan Kedaluwarsa -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Sertifikat Akan Kedaluwarsa (30 Hari)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Perusahaan</th>
                                                    <th>Objek</th>
                                                    <th>Berakhir</th>
                                                    <th>Sisa Hari</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                if (mysqli_num_rows($expiring_sertifikat_result) > 0): 
                                                    while($sertifikat = mysqli_fetch_assoc($expiring_sertifikat_result)):
                                                        $days_left = floor((strtotime($sertifikat['tanggal_berakhir']) - time()) / (60 * 60 * 24));
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($sertifikat['nama_perusahaan']) ?></td>
                                                    <td><?= htmlspecialchars($sertifikat['objek_diperiksa']) ?></td>
                                                    <td><?= formatDate($sertifikat['tanggal_berakhir']) ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= $days_left > 14 ? 'success' : ($days_left > 7 ? 'warning' : 'danger') ?>">
                                                            <?= $days_left ?> Hari
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    endwhile;
                                                else: 
                                                ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Tidak ada sertifikat yang akan berakhir dalam 30 hari</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row for Contract Expiring -->
                    <div class="row">
                        <!-- Kontrak Akan Berakhir -->
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Kontrak Akan Berakhir (30 Hari)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Perusahaan</th>
                                                    <th>Berakhir</th>
                                                    <th>Sisa Hari</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                if (mysqli_num_rows($kontrak_expiring_result) > 0): 
                                                    while($kontrak = mysqli_fetch_assoc($kontrak_expiring_result)):
                                                        $days_left = floor((strtotime($kontrak['tanggal_berakhir']) - time()) / (60 * 60 * 24));
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($kontrak['nama_perusahaan']) ?></td>
                                                    <td><?= formatDate($kontrak['tanggal_berakhir']) ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= $days_left > 14 ? 'success' : ($days_left > 7 ? 'warning' : 'danger') ?>">
                                                            <?= $days_left ?> Hari
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= 
                                                            $kontrak['status'] == 'aktif' ? 'success' : 
                                                            ($kontrak['status'] == 'kedaluwarsa' ? 'danger' : 'warning') ?>">
                                                            <?= ucfirst($kontrak['status']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    endwhile;
                                                else: 
                                                ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Tidak ada kontrak yang akan berakhir dalam 30 hari</td>
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

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Yakin ingin keluar?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Pilih "Logout" di bawah jika Anda siap untuk mengakhiri sesi Anda saat ini.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <a class="btn btn-primary" href="../../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../js/sb-admin-2.min.js"></script>

    <!-- Charts Script -->
    <script>
        // Inspeksi Trend Chart
        var inspeksiCtx = document.getElementById("inspeksiTrendChart");
        var inspeksiTrendChart = new Chart(inspeksiCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: "Jumlah Inspeksi",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: <?= json_encode($monthly_inspeksi) ?>,
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        time: {
                            unit: 'month'
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 6
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            beginAtZero: true,
                            callback: function(value) { return value; }
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: true
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10
                }
            }
        });

        // Kondisi Pie Chart
        var kondisiCtx = document.getElementById("kondisiPieChart");
        var kondisiData = <?= json_encode($kondisi_data) ?>;
        var kondisiLabels = <?= json_encode($kondisi_labels) ?>;
        
        if(kondisiData.length > 0) {
            var kondisiPieChart = new Chart(kondisiCtx, {
                type: 'doughnut',
                data: {
                    labels: kondisiLabels,
                    datasets: [{
                        data: kondisiData,
                        backgroundColor: [
                            '#1cc88a', // Baik - success
                            '#f6c23e', // Kurang baik - warning
                            '#e74a3b'  // Tidak layak - danger
                        ],
                        hoverBackgroundColor: [
                            '#17a673',
                            '#dda20a',
                            '#be2617'
                        ],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    },
                    legend: {
                        display: false
                    },
                    cutoutPercentage: 80,
                },
            });
        }
    </script>

</body>
</html>

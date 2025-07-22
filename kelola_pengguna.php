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

// Tambah Pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $nama = cleanInput($_POST['nama']);
    $email = cleanInput($_POST['email']);
    $password = password_hash(cleanInput($_POST['password']), PASSWORD_DEFAULT);
    $role = cleanInput($_POST['role']);

    $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssss', $nama, $email, $password, $role);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Pengguna berhasil ditambahkan!";
        header("Location: kelola_pengguna.php");
        exit;
    } else {
        $error = "Gagal menambahkan pengguna: " . mysqli_error($conn);
    }
}

// Edit Pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = cleanInput($_POST['id']);
    $nama = cleanInput($_POST['nama']);
    $email = cleanInput($_POST['email']);
    $role = cleanInput($_POST['role']);

    $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, email=?, role=? WHERE id_user=?");
    mysqli_stmt_bind_param($stmt, 'sssi', $nama, $email, $role, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Data pengguna berhasil diupdate!";
        header("Location: kelola_pengguna.php");
        exit;
    } else {
        $error = "Gagal mengupdate pengguna: " . mysqli_error($conn);
    }
}

// Hapus Pengguna (MODIFIKASI)
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Validasi ID
    if ($id <= 0) {
        $_SESSION['error'] = "ID pengguna tidak valid";
        header("Location: kelola_pengguna.php");
        exit;
    }
    
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Hapus sertifikat terkait
        $query1 = mysqli_query($conn, "DELETE s FROM sertifikat s
                                      INNER JOIN inspeksi i ON s.id_inspeksi = i.id_inspeksi
                                      INNER JOIN kontrak k ON i.id_kontrak = k.id_kontrak
                                      INNER JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
                                      WHERE p.id_user = $id");
        if (!$query1) {
            throw new Exception("Gagal menghapus sertifikat: " . mysqli_error($conn));
        }
        
        // 2. Hapus inspeksi terkait
        $query2 = mysqli_query($conn, "DELETE i FROM inspeksi i
                                      INNER JOIN kontrak k ON i.id_kontrak = k.id_kontrak
                                      INNER JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
                                      WHERE p.id_user = $id");
        if (!$query2) {
            throw new Exception("Gagal menghapus inspeksi: " . mysqli_error($conn));
        }
        
        // 3. Hapus kontrak terkait
        $query3 = mysqli_query($conn, "DELETE k FROM kontrak k
                                      INNER JOIN pengajuan p ON k.id_pengajuan = p.id_pengajuan
                                      WHERE p.id_user = $id");
        if (!$query3) {
            throw new Exception("Gagal menghapus kontrak: " . mysqli_error($conn));
        }
        
        // 4. Hapus objek inspeksi terkait
        $query4 = mysqli_query($conn, "DELETE o FROM objek_inspeksi o
                                      INNER JOIN pengajuan p ON o.id_pengajuan = p.id_pengajuan
                                      WHERE p.id_user = $id");
        if (!$query4) {
            throw new Exception("Gagal menghapus objek inspeksi: " . mysqli_error($conn));
        }
        
        // 5. Hapus pengajuan terkait
        $query5 = mysqli_query($conn, "DELETE FROM pengajuan WHERE id_user = $id");
        if (!$query5) {
            throw new Exception("Gagal menghapus pengajuan: " . mysqli_error($conn));
        }
        
        // 6. Hapus pengguna
        $query6 = mysqli_query($conn, "DELETE FROM users WHERE id_user = $id");
        if (!$query6) {
            throw new Exception("Gagal menghapus pengguna: " . mysqli_error($conn));
        }
        
        // Commit transaksi jika semua berhasil
        mysqli_commit($conn);
        $_SESSION['success'] = "Pengguna dan data terkait berhasil dihapus!";
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: kelola_pengguna.php");
    exit;
}

// Ambil data pengguna
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

// Ambil jumlah pengguna per role
$role_counts = [];
$count_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$count_result = mysqli_query($conn, $count_query);
while($row = mysqli_fetch_assoc($count_result)) {
    $role_counts[$row['role']] = $row['count'];
}

// Set default 0 untuk role yang tidak ada
$roles = ['admin', 'staff', 'pelanggan'];
foreach($roles as $role) {
    if(!isset($role_counts[$role])) {
        $role_counts[$role] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengguna</title>
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
                    <h1 class="h3 mb-0 text-gray-800">Kelola Pengguna</h1>
                    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#tambahModal">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Pengguna
                    </button>
                </div>
                <!-- Card Counter -->
    <div class="row mb-4">
        <!-- Admin Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Administrator</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $role_counts['admin'] ?> Akun
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Staff Inspeksi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $role_counts['staff'] ?> Akun
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pelanggan Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Pelanggan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $role_counts['pelanggan'] ?> Akun
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

                <!-- Notifikasi -->
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']) ?></div>
                <?php endif; ?>
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']) ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <!-- Tabel Pengguna -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Daftar Pengguna Sistem</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($user = mysqli_fetch_assoc($users)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['nama']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= 
                                                $user['role'] == 'admin' ? 'primary' : 
                                                ($user['role'] == 'staff' ? 'info' : 'secondary') ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($user['created_at']) ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm btn-circle edit-btn" 
                                                    data-toggle="modal" 
                                                    data-target="#editModal"
                                                    data-id="<?= $user['id_user'] ?>"
                                                    data-nama="<?= $user['nama'] ?>"
                                                    data-email="<?= $user['email'] ?>"
                                                    data-role="<?= $user['role'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="kelola_pengguna.php?hapus=<?= $user['id_user'] ?>" 
                                               class="btn btn-danger btn-sm btn-circle"
                                               onclick="return confirm('Yakin ingin menghapus pengguna ini? Semua data kontrak dan inspeksi terkait juga akan dihapus.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="pelanggan">Pelanggan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Pengguna</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="edit_role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="pelanggan">Pelanggan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SB Admin 2 Scripts -->
<script src="../../js/sb-admin-2.min.js"></script>

<script>
// Script untuk modal edit
$('.edit-btn').click(function() {
    const id = $(this).data('id');
    const nama = $(this).data('nama');
    const email = $(this).data('email');
    const role = $(this).data('role');
    
    $('#edit_id').val(id);
    $('#edit_nama').val(nama);
    $('#edit_email').val(email);
    $('#edit_role').val(role);
});
</script>

</body>
</html>
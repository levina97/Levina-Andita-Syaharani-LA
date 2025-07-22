<?php
session_start();
require '../../config/config.php'; // Sesuaikan path ke file koneksi database

if (!isset($_SESSION['user'])) {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user']['id_user'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Handle avatar upload
    if ($_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
            $target_path = '../../img/avatars/' . $filename;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
                // Update avatar in database
                $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id_user = ?");
                $stmt->bind_param('si', $filename, $user_id);
                $stmt->execute();
                $_SESSION['user']['avatar'] = $filename;
            } else {
                $error = 'Gagal mengupload gambar.';
            }
        } else {
            $error = 'Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.';
        }
    }

    // Update user data
    if (empty($error)) {
        try {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, password = ? WHERE id_user = ?");
                $stmt->bind_param('sssi', $nama, $email, $hashed_password, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ? WHERE id_user = ?");
                $stmt->bind_param('ssi', $nama, $email, $user_id);
            }

            if ($stmt->execute()) {
                $_SESSION['user']['nama'] = $nama;
                $_SESSION['user']['email'] = $email;
                $success = 'Profil berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui profil.';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Get current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Profil Pengguna - SI-MONIK3</title>
    
    <!-- SB Admin 2 CSS -->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 3px solid #4e73df;
            cursor: pointer;
            transition: all 0.3s;
        }
        .profile-img:hover {
            transform: scale(1.05);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        .btn-upload {
            border: 2px solid #4e73df;
            color: #4e73df;
            background-color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
        }
        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
        }
    </style>
</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include '../../includes/topbar.php'; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Profil Pengguna</h1>
                    </div>

                    <!-- Profile Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Edit Profil</h6>
                        </div>
                        <div class="card-body">
                            <?php if($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $error ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <?php elseif($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= $success ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <div class="mb-3">
                                            <img src="../../img/avatars/<?= $user['avatar'] ?>" 
                                                 class="profile-img rounded-circle"
                                                 id="avatarPreview"
                                                 alt="Avatar Pengguna">
                                        </div>
                                        <div class="upload-btn-wrapper">
                                            <button class="btn-upload"><i class="fas fa-upload"></i> Ganti Foto</button>
                                            <input type="file" name="avatar" id="avatarInput" accept="image/*">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Nama Lengkap</label>
                                            <input type="text" name="nama" 
                                                   class="form-control"
                                                   value="<?= htmlspecialchars($user['nama']) ?>"
                                                   required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Alamat Email</label>
                                            <input type="email" name="email" 
                                                   class="form-control"
                                                   value="<?= htmlspecialchars($user['email']) ?>"
                                                   required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Password Baru</label>
                                            <input type="password" name="password" 
                                                   class="form-control"
                                                   placeholder="Kosongkan jika tidak ingin mengubah">
                                            <small class="form-text text-muted">
                                                Minimal 8 karakter
                                            </small>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary btn-icon-split">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-save"></i>
                                            </span>
                                            <span class="text">Simpan Perubahan</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- SB Admin 2 Scripts -->
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../../js/sb-admin-2.min.js"></script>

    <!-- Custom Script -->
    <script>
    // Preview avatar sebelum upload
    document.getElementById('avatarInput').addEventListener('change', function(e) {
        const reader = new FileReader();
        reader.onload = function() {
            document.getElementById('avatarPreview').src = reader.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    });
    </script>

</body>
</html>

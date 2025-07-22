<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['user']['role'] ?? 'guest';

// Define role-specific colors
$roleColors = [
    'admin' => 'primary',
    'staff' => 'success',
    'pelanggan' => 'info',
    'guest' => 'secondary'
];

$roleIcons = [
    'admin' => 'fa-user-shield',
    'staff' => 'fa-briefcase',
    'pelanggan' => 'fa-user',
    'guest' => 'fa-user-circle'
];

$currentColor = $roleColors[$role] ?? 'primary';
$roleIcon = $roleIcons[$role] ?? 'fa-user-circle';

// Get user avatar - sesuaikan dengan path yang digunakan di profil.php
$userAvatar = $_SESSION['user']['avatar'] ?? 'default.svg';
$avatarPath = '../../img/avatars/' . $userAvatar;

// Check if avatar file exists, if not use default
if (!file_exists($avatarPath) || $userAvatar == 'default.svg') {
    $avatarPath = '../../img/avatars/default.svg';
}
?>

<!-- Sidebar -->
<ul class="navbar-nav sidebar sidebar-dark accordion shadow-sm" 
    id="accordionSidebar" style="background: linear-gradient(135deg, #<?= ($currentColor == 'primary') ? '4e73df 0%, #224abe' : (($currentColor == 'success') ? '1cc88a 0%, #13855c' : (($currentColor == 'info') ? '36b9cc 0%, #258391' : '858796 0%, #60616f')) ?> 100%);">

    <!-- Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center py-4 mb-2" href="#">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas <?= $roleIcon ?> fa-2x"></i>
        </div>
        <div class="sidebar-brand-text mx-3">
            SI-MONIK3
            <div class="text-xs text-white-50"><?= ucfirst($role) ?> Panel</div>
        </div>
    </a>

    <hr class="sidebar-divider my-0">
    
    <!-- User Profile Summary with Avatar -->
    <div class="user-profile text-center my-3 px-3">
        <div class="user-avatar mb-2">
            <img src="<?= $avatarPath ?>" alt="User Avatar" class="rounded-circle border border-white" 
                 style="width: 60px; height: 60px; object-fit: cover;">
        </div>
        <div class="user-info">
            <h6 class="text-white mb-0"><?= $_SESSION['user']['nama'] ?? 'Guest User' ?></h6>
            <span class="badge bg-white text-<?= $currentColor ?> font-weight-bold text-uppercase px-2 py-1 mt-1">
                <?= ucfirst($role) ?>
            </span>
        </div>
    </div>

    <hr class="sidebar-divider">

    <!-- Menu Heading -->
    <div class="sidebar-heading text-uppercase text-white-50 px-3 py-2 mt-1 mb-2">
        Main Navigation
    </div>

    <!-- Menu berdasarkan role -->
    <?php if ($role == 'admin'): ?>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../admin/index.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-fw fa-tachometer-alt text-white"></i>
                </div>
                <span>Dashboard Admin</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../admin/kelola_pengguna.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-fw fa-users-cog text-white"></i>
                </div>
                <span>Kelola Pengguna</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../admin/kelola_pengajuan.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-fw fa-file-signature text-white"></i>
                </div>
                <span>Kelola Pengajuan</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../admin/daftar_inspeksi.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-fw fa-clipboard-list text-white"></i>
                </div>
                <span>Daftar Inspeksi</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../admin/upload_sertifikat.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-upload text-white"></i>
                </div>
                <span>Upload Sertifikasi</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../admin/kelola_sertifikat.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-file-alt text-white"></i>
                </div>
                <span>Daftar Sertifikasi</span>
            </a>
        </li>
    <?php elseif ($role == 'staff'): ?>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../staff/index.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-fw fa-clipboard-list text-white"></i>
                </div>
                <span>Dashboard Staff</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../staff/tambah_inspeksi.php">
                <div class="icon-circle bg-white-20 mr-3">
                <i class="fas fa-fw fa-clipboard-check text-white"></i>
                </div>
                <span>Inspeksi Lapangan</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../staff/inspeksi.php">
                <div class="icon-circle bg-white-20 mr-3">
                <i class="fas fa-clipboard-list text-white"></i>
                </div>
                <span>Riwayat Inspeksi</span>
            </a>
        </li>
    <?php elseif ($role == 'pelanggan'): ?>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../pelanggan/index.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-fw fa-user text-white"></i>
                </div>
                <span>Dashboard Pelanggan</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../pelanggan/ajukan_inspeksi.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-fw fa-file-alt text-white"></i>
                </div>
                <span>Pengajuan Sertifikasi</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../pelanggan/kontrak.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-file-contract text-white"></i>
                </div>
                <span>Daftar Kontrak</span>
            </a>
        </li>
         <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../pelanggan/inspeksi.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-file-contract text-white"></i>
                </div>
                <span>Daftar Inspeksi</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-3" href="../pelanggan/sertifikat.php">
                <div class="icon-circle bg-white-20 mr-3">
                    <i class="fas fa-fw fa-award text-white"></i>
                </div>
                <span>Sertifikasi</span>
            </a>
        </li>
    <?php endif; ?>

    <hr class="sidebar-divider">
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center py-3" href="../<?= $role ?>/profil.php">
            <div class="icon-circle bg-white-20 mr-3">
                <i class="fas fa-fw fa-id-card text-white"></i>
            </div>
            <span>Profil Pengguna</span>
        </a>
    </li>
    <hr class="sidebar-divider">
    
    <!-- Additional Actions Section -->
    <div class="sidebar-heading text-uppercase text-white-50 px-3 py-2 mt-1 mb-2">
        Quick Actions
    </div>

    <li class="nav-item">
        <a class="nav-link d-flex align-items-center py-3" href="../../logout.php">
            <div class="icon-circle bg-white-20 mr-3">
                <i class="fas fa-fw fa-sign-out-alt text-white"></i>
            </div>
            <span>Logout</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">
</ul>

<!-- Add required CSS for the enhanced sidebar -->
<style>
    .sidebar {
        transition: all 0.3s ease-in-out;
        min-width: 14rem;
        width: 14rem;
    }
    
    .sidebar.toggled {
        min-width: 0 !important;
        width: 6.5rem !important;
        overflow: hidden;
    }
    
    .sidebar.toggled .sidebar-brand-text {
        display: none;
    }
    
    .sidebar.toggled .nav-item .nav-link span {
        display: none;
    }
    
    .sidebar.toggled .sidebar-heading {
        text-align: center;
        padding: 0;
        font-size: 0.65rem;
    }
    
    .sidebar.toggled .user-profile .user-info {
        display: none;
    }
    
    .sidebar.toggled .user-profile .user-avatar img {
        width: 40px;
        height: 40px;
    }
    
    .sidebar.toggled .icon-circle {
        margin-right: 0 !important;
        margin-left: 0.5rem;
    }
    
    .sidebar.toggled hr.sidebar-divider {
        margin: 0.5rem 0;
    }
    
    .sidebar.toggled .nav-item .nav-link {
        text-align: center;
        padding: 0.75rem 1rem;
        width: 6.5rem;
    }
    
    .bg-white-20 {
        background-color: rgba(255, 255, 255, 0.2);
    }
    
    .icon-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .nav-link {
        border-radius: 0.5rem;
        margin: 0 0.75rem;
        transition: all 0.2s;
    }
    
    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.15);
    }
    
    .nav-link:hover .icon-circle {
        background-color: rgba(255, 255, 255, 0.3);
    }
    
    .sidebar-divider {
        border-top: 1px solid rgba(255, 255, 255, 0.15);
    }
    
    .user-profile {
        transition: all 0.3s;
    }
    
    .user-avatar img {
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    
    .user-avatar img:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    
    #sidebarToggle {
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    #sidebarToggle i {
        transition: all 0.3s;
    }
    
    .sidebar.toggled #sidebarToggle i {
        transform: rotate(180deg);
    }
    
    /* Active state styling */
    .nav-item.active .nav-link {
        background-color: rgba(255, 255, 255, 0.2);
        font-weight: 600;
    }
    
    .nav-item.active .icon-circle {
        background-color: rgba(255, 255, 255, 0.3);
    }
</style>

<!-- Fix for content wrapper -->
<style>
    .content-wrapper {
        margin-left: 14rem;
        transition: all 0.3s ease-in-out;
    }
    
    body.sidebar-toggled .content-wrapper {
        margin-left: 6.5rem;
    }
    
    @media (max-width: 768px) {
        .sidebar {
            width: 0;
            overflow: hidden;
        }
        
        .sidebar.toggled {
            width: 14rem !important;
            overflow: visible;
        }
        
        .content-wrapper {
            margin-left: 0;
        }
        
        body.sidebar-toggled .content-wrapper {
            margin-left: 0;
        }
    }
</style>

<!-- Add necessary JavaScript for sidebar toggle animation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fix for sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('body').classList.toggle('sidebar-toggled');
            document.querySelector('.sidebar').classList.toggle('toggled');
            
            // Check if min-width style exists in CSS
            if (document.querySelector('.sidebar.toggled')) {
                // Manually toggle width for sidebar when toggled
                const sidebar = document.querySelector('.sidebar');
                if (sidebar.style.width === '6.5rem' || sidebar.style.width === '') {
                    sidebar.style.width = '0';
                    sidebar.style.overflow = 'hidden';
                } else {
                    sidebar.style.width = '6.5rem';
                    sidebar.style.overflow = '';
                }
                
                // Toggle content margin
                const content = document.querySelector('.content');
                if (content) {
                    if (content.style.marginLeft === '0px' || content.style.marginLeft === '') {
                        content.style.marginLeft = '0px';
                    } else {
                        content.style.marginLeft = '6.5rem';
                    }
                }
            }
        });
    }
    
    // Highlight active menu item based on current URL
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if (currentPath.includes(link.getAttribute('href'))) {
            link.parentElement.classList.add('active');
        }
    });
});
</script>

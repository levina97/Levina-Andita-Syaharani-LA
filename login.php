<?php include 'includes/header.php'; ?>

<div class="login-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg animate__animated animate__fadeIn">
                    <div class="card-header bg-transparent border-0 pt-4">
                        <div class="text-center">
                            <img src="img/surveyor_id.png" alt="PT Surveyor Indonesia Logo" class="img-fluid mb-3" style="max-height: 60px;">
                            <h2 class="fw-bold text-primary mb-1">SI-MONIK3</h2>
                            <p class="text-muted">Sistem Monitoring Inspeksi, Sertifikasi, dan Resertifikasi K3</p>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 p-lg-5">
                        <div class="text-center mb-4">
                            <div class="login-icon-container mb-3">
                                <i class="fas fa-user-lock login-icon"></i>
                            </div>
                            <h3 class="fw-bold">Masuk ke Akun Anda</h3>
                            <p class="text-muted">Masukkan email dan password untuk melanjutkan</p>
                        </div>
                        
                        <form action="login_proses.php" method="post" class="login-form">
                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-primary"></i>
                                    </span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="email" name="email" class="form-control border-start-0" id="email" placeholder="Email" required>
                                        <label for="email">Email</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-lock text-primary"></i>
                                    </span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="password" name="password" class="form-control border-start-0" id="password" placeholder="Password" required>
                                        <label for="password">Password</label>
                                    </div>
                                    <button type="button" class="btn btn-light border border-start-0" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">Ingat saya</label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk
                            </button>
                            
                            <div class="text-center mt-4">
                                <p class="mb-0">Belum punya akun? <a href="register.php" class="text-primary fw-bold">Daftar di sini</a></p>
                            </div>
                            <div class="text-center mt-3">
                                <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="small text-muted">
                        <i class="fas fa-shield-alt me-2"></i>
                        Sistem ini dilindungi dengan enkripsi SSL 256-bit
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .login-section {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), 
                    url('img/bg-pattern.png');
        background-size: cover;
    }
    
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    
    .card-header {
        position: relative;
    }
    
    .login-icon-container {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #0a2463 0%, #247ba0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin: 0 auto;
    }
    
    .login-icon {
        font-size: 28px;
        color: white;
    }
    
    .input-group-text {
        border-right: none;
        background-color: transparent;
    }
    
    .form-control {
        border-left: none;
        padding-left: 0;
    }
    
    .form-control:focus {
        box-shadow: none;
        border-color: #ced4da;
    }
    
    .form-floating > label {
        padding-left: 0;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #0a2463 0%, #247ba0 100%);
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    #togglePassword {
        cursor: pointer;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle eye icon
                const eyeIcon = this.querySelector('i');
                eyeIcon.classList.toggle('fa-eye');
                eyeIcon.classList.toggle('fa-eye-slash');
            });
        }
        
        // Form submission animation
        const loginForm = document.querySelector('.login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const submitButton = this.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...';
                    submitButton.disabled = true;
                }
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
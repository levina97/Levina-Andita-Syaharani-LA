<!DOCTYPE html>
<html>
<head>
    <title>Aplikasi Monitoring K3</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #0a2463;
            --secondary-color: #247ba0;
            --accent-color: #1e88e5;
            --text-light: #ffffff;
            --footer-bg-dark: #0a1933;
            --footer-bg-light: #15305c;
        }
        
        /* Footer Styles */
        .footer-custom {
            background: linear-gradient(135deg, var(--footer-bg-dark) 0%, var(--footer-bg-light) 100%);
            color: var(--text-light);
            padding: 2rem 0 1.5rem;
            position: relative;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .footer-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color) 0%, var(--accent-color) 100%);
        }
        
        .footer-logo {
            height: 50px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
        
        .footer-title {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .footer-links a:hover {
            color: white;
            transform: translateX(3px);
            display: inline-block;
        }
        
        .footer-links i {
            margin-right: 8px;
            font-size: 0.85rem;
        }
        
        .social-links {
            margin-top: 20px;
        }
        
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            margin: 0 5px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            margin-top: 30px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .footer-bottom a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-bottom a:hover {
            color: white;
        }
        
        @media (max-width: 767.98px) {
            .footer-section {
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Your main content container -->
    <div class="container mt-4">
        <!-- Main content goes here -->
    </div>
    
    <!-- Enhanced Footer -->
    <footer class="footer-custom mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 footer-section animate__animated animate__fadeIn">
                    <img src="img/surveyor_id.png" alt="PT Surveyor Indonesia Logo" class="footer-logo">
                    <h5 class="mt-3 mb-2">PT SURVEYOR INDONESIA</h5>
                    <p class="mb-4">Menyediakan layanan inspeksi, sertifikasi, dan resertifikasi K3 berkualitas tinggi untuk memastikan keselamatan dan kepatuhan standar industri.</p>
                    <div class="social-links">
                        <a href="#" class="animate__animated animate__fadeIn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="animate__animated animate__fadeIn" style="animation-delay: 0.1s"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="animate__animated animate__fadeIn" style="animation-delay: 0.2s"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="animate__animated animate__fadeIn" style="animation-delay: 0.3s"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 footer-section animate__animated animate__fadeIn" style="animation-delay: 0.1s">
                    <h5 class="footer-title">Layanan</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-check-circle"></i> Inspeksi K3</a></li>
                        <li><a href="#"><i class="fas fa-check-circle"></i> Sertifikasi</a></li>
                        <li><a href="#"><i class="fas fa-check-circle"></i> Resertifikasi</a></li>
                        <li><a href="#"><i class="fas fa-check-circle"></i> Pelatihan K3</a></li>
                        <li><a href="#"><i class="fas fa-check-circle"></i> Audit K3</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 footer-section animate__animated animate__fadeIn" style="animation-delay: 0.2s">
                    <h5 class="footer-title">Tautan</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-angle-right"></i> Beranda</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Tentang Kami</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Layanan</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> FAQ</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Kontak</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 col-md-6 footer-section animate__animated animate__fadeIn" style="animation-delay: 0.3s">
                    <h5 class="footer-title">Kontak Kami</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Jl. Jenderal Gatot Subroto No.54, Jakarta Selatan</p>
                    <p><i class="fas fa-phone-alt me-2"></i> +62 21 526 5526</p>
                    <p><i class="fas fa-envelope me-2"></i> info@surveyor.co.id</p>
                    <p><i class="fas fa-clock me-2"></i> Senin - Jumat: 08:00 - 17:00</p>
                </div>
            </div>
            
            <div class="footer-bottom text-center animate__animated animate__fadeIn" style="animation-delay: 0.4s">
                <p>&copy; <?= date('Y') ?> PT Surveyor Indonesia | Aplikasi Monitoring Inspeksi, Sertifikasi, dan Resertifikasi K3 | <a href="#">Kebijakan Privasi</a> | <a href="#">Syarat & Ketentuan</a></p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation classes when footer is in viewport
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const animatedElements = entry.target.querySelectorAll('.animate__animated');
                        animatedElements.forEach(el => {
                            el.style.opacity = '1';
                            el.style.visibility = 'visible';
                        });
                    }
                });
            }, { threshold: 0.1 });
            
            observer.observe(document.querySelector('.footer-custom'));
            
            // Set initial state for animated elements
            document.querySelectorAll('.footer-custom .animate__animated').forEach(el => {
                el.style.opacity = '0';
                el.style.visibility = 'hidden';
                el.style.transition = 'opacity 0.5s ease, visibility 0.5s ease';
            });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <title>SI-MONIK3-Beranda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #0a2463;
            --secondary-color: #247ba0;
            --accent-color: #1e88e5;
            --text-light: #ffffff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 12px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-size: 1.2rem;
            font-weight: 500;
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover {
            transform: translateY(-2px);
        }
        
        .company-logo {
            height: 42px;
            margin-right: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .company-logo:hover {
            transform: scale(1.05);
        }
        
        .navbar-brand-text {
            display: flex;
            flex-direction: column;
        }
        
        .company-name {
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: 0.5px;
            color: var(--text-light);
        }
        
        .app-name {
            font-size: 0.85rem;
            opacity: 0.9;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .nav-item {
            margin: 0 5px;
            position: relative;
        }
        
        .nav-link {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85) !important;
            padding: 10px 16px !important;
            border-radius: 4px;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
        }
        
        .nav-link:hover {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.15);
            font-weight: 600;
        }
        
        .nav-link i {
            margin-right: 6px;
        }
        
        /* Indicator line for active nav item */
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background-color: white;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after, 
        .nav-link.active::after {
            width: 80%;
        }
        
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background-color: rgba(10, 36, 99, 0.97);
                padding: 15px;
                border-radius: 8px;
                margin-top: 15px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }
            
            .nav-link {
                padding: 12px 20px !important;
                margin: 5px 0;
            }
            
            .nav-link::after {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand animate__animated animate__fadeIn" href="#">
                <img src="img/surveyor_id.png" alt="PT Surveyor Indonesia Logo" class="company-logo">
                <div class="navbar-brand-text">
                    <span class="company-name">PT SURVEYOR INDONESIA</span>
                    <span class="app-name">Aplikasi Monitoring Inspeksi, Sertifikasi, dan Resertifikasi K3</span>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto animate__animated animate__fadeInRight">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home"></i> Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#fitur"><i class="fas fa-th-large"></i> Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#tentang"><i class="fas fa-info-circle"></i> Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#kontak"><i class="fas fa-phone-alt"></i> Kontak</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Main content goes here -->
    </div>
    
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation classes to navbar items with slight delay for each item
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach((item, index) => {
                setTimeout(() => {
                    item.classList.add('animate__animated', 'animate__fadeInDown');
                }, 100 * (index + 1));
            });
            
            // Add hover animation for the logo
            const logo = document.querySelector('.company-logo');
            logo.addEventListener('mouseover', function() {
                this.classList.add('animate__animated', 'animate__pulse');
                setTimeout(() => {
                    this.classList.remove('animate__animated', 'animate__pulse');
                }, 1000);
            });
        });
    </script>
</body>
</html>
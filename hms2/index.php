<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect based on role
if (!empty($_SESSION['role'])) {
    $dashboard = $_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php';
    header("Location: $dashboard");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #1e293b;
            --accent-color: #f43f5e;
            --background-color: #f8fafc;
            --text-color: #1e293b;
            --gradient: linear-gradient(135deg, #6366f1, #4f46e5);
            --gradient-2: linear-gradient(135deg, #f43f5e, #e11d48);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .navbar {
            background: rgba(99, 102, 241, 0.95);
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(99, 102, 241, 0.98);
            padding: 0.8rem 5%;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            text-decoration: none;
        }

        .logo img {
            height: 45px;
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
            transition: transform 0.3s ease;
        }

        .logo:hover img {
            transform: scale(1.05);
        }

        .menu {
            display: flex;
            gap: 1rem;
        }

        .menu a {
            color: white;
            text-decoration: none;
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .menu a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .menu a i {
            font-size: 1.1rem;
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 6rem 2rem 2rem;
            background: var(--gradient);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1" fill="rgba(255,255,255,0.05)"/></svg>');
            opacity: 0.1;
        }

        .hero-content {
            max-width: 800px;
            color: white;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            animation: fadeInDown 1s ease;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: rgba(255,255,255,0.9);
            animation: fadeInUp 1s ease;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            animation: fadeInUp 1s ease 0.3s backwards;
        }

        .button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2.5rem;
            background: white;
            color: var(--primary-color);
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            cursor: pointer;
        }

        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .button.primary {
            background: var(--accent-color);
            color: white;
        }

        .button.primary:hover {
            background: #e11d48;
        }

        .features {
            padding: 6rem 2rem;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient);
            border-radius: 2px;
        }

        .section-title p {
            color: #64748b;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            text-align: center;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card i {
            font-size: 3rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .feature-card:hover i {
            transform: scale(1.1);
        }

        .feature-card h3 {
            margin-bottom: 1rem;
            color: var(--secondary-color);
            font-size: 1.5rem;
        }

        .feature-card p {
            color: #64748b;
            font-size: 1rem;
            line-height: 1.6;
        }

        .portals {
            padding: 6rem 2rem;
            background: var(--background-color);
        }

        .portals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .portal-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            text-align: center;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .portal-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 1;
        }

        .portal-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }

        .portal-card:hover::after {
            opacity: 0.05;
        }

        .portal-card > * {
            position: relative;
            z-index: 2;
        }

        .portal-card i {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.3s ease;
        }

        .portal-card:hover i {
            transform: scale(1.1);
        }

        .portal-card h3 {
            margin-bottom: 1rem;
            color: var(--secondary-color);
            font-size: 1.8rem;
        }

        .portal-card p {
            margin-bottom: 2rem;
            color: #64748b;
            font-size: 1.1rem;
        }

        .contact {
            padding: 6rem 2rem;
            background: white;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .contact-info {
            text-align: center;
        }

        .contact-info h3 {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
        }

        .contact-info p {
            color: #64748b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .social-links a {
            color: var(--primary-color);
            font-size: 1.5rem;
            transition: all 0.3s ease;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .social-links a:hover {
            transform: translateY(-5px);
            background: var(--gradient);
            color: white;
        }

        footer {
            background: var(--secondary-color);
            color: white;
            text-align: center;
            padding: 3rem 2rem;
            position: relative;
            overflow: hidden;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient);
        }

        footer p {
            margin: 0.8rem 0;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 1rem;
            }

            .menu {
                margin-top: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .button {
                width: 100%;
                justify-content: center;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .portal-card {
                padding: 2rem;
            }
        }

        /* Animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .feature-card, .portal-card {
            animation: float 6s ease-in-out infinite;
        }

        .feature-card:nth-child(2), .portal-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .feature-card:nth-child(3), .portal-card:nth-child(3) {
            animation-delay: 0.4s;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="#" class="logo">
            <img src="Logo.jpeg" alt="Hostel Management Logo">
            <span>Hostel Management</span>
        </a>
        <div class="menu">
            <a href="auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="auth/register.php"><i class="fas fa-user-plus"></i> Register</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>🏠 Welcome to the Future of Hostel Management</h1>
            <p>Experience seamless hostel management with our cutting-edge platform. Smart room allocation, instant payments, and real-time notifications - all in one place.</p>
            <div class="cta-buttons">
                <a href="auth/register.php" class="button primary">
                    <i class="fas fa-rocket"></i> Get Started
                </a>
                <a href="#features" class="button">
                    <i class="fas fa-info-circle"></i> Learn More
                </a>
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="section-title">
            <h2>✨ Smart Features</h2>
            <p>Discover how our platform revolutionizes hostel management with cutting-edge features</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-bed"></i>
                <h3>Smart Room Allocation</h3>
                <p>Real-time room availability tracking and instant booking system for hassle-free accommodation management.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Quick Complaint Resolution</h3>
                <p>Efficient complaint tracking and resolution system with real-time updates and status notifications.</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-money-bill-wave"></i>
                <h3>Easy Payment System</h3>
                <p>Secure and convenient payment processing with multiple payment options and automated receipts.</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-bell"></i>
                <h3>Instant Notifications</h3>
                <p>Stay updated with real-time notifications about announcements, payments, and important updates.</p>
            </div>
        </div>
    </section>

    <section class="portals">
        <div class="section-title">
            <h2>🎯 Access Portals</h2>
            <p>Choose your portal to access the system's features</p>
        </div>
        <div class="portals-grid">
            <div class="portal-card">
                <i class="fas fa-user-shield"></i>
                <h3>Admin Portal</h3>
                <p>Comprehensive dashboard for managing students, rooms, fees, and complaints efficiently.</p>
                <a href="auth/login.php" class="button primary">
                    <i class="fas fa-sign-in-alt"></i> Admin Login
                </a>
            </div>
            <div class="portal-card">
                <i class="fas fa-user-graduate"></i>
                <h3>Student Portal</h3>
                <p>User-friendly interface for room booking, fee payment, and complaint submission.</p>
                <div class="cta-buttons">
                    <a href="auth/register.php" class="button">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                    <a href="auth/login.php" class="button primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="contact">
        <div class="section-title">
            <h2>📞 Contact Us</h2>
            <p>Get in touch with us for any queries or support</p>
        </div>
        <div class="contact-grid">
            <div class="contact-info">
                <h3>Get in Touch</h3>
                <p><i class="fas fa-phone"></i> 9881604712 (Vilas Bhambere)</p>
                <p><i class="fas fa-map-marker-alt"></i> Kalyanpeth Junnar</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Hostel Management System | All Rights Reserved</p>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
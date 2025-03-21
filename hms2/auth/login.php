<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Query to check if the email exists
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Store user information in session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role']
            ];

            // Redirect based on the role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($user['role'] === 'student') {
                header("Location: ../student/dashboard.php");
            } else {
                $error = "Invalid user role.";
            }
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with this email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hostel Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #1e293b;
            --accent-color: #f43f5e;
            --background-color: #f8fafc;
            --text-color: #1e293b;
            --gradient: linear-gradient(135deg, #6366f1, #4f46e5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--background-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }

        .login-container h1 {
            color: var(--secondary-color);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            position: relative;
            padding-bottom: 1rem;
        }

        .login-container h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--gradient);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        button {
            width: 100%;
            padding: 1rem;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            color: var(--accent-color);
        }

        .back-to-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: var(--secondary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-to-home:hover {
            color: var(--primary-color);
            transform: translateX(-5px);
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem;
            }

            .back-to-home {
                top: 1rem;
                left: 1rem;
            }
        }

        /* Enhanced Button Styles */
        .button {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transition: all 0.5s ease;
        }

        .button:hover::before {
            left: 100%;
        }

        /* Enhanced Feature Card Styles */
        .feature-card {
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .feature-card:hover {
            transform: translateY(-10px) rotateX(5deg);
        }

        .feature-card i {
            transform-style: preserve-3d;
            transition: all 0.5s ease;
        }

        .feature-card:hover i {
            transform: translateZ(20px) scale(1.2);
        }

        /* Enhanced Portal Card Styles */
        .portal-card {
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            box-shadow: 
                20px 20px 60px #d9d9d9,
                -20px -20px 60px #ffffff;
        }

        .portal-card:hover {
            background: linear-gradient(145deg, #f5f5f5, #ffffff);
            box-shadow: 
                20px 20px 60px #d9d9d9,
                -20px -20px 60px #ffffff;
        }

        /* Enhanced Social Links */
        .social-links a {
            position: relative;
            overflow: hidden;
        }

        .social-links a::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            transform: scale(0);
            transition: transform 0.3s ease;
            border-radius: 50%;
            z-index: -1;
        }

        .social-links a:hover::before {
            transform: scale(1);
        }

        .social-links a:hover i {
            transform: rotate(360deg);
        }

        /* Enhanced Section Titles */
        .section-title h2 {
            position: relative;
            display: inline-block;
        }

        .section-title h2::before,
        .section-title h2::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: var(--gradient);
            top: 50%;
            transform: translateY(-50%);
        }

        .section-title h2::before {
            left: -60px;
        }

        .section-title h2::after {
            right: -60px;
        }

        /* Enhanced Hero Section */
        .hero::after {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Enhanced Navbar */
        .navbar {
            background: rgba(99, 102, 241, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .menu a {
            position: relative;
            overflow: hidden;
        }

        .menu a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: white;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .menu a:hover::after {
            width: 80%;
        }

        /* Enhanced Contact Section */
        .contact-info p {
            position: relative;
            padding: 10px 20px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .contact-info p:hover {
            background: rgba(99, 102, 241, 0.2);
            transform: translateX(10px);
        }

        /* Enhanced Footer */
        footer {
            position: relative;
            overflow: hidden;
        }

        footer::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 45%, rgba(255,255,255,0.1) 50%, transparent 55%);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Responsive Enhancements */
        @media (max-width: 768px) {
            .section-title h2::before,
            .section-title h2::after {
                width: 30px;
            }

            .section-title h2::before {
                left: -40px;
            }

            .section-title h2::after {
                right: -40px;
            }

            .contact-info p {
                padding: 8px 16px;
            }
        }

        /* Loading Animation */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loading.hide {
            opacity: 0;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            width: 50px;
            height: 50px;
            border: 5px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>

    <div class="login-container">
        <h1>Welcome Back</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required placeholder="Enter your email">
            </div>
            
            <div class="form-group">
            <label for="password">Password</label>
                <input type="password" name="password" id="password" required placeholder="Enter your password">
            </div>
            
            <button type="submit">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
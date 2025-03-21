<?php
session_start();
include '../includes/header.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a8a4e6;
            --accent-color: #00b894;
            --background-color: #f0f2f5;
            --card-background: #ffffff;
            --text-primary: #2d3436;
            --text-secondary: #636e72;
            --gradient-1: linear-gradient(135deg, #6c5ce7, #a8a4e6);
            --gradient-2: linear-gradient(135deg, #00b894, #00cec9);
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            color: var(--text-primary);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .welcome-section {
            background: var(--gradient-1);
            color: white;
            padding: 3rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.1;
        }

        .welcome-section h1 {
            margin: 0;
            font-size: 2.8rem;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .welcome-section p {
            margin: 1rem 0 0;
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .admin-nav {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .nav-card {
            background: var(--card-background);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .nav-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-1);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .nav-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .nav-card:hover::before {
            opacity: 1;
        }

        .nav-card i {
            font-size: 2.8rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .nav-card:hover i {
            transform: scale(1.1);
        }

        .nav-card a {
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.2rem;
            display: block;
            margin-bottom: 0.5rem;
        }

        .nav-card p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .logout-card {
            background: var(--gradient-2);
            color: white;
        }

        .logout-card i {
            background: none;
            -webkit-text-fill-color: white;
        }

        .logout-card a {
            color: white;
        }

        .logout-card p {
            color: rgba(255, 255, 255, 0.9);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            .welcome-section {
                padding: 2rem;
            }

            .welcome-section h1 {
                font-size: 2.2rem;
            }

            .nav-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
            <p>Manage your hostel efficiently with our comprehensive dashboard</p>
        </div>
        
        <nav class="admin-nav">
            <div class="nav-card">
                <i class="fas fa-users"></i>
                <a href="students.php">Manage Students</a>
                <p>Add, edit, and view student information with ease</p>
            </div>
            
            <div class="nav-card">
                <i class="fas fa-door-open"></i>
                <a href="rooms.php">Manage Rooms</a>
                <p>Handle room assignments and track capacity</p>
            </div>
            
            <div class="nav-card">
                <i class="fas fa-money-bill-wave"></i>
                <a href="fees.php">Manage Fees</a>
                <p>Track payments and monitor fee status</p>
            </div>
            
            <div class="nav-card">
                <i class="fas fa-comments"></i>
                <a href="complaints.php">Manage Complaints</a>
                <p>Address student concerns and feedback</p>
            </div>
            
            <div class="nav-card">
                <i class="fas fa-calendar-alt"></i>
                <a href="manage_leaves.php">Manage Leaves</a>
                <p>Process and track leave requests</p>
            </div>
            
            <div class="nav-card logout-card">
                <i class="fas fa-sign-out-alt"></i>
                <a href="../auth/logout.php">Logout</a>
                <p>Exit the admin dashboard</p>
            </div>
        </nav>
    </div>
</body>
</html>

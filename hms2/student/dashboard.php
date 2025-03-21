<?php
session_start();

// Correct include paths based on your project structure
include '../includes/header.php'; 
include '../includes/db_connect.php'; 

// Check if the session is set
if (!isset($_SESSION['user'])) {
    die("Session not set. Please log in.");
}

// Ensure only students can access
if ($_SESSION['user']['role'] !== 'student') {
    die("Access denied. Only students can access this page.");
}

// Fetch student details
$user_id = $_SESSION['user']['id'];

$query = "SELECT s.id, r.room_number 
          FROM students s 
          LEFT JOIN rooms r ON s.room_id = r.id 
          WHERE s.user_id = ?";
$stmt = $conn->prepare($query);

// Check if the query is valid
if (!$stmt) {
    die("Database preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    die("Database query failed: " . $stmt->error);
}

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("No student details found for this user.");
}

$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Hostel Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #185a9d;
            --secondary-color: #43cea2;
            --text-color: #333;
            --danger-color: #dc2626;
            --success-color: #059669;
            --warning-color: #d97706;
            --gradient: linear-gradient(135deg, #43cea2, #185a9d);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--gradient);
            color: var(--text-color);
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-x: hidden;
        }

        .back-to-home {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .back-to-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .back-to-home i {
            font-size: 1.1rem;
        }

        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            position: relative;
        }

        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1000px;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .dashboard-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }

        .dashboard-header {
            margin-bottom: 40px;
            position: relative;
            text-align: center;
        }

        .dashboard-header h1 {
            color: var(--primary-color);
            font-size: 2.8rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 15px;
            display: inline-block;
        }

        .dashboard-header h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient);
            border-radius: 2px;
        }

        .room-info {
            background: rgba(67, 206, 162, 0.1);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            transition: all 0.3s ease;
            border: 2px solid rgba(67, 206, 162, 0.2);
        }

        .room-info:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(67, 206, 162, 0.2);
        }

        .room-info i {
            color: var(--primary-color);
            font-size: 2rem;
        }

        .room-info p {
            margin: 0;
            font-size: 1.4rem;
            color: var(--primary-color);
            font-weight: 500;
        }

        .student-nav {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }

        .nav-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.4s ease;
            text-decoration: none;
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .nav-card::before {
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

        .nav-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .nav-card:hover::before {
            opacity: 0.05;
        }

        .nav-card > * {
            position: relative;
            z-index: 2;
        }

        .nav-card i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .nav-card:hover i {
            transform: scale(1.1);
        }

        .nav-card span {
            font-size: 1.2rem;
            font-weight: 500;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .logout-card {
            background: #fee2e2;
            border-color: rgba(220, 38, 38, 0.2);
        }

        .logout-card i {
            color: var(--danger-color);
        }

        .logout-card span {
            color: var(--danger-color);
        }

        .logout-card:hover {
            background: #fecaca;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 30px 20px;
                margin: 20px;
            }

            .dashboard-header h1 {
                font-size: 2.2rem;
            }

            .room-info {
                padding: 15px;
            }

            .room-info p {
                font-size: 1.2rem;
            }

            .student-nav {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .nav-card {
                padding: 25px;
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
    <div class="loading"></div>

    <a href="../index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>

    <div class="main-container">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
                <div class="room-info">
                    <i class="fas fa-door-open"></i>
                    <p>Room Assigned: <?php echo htmlspecialchars($student['room_number'] ?? 'Not Assigned'); ?></p>
                </div>
            </div>

            <nav class="student-nav">
                <a href="profile.php" class="nav-card">
                    <i class="fas fa-user-circle"></i>
                    <span>View/Edit Profile</span>
                </a>
                <a href="complaints.php" class="nav-card">
                    <i class="fas fa-comments"></i>
                    <span>Manage Complaints</span>
                </a>
                <a href="leave_request.php" class="nav-card">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Leave Requests</span>
                </a>
                <a href="../auth/logout.php" class="nav-card logout-card">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Loading animation
        window.addEventListener('load', function() {
            const loader = document.querySelector('.loading');
            setTimeout(() => {
                loader.classList.add('hide');
            }, 500);
        });

        // Add hover effect to nav cards
        document.querySelectorAll('.nav-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
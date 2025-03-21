<?php
include '../includes/header.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Handle leave request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];

    // Get student ID
    $student_query = "SELECT id FROM students WHERE user_id = ?";
    $stmt = $conn->prepare($student_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if ($student) {
        $student_id = $student['id'];
        $insert_query = "INSERT INTO leaves (student_id, start_date, end_date, reason) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("isss", $student_id, $start_date, $end_date, $reason);

        if ($insert_stmt->execute()) {
            $success = "Leave request submitted successfully!";
        } else {
            $error = "Error submitting leave request.";
        }
    } else {
        $error = "Student record not found.";
    }
}

// Fetch leave requests for the student
$leaves_query = "SELECT * FROM leaves WHERE student_id = (SELECT id FROM students WHERE user_id = ?)";
$stmt = $conn->prepare($leaves_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$leaves_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests - Hostel Management System</title>
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
        }

        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            position: relative;
        }

        .leave-form, .leave-history {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .leave-form::before, .leave-history::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }

        .page-header {
            margin-bottom: 40px;
            position: relative;
            text-align: center;
        }

        .page-header h2 {
            color: var(--primary-color);
            font-size: 2.8rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 15px;
            display: inline-block;
        }

        .page-header h2::after {
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

        .success, .error {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-size: 1rem;
            text-align: center;
            animation: slideIn 0.5s ease-out;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .success {
            background: #dcfce7;
            color: var(--success-color);
            border: 2px solid #86efac;
        }

        .error {
            background: #fee2e2;
            color: var(--danger-color);
            border: 2px solid #fca5a5;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: var(--primary-color);
            font-weight: 500;
            font-size: 1.1rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(67, 206, 162, 0.1);
            background: white;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .submit-btn {
            background: var(--gradient);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
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

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .leave-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 30px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .leave-table th,
        .leave-table td {
            padding: 20px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .leave-table th {
            background: #f8fafc;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .leave-table tr:last-child td {
            border-bottom: none;
        }

        .leave-table tr:hover {
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-pending {
            background: #fef3c7;
            color: var(--warning-color);
        }

        .status-approved {
            background: #dcfce7;
            color: var(--success-color);
        }

        .status-rejected {
            background: #fee2e2;
            color: var(--danger-color);
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            padding: 12px 24px;
            border-radius: 50px;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .back-button:hover {
            transform: translateX(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .leave-form, .leave-history {
                padding: 30px 20px;
                margin: 20px;
            }

            .page-header h2 {
                font-size: 2.2rem;
            }

            .leave-table {
                display: block;
                overflow-x: auto;
            }

            .form-group input,
            .form-group textarea {
                padding: 12px;
            }

            .submit-btn {
                padding: 12px 20px;
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

    <a href="dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="main-container">
        <div class="leave-form">
            <div class="page-header">
                <h2>Submit Leave Request</h2>
            </div>

            <?php if (isset($success)): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" required>
                </div>

                <div class="form-group">
                    <label for="reason">Reason:</label>
                    <textarea name="reason" required placeholder="Please provide a detailed reason for your leave request"></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Submit Leave Request
                </button>
            </form>
        </div>

        <div class="leave-history">
            <div class="page-header">
                <h2>Leave Request History</h2>
            </div>
            <table class="leave-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($leave = $leaves_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $leave['id']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($leave['start_date'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($leave['end_date'])); ?></td>
                        <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($leave['status']); ?>">
                                <i class="fas fa-circle"></i>
                                <?php echo ucfirst($leave['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($leave['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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

        // Date validation
        document.querySelector('input[name="end_date"]').addEventListener('change', function() {
            const startDate = document.querySelector('input[name="start_date"]').value;
            if (this.value < startDate) {
                alert('End date cannot be before start date');
                this.value = '';
            }
        });
    </script>
</body>
</html>
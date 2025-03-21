<?php
session_start();
include '../includes/header.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complaint_id = intval($_POST['complaint_id']);
    $status = $_POST['status'];

    $update_query = "UPDATE complaints SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $status, $complaint_id);

    if ($update_stmt->execute()) {
        $success = "Complaint status updated successfully!";
    } else {
        $error = "Failed to update complaint status.";
    }
}

// Fetch all complaints
$complaints_query = "SELECT c.id, u.name, c.title, c.description, c.status, c.created_at 
                     FROM complaints c 
                     JOIN students s ON c.student_id = s.id 
                     JOIN users u ON s.user_id = u.id";
$complaints_result = $conn->query($complaints_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Complaints - Hostel Management System</title>
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
            padding: 2rem;
        }

        .complaints-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .complaints-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }

        h1 {
            color: var(--secondary-color);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            position: relative;
            padding-bottom: 1rem;
        }

        h1::after {
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

        .success {
            background: #dcfce7;
            color: #16a34a;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 2rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
            color: var(--secondary-color);
            font-weight: 600;
        }

        tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-resolved {
            background: #dcfce7;
            color: #16a34a;
        }

        .action-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        select {
            padding: 0.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--secondary-color);
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        button {
            padding: 0.5rem 1rem;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3);
        }

        .back-button {
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

        .back-button:hover {
            color: var(--primary-color);
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .complaints-container {
                padding: 2rem;
            }

            h1 {
                font-size: 2rem;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            .action-form {
                flex-direction: column;
            }

            .back-button {
                top: 1rem;
                left: 1rem;
            }
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="complaints-container">
        <h1>Manage Complaints</h1>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($complaint = $complaints_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $complaint['id']; ?></td>
                        <td><?php echo htmlspecialchars($complaint['name']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['description']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($complaint['status']); ?>">
                                <?php echo htmlspecialchars($complaint['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($complaint['created_at']); ?></td>
                        <td>
                            <form method="POST" class="action-form">
                                <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                                <select name="status" required>
                                    <option value="Pending" <?php if ($complaint['status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Processing" <?php if ($complaint['status'] === 'Processing') echo 'selected'; ?>>Processing</option>
                                    <option value="Resolved" <?php if ($complaint['status'] === 'Resolved') echo 'selected'; ?>>Resolved</option>
                                </select>
                                <button type="submit">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 
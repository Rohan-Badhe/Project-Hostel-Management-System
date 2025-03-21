<?php
include '../includes/header.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch all students
$query = "SELECT s.id, u.name, u.email, r.room_number 
          FROM students s 
          LEFT JOIN users u ON s.user_id = u.id 
          LEFT JOIN rooms r ON s.room_id = r.id";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Hostel Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #43cea2, #185a9d);
            color: #fff;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            text-align: center;
            margin-top: 80px;
        }

        .students-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 1200px;
            color: #333;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(24, 90, 157, 0.1);
        }

        .page-header h1 {
            color: #185a9d;
            font-size: 2.5rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header h1 i {
            color: #43cea2;
        }

        .add-button {
            background: linear-gradient(135deg, #43cea2, #185a9d);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .add-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .student-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .student-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .student-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #43cea2, #185a9d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 500;
        }

        .student-info h3 {
            margin: 0;
            color: #185a9d;
            font-size: 1.2rem;
        }

        .student-info p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        .student-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .detail-item i {
            color: #185a9d;
            font-size: 1.2rem;
            margin-bottom: 8px;
        }

        .detail-item p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        .detail-item .value {
            margin: 5px 0 0;
            color: #185a9d;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-edit {
            background: linear-gradient(135deg, #43cea2, #185a9d);
            color: white;
        }

        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-active i {
            color: #166534;
        }

        @media (max-width: 768px) {
            .students-container {
                width: 95%;
                padding: 20px;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .students-grid {
                grid-template-columns: 1fr;
            }

            .main-container {
                margin-top: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="students-container">
            <div class="page-header">
                <h1><i class="fas fa-users"></i> Manage Students</h1>
                <a href="add_student.php" class="add-button">
                    <i class="fas fa-plus"></i> Add New Student
                </a>
            </div>

            <div class="students-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="student-card">
                        <span class="status-badge status-active">
                            <i class="fas fa-check-circle"></i> Active
                        </span>
                        
                        <div class="student-header">
                            <div class="student-avatar">
                                <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                            </div>
                            <div class="student-info">
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                <p><?php echo htmlspecialchars($row['email']); ?></p>
                            </div>
                        </div>

                        <div class="student-details">
                            <div class="detail-item">
                                <i class="fas fa-id-card"></i>
                                <p>Student ID</p>
                                <div class="value"><?php echo $row['id']; ?></div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-door-open"></i>
                                <p>Room</p>
                                <div class="value"><?php echo $row['room_number'] ?? 'Unassigned'; ?></div>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn btn-delete" onclick="deleteStudent(<?php echo $row['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
        function deleteStudent(id) {
            if (confirm('Are you sure you want to delete this student?')) {
                // Add delete functionality here
                console.log('Deleting student:', id);
            }
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
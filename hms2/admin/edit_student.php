<?php
include '../includes/header.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch student details
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];

    // Query to fetch student details
    $query = "SELECT s.id, u.name, u.email, s.room_id, s.contact_number, s.address 
              FROM students s 
              JOIN users u ON s.user_id = u.id 
              WHERE s.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
    } else {
        die("Student not found.");
    }
} else {
    header("Location: students.php");
    exit;
}

// Update student details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $room_id = $_POST['room_id'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    // Update users table
    $update_user_query = "UPDATE users SET name = ?, email = ? WHERE id = ?";
    $user_stmt = $conn->prepare($update_user_query);
    $user_stmt->bind_param("ssi", $name, $email, $student['id']);
    $user_stmt->execute();

    // Update students table
    $update_student_query = "UPDATE students SET room_id = ?, contact_number = ?, address = ? WHERE id = ?";
    $student_stmt = $conn->prepare($update_student_query);
    $student_stmt->bind_param("issi", $room_id, $contact, $address, $student_id);

    if ($student_stmt->execute()) {
        header("Location: students.php");
        exit;
    } else {
        $error = "Failed to update student details.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Hostel Management System</title>
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

        .form-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            color: #333;
        }

        .page-header {
            margin-bottom: 30px;
            position: relative;
        }

        .page-header h1 {
            color: #185a9d;
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            padding-bottom: 15px;
        }

        .page-header h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(135deg, #43cea2, #185a9d);
            border-radius: 2px;
        }

        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
            animation: shake 0.5s ease-in-out;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #185a9d;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #43cea2;
            box-shadow: 0 0 0 3px rgba(67, 206, 162, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .submit-btn {
            background: linear-gradient(135deg, #43cea2, #185a9d);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #185a9d;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            transform: translateX(-5px);
            color: #43cea2;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @media (max-width: 768px) {
            .form-container {
                width: 90%;
                padding: 20px;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .main-container {
                margin-top: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="form-container">
            <div class="page-header">
                <h1>Edit Student</h1>
            </div>

            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="room_id">Room ID</label>
                    <input type="number" name="room_id" value="<?php echo htmlspecialchars($student['room_id']); ?>">
                </div>

                <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input type="text" name="contact" value="<?php echo htmlspecialchars($student['contact_number']); ?>">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" required><?php echo htmlspecialchars($student['address']); ?></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Update Student
                </button>
            </form>

            <a href="students.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Manage Students
            </a>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
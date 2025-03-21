<?php
include '../includes/header.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $room_id = empty($_POST['room_id']) ? null : $_POST['room_id'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    // Check if email already exists
    $email_check_query = "SELECT id FROM users WHERE email = ?";
    $email_check_stmt = $conn->prepare($email_check_query);
    $email_check_stmt->bind_param("s", $email);
    $email_check_stmt->execute();
    $email_check_result = $email_check_stmt->get_result();

    if ($email_check_result->num_rows > 0) {
        $error = "The email address is already in use. Please use a different email.";
    } else {
        // Check if the room exists and has capacity
        $room_check_query = "SELECT id, available_capacity FROM rooms WHERE id = ? AND available_capacity > 0";
        $room_check_stmt = $conn->prepare($room_check_query);
        $room_check_stmt->bind_param("i", $room_id);
        $room_check_stmt->execute();
        $room_check_result = $room_check_stmt->get_result();

        if ($room_check_result->num_rows === 0) {
            $error = "Room is full or does not exist. Please choose another room.";
        } else {
            // Insert into users table
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = "student";
            $user_query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $user_stmt = $conn->prepare($user_query);
            $user_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($user_stmt->execute()) {
                $user_id = $conn->insert_id;

                // Insert into students table
                $student_query = "INSERT INTO students (user_id, room_id, contact_number, address) VALUES (?, ?, ?, ?)";
                $student_stmt = $conn->prepare($student_query);
                $student_stmt->bind_param("iiss", $user_id, $room_id, $contact, $address);

                if ($student_stmt->execute()) {
                    // Update room's available capacity
                    $update_room_query = "UPDATE rooms SET available_capacity = available_capacity - 1 WHERE id = ?";
                    $update_room_stmt = $conn->prepare($update_room_query);
                    $update_room_stmt->bind_param("i", $room_id);
                    $update_room_stmt->execute();

                    $success = "Student added successfully!";
                } else {
                    $error = "Failed to add student details: " . $conn->error;
                }
            } else {
                $error = "Failed to create user: " . $conn->error;
            }
        }
    }
}

// Fetch all students
$query = "SELECT s.id AS student_id, u.id AS user_id, u.name, u.email, s.room_id, s.contact_number, s.address 
          FROM students s 
          JOIN users u ON s.user_id = u.id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Hostel Management System</title>
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

        .form-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
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

        h2 {
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
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

        input, select, textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        input:focus, select:focus, textarea:focus {
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
            .form-container {
                padding: 2rem;
            }

            .back-button {
                top: 1rem;
                left: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="form-container">
        <h1>Manage Students</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

        <form method="POST">
            <h2>Add New Student</h2>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required placeholder="Enter student's full name">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required placeholder="Enter student's email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required placeholder="Create a password">
            </div>

            <div class="form-group">
                <label for="room_id">Select Room</label>
                <select name="room_id" id="room_id" required>
                    <option value="" disabled selected>Choose a room</option>
                    <?php
                    // Fetch available rooms for dropdown
                    $rooms_query = "SELECT id, room_number, available_capacity FROM rooms WHERE available_capacity > 0";
                    $rooms_result = $conn->query($rooms_query);

                    while ($room = $rooms_result->fetch_assoc()) {
                        echo "<option value='" . $room['id'] . "'>Room " . $room['room_number'] . " (Available: " . $room['available_capacity'] . ")</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="contact">Contact Number</label>
                <input type="text" name="contact" id="contact" required placeholder="Enter contact number">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" required placeholder="Enter student's address"></textarea>
            </div>

            <button type="submit" name="add_student">
                <i class="fas fa-user-plus"></i> Add Student
            </button>
        </form>
    </div>
</body>
</html>
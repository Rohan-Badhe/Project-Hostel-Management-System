<?php
// Updated register.php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $room_id = $_POST['room_id']; // The room chosen by the student

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $email_check_query = "SELECT id FROM users WHERE email = ?";
        $email_check_stmt = $conn->prepare($email_check_query);
        $email_check_stmt->bind_param("s", $email);
        $email_check_stmt->execute();
        $email_check_result = $email_check_stmt->get_result();

        if ($email_check_result->num_rows > 0) {
            $error = "The email address is already in use. Please use a different email.";
        } else {
            // Check if the room exists and is available
            $room_check_query = "SELECT id, available_capacity FROM rooms WHERE id = ? AND available_capacity > 0";
            $room_check_stmt = $conn->prepare($room_check_query);
            $room_check_stmt->bind_param("i", $room_id);
            $room_check_stmt->execute();
            $room_check_result = $room_check_stmt->get_result();

            if ($room_check_result->num_rows == 0) {
                $error = "Room is full or does not exist. Please choose another room.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = "student"; // Default role is student

                // Insert into users table
                $user_query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
                $user_stmt = $conn->prepare($user_query);
                $user_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

                if ($user_stmt->execute()) {
                    // Get the user ID
                    $user_id = $conn->insert_id;

                    // Insert into students table
                    $student_query = "INSERT INTO students (user_id, room_id, contact_number, address) VALUES (?, ?, ?, ?)";
                    $student_stmt = $conn->prepare($student_query);
                    $student_stmt->bind_param("iiss", $user_id, $room_id, $contact_number, $address);

                    if ($student_stmt->execute()) {
                        // Update the room's available capacity
                        $update_room_query = "UPDATE rooms SET available_capacity = available_capacity - 1 WHERE id = ?";
                        $update_room_stmt = $conn->prepare($update_room_query);
                        $update_room_stmt->bind_param("i", $room_id);
                        $update_room_stmt->execute();

                        // Success message and redirection to login
                        $success = "Registration successful! You can now log in.";
                        header("Location: login.php");
                        exit;
                    } else {
                        $error = "Failed to add student details.";
                    }
                } else {
                    $error = "Failed to create user.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hostel Management System</title>
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

        .register-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }

        .register-container h1 {
            color: var(--secondary-color);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            position: relative;
            padding-bottom: 1rem;
        }

        .register-container h1::after {
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

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
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
            .register-container {
                padding: 2rem;
            }

            .back-to-home {
                top: 1rem;
                left: 1rem;
            }
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>

    <div class="register-container">
        <h1>Create Account</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <form method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required placeholder="Create a password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirm your password">
            </div>

            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="text" name="contact_number" id="contact_number" required placeholder="Enter your contact number">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" required placeholder="Enter your address"></textarea>
            </div>

            <div class="form-group">
                <label for="room_id">Select Room</label>
                <select name="room_id" id="room_id" required>
                    <option value="">Choose a room</option>
                    <?php
                    // Fetch available rooms for the dropdown
                    $rooms_query = "SELECT id, room_number, available_capacity FROM rooms WHERE available_capacity > 0";
                    $rooms_result = $conn->query($rooms_query);

                    while ($room = $rooms_result->fetch_assoc()) {
                        echo "<option value='" . $room['id'] . "'>Room " . $room['room_number'] . " (Available: " . $room['available_capacity'] . ")</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
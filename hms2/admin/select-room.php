<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = intval($_POST['room_id']);
    $user_id = $_SESSION['user']['id'];

    // Check room capacity
    $query = "SELECT capacity, available_capacity FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();

    if ($room['available_capacity'] > 0) {
        // Update student's room
        $update_query = "UPDATE students SET room_id = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $room_id, $user_id);

        if ($update_stmt->execute()) {
            // Update room's available capacity
            $room_update_query = "UPDATE rooms SET available_capacity = available_capacity - 1 WHERE id = ?";
            $room_update_stmt = $conn->prepare($room_update_query);
            $room_update_stmt->bind_param("i", $room_id);
            $room_update_stmt->execute();

            header("Location: dashboard.php"); // Redirect to dashboard
        } else {
            $error = "Failed to assign room.";
        }
    } else {
        $error = "Room is full. Please select another room.";
    }
}

// Fetch available rooms
$rooms_query = "SELECT id, room_number, available_capacity FROM rooms WHERE available_capacity > 0";
$rooms_result = $conn->query($rooms_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Room - Hostel Management System</title>
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

        .select-room-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
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
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #185a9d;
            font-weight: 500;
        }

        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23185a9d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }

        .form-group select:focus {
            outline: none;
            border-color: #43cea2;
            box-shadow: 0 0 0 3px rgba(67, 206, 162, 0.1);
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

        .room-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: left;
        }

        .room-info h3 {
            color: #185a9d;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .room-info p {
            color: #64748b;
            margin: 5px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @media (max-width: 768px) {
            .select-room-container {
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
        <div class="select-room-container">
            <div class="page-header">
                <h1>Select Your Room</h1>
            </div>

            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="room_id">Available Rooms</label>
                    <select name="room_id" required>
                        <option value="">Select a room</option>
                        <?php while ($room = $rooms_result->fetch_assoc()): ?>
                            <option value="<?php echo $room['id']; ?>">
                                Room <?php echo $room['room_number']; ?> (Available: <?php echo $room['available_capacity']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-check"></i> Select Room
                </button>
            </form>

            <div class="room-info">
                <h3><i class="fas fa-info-circle"></i> Room Selection Guidelines</h3>
                <p><i class="fas fa-check-circle"></i> Choose a room with available capacity</p>
                <p><i class="fas fa-exclamation-circle"></i> Room selection is final and cannot be changed immediately</p>
                <p><i class="fas fa-users"></i> Rooms are shared with other students</p>
            </div>

            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
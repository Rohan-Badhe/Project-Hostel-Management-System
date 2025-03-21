<?php
include '../includes/header.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle Room Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = $_POST['room_number'];
    $capacity = intval($_POST['capacity']);
    $available_capacity = $capacity;

    $query = "INSERT INTO rooms (room_number, capacity, available_capacity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $room_number, $capacity, $available_capacity);

    if ($stmt->execute()) {
        $success = "Room added successfully!";
    } else {
        $error = "Failed to add room: " . $conn->error;
    }
}

// Fetch Rooms
$rooms_query = "SELECT * FROM rooms";
$rooms_result = $conn->query($rooms_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Hostel Management System</title>
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

        .rooms-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 1000px;
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

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
            animation: slideIn 0.5s ease-in-out;
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

        .add-room-form {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            align-items: center;
        }

        .form-group {
            flex: 1;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #185a9d;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #43cea2;
            box-shadow: 0 0 0 3px rgba(67, 206, 162, 0.1);
        }

        .submit-btn {
            background: linear-gradient(135deg, #43cea2, #185a9d);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .rooms-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .rooms-table th,
        .rooms-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .rooms-table th {
            background: #f8fafc;
            color: #185a9d;
            font-weight: 500;
        }

        .rooms-table tr:hover {
            background: #f8fafc;
        }

        .rooms-table tr:last-child td {
            border-bottom: none;
        }

        .capacity-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .capacity-badge.full {
            background: #fee2e2;
            color: #dc2626;
        }

        .capacity-badge.available {
            background: #dcfce7;
            color: #166534;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .rooms-container {
                width: 95%;
                padding: 20px;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .form-row {
                flex-direction: column;
                gap: 15px;
            }

            .main-container {
                margin-top: 120px;
            }

            .rooms-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="rooms-container">
            <div class="page-header">
                <h1>Manage Rooms</h1>
            </div>

            <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

            <div class="add-room-form">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="room_number">Room Number</label>
                            <input type="text" name="room_number" required>
                        </div>
                        <div class="form-group">
                            <label for="capacity">Capacity</label>
                            <input type="number" name="capacity" required min="1">
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-plus"></i> Add Room
                    </button>
                </form>
            </div>

            <table class="rooms-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Room Number</th>
                        <th>Capacity</th>
                        <th>Available Capacity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($room = $rooms_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $room['id']; ?></td>
                            <td><?php echo $room['room_number']; ?></td>
                            <td><?php echo $room['capacity']; ?></td>
                            <td>
                                <span class="capacity-badge <?php echo $room['available_capacity'] > 0 ? 'available' : 'full'; ?>">
                                    <i class="fas <?php echo $room['available_capacity'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                    <?php echo $room['available_capacity']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
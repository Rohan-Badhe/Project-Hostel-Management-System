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

            header("Location: dashboard.php");
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

        .select-room-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .select-room-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-size: 2.8rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 15px;
            display: inline-block;
        }

        .page-header h1::after {
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

        .error {
            background: #fee2e2;
            color: var(--danger-color);
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-size: 1rem;
            text-align: center;
            animation: shake 0.5s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 2px solid #fca5a5;
        }

        .form-group {
            margin-bottom: 30px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: var(--primary-color);
            font-weight: 500;
            font-size: 1.1rem;
        }

        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
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
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(67, 206, 162, 0.1);
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
            margin-bottom: 30px;
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

        .room-info {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-top: 30px;
            text-align: left;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .room-info h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.3rem;
        }

        .room-info p {
            color: #64748b;
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .room-info p:hover {
            background: #f1f5f9;
            transform: translateX(5px);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 10px 20px;
            border-radius: 8px;
            background: #f8fafc;
        }

        .back-button:hover {
            transform: translateX(-5px);
            background: #f1f5f9;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
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

        @media (max-width: 768px) {
            .select-room-container {
                padding: 30px 20px;
                margin: 20px;
            }

            .page-header h1 {
                font-size: 2.2rem;
            }

            .room-info {
                padding: 20px;
            }

            .room-info p {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="loading"></div>

    <div class="main-container">
        <div class="select-room-container">
            <div class="page-header">
                <h1>Select Your Room</h1>
            </div>

            <?php if (isset($error)): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

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
                <p><i class="fas fa-clock"></i> Room changes are only allowed at the start of each semester</p>
            </div>

            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
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

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const roomSelect = document.querySelector('select[name="room_id"]');
            if (!roomSelect.value) {
                e.preventDefault();
                alert('Please select a room');
            }
        });
    </script>
</body>
</html>
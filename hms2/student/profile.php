<?php
include '../includes/header.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: /auth/login.php");
    exit;
}

// Fetch student profile
$user_id = $_SESSION['user']['id'];
$query = "SELECT s.contact_number, s.address FROM students s WHERE s.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    $update_query = "UPDATE students SET contact_number = ?, address = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssi", $contact_number, $address, $user_id);

    if ($update_stmt->execute()) {
        $success = "Profile updated successfully!";
    } else {
        $error = "Error updating profile.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - Hostel Management System</title>
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

        .profile-container {
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

        .profile-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .profile-header h2 {
            color: var(--primary-color);
            font-size: 2.8rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 15px;
            display: inline-block;
        }

        .profile-header h2::after {
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

        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .info-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .info-card i {
            font-size: 2rem;
            color: var(--primary-color);
            background: rgba(24, 90, 157, 0.1);
            padding: 15px;
            border-radius: 50%;
        }

        .info-content h3 {
            color: var(--primary-color);
            margin-bottom: 5px;
            font-size: 1.2rem;
        }

        .info-content p {
            color: #64748b;
            font-size: 1rem;
        }

        .profile-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
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
            .profile-container {
                padding: 30px 20px;
                margin: 20px;
            }

            .profile-header h2 {
                font-size: 2.2rem;
            }

            .profile-info {
                grid-template-columns: 1fr;
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
        <div class="profile-container">
            <div class="profile-header">
                <h2>Student Profile</h2>
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

            <div class="profile-info">
                <div class="info-card">
                    <i class="fas fa-user"></i>
                    <div class="info-content">
                        <h3>Student Name</h3>
                        <p><?php echo htmlspecialchars($_SESSION['user']['name']); ?></p>
                    </div>
                </div>
                <div class="info-card">
                    <i class="fas fa-envelope"></i>
                    <div class="info-content">
                        <h3>Email</h3>
                        <p><?php echo htmlspecialchars($_SESSION['user']['email']); ?></p>
                    </div>
                </div>
            </div>

            <div class="profile-form">
                <form method="post">
                    <div class="form-group">
                        <label for="contact_number">Contact Number:</label>
                        <input type="tel" name="contact_number" value="<?php echo htmlspecialchars($profile['contact_number'] ?? ''); ?>" required placeholder="Enter your contact number">
                    </div>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <textarea name="address" required placeholder="Enter your address"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
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

        // Phone number validation
        document.querySelector('input[name="contact_number"]').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+]/g, '');
        });
    </script>
</body>
</html>
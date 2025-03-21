<?php
include '../includes/header.php';
include '../includes/db_connect.php';

// Constants
define('ANNUAL_FEE', 15000); // Annual fees for students

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        // Update fee status
        $fee_id = intval($_POST['fee_id']);
        $status = $_POST['status'];

        $query = "UPDATE fees SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $fee_id);

        if ($stmt->execute()) {
            $success = "Fee status updated successfully!";
        } else {
            $error = "Error updating fee status: " . $conn->error;
        }
    } elseif (isset($_POST['update_fee'])) {
        // Update fee amount
        $fee_id = intval($_POST['fee_id']);
        $amount = floatval($_POST['amount']);

        // Check total paid fees
        $total_query = "SELECT SUM(amount) AS total_paid FROM fees WHERE student_id = 
                         (SELECT student_id FROM fees WHERE id = ?)";
        $total_stmt = $conn->prepare($total_query);
        $total_stmt->bind_param("i", $fee_id);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result();
        $total_paid = $total_result->fetch_assoc()['total_paid'] ?? 0;

        // Check remaining amount
        $remaining = ANNUAL_FEE - $total_paid;
        if ($remaining <= 0) {
            $error = "The fees are already fully paid.";
        } else {
            $query = "UPDATE fees SET amount = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("di", $amount, $fee_id);

            if ($stmt->execute()) {
                $success = "Fee amount updated successfully!";
            } else {
                $error = "Error updating fee amount: " . $conn->error;
            }
        }
    }
}

$fees_query = "SELECT f.id, u.name, f.amount, f.status, f.due_date, 
                      (SELECT SUM(amount) FROM fees WHERE student_id = s.id) AS total_paid 
               FROM fees f 
               JOIN students s ON f.student_id = s.id 
               JOIN users u ON s.user_id = u.id";
$fees_result = $conn->query($fees_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fees</title>
    <link rel="stylesheet" href="../css/admin-fees.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a8a4e6;
            --accent-color: #00b894;
            --background-color: #f0f2f5;
            --card-background: #ffffff;
            --text-primary: #2d3436;
            --text-secondary: #636e72;
            --gradient-1: linear-gradient(135deg, #6c5ce7, #a8a4e6);
            --gradient-2: linear-gradient(135deg, #00b894, #00cec9);
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            color: var(--text-primary);
        }

        .fees-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: var(--shadow-md);
        }

        .page-header h1 {
            color: var(--text-primary);
            margin: 0;
            font-size: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header h1 i {
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .fee-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: var(--shadow-md);
            text-align: center;
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .summary-card i {
            font-size: 2.5rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .summary-card h3 {
            margin: 0;
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .summary-card p {
            margin: 0.5rem 0 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .table-container {
            background: var(--card-background);
            border-radius: 15px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1.2rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        th {
            background: var(--gradient-1);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: rgba(108, 92, 231, 0.05);
        }

        .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-pending {
            background-color: rgba(255, 152, 0, 0.1);
            color: #f57c00;
        }

        .status-pending i {
            color: #f57c00;
        }

        .status-paid {
            background-color: rgba(0, 184, 148, 0.1);
            color: #00b894;
        }

        .status-paid i {
            color: #00b894;
        }

        .action-buttons {
            display: flex;
            gap: 0.8rem;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-update {
            background: var(--gradient-1);
            color: white;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .success-message {
            background-color: rgba(0, 184, 148, 0.1);
            color: #00b894;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error-message {
            background-color: rgba(255, 118, 117, 0.1);
            color: #ff7675;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        select, input[type="number"] {
            padding: 0.6rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        select:focus, input[type="number"]:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        @media (max-width: 768px) {
            .fees-container {
                margin: 1rem auto;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .table-container {
                overflow-x: auto;
            }

            th, td {
                padding: 1rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="fees-container">
        <div class="page-header">
            <h1><i class="fas fa-money-bill-wave"></i> Manage Fees</h1>
        </div>

        <?php if (isset($success)) echo "<div class='success-message'><i class='fas fa-check-circle'></i> $success</div>"; ?>
        <?php if (isset($error)) echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> $error</div>"; ?>

        <div class="fee-summary">
            <div class="summary-card">
                <i class="fas fa-users"></i>
                <h3>Total Students</h3>
                <p><?php echo $fees_result->num_rows; ?></p>
            </div>
            <div class="summary-card">
                <i class="fas fa-check-circle"></i>
                <h3>Fully Paid</h3>
                <p><?php 
                    $fully_paid = 0;
                    while ($fee = $fees_result->fetch_assoc()) {
                        if (($fee['total_paid'] ?? 0) >= ANNUAL_FEE) {
                            $fully_paid++;
                        }
                    }
                    echo $fully_paid;
                ?></p>
            </div>
            <div class="summary-card">
                <i class="fas fa-clock"></i>
                <h3>Pending Payments</h3>
                <p><?php echo $fees_result->num_rows - $fully_paid; ?></p>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Paid Fees</th>
                        <th>Remaining Fees</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $fees_result->data_seek(0); // Reset the result pointer
                    while ($fee = $fees_result->fetch_assoc()): 
                        $remaining_fees = ANNUAL_FEE - ($fee['total_paid'] ?? 0);
                    ?>
                        <tr>
                            <td><?php echo $fee['id']; ?></td>
                            <td><?php echo htmlspecialchars($fee['name']); ?></td>
                            <td><?php echo $fee['total_paid'] ?? 0; ?></td>
                            <td>
                                <?php 
                                if ($remaining_fees <= 0) {
                                    echo "Fully Paid";
                                } else {
                                    echo $remaining_fees;
                                }
                                ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $fee['status'] === 'paid' ? 'status-paid' : 'status-pending'; ?>">
                                    <i class="fas <?php echo $fee['status'] === 'paid' ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                                    <?php echo ucfirst($fee['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $fee['due_date']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="fee_id" value="<?php echo $fee['id']; ?>">
                                        <select name="status" required>
                                            <option value="pending" <?php if ($fee['status'] === 'pending') echo 'selected'; ?>>Pending</option>
                                            <option value="paid" <?php if ($fee['status'] === 'paid') echo 'selected'; ?>>Paid</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-update">
                                            <i class="fas fa-save"></i> Update Status
                                        </button>
                                    </form>

                                    <?php if ($remaining_fees > 0): ?>
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="fee_id" value="<?php echo $fee['id']; ?>">
                                            <input type="number" name="amount" step="0.01" min="0" value="0" required>
                                            <button type="submit" name="update_fee" class="btn btn-update">
                                                <i class="fas fa-plus"></i> Add Payment
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

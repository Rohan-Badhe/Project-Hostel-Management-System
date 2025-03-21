<?php
include '../includes/header.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle leave status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = intval($_POST['leave_id']);
    $status = $_POST['status'];

    $update_query = "UPDATE leaves SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $leave_id);

    if ($stmt->execute()) {
        $success = "Leave status updated successfully!";
    } else {
        $error = "Error updating leave status.";
    }
}

// Fetch all leave requests
$leaves_query = "SELECT l.id, u.name, l.start_date, l.end_date, l.reason, l.status, l.created_at 
                 FROM leaves l 
                 JOIN students s ON l.student_id = s.id 
                 JOIN users u ON s.user_id = u.id";
$result = $conn->query($leaves_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/admin-manage-leaves.css">

</head>
<body>
<h1>Manage Leave Requests</h1>
<?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Student Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Submitted At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($leave = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $leave['id']; ?></td>
            <td><?php echo htmlspecialchars($leave['name']); ?></td>
            <td><?php echo $leave['start_date']; ?></td>
            <td><?php echo $leave['end_date']; ?></td>
            <td><?php echo htmlspecialchars($leave['reason']); ?></td>
            <td><?php echo $leave['status']; ?></td>
            <td><?php echo $leave['created_at']; ?></td>
            <td>
                <form method="POST" style="display: inline-block;">
                    <input type="hidden" name="leave_id" value="<?php echo $leave['id']; ?>">
                    <select name="status" required>
                        <option value="Pending" <?php if ($leave['status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Approved" <?php if ($leave['status'] === 'Approved') echo 'selected'; ?>>Approved</option>
                        <option value="Denied" <?php if ($leave['status'] === 'Denied') echo 'selected'; ?>>Denied</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>


    
</body>
</html>
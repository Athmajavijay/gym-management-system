<?php
require_once '../includes/db_connect.php';

// Get all members
$query = "SELECT * FROM members ORDER BY join_date DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Members - FitZone Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏋️ FitZone Gym Management</h1>
            <p>All Members</p>
        </header>

        <nav>
            <ul>
                <li><a href="../index.php">🏠 Home</a></li>
                <li><a href="add_member.php">➕ Add Member</a></li>
                <li><a href="view_members.php">👥 View Members</a></li>
                <li><a href="../trainers/add_trainer.php">➕ Add Trainer</a></li>
                <li><a href="../trainers/view_trainers.php">💪 View Trainers</a></li>
                <li><a href="../classes/add_class.php">➕ Add Class</a></li>
                <li><a href="../classes/view_classes.php">📅 View Classes</a></li>
                <li><a href="../enrollments/enroll.php">📝 Enroll</a></li>
            </ul>
        </nav>

        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>All Members (<?php echo $result->num_rows; ?>)</h2>
                <a href="delete_member.php" class="btn btn-danger">🗑️ Delete Member</a>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Membership</th>
                                <th>Join Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['member_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($row['membership_type']); ?>">
                                            <?php echo $row['membership_type']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['join_date'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="delete_member.php?id=<?php echo $row['member_id']; ?>" 
                                           class="btn btn-danger" 
                                           style="padding: 5px 10px; font-size: 0.85em;">
                                            🗑️ Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    No members found. <a href="add_member.php">Add your first member!</a>
                </div>
            <?php endif; ?>
        </div>

        <footer>
            <p>&copy; 2026 FitZone Gym Management System | All Rights Reserved</p>
        </footer>
    </div>
</body>
</html>

<?php $conn->close(); ?>
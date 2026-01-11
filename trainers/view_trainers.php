<?php
require_once '../includes/db_connect.php';

$query = "SELECT * FROM trainers ORDER BY hire_date DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Trainers - FitZone Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏋️ FitZone Gym Management</h1>
            <p>Our Expert Trainers</p>
        </header>

        <nav>
            <ul>
                <li><a href="../index.php">🏠 Home</a></li>
                <li><a href="../members/add_member.php">➕ Add Member</a></li>
                <li><a href="../members/view_members.php">👥 View Members</a></li>
                <li><a href="add_trainer.php">➕ Add Trainer</a></li>
                <li><a href="view_trainers.php">💪 View Trainers</a></li>
                <li><a href="../classes/add_class.php">➕ Add Class</a></li>
                <li><a href="../classes/view_classes.php">📅 View Classes</a></li>
                <li><a href="../enrollments/enroll.php">📝 Enroll</a></li>
            </ul>
        </nav>

        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>All Trainers (<?php echo $result->num_rows; ?>)</h2>
                <a href="delete_trainer.php" class="btn btn-danger">🗑️ Delete Trainer</a>
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
                                <th>Specialty</th>
                                <th>Experience</th>
                                <th>Certification</th>
                                <th>Hire Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['trainer_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span style="color: #667eea; font-weight: 600;">
                                            <?php echo htmlspecialchars($row['specialty']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['experience_years'] ? $row['experience_years'] . ' years' : 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($row['certification'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['hire_date'])); ?></td>
                                    <td>
                                        <a href="delete_trainer.php?id=<?php echo $row['trainer_id']; ?>" 
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
                    No trainers found. <a href="add_trainer.php">Add your first trainer!</a>
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
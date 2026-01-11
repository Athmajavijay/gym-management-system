<?php
require_once '../includes/db_connect.php';

// Get all classes with trainer names
$query = "SELECT c.*, t.full_name as trainer_name 
          FROM classes c 
          JOIN trainers t ON c.trainer_id = t.trainer_id 
          ORDER BY c.class_id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Classes - FitZone Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏋️ FitZone Gym Management</h1>
            <p>All Classes</p>
        </header>

        <nav>
            <ul>
                <li><a href="../index.php">🏠 Home</a></li>
                <li><a href="../members/add_member.php">➕ Add Member</a></li>
                <li><a href="../members/view_members.php">👥 View Members</a></li>
                <li><a href="../trainers/add_trainer.php">➕ Add Trainer</a></li>
                <li><a href="../trainers/view_trainers.php">💪 View Trainers</a></li>
                <li><a href="add_class.php">➕ Add Class</a></li>
                <li><a href="view_classes.php">📅 View Classes</a></li>
                <li><a href="../enrollments/enroll.php">📝 Enroll</a></li>
            </ul>
        </nav>

        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>All Classes (<?php echo $result->num_rows; ?>)</h2>
                <a href="delete_class.php" class="btn btn-danger">🗑️ Delete Class</a>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Class Name</th>
                                <th>Type</th>
                                <th>Trainer</th>
                                <th>Schedule</th>
                                <th>Duration</th>
                                <th>Enrolled</th>
                                <th>Capacity</th>
                                <th>Available</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php 
                                $available = $row['capacity'] - $row['current_enrolled'];
                                $percentage = ($row['current_enrolled'] / $row['capacity']) * 100;
                                ?>
                                <tr>
                                    <td><?php echo $row['class_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['class_name']); ?></strong></td>
                                    <td>
                                        <span class="badge badge-basic">
                                            <?php echo htmlspecialchars($row['class_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['trainer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['schedule']); ?></td>
                                    <td><?php echo $row['duration_minutes']; ?> min</td>
                                    <td style="text-align: center;"><?php echo $row['current_enrolled']; ?></td>
                                    <td style="text-align: center;"><?php echo $row['capacity']; ?></td>
                                    <td style="text-align: center;">
                                        <span style="color: <?php echo $available > 0 ? '#38a169' : '#e53e3e'; ?>; font-weight: 600;">
                                            <?php echo $available; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="delete_class.php?id=<?php echo $row['class_id']; ?>" 
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
                    No classes found. <a href="add_class.php">Add your first class!</a>
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
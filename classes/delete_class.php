<?php
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle deletion
if (isset($_GET['id']) && isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $class_id = intval($_GET['id']);
    
    // Delete class (enrollments will be deleted automatically due to CASCADE)
    $stmt = $conn->prepare("DELETE FROM classes WHERE class_id = ?");
    $stmt->bind_param("i", $class_id);
    
    if ($stmt->execute()) {
        $success_message = "Class deleted successfully!";
    } else {
        $error_message = "Error deleting class: " . $stmt->error;
    }
    $stmt->close();
}

// Get class details if ID is provided
$class = null;
if (isset($_GET['id']) && !isset($_GET['confirm'])) {
    $class_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT c.*, t.full_name as trainer_name FROM classes c JOIN trainers t ON c.trainer_id = t.trainer_id WHERE c.class_id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $class = $result->fetch_assoc();
    $stmt->close();
    
    // Get enrolled members
    $enrolled_query = $conn->prepare("SELECT m.full_name FROM enrollments e JOIN members m ON e.member_id = m.member_id WHERE e.class_id = ?");
    $enrolled_query->bind_param("i", $class_id);
    $enrolled_query->execute();
    $enrolled_members = $enrolled_query->get_result();
}

// Get all classes
$all_classes = $conn->query("SELECT c.*, t.full_name as trainer_name FROM classes c JOIN trainers t ON c.trainer_id = t.trainer_id ORDER BY c.class_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Class - FitZone Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏋️ FitZone Gym Management</h1>
            <p>Delete Class</p>
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
            <h2>Delete Class</h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                    <br><a href="view_classes.php">← Back to Classes List</a>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if ($class && !$success_message): ?>
                <div class="card" style="max-width: 600px; margin: 0 auto; border: 2px solid #e53e3e;">
                    <h3 style="color: #e53e3e;">⚠️ Confirm Deletion</h3>
                    <p><strong>Are you sure you want to delete this class?</strong></p>
                    
                    <div style="background: #f7fafc; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <p><strong>Class Name:</strong> <?php echo htmlspecialchars($class['class_name']); ?></p>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($class['class_type']); ?></p>
                        <p><strong>Trainer:</strong> <?php echo htmlspecialchars($class['trainer_name']); ?></p>
                        <p><strong>Schedule:</strong> <?php echo htmlspecialchars($class['schedule']); ?></p>
                        <p><strong>Enrolled:</strong> <?php echo $class['current_enrolled']; ?> / <?php echo $class['capacity']; ?></p>
                    </div>
                    
                    <?php if ($enrolled_members->num_rows > 0): ?>
                        <div class="alert alert-error">
                            <strong>Warning:</strong> This class has <?php echo $enrolled_members->num_rows; ?> enrolled member(s):
                            <ul style="margin: 10px 0 0 20px;">
                                <?php while ($member = $enrolled_members->fetch_assoc()): ?>
                                    <li><?php echo htmlspecialchars($member['full_name']); ?></li>
                                <?php endwhile; ?>
                            </ul>
                            <p>All enrollments will also be deleted!</p>
                        </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <a href="delete_class.php?id=<?php echo $class['class_id']; ?>&confirm=yes" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you absolutely sure? This cannot be undone!');">
                            Yes, Delete Class
                        </a>
                        <a href="view_classes.php" class="btn">Cancel</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <h3>Select Class to Delete</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Class Name</th>
                                    <th>Type</th>
                                    <th>Trainer</th>
                                    <th>Schedule</th>
                                    <th>Enrolled</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($c = $all_classes->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $c['class_id']; ?></td>
                                        <td><?php echo htmlspecialchars($c['class_name']); ?></td>
                                        <td><?php echo htmlspecialchars($c['class_type']); ?></td>
                                        <td><?php echo htmlspecialchars($c['trainer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($c['schedule']); ?></td>
                                        <td><?php echo $c['current_enrolled']; ?>/<?php echo $c['capacity']; ?></td>
                                        <td>
                                            <a href="delete_class.php?id=<?php echo $c['class_id']; ?>" 
                                               class="btn btn-danger" 
                                               style="padding: 8px 15px; font-size: 0.9em;">
                                                🗑️ Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
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
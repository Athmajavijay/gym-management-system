<?php
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle deletion
if (isset($_GET['id']) && isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $trainer_id = intval($_GET['id']);
    
    // Check if trainer has classes
    $check = $conn->prepare("SELECT COUNT(*) as count FROM classes WHERE trainer_id = ?");
    $check->bind_param("i", $trainer_id);
    $check->execute();
    $class_count = $check->get_result()->fetch_assoc()['count'];
    
    if ($class_count > 0) {
        $error_message = "Cannot delete trainer! They have $class_count active class(es). Please reassign or delete those classes first.";
    } else {
        $stmt = $conn->prepare("DELETE FROM trainers WHERE trainer_id = ?");
        $stmt->bind_param("i", $trainer_id);
        
        if ($stmt->execute()) {
            $success_message = "Trainer deleted successfully!";
        } else {
            $error_message = "Error deleting trainer: " . $stmt->error;
        }
        $stmt->close();
    }
    $check->close();
}

// Get trainer details if ID is provided
$trainer = null;
if (isset($_GET['id']) && !isset($_GET['confirm'])) {
    $trainer_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM trainers WHERE trainer_id = ?");
    $stmt->bind_param("i", $trainer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $trainer = $result->fetch_assoc();
    $stmt->close();
    
    // Get trainer's classes
    $classes_query = $conn->prepare("SELECT class_name, schedule FROM classes WHERE trainer_id = ?");
    $classes_query->bind_param("i", $trainer_id);
    $classes_query->execute();
    $classes = $classes_query->get_result();
}

// Get all trainers
$all_trainers = $conn->query("SELECT * FROM trainers ORDER BY full_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Trainer - FitZone Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏋️ FitZone Gym Management</h1>
            <p>Delete Trainer</p>
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
            <h2>Delete Trainer</h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                    <br><a href="view_trainers.php">← Back to Trainers List</a>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if ($trainer && !$success_message): ?>
                <div class="card" style="max-width: 600px; margin: 0 auto; border: 2px solid #e53e3e;">
                    <h3 style="color: #e53e3e;">⚠️ Confirm Deletion</h3>
                    <p><strong>Are you sure you want to delete this trainer?</strong></p>
                    
                    <div style="background: #f7fafc; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($trainer['full_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($trainer['email']); ?></p>
                        <p><strong>Specialty:</strong> <?php echo htmlspecialchars($trainer['specialty']); ?></p>
                        <p><strong>Experience:</strong> <?php echo $trainer['experience_years']; ?> years</p>
                    </div>
                    
                    <?php if ($classes->num_rows > 0): ?>
                        <div class="alert alert-error">
                            <strong>Cannot Delete!</strong> This trainer is assigned to <?php echo $classes->num_rows; ?> class(es):
                            <ul style="margin: 10px 0 0 20px;">
                                <?php while ($class = $classes->fetch_assoc()): ?>
                                    <li><?php echo htmlspecialchars($class['class_name']) . ' - ' . htmlspecialchars($class['schedule']); ?></li>
                                <?php endwhile; ?>
                            </ul>
                            <p>Please reassign or delete these classes first!</p>
                        </div>
                        <a href="view_trainers.php" class="btn">← Back to Trainers</a>
                    <?php else: ?>
                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                            <a href="delete_trainer.php?id=<?php echo $trainer['trainer_id']; ?>&confirm=yes" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you absolutely sure? This cannot be undone!');">
                                Yes, Delete Trainer
                            </a>
                            <a href="view_trainers.php" class="btn">Cancel</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <h3>Select Trainer to Delete</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Specialty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($t = $all_trainers->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $t['trainer_id']; ?></td>
                                        <td><?php echo htmlspecialchars($t['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($t['email']); ?></td>
                                        <td><?php echo htmlspecialchars($t['specialty']); ?></td>
                                        <td>
                                            <a href="delete_trainer.php?id=<?php echo $t['trainer_id']; ?>" 
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
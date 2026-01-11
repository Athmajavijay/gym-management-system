<?php
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Get all trainers for dropdown
$trainers_query = "SELECT trainer_id, full_name FROM trainers ORDER BY full_name";
$trainers = $conn->query($trainers_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = $_POST['class_name'];
    $trainer_id = $_POST['trainer_id'];
    $schedule = $_POST['schedule'];
    $duration_minutes = $_POST['duration_minutes'];
    $capacity = $_POST['capacity'];
    $class_type = $_POST['class_type'];
    
    if (!empty($class_name) && !empty($trainer_id) && !empty($schedule) && !empty($capacity)) {
        $stmt = $conn->prepare("INSERT INTO classes (class_name, trainer_id, schedule, duration_minutes, capacity, class_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisiis", $class_name, $trainer_id, $schedule, $duration_minutes, $capacity, $class_type);
        
        if ($stmt->execute()) {
            $success_message = "Class added successfully!";
            $_POST = array();
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Please fill in all required fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Class - FitZone Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏋️ FitZone Gym Management</h1>
            <p>Add New Class</p>
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
            <h2>Add New Class</h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="class_name">Class Name *</label>
                        <input type="text" id="class_name" name="class_name" placeholder="e.g., Morning Yoga" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="trainer_id">Select Trainer *</label>
                        <select id="trainer_id" name="trainer_id" required>
                            <option value="">Choose a trainer...</option>
                            <?php 
                            $trainers->data_seek(0); // Reset pointer
                            while ($trainer = $trainers->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $trainer['trainer_id']; ?>">
                                    <?php echo htmlspecialchars($trainer['full_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="class_type">Class Type *</label>
                        <select id="class_type" name="class_type" required>
                            <option value="Yoga">Yoga</option>
                            <option value="Strength">Strength Training</option>
                            <option value="Cardio">Cardio</option>
                            <option value="HIIT">HIIT</option>
                            <option value="Spin">Spin/Cycling</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="schedule">Schedule *</label>
                        <input type="text" id="schedule" name="schedule" placeholder="e.g., Mon/Wed/Fri 7:00 AM" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration_minutes">Duration (minutes)</label>
                        <input type="number" id="duration_minutes" name="duration_minutes" value="60" min="15" max="180">
                    </div>
                    
                    <div class="form-group">
                        <label for="capacity">Class Capacity *</label>
                        <input type="number" id="capacity" name="capacity" min="1" max="100" value="20" required>
                    </div>
                    
                    <button type="submit" class="btn">Add Class</button>
                </form>
            </div>
        </div>

        <footer>
            <p>&copy; 2026 FitZone Gym Management System | All Rights Reserved</p>
        </footer>
    </div>
</body>
</html>

<?php $conn->close(); ?>
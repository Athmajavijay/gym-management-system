<?php
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Get all members
$members_query = "SELECT member_id, full_name, email FROM members WHERE status = 'Active' ORDER BY full_name";
$members = $conn->query($members_query);

// Get all classes with available spots
$classes_query = "SELECT c.class_id, c.class_name, c.schedule, c.capacity, c.current_enrolled, t.full_name as trainer_name
                  FROM classes c
                  JOIN trainers t ON c.trainer_id = t.trainer_id
                  ORDER BY c.class_name";
$classes = $conn->query($classes_query);

// Handle enrollment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = $_POST['member_id'];
    $class_id = $_POST['class_id'];
    $enrollment_date = date('Y-m-d');
    
    if (!empty($member_id) && !empty($class_id)) {
        // Check if already enrolled
        $check = $conn->prepare("SELECT enrollment_id FROM enrollments WHERE member_id = ? AND class_id = ?");
        $check->bind_param("ii", $member_id, $class_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "This member is already enrolled in this class!";
        } else {
            // Check class capacity
            $capacity_check = $conn->prepare("SELECT capacity, current_enrolled FROM classes WHERE class_id = ?");
            $capacity_check->bind_param("i", $class_id);
            $capacity_check->execute();
            $class_info = $capacity_check->get_result()->fetch_assoc();
            
            if ($class_info['current_enrolled'] >= $class_info['capacity']) {
                $error_message = "This class is full! Cannot enroll.";
            } else {
                // Insert enrollment
                $stmt = $conn->prepare("INSERT INTO enrollments (member_id, class_id, enrollment_date) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $member_id, $class_id, $enrollment_date);
                
                if ($stmt->execute()) {
                    // Update class enrollment count
                    $update = $conn->prepare("UPDATE classes SET current_enrolled = current_enrolled + 1 WHERE class_id = ?");
                    $update->bind_param("i", $class_id);
                    $update->execute();
                    
                    $success_message = "Member enrolled successfully!";
                    $_POST = array();
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
            $capacity_check->close();
        }
        $check->close();
    } else {
        $error_message = "Please select both member and class!";
    }
}

// Get all current enrollments
$enrollments_query = "SELECT e.enrollment_id, e.enrollment_date, e.status,
                      m.full_name as member_name, m.email,
                      c.class_name, c.schedule,
                      t.full_name as trainer_name
                      FROM enrollments e
                      JOIN members m ON e.member_id = m.member_id
                      JOIN classes c ON e.class_id = c.class_id
                      JOIN trainers t ON c.trainer_id = t.trainer_id
                      ORDER BY e.enrollment_date DESC";
$enrollments = $conn->query($enrollments_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Members - FitZone Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏋️ FitZone Gym Management</h1>
            <p>Enroll Members in Classes</p>
        </header>

        <nav>
            <ul>
                <li><a href="../index.php">🏠 Home</a></li>
                <li><a href="../members/add_member.php">➕ Add Member</a></li>
                <li><a href="../members/view_members.php">👥 View Members</a></li>
                <li><a href="../trainers/add_trainer.php">➕ Add Trainer</a></li>
                <li><a href="../trainers/view_trainers.php">💪 View Trainers</a></li>
                <li><a href="../classes/add_class.php">➕ Add Class</a></li>
                <li><a href="../classes/view_classes.php">📅 View Classes</a></li>
                <li><a href="../enrollments/enroll.php">📝 Enroll</a></li>
            </ul>
        </nav>

        <div class="content">
            <h2>Enroll Member in Class</h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
                <!-- Enrollment Form -->
                <div class="card">
                    <h3>New Enrollment</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="member_id">Select Member *</label>
                            <select id="member_id" name="member_id" required>
                                <option value="">Choose a member...</option>
                                <?php 
                                $members->data_seek(0);
                                while ($member = $members->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $member['member_id']; ?>">
                                        <?php echo htmlspecialchars($member['full_name']) . ' (' . htmlspecialchars($member['email']) . ')'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="class_id">Select Class *</label>
                            <select id="class_id" name="class_id" required>
                                <option value="">Choose a class...</option>
                                <?php 
                                $classes->data_seek(0);
                                while ($class = $classes->fetch_assoc()): 
                                    $available = $class['capacity'] - $class['current_enrolled'];
                                    $status = $available > 0 ? "✓ Available: $available spots" : "✗ FULL";
                                ?>
                                    <option value="<?php echo $class['class_id']; ?>" <?php echo $available <= 0 ? 'disabled' : ''; ?>>
                                        <?php echo htmlspecialchars($class['class_name']) . ' - ' . htmlspecialchars($class['schedule']) . ' (' . $status . ')'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn">Enroll Member</button>
                    </form>
                </div>
                
                <!-- Quick Stats -->
                <div class="card">
                    <h3>Enrollment Statistics</h3>
                    <div style="padding: 20px 0;">
                        <?php
                        $total_enrollments = $conn->query("SELECT COUNT(*) as count FROM enrollments")->fetch_assoc()['count'];
                        $active_enrollments = $conn->query("SELECT COUNT(*) as count FROM enrollments WHERE status = 'Active'")->fetch_assoc()['count'];
                        $total_members = $conn->query("SELECT COUNT(*) as count FROM members")->fetch_assoc()['count'];
                        $total_classes = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];
                        ?>
                        <div style="margin-bottom: 20px;">
                            <strong style="font-size: 2em; color: #667eea;"><?php echo $total_enrollments; ?></strong>
                            <p style="color: #666;">Total Enrollments</p>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <strong style="font-size: 2em; color: #48bb78;"><?php echo $active_enrollments; ?></strong>
                            <p style="color: #666;">Active Enrollments</p>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <strong style="font-size: 1.5em; color: #666;">
                                <?php echo $total_members; ?> Members / <?php echo $total_classes; ?> Classes
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Current Enrollments Table -->
            <div class="card">
                <h3>All Enrollments (<?php echo $enrollments->num_rows; ?>)</h3>
                
                <?php if ($enrollments->num_rows > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Member</th>
                                    <th>Email</th>
                                    <th>Class</th>
                                    <th>Schedule</th>
                                    <th>Trainer</th>
                                    <th>Enrolled Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($enrollment = $enrollments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $enrollment['enrollment_id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($enrollment['member_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($enrollment['email']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['class_name']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['schedule']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['trainer_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($enrollment['status']); ?>">
                                                <?php echo $enrollment['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No enrollments yet. Start enrolling members in classes!
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <footer>
            <p>&copy; 2026 FitZone Gym Management System | All Rights Reserved</p>
        </footer>
    </div>
</body>
</html>

<?php $conn->close(); ?>
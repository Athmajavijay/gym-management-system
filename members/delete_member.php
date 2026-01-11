<?php
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle deletion
if (isset($_GET['id']) && isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $member_id = intval($_GET['id']);
    
    // Delete member (this will also delete their enrollments due to CASCADE)
    $stmt = $conn->prepare("DELETE FROM members WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    
    if ($stmt->execute()) {
        $success_message = "Member deleted successfully!";
    } else {
        $error_message = "Error deleting member: " . $stmt->error;
    }
    $stmt->close();
}

// Get member details if ID is provided
$member = null;
if (isset($_GET['id']) && !isset($_GET['confirm'])) {
    $member_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();
    $stmt->close();
    
    // Get member's enrollments
    $enrollments_query = $conn->prepare("SELECT c.class_name FROM enrollments e JOIN classes c ON e.class_id = c.class_id WHERE e.member_id = ?");
    $enrollments_query->bind_param("i", $member_id);
    $enrollments_query->execute();
    $enrollments = $enrollments_query->get_result();
}

// Get all members for listing
$all_members = $conn->query("SELECT * FROM members ORDER BY full_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Member - FitZone Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏋️ FitZone Gym Management</h1>
            <p>Delete Member</p>
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
            <h2>Delete Member</h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                    <br><a href="view_members.php">← Back to Members List</a>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if ($member && !$success_message): ?>
                <!-- Confirmation -->
                <div class="card" style="max-width: 600px; margin: 0 auto; border: 2px solid #e53e3e;">
                    <h3 style="color: #e53e3e;">⚠️ Confirm Deletion</h3>
                    <p><strong>Are you sure you want to delete this member?</strong></p>
                    
                    <div style="background: #f7fafc; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($member['full_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($member['email']); ?></p>
                        <p><strong>Membership:</strong> <?php echo $member['membership_type']; ?></p>
                        <p><strong>Join Date:</strong> <?php echo date('M d, Y', strtotime($member['join_date'])); ?></p>
                    </div>
                    
                    <?php if ($enrollments->num_rows > 0): ?>
                        <div class="alert alert-error">
                            <strong>Warning:</strong> This member is enrolled in <?php echo $enrollments->num_rows; ?> class(es):
                            <ul style="margin: 10px 0 0 20px;">
                                <?php while ($enroll = $enrollments->fetch_assoc()): ?>
                                    <li><?php echo htmlspecialchars($enroll['class_name']); ?></li>
                                <?php endwhile; ?>
                            </ul>
                            <p>All enrollments will also be deleted!</p>
                        </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <a href="delete_member.php?id=<?php echo $member['member_id']; ?>&confirm=yes" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you absolutely sure? This cannot be undone!');">
                            Yes, Delete Member
                        </a>
                        <a href="view_members.php" class="btn">Cancel</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Select member to delete -->
                <div class="card">
                    <h3>Select Member to Delete</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Membership</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($m = $all_members->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $m['member_id']; ?></td>
                                        <td><?php echo htmlspecialchars($m['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($m['email']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($m['membership_type']); ?>">
                                                <?php echo $m['membership_type']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="delete_member.php?id=<?php echo $m['member_id']; ?>" 
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
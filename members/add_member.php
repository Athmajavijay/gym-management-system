<?php
require_once '../includes/db_connect.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $membership_type = $_POST['membership_type'];
    $join_date = date('Y-m-d');
    
    // Validate input
    if (!empty($full_name) && !empty($email)) {
        // Check if email already exists
        $check = $conn->prepare("SELECT member_id FROM members WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Email already exists! Please use a different email.";
        } else {
            // Insert new member
            $stmt = $conn->prepare("INSERT INTO members (full_name, email, phone, membership_type, join_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $full_name, $email, $phone, $membership_type, $join_date);
            
            if ($stmt->execute()) {
                $success_message = "Member added successfully!";
                // Clear form
                $_POST = array();
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
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
    <title>Add Member - FitZone Gym</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏋️ FitZone Gym Management</h1>
            <p>Add New Member</p>
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
            <h2>Add New Member</h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="555-1234">
                    </div>
                    
                    <div class="form-group">
                        <label for="membership_type">Membership Type *</label>
                        <select id="membership_type" name="membership_type" required>
                            <option value="Basic">Basic - $29/month</option>
                            <option value="Premium">Premium - $59/month</option>
                            <option value="VIP">VIP - $99/month</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Add Member</button>
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
<?php

// Include database connection
require_once 'includes/db_connect.php';

// Get statistics from database
$total_members = $conn->query("SELECT COUNT(*) as count FROM members")->fetch_assoc()['count'];
$total_trainers = $conn->query("SELECT COUNT(*) as count FROM trainers")->fetch_assoc()['count'];
$total_classes = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];
$total_enrollments = $conn->query("SELECT COUNT(*) as count FROM enrollments")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitZone Gym Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <h1>🏋️ FitZone Gym Management</h1>
            <p>Your Premier Fitness Destination</p>
        </header>

        <!-- Navigation -->
        <nav>
            <ul>
                <li><a href="index.php">🏠 Home</a></li>
                <li><a href="members/add_member.php">➕ Add Member</a></li>
                <li><a href="members/view_members.php">👥 View Members</a></li>
                <li><a href="trainers/add_trainer.php">➕ Add Trainer</a></li>
                <li><a href="trainers/view_trainers.php">💪 View Trainers</a></li>
                <li><a href="classes/add_class.php">➕ Add Class</a></li>
                <li><a href="classes/view_classes.php">📅 View Classes</a></li>
                <li><a href="enrollments/enroll.php">📝 Enroll</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <h2>Dashboard Overview</h2>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3><?php echo $total_members; ?></h3>
                    <p>Total Members</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_trainers; ?></h3>
                    <p>Total Trainers</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_classes; ?></h3>
                    <p>Total Classes</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_enrollments; ?></h3>
                    <p>Total Enrollments</p>
                </div>
            </div>

            <!-- About Section -->
            <div class="card">
                <h3>About FitZone Gym</h3>
                <p>Welcome to FitZone, your premier fitness destination! We offer state-of-the-art equipment, expert trainers, and a variety of classes designed to help you reach your fitness goals.</p>
                
                <h3 style="margin-top: 20px;">Our Features</h3>
                <ul style="line-height: 2; margin-left: 20px;">
                    <li>✅ Expert certified trainers</li>
                    <li>✅ Wide variety of fitness classes</li>
                    <li>✅ Flexible membership plans</li>
                    <li>✅ Modern equipment and facilities</li>
                    <li>✅ Personalized training programs</li>
                </ul>
            </div>

            <!-- Recent Activities -->
            <div class="card">
                <h3>Recent Enrollments</h3>
                <?php
                $recent = $conn->query("
                    SELECT m.full_name, c.class_name, e.enrollment_date 
                    FROM enrollments e
                    JOIN members m ON e.member_id = m.member_id
                    JOIN classes c ON e.class_id = c.class_id
                    ORDER BY e.enrollment_date DESC
                    LIMIT 5
                ");
                
                if ($recent->num_rows > 0) {
                    echo "<div class='table-container'><table>";
                    echo "<thead><tr><th>Member</th><th>Class</th><th>Date</th></tr></thead><tbody>";
                    while ($row = $recent->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['class_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['enrollment_date']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                } else {
                    echo "<p>No recent enrollments.</p>";
                }
                ?>
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <p>&copy; 2026 FitZone Gym Management System | All Rights Reserved</p>
        </footer>
    </div>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
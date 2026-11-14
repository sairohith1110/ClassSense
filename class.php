

<?php
session_start();

require_once "includes/db.php";

/* CHECK LOGIN */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];
$user_role = $_SESSION["user_role"];

/* CHECK CLASS ID */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid class ID.");
}

$class_id = (int) $_GET["id"];

/* GET CLASS DETAILS */
$sql = "SELECT classes.*, users.name AS teacher_name
        FROM classes
        JOIN users ON classes.teacher_id = users.id
        WHERE classes.id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed.");
}

mysqli_stmt_bind_param($stmt, "i", $class_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    die("Class not found.");
}

$class = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/* CHECK USER ROLE AND CLASS ACCESS */

$is_teacher = false;
$is_student = false;

if ($user_role === "teacher") {

    // Teacher can access only their own classes
    if ((int) $class["teacher_id"] !== $user_id) {
        die("You are not authorized to access this class.");
    }

    $is_teacher = true;
    $dashboard_link = "teacher_dashboard.php";

} elseif ($user_role === "student") {

    // Student must be enrolled in this class
    $check_sql = "SELECT id
                  FROM enrollments
                  WHERE student_id = ? AND class_id = ?";

    $check_stmt = mysqli_prepare($conn, $check_sql);

    if (!$check_stmt) {
        die("Enrollment check failed.");
    }

    mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $class_id);
    mysqli_stmt_execute($check_stmt);

    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) === 0) {
        die("You are not enrolled in this class.");
    }

    mysqli_stmt_close($check_stmt);

    $is_student = true;
    $dashboard_link = "student_dashboard.php";

} else {

    die("Invalid user role.");
}

/* GET CLASS ANNOUNCEMENTS */
$post_sql = "SELECT posts.title,
                    posts.content,
                    posts.created_at,
                    users.name AS teacher_name
             FROM posts
             JOIN users ON posts.teacher_id = users.id
             WHERE posts.class_id = ?
             ORDER BY posts.created_at DESC";

$post_stmt = mysqli_prepare($conn, $post_sql);

if (!$post_stmt) {
    die("Could not load class announcements.");
}

mysqli_stmt_bind_param($post_stmt, "i", $class_id);
mysqli_stmt_execute($post_stmt);

$post_result = mysqli_stmt_get_result($post_stmt);
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Open Class - ClassSense</title>

    <link rel="stylesheet" href="css/class.css">

</head>

<body>

<div class="class-page">

    <!-- NAVIGATION -->

    <nav class="class-nav">

        <h2>🎓 Class<span>Sense</span></h2>

        <div>
          <a href="<?php echo htmlspecialchars($dashboard_link); ?>">Dashboard</a>
            <a href="my_classes.php">My Classes</a>
            <a href="logout.php">Logout</a>
        </div>

    </nav>


    <!-- BREADCRUMB -->

    <div class="breadcrumb">

        <a href="student_dashboard.php">Dashboard</a>
        /
        <a href="my_classes.php">My Classes</a>
        / Open Class

    </div>


    <!-- CLASS INFORMATION -->

    <section class="class-header">

        <h1>
            📚 <?php echo htmlspecialchars($class["class_name"]); ?>
        </h1>

        <p>
            Welcome to your classroom.
            <?php echo htmlspecialchars($class["subject"]); ?>
        </p>

        <p>
            👨‍🏫 Teacher:
            <?php echo htmlspecialchars($class["teacher_name"]); ?>
        </p>

        <p>
            📅 Day:
            <?php echo htmlspecialchars($class["day_of_week"]); ?>
        </p>

        <p>
            🕒 Start Time:
            <?php echo htmlspecialchars($class["start_time"]); ?>
        </p>

        <div class="class-code">

            Class Code:
            <?php echo htmlspecialchars($class["class_code"]); ?>

        </div>

    </section>


    <!-- CLASS ACTIONS -->

    <div class="class-actions">

        <?php if ($is_teacher): ?>

            <div class="action-card">

                <h3>📝 Create Announcement</h3>

                <p>
                    Share important information, notes and updates
                    with your students.
                </p>

                <a href="create_post.php?id=<?php echo $class_id; ?>">
                    Create Post →
                </a>

            </div>


            <div class="action-card">

                <h3>📊 Student Understanding</h3>

                <p>
                    Monitor student responses and understand
                    which topics need more explanation.
                </p>

                <a href="teacher_signals.php?id=<?php echo $class_id; ?>">
                    View Understanding →
                </a>

            </div>

        <?php endif; ?>


        <?php if ($is_student): ?>

            <div class="action-card">

                <h3>💬 Learning Feedback</h3>

                <p>
                    Tell your teacher how well you understand
                    today's classroom topic.
                </p>

                <a href="signal.php?id=<?php echo $class_id; ?>">
                    Give Feedback →
                </a>

            </div>

            <div class="action-card">

                <h3>📢 Class Announcements</h3>

                <p>
                    Check the latest classroom announcements
                    and important updates from your teacher.
                </p>

                <a href="#announcements">
                    View Announcements →
                </a>

            </div>

        <?php endif; ?>

    </div>


    <!-- CLASS UNDERSTANDING -->

    <section class="class-section">

        <h2>🧠 Class Understanding</h2>

        <p>
            ClassSense helps teachers understand students'
            learning progress through simple feedback.
        </p>

        <?php if ($is_teacher): ?>

            <p style="margin-top:15px; color:#777;">
                View your students' understanding signals
                to identify topics that need additional explanation.
            </p>

        <?php elseif ($is_student): ?>

            <p style="margin-top:15px; color:#777;">
                Your feedback helps your teacher improve
                the classroom learning experience.
            </p>

        <?php endif; ?>

    </section>


    <!-- ANNOUNCEMENTS -->

    <section class="class-section" id="announcements">

        <h2>📢 Class Announcements</h2>

        <?php if (mysqli_num_rows($post_result) > 0): ?>

            <?php while ($post = mysqli_fetch_assoc($post_result)): ?>

                <div class="announcement">

                    <h3>
                        <?php echo htmlspecialchars($post["title"]); ?>
                    </h3>

                    <p>
                        <?php echo nl2br(htmlspecialchars($post["content"])); ?>
                    </p>

                    <small>
                        Posted by
                        <?php echo htmlspecialchars($post["teacher_name"]); ?>

                        |

                        <?php echo htmlspecialchars($post["created_at"]); ?>
                    </small>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p style="color:#888; padding:10px 0;">
                📭 No announcements yet.
                Check back later for classroom updates.
            </p>

        <?php endif; ?>

    </section>


    <!-- BACK BUTTON -->

    <a href="my_classes.php" class="back-link">
        ← Back to My Classes
    </a>

</div>

</body>
</html>

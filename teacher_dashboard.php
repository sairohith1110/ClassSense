

<?php
session_start();

require_once "includes/db.php";

/* CHECK LOGIN */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

/* ONLY TEACHERS CAN ACCESS */
if ($_SESSION["user_role"] !== "teacher") {
    header("Location: student_dashboard.php");
    exit();
}

$teacher_id = (int) $_SESSION["user_id"];
$teacher_name = $_SESSION["user_name"];

/* GET TEACHER'S CLASSES */
$sql = "SELECT *
        FROM classes
        WHERE teacher_id = ?
        ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Could not load classes.");
}

mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$classes = [];

while ($row = mysqli_fetch_assoc($result)) {
    $classes[] = $row;
}

mysqli_stmt_close($stmt);

$total_classes = count($classes);

/* GET TODAY */
date_default_timezone_set("Asia/Kolkata");
$today = date("l");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Teacher Dashboard - ClassSense</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #20243a;
        }

        .navbar {
            background: #20243a;
            color: white;
            padding: 20px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .navbar h2 {
            margin: 0;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 18px;
        }

        .container {
            max-width: 1150px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome {
            background: linear-gradient(120deg, #5146d8, #7967ed);
            color: white;
            padding: 35px;
            border-radius: 18px;
            margin-bottom: 28px;
        }

        .welcome h1 {
            margin-top: 0;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .stat-card h2 {
            font-size: 32px;
            color: #5146d8;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .action-card {
            background: white;
            padding: 24px;
            border-radius: 15px;
            text-decoration: none;
            color: #20243a;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: 0.2s;
        }

        .action-card:hover {
            transform: translateY(-4px);
        }

        .action-card h3 {
            color: #5146d8;
        }

        .class-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 18px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .class-card h3 {
            color: #5146d8;
            margin-top: 0;
        }

        .class-card p {
            color: #60677a;
            line-height: 1.6;
        }

        .class-links a {
            display: inline-block;
            margin: 8px 8px 0 0;
            padding: 10px 16px;
            background: #5146d8;
            color: white;
            border-radius: 8px;
            text-decoration: none;
        }

        .class-links a:hover {
            background: #3930a8;
        }

        .empty {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            color: #687087;
        }

        @media (max-width: 600px) {
            .navbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .navbar a {
                margin: 0 12px 0 0;
                display: inline-block;
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>

    <header class="navbar">

        <h2>ClassSense</h2>

        <nav>
            <a href="teacher_dashboard.php">Dashboard</a>
            <a href="my_classes.php">My Classes</a>
            <a href="today_classes.php">Today's Classes</a>
            <a href="create_class.php">Create Class</a>
            <a href="logout.php">Logout</a>
        </nav>

    </header>

    <main class="container">

        <section class="welcome">

            <h1>
                Welcome, <?php echo htmlspecialchars($teacher_name); ?> 👋
            </h1>

            <p>
                Your classroom. Your students. Better understanding.
            </p>

            <p>
                Today is <?php echo htmlspecialchars($today); ?>.
            </p>

        </section>

        <section class="stats">

            <div class="stat-card">
                <h3>Total Classes Created</h3>

                <h2>
                    <?php echo $total_classes; ?>
                </h2>

                <p>Your classroom overview</p>
            </div>

        </section>

        <section class="actions">

            <a href="create_class.php" class="action-card">
                <h3>➕ Create Class</h3>
                <p>Create a new classroom for your students.</p>
            </a>

            <a href="my_classes.php" class="action-card">
                <h3>📚 My Classes</h3>
                <p>View and manage your classrooms.</p>
            </a>

            <a href="today_classes.php" class="action-card">
                <h3>📅 Today's Classes</h3>
                <p>Check your scheduled classes.</p>
            </a>

        </section>

        <h2>Your Created Classes</h2>

        <?php if ($total_classes > 0): ?>

            <?php foreach ($classes as $class): ?>

                <div class="class-card">

                    <h3>
                        <?php echo htmlspecialchars($class["class_name"]); ?>
                    </h3>

                    <p>
                        <strong>Subject:</strong>
                        <?php echo htmlspecialchars($class["subject"]); ?>
                    </p>

                    <p>
                        <strong>Class Code:</strong>
                        <?php echo htmlspecialchars($class["class_code"]); ?>
                    </p>

                    <p>
                        <strong>Day:</strong>
                        <?php echo htmlspecialchars($class["day_of_week"]); ?>
                    </p>

                    <p>
                        <strong>Time:</strong>
                        <?php echo htmlspecialchars($class["start_time"]); ?>
                    </p>

                    <div class="class-links">

                        <a href="class.php?id=<?php echo (int) $class["id"]; ?>">
                            Open Class
                        </a>

                        <a href="teacher_signals.php?id=<?php echo (int) $class["id"]; ?>">
                            Student Feedback
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty">

                <h3>No classes created yet.</h3>

                <p>Create your first class to get started.</p>

                <a href="create_class.php">
                    Create Your First Class
                </a>

            </div>

        <?php endif; ?>

    </main>

</body>
</html>

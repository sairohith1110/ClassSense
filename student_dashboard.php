

<?php
session_start();

require_once "includes/db.php";

/* CHECK LOGIN */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

/* ONLY STUDENTS */
if ($_SESSION["user_role"] !== "student") {
    header("Location: teacher_dashboard.php");
    exit();
}

$student_id = (int) $_SESSION["user_id"];
$student_name = $_SESSION["user_name"];

/* COUNT JOINED CLASSES */
$sql = "SELECT COUNT(*) AS total
        FROM enrollments
        WHERE student_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

$total_classes = (int) $data["total"];

mysqli_stmt_close($stmt);

/* GET RECENT CLASSES */
$sql = "SELECT classes.*
        FROM enrollments
        JOIN classes ON enrollments.class_id = classes.id
        WHERE enrollments.student_id = ?
        ORDER BY classes.id DESC
        LIMIT 3";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

$class_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard - ClassSense</title>

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
            margin-left: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 45px auto;
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card, .action-card, .class-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .stat-card h2 {
            color: #5146d8;
            font-size: 32px;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .action-card {
            text-decoration: none;
            color: #20243a;
            transition: 0.2s;
        }

        .action-card:hover {
            transform: translateY(-4px);
        }

        .action-card h3 {
            color: #5146d8;
        }

        .class-card {
            margin-bottom: 15px;
        }

        .class-card a {
            display: inline-block;
            background: #5146d8;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
        }

        .empty {
            color: #687087;
        }

        @media (max-width: 600px) {
            .navbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .navbar a {
                margin-left: 0;
                margin-right: 12px;
            }
        }
    </style>
</head>

<body>

    <header class="navbar">

        <h2>ClassSense</h2>

        <nav>
            <a href="student_dashboard.php">Dashboard</a>
            <a href="my_classes.php">My Classes</a>
            <a href="today_classes.php">Today's Classes</a>
            <a href="join_class.php">Join Class</a>
            <a href="logout.php">Logout</a>
        </nav>

    </header>

    <main class="container">

        <section class="welcome">
            <h1>
                Welcome, <?php echo htmlspecialchars($student_name); ?> 👋
            </h1>

            <p>Your classroom. Your understanding. Your progress.</p>
        </section>

        <section class="stats">

            <div class="stat-card">
                <h3>My Classes</h3>
                <h2><?php echo $total_classes; ?></h2>
                <p>Classes you have joined</p>
            </div>

        </section>

        <section class="actions">

            <a href="join_class.php" class="action-card">
                <h3>➕ Join a Class</h3>
                <p>Enter your teacher's class code.</p>
            </a>

            <a href="my_classes.php" class="action-card">
                <h3>📚 My Classes</h3>
                <p>View all your enrolled classes.</p>
            </a>

            <a href="today_classes.php" class="action-card">
                <h3>📅 Today's Classes</h3>
                <p>See your scheduled classes.</p>
            </a>

        </section>

        <h2>Recently Joined Classes</h2>

        <?php if (mysqli_num_rows($class_result) > 0): ?>

            <?php while ($class = mysqli_fetch_assoc($class_result)): ?>

                <div class="class-card">

                    <h3>
                        <?php echo htmlspecialchars($class["class_name"]); ?>
                    </h3>

                    <p>
                        Subject:
                        <?php echo htmlspecialchars($class["subject"]); ?>
                    </p>

                    <a href="class.php?id=<?php echo (int) $class["id"]; ?>">
                        Open Class
                    </a>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p class="empty">
                You haven't joined any classes yet. Join a class to get started!
            </p>

        <?php endif; ?>

    </main>

</body>
</html>
<?php
mysqli_stmt_close($stmt);
?>

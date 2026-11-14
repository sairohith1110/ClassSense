

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

date_default_timezone_set("Asia/Kolkata");
$today = date("l");

$classes = [];

/* TEACHER TODAY'S CLASSES */
if ($user_role === "teacher") {

    $sql = "SELECT classes.*, users.name AS teacher_name
            FROM classes
            JOIN users ON classes.teacher_id = users.id
            WHERE classes.teacher_id = ?
            AND LOWER(classes.day_of_week) = LOWER(?)
            ORDER BY classes.start_time ASC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $today);

/* STUDENT TODAY'S CLASSES */
} elseif ($user_role === "student") {

    $sql = "SELECT classes.*, users.name AS teacher_name
            FROM enrollments
            JOIN classes ON enrollments.class_id = classes.id
            JOIN users ON classes.teacher_id = users.id
            WHERE enrollments.student_id = ?
            AND LOWER(classes.day_of_week) = LOWER(?)
            ORDER BY classes.start_time ASC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $today);

} else {
    die("Invalid user role.");
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $classes[] = $row;
}

mysqli_stmt_close($stmt);

$dashboard_link = ($user_role === "teacher")
    ? "teacher_dashboard.php"
    : "student_dashboard.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Classes - ClassSense</title>

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

        .header {
            background: #20243a;
            color: white;
            padding: 20px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header a {
            color: white;
            text-decoration: none;
            margin-left: 18px;
        }

        .container {
            max-width: 1000px;
            margin: 45px auto;
            padding: 0 20px;
        }

        .container h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #687087;
            margin-bottom: 28px;
        }

        .class-card {
            background: white;
            padding: 25px;
            margin-bottom: 18px;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        }

        .class-card h2 {
            margin-top: 0;
            color: #5146d8;
        }

        .class-card p {
            color: #555d72;
            line-height: 1.7;
        }

        .open-button {
            display: inline-block;
            padding: 11px 20px;
            background: #5146d8;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 10px;
        }

        .empty {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 14px;
            color: #687087;
        }
    </style>
</head>

<body>

    <header class="header">
        <h2>ClassSense</h2>

        <nav>
            <a href="<?php echo htmlspecialchars($dashboard_link); ?>">
                Dashboard
            </a>

            <a href="my_classes.php">My Classes</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="container">

        <h1>Today's Classes 📚</h1>

        <p class="subtitle">
            Your scheduled classes for <?php echo htmlspecialchars($today); ?>.
        </p>

        <?php if (count($classes) > 0): ?>

            <?php foreach ($classes as $class): ?>

                <div class="class-card">

                    <h2>
                        <?php echo htmlspecialchars($class["class_name"]); ?>
                    </h2>

                    <p>
                        <strong>Subject:</strong>
                        <?php echo htmlspecialchars($class["subject"]); ?>
                    </p>

                    <p>
                        <strong>Teacher:</strong>
                        <?php echo htmlspecialchars($class["teacher_name"]); ?>
                    </p>

                    <p>
                        <strong>Time:</strong>
                        <?php echo htmlspecialchars($class["start_time"]); ?>
                    </p>

                    <a
                        class="open-button"
                        href="class.php?id=<?php echo (int) $class["id"]; ?>">
                        Open Class
                    </a>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty">
                <h2>No classes scheduled today.</h2>
                <p>Check My Classes for your other scheduled classes.</p>
            </div>

        <?php endif; ?>

    </main>

</body>
</html>

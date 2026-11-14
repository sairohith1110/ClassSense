

<?php
session_start();

require_once "includes/db.php";

/* CHECK LOGIN */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

/* ONLY STUDENTS CAN JOIN */
if ($_SESSION["user_role"] !== "student") {
    die("Only students can join classes.");
}

$student_id = (int) $_SESSION["user_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $class_code = strtoupper(trim($_POST["class_code"] ?? ""));

    if ($class_code === "") {

        $message = "Please enter a class code.";

    } else {

        /* FIND CLASS */
        $class_sql = "SELECT id, class_name
                      FROM classes
                      WHERE class_code = ?";

        $class_stmt = mysqli_prepare($conn, $class_sql);
        mysqli_stmt_bind_param($class_stmt, "s", $class_code);
        mysqli_stmt_execute($class_stmt);

        $class_result = mysqli_stmt_get_result($class_stmt);

        if (mysqli_num_rows($class_result) === 0) {

            $message = "Invalid class code. Please try again.";

        } else {

            $class = mysqli_fetch_assoc($class_result);
            $class_id = (int) $class["id"];

            /* CHECK DUPLICATE ENROLLMENT */
            $check_sql = "SELECT id
                          FROM enrollments
                          WHERE student_id = ? AND class_id = ?";

            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "ii", $student_id, $class_id);
            mysqli_stmt_execute($check_stmt);

            $check_result = mysqli_stmt_get_result($check_stmt);

            if (mysqli_num_rows($check_result) > 0) {

                $message = "You have already joined this class.";

            } else {

                /* JOIN CLASS */
                $insert_sql = "INSERT INTO enrollments (student_id, class_id)
                               VALUES (?, ?)";

                $insert_stmt = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param($insert_stmt, "ii", $student_id, $class_id);

                if (mysqli_stmt_execute($insert_stmt)) {

                    header("Location: my_classes.php?joined=1");
                    exit();

                } else {

                    $message = "Unable to join class. Please try again.";
                }

                mysqli_stmt_close($insert_stmt);
            }

            mysqli_stmt_close($check_stmt);
        }

        mysqli_stmt_close($class_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Join Class - ClassSense</title>

    <link rel="stylesheet" href="css/join_class.css">
</head>

<body>

    <div class="join-class-page">

        <div class="join-class-container">

            <h1>Join a Class</h1>

            <p>Enter the class code provided by your teacher.</p>

            <?php if ($message !== ""): ?>
                <div class="message">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <input
                    type="text"
                    name="class_code"
                    placeholder="Enter Class Code"
                    maxlength="6"
                    required
                >

                <button type="submit">
                    Join Class
                </button>

            </form>

            <a href="student_dashboard.php">
                ← Back to Dashboard
            </a>

        </div>

    </div>

</body>
</html>

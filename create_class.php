
<?php

session_start();

require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["user_role"] !== "teacher") {
    die("Only teachers can create a class.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $class_name = trim($_POST["class_name"]);
    $subject = trim($_POST["subject"]);
    $day_of_week = $_POST["day_of_week"];
    $start_time = $_POST["start_time"];

    if ($class_name === "" || $subject === "") {
        $message = "Please fill all fields.";
    } else {

        $class_code = strtoupper(substr(md5(uniqid()), 0, 6));

        $teacher_id = $_SESSION["user_id"];

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO classes (teacher_id, class_name, subject, class_code, day_of_week, start_time)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "isssss",
            $teacher_id,
            $class_name,
            $subject,
            $class_code,
            $day_of_week,
            $start_time
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Class created successfully! Class Code: " . $class_code;
        } else {
            $message = "Error creating class.";
        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Class - ClassSense</title>

    <link rel="stylesheet" href="create_class.css">
</head>

<body>

    <div class="create-class-page">

        <div class="create-class-container">

            <h1>ClassSense</h1>

            <p>Create a new class and invite your students.</p>

            <h2>Create a New Class</h2>

            <?php if ($message !== ""): ?>
                <p><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form method="POST" class="create-class-form">

                <div class="form-group">
                    <label>Class Name</label>
                    <input
                        type="text"
                        name="class_name"
                        placeholder="Example: CSE-A"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <input
                        type="text"
                        name="subject"
                        placeholder="Example: Data Structures"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Day</label>

                    <select name="day_of_week" required>
                        <option value="">Select Day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Start Time</label>

                    <input
                        type="time"
                        name="start_time"
                        required
                    >
                </div>

                <button type="submit">Create Class</button>

            </form>

            <a href="student_dashboard.php" class="create-class-back">
                ← Back to Dashboard
            </a>

        </div>

    </div>

</body>

</html>


<?php
session_start();

require_once "includes/db.php";

/* CHECK LOGIN */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

/* ONLY STUDENTS CAN SUBMIT FEEDBACK */
if ($_SESSION["user_role"] !== "student") {
    die("Only students can submit understanding signals.");
}

/* CHECK CLASS ID */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid class ID.");
}

$class_id = (int) $_GET["id"];
$student_id = (int) $_SESSION["user_id"];

$message = "";

/* VERIFY STUDENT ENROLLMENT */
$enroll_sql = "SELECT id FROM enrollments
               WHERE student_id = ? AND class_id = ?";

$enroll_stmt = mysqli_prepare($conn, $enroll_sql);
mysqli_stmt_bind_param($enroll_stmt, "ii", $student_id, $class_id);
mysqli_stmt_execute($enroll_stmt);

$enroll_result = mysqli_stmt_get_result($enroll_stmt);

if (mysqli_num_rows($enroll_result) === 0) {
    die("You are not enrolled in this class.");
}

mysqli_stmt_close($enroll_stmt);

/* PROCESS FEEDBACK */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $signal_type = $_POST["signal_type"] ?? "";
    $topic = trim($_POST["topic"] ?? "");

    $allowed_signals = [
        "understand",
        "confused",
        "need_example",
        "too_fast"
    ];

    if (!in_array($signal_type, $allowed_signals, true)) {
        $message = "Invalid feedback selection.";

    } elseif ($topic === "") {
        $message = "Please enter the topic.";

    } else {

        /* CHECK EXISTING FEEDBACK */
        $check_sql = "SELECT id
                      FROM understanding_signals
                      WHERE student_id = ? AND class_id = ?";

        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ii", $student_id, $class_id);
        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {

            $existing = mysqli_fetch_assoc($check_result);
            $signal_id = (int) $existing["id"];

            /* UPDATE FEEDBACK */
            $update_sql = "UPDATE understanding_signals
                           SET signal_type = ?,
                               topic = ?,
                               created_at = CURRENT_TIMESTAMP
                           WHERE id = ? AND student_id = ? AND class_id = ?";

            $update_stmt = mysqli_prepare($conn, $update_sql);

            mysqli_stmt_bind_param(
                $update_stmt,
                "ssiii",
                $signal_type,
                $topic,
                $signal_id,
                $student_id,
                $class_id
            );

            if (mysqli_stmt_execute($update_stmt)) {
                $message = "Your response has been updated!";
            } else {
                $message = "Could not update your response.";
            }

            mysqli_stmt_close($update_stmt);

        } else {

            /* INSERT FIRST FEEDBACK */
            $insert_sql = "INSERT INTO understanding_signals
                           (student_id, class_id, signal_type, topic)
                           VALUES (?, ?, ?, ?)";

            $insert_stmt = mysqli_prepare($conn, $insert_sql);

            mysqli_stmt_bind_param(
                $insert_stmt,
                "iiss",
                $student_id,
                $class_id,
                $signal_type,
                $topic
            );

            if (mysqli_stmt_execute($insert_stmt)) {
                $message = "Your response has been recorded!";
            } else {
                $message = "Could not record your response.";
            }

            mysqli_stmt_close($insert_stmt);
        }

        mysqli_stmt_close($check_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ClassSense - Understanding</title>

    <link rel="stylesheet" href="css/signal.css">
</head>

<body>

    <div class="feedback-page">

        <div class="feedback-container">

            <h1>ClassSense</h1>

            <p class="feedback-subtitle">
                How are you following the class?
                Tell your teacher how well you understand the topic.
            </p>

            <?php if ($message !== ""): ?>
                <p class="feedback-message">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            <?php endif; ?>

            <form method="POST">

                <label for="topic">What topic are you learning?</label>

                <input
                    type="text"
                    id="topic"
                    name="topic"
                    placeholder="Example: Linked Lists"
                    required
                >

                <div class="feedback-options">

                    <button
                        type="submit"
                        name="signal_type"
                        value="understand"
                        class="feedback-option">
                        ✅ I Understand
                    </button>

                    <button
                        type="submit"
                        name="signal_type"
                        value="confused"
                        class="feedback-option">
                        😕 I'm Confused
                    </button>

                    <button
                        type="submit"
                        name="signal_type"
                        value="need_example"
                        class="feedback-option">
                        💡 I Need an Example
                    </button>

                    <button
                        type="submit"
                        name="signal_type"
                        value="too_fast"
                        class="feedback-option">
                        ⏩ The Class Is Too Fast
                    </button>

                </div>

            </form>

            <a
                href="class.php?id=<?php echo $class_id; ?>"
                class="feedback-back">
                ← Back to Class
            </a>

        </div>

    </div>

</body>
</html>

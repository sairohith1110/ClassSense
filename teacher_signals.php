
<?php
session_start();
require_once "includes/db.php";

/* =========================
   LOGIN CHECK
========================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["user_role"] !== "teacher") {
    die("Only teachers can view this dashboard.");
}

/* =========================
   GET CLASS ID
========================= */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Class ID missing. Please open a class from My Classes.");
}

$class_id = (int) $_GET["id"];
$teacher_id = (int) $_SESSION["user_id"];

/* =========================
   VERIFY CLASS OWNER
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT class_name, subject, class_code
     FROM classes
     WHERE id = ? AND teacher_id = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $class_id, $teacher_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    die("Class not found or you are not the teacher of this class.");
}

$class = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

/* =========================
   STUDENT COUNT
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM enrollments
     WHERE class_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $class_id);
mysqli_stmt_execute($stmt);

$student_count = (int) mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)["total"];

mysqli_stmt_close($stmt);

/* =========================
   SIGNAL COUNTS
========================= */

$understand = 0;
$confused = 0;
$need_example = 0;
$too_fast = 0;

$stmt = mysqli_prepare(
    $conn,
    "SELECT signal_type, COUNT(*) AS total
     FROM understanding_signals
     WHERE class_id = ?
     GROUP BY signal_type"
);

mysqli_stmt_bind_param($stmt, "i", $class_id);
mysqli_stmt_execute($stmt);

$count_result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($count_result)) {

    switch ($row["signal_type"]) {

        case "understand":
            $understand = (int) $row["total"];
            break;

        case "confused":
            $confused = (int) $row["total"];
            break;

        case "need_example":
            $need_example = (int) $row["total"];
            break;

        case "too_fast":
            $too_fast = (int) $row["total"];
            break;
    }
}

mysqli_stmt_close($stmt);

/* =========================
   CALCULATIONS
========================= */

$total_responses =
    $understand +
    $confused +
    $need_example +
    $too_fast;

$help_needed =
    $confused +
    $need_example +
    $too_fast;

/* Unique students who submitted feedback */

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(DISTINCT student_id) AS total
     FROM understanding_signals
     WHERE class_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $class_id);
mysqli_stmt_execute($stmt);

$responded_students = (int) mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)["total"];

mysqli_stmt_close($stmt);

$response_rate = $student_count > 0
    ? round(($responded_students / $student_count) * 100)
    : 0;

/* Percentages */

$understand_percent = $total_responses > 0
    ? round(($understand / $total_responses) * 100)
    : 0;

$confused_percent = $total_responses > 0
    ? round(($confused / $total_responses) * 100)
    : 0;

$need_example_percent = $total_responses > 0
    ? round(($need_example / $total_responses) * 100)
    : 0;

$too_fast_percent = $total_responses > 0
    ? round(($too_fast / $total_responses) * 100)
    : 0;

/* =========================
   CLASS STATUS
========================= */

if ($total_responses === 0) {

    $class_status = "Waiting for student responses";
    $status_class = "waiting";

} elseif ($understand_percent >= 70) {

    $class_status = "Class is going well";
    $status_class = "good";

} elseif (($confused_percent + $need_example_percent + $too_fast_percent) >= 50) {

    $class_status = "Many students need help";
    $status_class = "warning";

} else {

    $class_status = "Some students may need help";
    $status_class = "normal";
}

/* =========================
   TEACHER RECOMMENDATION
========================= */

if ($total_responses === 0) {

    $recommendation = "Wait for students to submit their understanding.";

} elseif ($confused_percent >= 40) {

    $recommendation = "Many students are confused. Explain the topic again using a simple example.";

} elseif ($need_example_percent >= 30) {

    $recommendation = "Students need examples. Give a practical example of the topic.";

} elseif ($too_fast_percent >= 30) {

    $recommendation = "Students feel the class is too fast. Slow down and explain the current concept again.";

} elseif ($understand_percent >= 70) {

    $recommendation = "Most students understand. You can continue to the next concept.";

} else {

    $recommendation = "Some students may need additional explanation. Check their questions.";
}

/* =========================
   TOP DIFFICULT TOPICS
========================= */

$topic_stmt = mysqli_prepare(
    $conn,
    "SELECT topic, COUNT(*) AS difficulty_count
     FROM understanding_signals
     WHERE class_id = ?
     AND signal_type IN ('confused', 'need_example', 'too_fast')
     AND topic IS NOT NULL
     AND topic != ''
     GROUP BY topic
     ORDER BY difficulty_count DESC"
);

mysqli_stmt_bind_param($topic_stmt, "i", $class_id);
mysqli_stmt_execute($topic_stmt);

$topic_result = mysqli_stmt_get_result($topic_stmt);

$topics = [];

while ($topic = mysqli_fetch_assoc($topic_result)) {
    $topics[] = $topic;
}

mysqli_stmt_close($topic_stmt);

/* =========================
   STUDENT LIST
========================= */

$student_stmt = mysqli_prepare(
    $conn,
    "SELECT users.name, users.email
     FROM enrollments
     INNER JOIN users
     ON enrollments.student_id = users.id
     WHERE enrollments.class_id = ?
     ORDER BY users.name"
);

mysqli_stmt_bind_param($student_stmt, "i", $class_id);
mysqli_stmt_execute($student_stmt);

$student_result = mysqli_stmt_get_result($student_stmt);

$students = [];

while ($student = mysqli_fetch_assoc($student_result)) {
    $students[] = $student;
}

mysqli_stmt_close($student_stmt);

/* =========================
   RECENT STUDENT SIGNALS
========================= */

$signal_stmt = mysqli_prepare(
    $conn,
    "SELECT users.name,
            understanding_signals.signal_type,
            understanding_signals.topic,
            understanding_signals.created_at
     FROM understanding_signals
     INNER JOIN users
     ON understanding_signals.student_id = users.id
     WHERE understanding_signals.class_id = ?
     ORDER BY understanding_signals.created_at DESC"
);

mysqli_stmt_bind_param($signal_stmt, "i", $class_id);
mysqli_stmt_execute($signal_stmt);

$signal_result = mysqli_stmt_get_result($signal_stmt);

$signals = [];

while ($signal = mysqli_fetch_assoc($signal_result)) {
    $signals[] = $signal;
}

mysqli_stmt_close($signal_stmt);

/* Signal display names */

function signalLabel($type) {

    $labels = [
        "understand" => "I Understand",
        "confused" => "I'm Confused",
        "need_example" => "Need an Example",
        "too_fast" => "Too Fast"
    ];

    return $labels[$type] ?? "Unknown";
}

function signalEmoji($type) {

    $emojis = [
        "understand" => "😊",
        "confused" => "😕",
        "need_example" => "💡",
        "too_fast" => "⏩"
    ];

    return $emojis[$type] ?? "❔";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta http-equiv="refresh" content="15">

    <title>Understanding Dashboard - ClassSense</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f6ff;
            color: #222448;
        }

        a {
            text-decoration: none;
        }

        /* HEADER */

        .header {
            background: #171936;
            padding: 22px 35px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .brand {
            font-size: 25px;
            font-weight: 800;
        }

        .brand span {
            color: #a395ff;
        }

        .header-link {
            background: #35345f;
            color: white;
            padding: 11px 18px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
        }

        /* MAIN */

        .container {
            max-width: 1250px;
            margin: auto;
            padding: 35px 25px;
        }

        .page-heading {
            margin-bottom: 25px;
        }

        .page-heading h1 {
            font-size: 29px;
            margin-bottom: 8px;
        }

        .page-heading p {
            color: #85869b;
            font-size: 14px;
        }

        .class-info {
            background: white;
            border: 1px solid #e7e7f0;
            padding: 23px;
            border-radius: 17px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .class-info h2 {
            font-size: 21px;
            margin-bottom: 6px;
        }

        .class-info p {
            color: #85869b;
            font-size: 14px;
        }

        .class-code {
            background: #eeebff;
            padding: 12px 17px;
            border-radius: 10px;
            color: #5849d5;
            font-weight: 700;
        }

        /* STATUS */

        .status-box {
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            font-weight: 700;
        }

        .status-box.good {
            background: #e6fff0;
            color: #167b49;
        }

        .status-box.warning {
            background: #fff0e8;
            color: #bd592c;
        }

        .status-box.waiting {
            background: #eeeaff;
            color: #5948cb;
        }

        .status-box.normal {
            background: #edf4ff;
            color: #3264aa;
        }

        /* STATISTICS */

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e9e9f2;
            border-radius: 16px;
            padding: 23px;
            box-shadow: 0 5px 20px #22244808;
        }

        .stat-card .icon {
            font-size: 25px;
            margin-bottom: 14px;
        }

        .stat-card h2 {
            font-size: 28px;
            margin-bottom: 5px;
            color: #5447d5;
        }

        .stat-card p {
            color: #85869b;
            font-size: 13px;
        }

        /* SECTIONS */

        .section {
            background: white;
            border: 1px solid #e9e9f2;
            border-radius: 17px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px #22244808;
        }

        .section h2 {
            font-size: 21px;
            margin-bottom: 22px;
        }

        /* PROGRESS */

        .progress-item {
            margin-bottom: 23px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .progress-header strong {
            color: #34365c;
        }

        .progress-header span {
            color: #777990;
        }

        .progress-track {
            height: 13px;
            background: #ececf3;
            border-radius: 20px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 20px;
            transition: width .4s;
        }

        .green {
            background: #27b879;
        }

        .red {
            background: #f16b75;
        }

        .orange {
            background: #f3a44c;
        }

        .blue {
            background: #5d8bea;
        }

        /* STUDENTS */

        .student-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 13px;
        }

        .student-item {
            background: #f8f8ff;
            padding: 15px;
            border-radius: 11px;
            border: 1px solid #ededf5;
        }

        .student-item strong {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .student-item span {
            color: #85869b;
            font-size: 12px;
            overflow-wrap: anywhere;
        }

        /* SIGNALS */

        .signal-item {
            padding: 17px;
            border: 1px solid #ededf5;
            border-radius: 12px;
            margin-bottom: 13px;
            background: #fcfcff;
        }

        .signal-top {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }

        .signal-name {
            font-weight: 700;
            font-size: 14px;
        }

        .signal-status {
            background: #eeebff;
            color: #5948d5;
            border-radius: 20px;
            padding: 6px 11px;
            font-size: 11px;
            font-weight: 700;
        }

        .signal-item p {
            color: #777990;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .signal-item small {
            color: #999aab;
            font-size: 11px;
        }

        /* TOPICS */

        .topic-item {
            padding: 15px;
            background: #fff5ed;
            border-left: 4px solid #f0a05b;
            border-radius: 9px;
            margin-bottom: 12px;
        }

        .topic-item strong {
            display: block;
            margin-bottom: 6px;
        }

        .topic-item span {
            color: #888;
            font-size: 12px;
        }

        /* RECOMMENDATION */

        .recommendation {
            background: linear-gradient(135deg, #eeebff, #f7efff);
            padding: 23px;
            border-radius: 15px;
            line-height: 1.8;
            color: #51449d;
            font-size: 14px;
        }

        .recommendation h3 {
            margin-bottom: 8px;
            color: #34305e;
        }

        .empty {
            color: #88899d;
            padding: 15px 0;
            font-size: 14px;
        }

        .back-button {
            display: inline-block;
            background: #5c4cdb;
            color: white;
            padding: 13px 22px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            margin-top: 5px;
        }

        /* RESPONSIVE */

        @media(max-width: 900px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media(max-width: 600px) {

            .header {
                padding: 18px;
            }

            .container {
                padding: 22px 14px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .student-list {
                grid-template-columns: 1fr;
            }

            .class-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-heading h1 {
                font-size: 23px;
            }

            .signal-top {
                flex-direction: column;
            }

        }

    </style>

</head>

<body>

    <!-- HEADER -->

    <header class="header">

        <div class="brand">
            🎓 Class<span>Sense</span>
        </div>

        <a href="my_classes.php" class="header-link">
            ← Back to My Classes
        </a>

    </header>

    <main class="container">

        <!-- PAGE TITLE -->

        <div class="page-heading">

            <h1>Understanding Dashboard 📊</h1>

            <p>
                Monitor student understanding and classroom performance.
            </p>

        </div>

        <!-- CLASS DETAILS -->

        <div class="class-info">

            <div>

                <h2>
                    <?php echo htmlspecialchars($class["class_name"]); ?>
                </h2>

                <p>
                    Subject:
                    <?php echo htmlspecialchars($class["subject"]); ?>
                </p>

            </div>

            <div class="class-code">
                Code: <?php echo htmlspecialchars($class["class_code"]); ?>
            </div>

        </div>

        <!-- CLASS STATUS -->

        <div class="status-box <?php echo $status_class; ?>">

            📌 Class Status:
            <?php echo htmlspecialchars($class_status); ?>

        </div>

        <!-- STATISTICS -->

        <div class="stats">

            <div class="stat-card">
                <div class="icon">👨‍🎓</div>
                <h2><?php echo $student_count; ?></h2>
                <p>Students Enrolled</p>
            </div>

            <div class="stat-card">
                <div class="icon">💬</div>
                <h2><?php echo $total_responses; ?></h2>
                <p>Total Responses</p>
            </div>

            <div class="stat-card">
                <div class="icon">📈</div>
                <h2><?php echo $response_rate; ?>%</h2>
                <p>Student Response Rate</p>
            </div>

            <div class="stat-card">
                <div class="icon">🆘</div>
                <h2><?php echo $help_needed; ?></h2>
                <p>Responses Needing Help</p>
            </div>

        </div>

        <!-- UNDERSTANDING SUMMARY -->

        <section class="section">

            <h2>Class Understanding</h2>

            <div class="progress-item">

                <div class="progress-header">
                    <strong>😊 I Understand</strong>
                    <span><?php echo $understand; ?> responses (<?php echo $understand_percent; ?>%)</span>
                </div>

                <div class="progress-track">
                    <div class="progress-fill green"
                         style="width:<?php echo $understand_percent; ?>%">
                    </div>
                </div>

            </div>

            <div class="progress-item">

                <div class="progress-header">
                    <strong>😕 I'm Confused</strong>
                    <span><?php echo $confused; ?> responses (<?php echo $confused_percent; ?>%)</span>
                </div>

                <div class="progress-track">
                    <div class="progress-fill red"
                         style="width:<?php echo $confused_percent; ?>%">
                    </div>
                </div>

            </div>

            <div class="progress-item">

                <div class="progress-header">
                    <strong>💡 Need an Example</strong>
                    <span><?php echo $need_example; ?> responses (<?php echo $need_example_percent; ?>%)</span>
                </div>

                <div class="progress-track">
                    <div class="progress-fill orange"
                         style="width:<?php echo $need_example_percent; ?>%">
                    </div>
                </div>

            </div>

            <div class="progress-item">

                <div class="progress-header">
                    <strong>⏩ Too Fast</strong>
                    <span><?php echo $too_fast; ?> responses (<?php echo $too_fast_percent; ?>%)</span>
                </div>

                <div class="progress-track">
                    <div class="progress-fill blue"
                         style="width:<?php echo $too_fast_percent; ?>%">
                    </div>
                </div>

            </div>

        </section>

        <!-- STUDENTS -->

        <section class="section">

            <h2>👨‍🎓 Students Enrolled</h2>

            <?php if (count($students) > 0): ?>

                <div class="student-list">

                    <?php foreach ($students as $student): ?>

                        <div class="student-item">

                            <strong>
                                <?php echo htmlspecialchars($student["name"]); ?>
                            </strong>

                            <span>
                                <?php echo htmlspecialchars($student["email"]); ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <p class="empty">
                    No students have joined this class yet.
                </p>

            <?php endif; ?>

        </section>

        <!-- STUDENT SIGNALS -->

        <section class="section">

            <h2>💬 Recent Student Understanding</h2>

            <?php if (count($signals) > 0): ?>

                <?php foreach ($signals as $signal): ?>

                    <div class="signal-item">

                        <div class="signal-top">

                            <span class="signal-name">
                                <?php echo htmlspecialchars($signal["name"]); ?>
                            </span>

                            <span class="signal-status">
                                <?php
                                echo signalEmoji($signal["signal_type"])
                                    . " "
                                    . signalLabel($signal["signal_type"]);
                                ?>
                            </span>

                        </div>

                        <p>
                            Topic:
                            <?php
                            echo htmlspecialchars(
                                $signal["topic"] ?? "Not specified"
                            );
                            ?>
                        </p>

                        <small>
                            Updated:
                            <?php echo htmlspecialchars($signal["created_at"]); ?>
                        </small>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p class="empty">
                    No student responses yet.
                </p>

            <?php endif; ?>

        </section>

        <!-- DIFFICULT TOPICS -->

        <section class="section">

            <h2>⚠️ Difficult Topics</h2>

            <?php if (count($topics) > 0): ?>

                <?php foreach ($topics as $topic): ?>

                    <div class="topic-item">

                        <strong>
                            <?php echo htmlspecialchars($topic["topic"]); ?>
                        </strong>

                        <span>
                            <?php echo (int)$topic["difficulty_count"]; ?>
                            responses indicate difficulty.
                        </span>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p class="empty">
                    No difficult topics identified yet.
                </p>

            <?php endif; ?>

        </section>

        <!-- TEACHER RECOMMENDATION -->

        <section class="section">

            <h2>💡 Teacher Recommendation</h2>

            <div class="recommendation">

                <h3>Recommended Action</h3>

                <?php echo htmlspecialchars($recommendation); ?>

            </div>

        </section>

        <a href="my_classes.php" class="back-button">
            ← Back to My Classes
        </a>

    </main>

</body>

</html>

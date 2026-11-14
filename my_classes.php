
<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];
$user_role = $_SESSION["user_role"];
$user_name = $_SESSION["user_name"];

if ($user_role === "student") {

    $sql = "SELECT classes.*
            FROM enrollments
            INNER JOIN classes
            ON enrollments.class_id = classes.id
            WHERE enrollments.student_id = ?
            ORDER BY classes.id DESC";

} elseif ($user_role === "teacher") {

    $sql = "SELECT *
            FROM classes
            WHERE teacher_id = ?
            ORDER BY id DESC";

} else {
    die("Invalid user role.");
}

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$total_classes = mysqli_num_rows($result);
$classes = [];

while ($class = mysqli_fetch_assoc($result)) {
    $classes[] = $class;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Classes - ClassSense</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f6ff;
            color: #20234b;
        }

        a {
            text-decoration: none;
        }

        /* MAIN LAYOUT */

        .app {
            min-height: 100vh;
            display: flex;
        }

        /* SIDEBAR */

        .sidebar {
            width: 260px;
            background: #171936;
            color: white;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            padding: 30px 18px;
            display: flex;
            flex-direction: column;
        }

        .brand {
            font-size: 27px;
            font-weight: 800;
            padding: 5px 12px 40px;
        }

        .brand span {
            color: #9b8cff;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .nav-item {
            padding: 15px;
            color: #c8c9df;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            transition: .3s;
        }

        .nav-item:hover,
        .nav-item.active {
            background: #34325f;
            color: white;
        }

        .sidebar-message {
            margin-top: auto;
            padding: 20px 10px;
            text-align: center;
            color: #bfc0da;
            font-size: 13px;
            line-height: 1.7;
        }

        /* MAIN CONTENT */

        .main {
            margin-left: 260px;
            width: calc(100% - 260px);
        }

        /* HEADER */

        .top-header {
            height: 85px;
            background: white;
            border-bottom: 1px solid #e9e9f2;
            padding: 0 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-header h1 {
            font-size: 23px;
        }

        .top-header p {
            color: #88899f;
            font-size: 13px;
            margin-top: 5px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 42px;
            height: 42px;
            background: #e8e3ff;
            color: #5949d5;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-weight: 800;
        }

        .profile span {
            display: block;
            font-size: 12px;
            color: #85869d;
            margin-top: 3px;
        }

        /* CONTENT */

        .content {
            padding: 35px;
        }

        .intro {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 28px;
        }

        .intro h2 {
            font-size: 27px;
            margin-bottom: 8px;
        }

        .intro p {
            color: #85869c;
            font-size: 14px;
        }

        .search-box {
            width: 260px;
            padding: 13px 16px;
            border: 1px solid #dedfee;
            border-radius: 12px;
            outline: none;
            font-size: 14px;
        }

        .search-box:focus {
            border-color: #7061e8;
            box-shadow: 0 0 0 3px #7061e820;
        }

        /* STATISTICS */

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 30px;
        }

        .stat {
            background: white;
            padding: 23px;
            border-radius: 17px;
            border: 1px solid #e9e9f2;
            box-shadow: 0 5px 20px #20234b08;
        }

        .stat strong {
            display: block;
            font-size: 25px;
            color: #5547d5;
            margin-bottom: 5px;
        }

        .stat span {
            font-size: 13px;
            color: #88899f;
        }

        /* CLASS GRID */

        .classes-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
        }

        .class-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #e8e8f2;
            box-shadow: 0 7px 25px #20234b08;
            transition: .3s;
        }

        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px #20234b15;
        }

        .card-top {
            background: linear-gradient(135deg, #eae7ff, #f2efff);
            padding: 23px;
        }

        .class-icon {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 13px;
            display: grid;
            place-items: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .card-top h3 {
            font-size: 19px;
            margin-bottom: 6px;
            color: #272752;
        }

        .card-top p {
            color: #777895;
            font-size: 13px;
        }

        .card-body {
            padding: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 11px 0;
            border-bottom: 1px solid #f0f0f5;
            font-size: 13px;
        }

        .info-row:last-child {
            border: none;
        }

        .info-row span {
            color: #85869b;
        }

        .info-row strong {
            color: #34365d;
            text-align: right;
        }

        /* BUTTONS */

        .card-actions {
            padding: 0 20px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .action-btn {
            display: block;
            text-align: center;
            padding: 13px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            transition: .3s;
        }

        .open-class {
            background: linear-gradient(135deg, #5e50df, #8063ee);
            color: white;
        }

        .open-class:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 18px #6655df40;
        }

        .feedback-btn {
            background: #f0edff;
            color: #5848d3;
        }

        .teacher-dashboard-btn {
            background: #e8fff1;
            color: #16804d;
        }

        .feedback-btn:hover,
        .teacher-dashboard-btn:hover {
            filter: brightness(.96);
        }

        /* EMPTY STATE */

        .empty-state {
            background: white;
            padding: 65px 20px;
            text-align: center;
            border-radius: 18px;
            border: 1px solid #e8e8f2;
        }

        .empty-state .emoji {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .empty-state h2 {
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #85869b;
            margin-bottom: 22px;
            font-size: 14px;
        }

        .empty-state a {
            display: inline-block;
            padding: 13px 22px;
            background: #5d4be5;
            color: white;
            border-radius: 10px;
            font-weight: 700;
        }

        /* FOOTER */

        .footer {
            margin-top: 30px;
            padding: 20px;
            border-radius: 14px;
            background: #eeebff;
            color: #5a519c;
            font-size: 13px;
        }

        /* RESPONSIVE */

        @media(max-width: 1100px) {
            .classes-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width: 750px) {
            .sidebar {
                display: none;
            }

            .main {
                margin-left: 0;
                width: 100%;
            }

            .content {
                padding: 22px 15px;
            }

            .top-header {
                padding: 0 16px;
            }

            .intro {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                width: 100%;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .classes-grid {
                grid-template-columns: 1fr;
            }

            .profile-info {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="app">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="brand">
            🎓 Class<span>Sense</span>
        </div>

        <nav class="sidebar-nav">

            <a href="<?php echo $user_role === 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'; ?>"
               class="nav-item">
                🏠 Dashboard
            </a>

            <a href="my_classes.php" class="nav-item active">
                📚 My Classes
            </a>

            <a href="today_classes.php" class="nav-item">
                📅 Today's Classes
            </a>

            <?php if ($user_role === "student"): ?>

                <a href="my_classes.php" class="nav-item">
                    💡 Learning Feedback
                </a>

            <?php endif; ?>

            <?php if ($user_role === "teacher"): ?>

                <a href="create_class.php" class="nav-item">
                    ➕ Create Class
                </a>

                <a href="my_classes.php" class="nav-item">
                    📊 Understanding Dashboard
                </a>

            <?php endif; ?>

            <a href="logout.php" class="nav-item">
                🚪 Logout
            </a>

        </nav>

        <div class="sidebar-message">
            💜
            <p>
                Your classroom. Your understanding.
                Your voice matters.
            </p>
        </div>

    </aside>

    <!-- MAIN -->

    <main class="main">

        <header class="top-header">

            <div>
                <h1>My Classes 📚</h1>
                <p>Manage your classroom in one place.</p>
            </div>

            <div class="profile">

                <div class="avatar">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>

                <div class="profile-info">
                    <strong><?php echo htmlspecialchars($user_name); ?></strong>
                    <span><?php echo ucfirst($user_role); ?></span>
                </div>

            </div>

        </header>

        <section class="content">

            <div class="intro">

                <div>
                    <h2>
                        <?php echo $user_role === "student"
                            ? "Your Learning Spaces"
                            : "Your Classrooms"; ?>
                    </h2>

                    <p>
                        <?php echo $user_role === "student"
                            ? "View all the classes you have joined."
                            : "View your created classes and student understanding."; ?>
                    </p>
                </div>

                <input
                    type="text"
                    id="classSearch"
                    class="search-box"
                    placeholder="🔍 Search classes..."
                    oninput="searchClasses()"
                >

            </div>

            <!-- STATISTICS -->

            <div class="stats">

                <div class="stat">
                    <strong><?php echo $total_classes; ?></strong>
                    <span>Total Classes</span>
                </div>

                <div class="stat">
                    <strong><?php echo $total_classes; ?></strong>
                    <span>Active Classes</span>
                </div>

                <div class="stat">
                    <strong>ClassSense</strong>
                    <span>Your Learning Space</span>
                </div>

            </div>

            <!-- CLASSES -->

            <?php if ($total_classes > 0): ?>

                <div class="classes-grid" id="classesGrid">

                    <?php
                    $icons = ["📘", "📗", "📙", "📕", "💻", "📚"];
                    $count = 0;
                    ?>

                    <?php foreach ($classes as $class): ?>

                        <div class="class-card">

                            <div class="card-top">

                                <div class="class-icon">
                                    <?php echo $icons[$count % count($icons)]; ?>
                                </div>

                                <h3>
                                    <?php echo htmlspecialchars($class["class_name"]); ?>
                                </h3>

                                <p>
                                    <?php echo htmlspecialchars($class["subject"]); ?>
                                </p>

                            </div>

                            <div class="card-body">

                                <div class="info-row">
                                    <span>🔑 Class Code</span>
                                    <strong>
                                        <?php echo htmlspecialchars($class["class_code"]); ?>
                                    </strong>
                                </div>

                                <div class="info-row">
                                    <span>📅 Class Day</span>
                                    <strong>
                                        <?php echo htmlspecialchars($class["day_of_week"]); ?>
                                    </strong>
                                </div>

                                <div class="info-row">
                                    <span>🕐 Start Time</span>
                                    <strong>
                                        <?php echo date("h:i A", strtotime($class["start_time"])); ?>
                                    </strong>
                                </div>

                            </div>

                            <div class="card-actions">

                                <a
                                    href="class.php?id=<?php echo (int)$class["id"]; ?>"
                                    class="action-btn open-class"
                                >
                                    🚀 Open Class
                                </a>

                                <?php if ($user_role === "student"): ?>

                                    <a
                                        href="class.php?id=<?php echo (int)$class["id"]; ?>"
                                        class="action-btn feedback-btn"
                                    >
                                        💡 Give Learning Feedback
                                    </a>

                                <?php else: ?>

                                    <a
                                        href="teacher_signals.php?id=<?php echo (int)$class["id"]; ?>"
                                        class="action-btn teacher-dashboard-btn"
                                    >
                                        📊 View Understanding Dashboard
                                    </a>

                                <?php endif; ?>

                            </div>

                        </div>

                        <?php $count++; ?>

                    <?php endforeach; ?>

                </div>

                <p id="noSearchResults" style="display:none; text-align:center; padding:30px;">
                    No matching classes found.
                </p>

            <?php else: ?>

                <div class="empty-state">

                    <div class="emoji">📚</div>

                    <h2>No Classes Yet</h2>

                    <p>
                        <?php echo $user_role === "student"
                            ? "You haven't joined any classes yet."
                            : "You haven't created any classes yet."; ?>
                    </p>

                    <?php if ($user_role === "student"): ?>

                        <a href="join_class.php">➕ Join a Class</a>

                    <?php else: ?>

                        <a href="create_class.php">➕ Create a Class</a>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

            <div class="footer">
                💜 <strong>ClassSense:</strong>
                Your classroom. Your understanding. Your voice matters.
            </div>

        </section>

    </main>

</div>

<script>
function searchClasses() {

    const search = document
        .getElementById("classSearch")
        .value.toLowerCase();

    const cards = document.querySelectorAll(".class-card");

    let visible = 0;

    cards.forEach(function(card) {

        const text = card.innerText.toLowerCase();

        if (text.includes(search)) {
            card.style.display = "";
            visible++;
        } else {
            card.style.display = "none";
        }

    });

    const noResults = document.getElementById("noSearchResults");

    if (noResults) {
        noResults.style.display = visible === 0 ? "block" : "none";
    }
}
</script>

</body>
</html>

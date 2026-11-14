<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("Class ID missing.");
}

$class_id = intval($_GET["id"]);
$teacher_id = $_SESSION["user_id"];
$message = "";

/* Get class */
$stmt = mysqli_prepare($conn,
    "SELECT class_name FROM classes WHERE id = ? AND teacher_id = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $class_id, $teacher_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) != 1) {
    die("You are not the teacher of this class.");
}

$class = mysqli_fetch_assoc($result);


/* Create post */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if ($title == "" || $content == "") {
        $message = "Please fill all fields.";
    } else {

        $stmt = mysqli_prepare($conn,
            "INSERT INTO posts (class_id, teacher_id, title, content)
             VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iiss",
            $class_id,
            $teacher_id,
            $title,
            $content
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Post created successfully!";
        } else {
            $message = "Could not create post.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Post - ClassSense</title>
    <link rel="stylesheet" href="css/create_post.css">
</head>

<body>
    <div class="create-post-container">
<h1>ClassSense</h1>

<h2>Create Post</h2>

<p>Class: <?php echo htmlspecialchars($class["class_name"]); ?></p>

<?php if ($message != ""): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form method="POST">

    <label>Title:</label><br>
    <input type="text" name="title"><br><br>

    <label>Content:</label><br>
    <textarea name="content" rows="6" cols="50"></textarea><br><br>

    <button type="submit">Create Post</button>

</form>

<br>

<a href="student_dashboard.php">Back to Dashboard</a>

</body>
</html>
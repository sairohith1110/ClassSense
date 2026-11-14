<?php

require_once "includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    if (empty($name) || empty($email) || empty($password) || empty($role)) {

        $message = "Please fill all fields.";

    } else {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password, role)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $name,
            $email,
            $hashedPassword,
            $role
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Registration successful!";
        } else {
            $message = "Registration failed.";
        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>ClassSense - Register</title>
    <!-- Add this line below -->
     <link rel = "stylesheet" href = "style.css">   
</head>

<body>

<div class="register-container">

    <h1>Create Account</h1>

    <?php
    if ($message != "") {
        echo "<p class='message'>$message</p>";
    }
    ?>

    
<form method="POST">

    <label>Name:</label>
    <input type="text" name="name" placeholder="Enter your name" required>

    <label>Email:</label>
    <input type="email" name="email" placeholder="Enter your email" required>

    <label>Password:</label>
    <input type="password" name="password" placeholder="Enter your password" required>

    <label>Role:</label>
    <select name="role" required>
        <option value="">Select Role</option>
        <option value="student">Student</option>
        <option value="teacher">Teacher</option>
    </select>

    <button type="submit">Register</button>

</form>


</div>

</body>
</html>
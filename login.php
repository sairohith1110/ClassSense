
<?php
session_start();

require_once "includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {

        $message = "Please enter email and password.";

    } else {

        $sql = "SELECT id, name, password, role
                FROM users
                WHERE email = ?
                ORDER BY id DESC
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {

                $user = mysqli_fetch_assoc($result);

                if (password_verify($password, $user["password"])) {

                    session_regenerate_id(true);

                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["user_name"] = $user["name"];
                    $_SESSION["user_role"] = $user["role"];

                    if ($user["role"] === "teacher") {

                        header("Location: teacher_dashboard.php");
                        exit;

                    } elseif ($user["role"] === "student") {

                        header("Location: student_dashboard.php");
                        exit;

                    } else {

                        session_unset();
                        session_destroy();
                        die("Invalid user role.");
                    }

                } else {

                    $message = "Invalid email or password.";
                }

            } else {

                $message = "Invalid email or password.";
            }

            mysqli_stmt_close($stmt);

        } else {

            $message = "Database query failed.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - ClassSense</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {

            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            background:
                linear-gradient(
                    135deg,
                    #f5f3ff,
                    #ede9fe,
                    #ffffff
                );

            padding: 20px;

        }

        /* MAIN CONTAINER */

        .login-container {

            width: 900px;
            max-width: 100%;

            min-height: 560px;

            background: white;

            border-radius: 28px;

            overflow: hidden;

            display: flex;

            box-shadow:
                0 25px 60px rgba(76, 55, 120, 0.15);

        }


        /* LEFT SIDE */

        .login-left {

            width: 48%;

            padding: 55px 45px;

            background:
                linear-gradient(
                    145deg,
                    #7c3aed,
                    #8b5cf6,
                    #a855f7
                );

            color: white;

            display: flex;

            flex-direction: column;

            justify-content: center;

            position: relative;

            overflow: hidden;

        }

        .login-left::before {

            content: "";

            position: absolute;

            width: 250px;
            height: 250px;

            border-radius: 50%;

            background: rgba(255,255,255,0.08);

            top: -80px;
            right: -80px;

        }

        .login-left::after {

            content: "";

            position: absolute;

            width: 180px;
            height: 180px;

            border-radius: 50%;

            background: rgba(255,255,255,0.06);

            bottom: -70px;
            left: -60px;

        }

        .brand {

            font-size: 30px;

            font-weight: bold;

            margin-bottom: 60px;

            position: relative;

            z-index: 1;

        }

        .brand span {

            color: #ede9fe;

        }

        .welcome-icon {

            font-size: 65px;

            margin-bottom: 25px;

            position: relative;

            z-index: 1;

        }

        .login-left h1 {

            font-size: 38px;

            line-height: 1.2;

            margin-bottom: 20px;

            position: relative;

            z-index: 1;

        }

        .login-left p {

            font-size: 16px;

            line-height: 1.7;

            color: #f3e8ff;

            position: relative;

            z-index: 1;

        }


        /* RIGHT SIDE */

        .login-right {

            width: 52%;

            padding: 55px 50px;

            display: flex;

            flex-direction: column;

            justify-content: center;

        }

        .login-right h2 {

            font-size: 30px;

            color: #222;

            margin-bottom: 10px;

        }

        .subtitle {

            color: #777;

            font-size: 14px;

            margin-bottom: 30px;

        }


        /* ERROR */

        .error-message {

            background: #fff1f2;

            color: #be123c;

            border: 1px solid #fecdd3;

            padding: 12px 15px;

            border-radius: 10px;

            font-size: 14px;

            margin-bottom: 20px;

        }


        /* FORM */

        .form-group {

            margin-bottom: 20px;

        }

        .form-group label {

            display: block;

            font-size: 14px;

            font-weight: bold;

            color: #333;

            margin-bottom: 8px;

        }

        .input-wrapper {

            position: relative;

        }

        .input-icon {

            position: absolute;

            left: 15px;

            top: 50%;

            transform: translateY(-50%);

            font-size: 18px;

        }

        .form-group input {

            width: 100%;

            padding: 14px 15px 14px 45px;

            border: 1px solid #ddd6fe;

            border-radius: 12px;

            outline: none;

            font-size: 14px;

            transition: 0.3s;

            background: #fafafa;

        }

        .form-group input:focus {

            border-color: #8b5cf6;

            background: white;

            box-shadow:
                0 0 0 4px rgba(139,92,246,0.1);

        }


        /* LOGIN BUTTON */

        .login-button {

            width: 100%;

            border: none;

            padding: 15px;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #7c3aed,
                    #9333ea
                );

            color: white;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.3s;

            margin-top: 5px;

        }

        .login-button:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 25px rgba(124,58,237,0.25);

        }


        /* REGISTER */

        .register-text {

            text-align: center;

            margin-top: 25px;

            color: #777;

            font-size: 14px;

        }

        .register-text a {

            color: #7c3aed;

            font-weight: bold;

            text-decoration: none;

        }

        .register-text a:hover {

            text-decoration: underline;

        }


        /* FOOTER */

        .footer {

            text-align: center;

            margin-top: 30px;

            font-size: 12px;

            color: #aaa;

        }


        /* MOBILE */

        @media (max-width: 750px) {

            .login-container {

                flex-direction: column;

                min-height: auto;

            }

            .login-left {

                width: 100%;

                padding: 35px 30px;

                text-align: center;

            }

            .brand {

                margin-bottom: 25px;

            }

            .welcome-icon {

                font-size: 45px;

                margin-bottom: 10px;

            }

            .login-left h1 {

                font-size: 28px;

            }

            .login-left p {

                font-size: 14px;

            }

            .login-right {

                width: 100%;

                padding: 40px 30px;

            }

        }

    </style>

</head>

<body>


<div class="login-container">


    <!-- LEFT -->

    <div class="login-left">

        <div class="brand">
            🎓 Class<span>Sense</span>
        </div>

        <div class="welcome-icon">
            👋
        </div>

        <h1>
            Welcome Back!
        </h1>

        <p>
            Continue your learning journey
            and stay connected with your classroom.
        </p>

    </div>


    <!-- RIGHT -->

    <div class="login-right">

        <h2>
            Login
        </h2>

        <p class="subtitle">
            Enter your details to access your account.
        </p>


        <?php if ($message != ""): ?>

            <div class="error-message">
                ⚠️
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- EMAIL -->

            <div class="form-group">

                <label>
                    Email Address
                </label>

                <div class="input-wrapper">

                    <span class="input-icon">
                        📧
                    </span>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?php echo isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : ""; ?>"
                        required
                    >

                </div>

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label>
                    Password
                </label>

                <div class="input-wrapper">

                    <span class="input-icon">
                        🔒
                    </span>

                    <input
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>

            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                class="login-button"
            >
                🔐 Login
            </button>

        </form>


        <div class="register-text">

            Don't have an account?

            <a href="register.php">
                Register here
            </a>

        </div>


        <div class="footer">

            Your classroom. Your understanding. 💜

        </div>

    </div>

</div>


</body>

</html>
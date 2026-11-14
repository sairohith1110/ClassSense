<?php

session_start();

/* Destroy all session data */
$_SESSION = array();

session_destroy();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logout - ClassSense</title>

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
        }

        .logout-container {
            width: 420px;
            max-width: 90%;

            background: white;

            padding: 45px 35px;

            border-radius: 24px;

            text-align: center;

            box-shadow:
                0 20px 50px rgba(88, 70, 150, 0.15);

            animation: fadeIn 0.5s ease;
        }

        .logo {
            font-size: 30px;
            font-weight: bold;

            color: #222;

            margin-bottom: 25px;
        }

        .logo span {
            color: #7c3aed;
        }

        .logout-icon {
            width: 85px;
            height: 85px;

            margin: 0 auto 25px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #f3e8ff;

            border-radius: 50%;

            font-size: 42px;
        }

        h1 {
            color: #222;
            font-size: 26px;

            margin-bottom: 12px;
        }

        p {
            color: #777;

            font-size: 15px;

            line-height: 1.6;

            margin-bottom: 28px;
        }

        .login-btn {
            display: inline-block;

            padding: 13px 30px;

            background: linear-gradient(
                135deg,
                #7c3aed,
                #9333ea
            );

            color: white;

            text-decoration: none;

            border-radius: 12px;

            font-weight: bold;

            transition: 0.3s;
        }

        .login-btn:hover {
            transform: translateY(-2px);

            box-shadow:
                0 10px 20px rgba(124, 58, 237, 0.25);
        }

        .footer {
            margin-top: 25px;

            font-size: 12px;

            color: #aaa;
        }

        @keyframes fadeIn {

            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }

        @media (max-width: 500px) {

            .logout-container {
                padding: 35px 25px;
            }

            h1 {
                font-size: 23px;
            }

        }

    </style>

</head>

<body>

    <div class="logout-container">

        <div class="logo">
            🎓 Class<span>Sense</span>
        </div>

        <div class="logout-icon">
            👋
        </div>

        <h1>
            You’ve been logged out
        </h1>

        <p>
            Thank you for using ClassSense.<br>
            See you again soon!
        </p>

        <a href="login.php" class="login-btn">
            🔐 Login Again
        </a>

        <div class="footer">
            Your classroom. Your understanding.
        </div>

    </div>

</body>

</html>
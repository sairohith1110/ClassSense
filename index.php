
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassSense | Smart Classroom</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #101b3c, #243b70);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .container {
            max-width: 750px;
        }

        .logo {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .logo span {
            color: #60d5ff;
        }

        h1 {
            font-size: 42px;
            margin-bottom: 18px;
        }

        p {
            font-size: 18px;
            color: #d6e0f5;
            line-height: 1.7;
            margin-bottom: 35px;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }

        .login {
            background: #60d5ff;
            color: #102044;
        }

        .register {
            border: 2px solid white;
            color: white;
        }

        .btn:hover {
            transform: translateY(-3px);
            opacity: 0.85;
        }

        .footer {
            margin-top: 65px;
            font-size: 13px;
            color: #aebddc;
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="logo">
            Class<span>Sense</span>
        </div>

        <h1>Welcome to ClassSense</h1>

        <p>
            Smart Classroom Understanding System.<br>
            Helping teachers understand students better
            through simple classroom feedback.
        </p>

        <div class="buttons">
            <a href="login.php" class="btn login">Login</a>

            <a href="register.php" class="btn register">Register</a>
        </div>

        <div class="footer">
            © 2026 ClassSense. Smart Learning, Better Understanding.
        </div>

    </div>

</body>
</html>

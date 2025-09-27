<?php
session_start();
include 'db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['id']   = $user['id'];   
            $_SESSION['user'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: books.php");
            }
            exit();
        } else {
            $message = "❌ Incorrect password.";
        }
    } else {
        $message = "❌ Account not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('pinkbooks.avif') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }

        .box {
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            padding: 40px;
            width: 400px;
            max-width: 90%;
            margin: 100px auto;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 26px;
            color: white;
        }

        input, button {
            padding: 10px;
            margin: 10px 0;
            width: 70%;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            backdrop-filter: blur(5px);
        }

        input::placeholder {
            color: #eee;
        }

        button {
            background-color: rgba(255, 255, 255, 0.3);
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: rgba(255, 255, 255, 0.5);
        }

        a {
            color: #ddd;
            text-decoration: none;
            font-size: 14px;
        }

        a:hover {
            text-decoration: underline;
        }

        .msg {
            color: #ff4d4d;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>📚 LIBRARY LOGIN</h2>
        <?php if (!empty($message)): ?>
            <p class="msg"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        <a href="register.php">Don't have an account? Register</a>
    </div>
</body>
</html>
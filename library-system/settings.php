<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$email = htmlspecialchars($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings</title>
<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: linear-gradient(135deg,#ff9a9e,#fad0c4,#fbc2eb);
        height: 100vh;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .settings-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        padding: 40px 30px;
        width: 320px;
        text-align: center;
        animation: fadeIn 0.6s ease;
    }
    h2 {
        margin: 0 0 20px;
        font-size: 1.8em;
        color: #333;
    }
    .user-email {
        font-size: 0.95em;
        color: #666;
        margin-bottom: 25px;
        word-wrap: break-word;
    }
    .button {
        display: block;
        margin: 12px auto;
        padding: 12px 20px;
        width: 80%;
        background: #ff6b6b;
        color: #fff;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s;
    }
    .button:hover {
        background: #e63946;
    }
    .back-button {
        background: #4a90e2;
    }
    .back-button:hover {
        background: #357ab7;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px);}
        to { opacity: 1; transform: translateY(0);}
    }
</style>
</head>
<body>

<div class="settings-card">
    <h2>Settings</h2>
    <p class="user-email"><?= $email ?></p>
    <a href="logout.php" class="button">Logout</a>
    <a href="books.php" class="button back-button">Go Back</a>
</div>

</body>
</html>

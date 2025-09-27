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
<title>User Profile</title>
<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: linear-gradient(135deg,#6fb1fc,#4364f7,#3f51b5);
        height: 100vh;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .profile-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        padding: 40px 30px;
        width: 320px;
        text-align: center;
        animation: fadeIn 0.6s ease;
    }
    .profile-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: #3f51b5;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        margin: 0 auto 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    h2 {
        margin: 0 0 10px;
        font-size: 1.6em;
        color: #333;
    }
    p {
        color: #555;
        font-size: 1em;
        margin: 5px 0 20px;
        word-wrap: break-word;
    }
    a.button {
        display: inline-block;
        padding: 10px 20px;
        background: #3f51b5;
        color: #fff;
        border-radius: 25px;
        text-decoration: none;
        transition: background 0.3s;
        font-weight: 600;
    }
    a.button:hover {
        background: #2c3a91;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px);}
        to { opacity: 1; transform: translateY(0);}
    }
</style>
</head>
<body>

<div class="profile-card">
    <div class="profile-avatar">
        <?= strtoupper($email[0]) ?>
    </div>
    <h2>My Profile</h2>
    <p><strong>Email:</strong><br><?= $email ?></p>
    <a href="books.php" class="button"> Go Back</a>
</div>

</body>
</html>

<?php
session_start();
include 'db.php';

// Check if logged in and role is admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// If form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category = trim($_POST['category']);
    $title = trim($_POST['title']);

    if (!empty($category) && !empty($title)) {
        $stmt = $conn->prepare("INSERT INTO books (category, title) VALUES (?, ?)");
        $stmt->bind_param("ss", $category, $title);

        if ($stmt->execute()) {
            $success = "✅ New book added successfully!";
        } else {
            $error = "❌ Failed to add book.";
        }
    } else {
        $error = "⚠ Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Book</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('images/bookshelf-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            text-align: center;
            padding: 30px;
        }
        form {
            background: rgba(255,255,255,0.9);
            padding: 20px;
            width: 300px;
            margin: auto;
            border-radius: 10px;
        }
        input, select {
            padding: 8px;
            width: 90%;
            margin: 10px 0;
        }
        button {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background: #218838;
        }
        .msg {
            margin: 10px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>📚 Add New Book</h2>

<?php if (!empty($success)) echo "<div class='msg' style='color:green;'>$success</div>"; ?>
<?php if (!empty($error)) echo "<div class='msg' style='color:red;'>$error</div>"; ?>

<form method="POST">
    <label>Category:</label><br>
    <input type="text" name="category" placeholder="Enter category"><br>
    
    <label>Title:</label><br>
    <input type="text" name="title" placeholder="Enter book title"><br>

    <button type="submit">➕ Add Book</button>
</form>

<br>
<a href="admindashboard.php">⬅ Back to Admin Dashboard</a>

</body>
</html>

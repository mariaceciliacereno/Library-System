<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];

// Get user ID from email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$user_id = $user['id'];

// Fetch borrowed books
$stmt = $conn->prepare("SELECT b.category, b.title, br.borrowed_at, br.returned_at 
                        FROM borrow_requests br 
                        JOIN books b ON br.book_id = b.id 
                        WHERE br.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$borrowed_books = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Borrowings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            background: url('mybooks.avif') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }
        .container {
            background: rgba(0, 0, 0, 0.75);
            padding: 30px;
            border-radius: 10px;
            width: 80%;
            margin: 50px auto;
        }
        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 100%;
            background-color: white;
            color: black;
        }
        th, td {
            border: 1px solid #444;
            padding: 10px;
        }
        th {
            background-color: #f0f0f0;
        }
        a.button {
            display: inline-block;
            margin: 10px;
            text-decoration: none;
            background: #007BFF;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
        }
        a.button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📘 My Borrowings</h2>

    <?php if ($borrowed_books->num_rows > 0): ?>
    <table>
        <tr>
            <th>Category</th>
            <th>Title</th>
            <th>Borrowed At</th>
            <th>Returned At</th>
        </tr>
        <?php while ($row = $borrowed_books->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['borrowed_at']) ?></td>
            <td>
                <?= $row['returned_at'] ? htmlspecialchars($row['returned_at']) : "<i>Not returned yet</i>" ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>You have not borrowed any books yet.</p>
    <?php endif; ?>
    <a href="books.php" class="button">Back to Books</a>
</div>
</body>
</html>

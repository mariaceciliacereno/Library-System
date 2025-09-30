<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT b.category, b.title, u.email, br.borrowed_at, br.returned_at
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.id
    JOIN books b ON br.book_id = b.id
    ORDER BY br.borrowed_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Borrowed Books</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('../magical-library.avif') no-repeat center center fixed;
            background-size: cover;
        }

        .container {
            max-width: 1100px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #000;
        }

        h2 {
            text-align: center;
            color: white;
            text-shadow: 1px 1px 5px black;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: center;
        }

        th {
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
        }

        td {
            background-color: rgba(255, 255, 255, 0.8);
        }

        a.logout {
            float: right;
            font-size: 14px;
            color: #ffdd57;
            text-decoration: none;
        }

        a.logout:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 Borrowed Books <a href="../logout.php" class="logout">Logout</a></h2>
    <table>
        <tr>
            <th>Category</th>
            <th>Title</th>
            <th>User Email</th>
            <th>Borrowed At</th>
            <th>Returned At</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['borrowed_at'] ?></td>
                <td><?= $row['returned_at'] ? $row['returned_at'] : 'Not Returned' ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
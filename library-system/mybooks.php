<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];

/* ---- Fetch all books this user borrowed ---- */
$sql = "
    SELECT
        b.number_of_books AS book_no,       -- 'No.' entered by admin
        b.category,
        b.title,
        DATE_FORMAT(br.borrowed_at, '%d/%m/%Y %l:%i %p')  AS borrowed_at,
        DATE_FORMAT(br.returned_at, '%d/%m/%Y %l:%i %p')  AS returned_at
    FROM borrow_requests br
    JOIN books b  ON br.book_id = b.id
    JOIN users u  ON br.user_id = u.id
    WHERE u.email = ?
    ORDER BY br.borrowed_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Borrowed Books</title>
<style>
/* ---- Page background ---- */
body {
    margin: 0;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background: url('mybooks.avif') no-repeat center center fixed;
    background-size: cover;
}
.container {
    max-width: 1000px;
    margin: 60px auto;
    padding: 30px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
h2 {
    text-align: center;
    font-size: 30px;
    margin-top: 0;
    color: #333;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
th, td {
    padding: 12px 15px;
    text-align: center;
    font-size: 15px;
}
th {
    background: #4a4a4a;
    color: #fff;
    letter-spacing: 0.5px;
}
tr:nth-child(even) { background: #f9f9f9; }
.back-btn {
    display: inline-block;
    margin-top: 20px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: #fff;
    padding: 10px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.25s ease;
}
.back-btn:hover {
    background: linear-gradient(135deg, #0056b3, #003f7f);
    transform: translateY(-2px);
}
</style>
</head>
<body>
<div class="container">
  <h2>📚 My Borrowed Books</h2>

  <table>
    <thead>
      <tr>
        <th>No.</th>
        <th>Category</th>
        <th>Title</th>
        <th>Borrowed At</th>
        <th>Returned At</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['book_no']) ?></td> <!-- ✅ No. (admin entered) -->
          <td><?= htmlspecialchars($row['category']) ?></td>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td><?= htmlspecialchars($row['borrowed_at']) ?></td>
          <td><?= $row['returned_at'] ?: '<i>Not Returned</i>' ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">You have not borrowed any books yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>

  <div style="text-align:center;">
    <a href="books.php" class="back-btn">⬅ Back to Books</a>
  </div>
</div>
</body>
</html>

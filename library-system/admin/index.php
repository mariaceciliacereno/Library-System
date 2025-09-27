<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include_once __DIR__ . '/../db.php';

// Fetch borrowed books
$stmt = $conn->prepare("
    SELECT b.category, b.title, u.email, br.borrowed_at, br.returned_at, br.status
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.id
    JOIN books b ON br.book_id = b.id
    ORDER BY br.borrowed_at DESC
");
$stmt->execute();
$result = $stmt->get_result();

// Helper to compute penalty
function calculatePenalty($borrowed_at, $returned_at) {
    $penaltyPerDay = 10;
    $dueDate = new DateTime($borrowed_at);
    $dueDate->modify('+7 days');
    $returnDate = $returned_at ? new DateTime($returned_at) : new DateTime();
    if ($returnDate > $dueDate) {
        $daysLate = $dueDate->diff($returnDate)->days;
        return "₱" . ($daysLate * $penaltyPerDay);
    }
    return "₱0";
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<style>
body {
  margin:0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: url('../magical-library.avif') no-repeat center center fixed;
  background-size: cover;
}
.container {
  max-width:1100px;
  margin:40px auto;
  padding:25px;
  background:rgba(255,255,255,0.95);
  border-radius:12px;
  position: relative;
  box-shadow:0 8px 20px rgba(0,0,0,0.15);
}
h2 {
  text-align:center;
  margin-bottom: 20px;
  font-size: 28px;
  color:#333;
}
.logout {
  position:absolute;
  top:20px;
  right:20px;
  background: #ff4b5c;
  color: #fff;
  text-decoration: none;
  padding: 10px 18px;
  border-radius: 30px;
  font-weight: bold;
  transition: background 0.3s ease, transform 0.2s ease;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.logout:hover {
  background: #e63946;
  transform: translateY(-2px);
}
table {
  width:100%;
  border-collapse:collapse;
  margin-top:20px;
  border-radius:8px;
  overflow:hidden;
  box-shadow: 0 3px 6px rgba(0,0,0,0.1);
}
th {
  background:#4a4a4a;
  color:#fff;
  padding:12px;
  font-size:15px;
  letter-spacing:0.5px;
}
td {
  padding:10px;
  border-bottom:1px solid #ddd;
  text-align:center;
  font-size:14px;
}
tr:nth-child(even){background:#f9f9f9;}
/* Stylish message button */
.message-btn {
  display:inline-block;
  background: linear-gradient(135deg,#007bff,#0056b3);
  color:#fff;
  padding:8px 16px;
  text-decoration:none;
  border-radius:25px;
  font-size:14px;
  font-weight:600;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  transition: all 0.25s ease;
}
.message-btn:hover {
  background: linear-gradient(135deg,#0056b3,#003f7f);
  transform: translateY(-2px);
}
</style>
</head>
<body>
<div class="container">
  <a href="../logout.php" class="logout">Logout</a>
  <h2>📊 Borrowed Books</h2>

  <table>
    <thead>
      <tr>
        <th>Category</th>
        <th>Title</th>
        <th>User Email</th>
        <th>Borrowed At</th>
        <th>Returned At</th>
        <th>Status</th>
        <th>Penalty</th>
        <th>Message</th>
      </tr>
    </thead>
    <tbody>
    <?php while($row = $result->fetch_assoc()):
        $penalty = calculatePenalty($row['borrowed_at'], $row['returned_at']);
    ?>
      <tr>
        <td><?=htmlspecialchars($row['category'])?></td>
        <td><?=htmlspecialchars($row['title'])?></td>
        <td><?=htmlspecialchars($row['email'])?></td>
        <td><?=$row['borrowed_at']?></td>
        <td><?=$row['returned_at'] ?: '<i>Not returned</i>'?></td>
        <td><?=ucfirst($row['status'])?></td>
        <td><?=$penalty?></td>
        <td>
           <a class="message-btn" href="admin_message.php?user=<?= urlencode($row['email']) ?>">💬 Message</a>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>

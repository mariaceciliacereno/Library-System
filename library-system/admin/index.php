<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include_once __DIR__ . '/../db.php';

/*
 * Fetch borrowed books and include the book's primary key (id) so we can
 * display it as the “No.” column in the admin dashboard.
 */
$stmt = $conn->prepare("
    SELECT 
        b.number_of_books AS book_no,
        b.category,
        b.title,
        u.email,
        br.borrowed_at,
        br.returned_at,
        DATE_FORMAT(br.borrowed_at, '%d/%m/%Y %l:%i %p')  AS borrowed_display,
        DATE_FORMAT(br.returned_at,  '%d/%m/%Y %l:%i %p') AS returned_display,
        br.status
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.id
    JOIN books b ON br.book_id = b.id
    ORDER BY br.borrowed_at DESC
");
$stmt->execute();
$result = $stmt->get_result();

/**
 * Helper to compute penalty
 */
function calculatePenalty($borrowed_at, $returned_at) {
    $penaltyPerDay = 10; // pesos per day
    $dueDate    = new DateTime($borrowed_at);
    $dueDate->modify('+7 days');
    $returnDate = $returned_at ? new DateTime($returned_at) : new DateTime();
    if ($returnDate > $dueDate) {
        $daysLate = $dueDate->diff($returnDate)->days;
        return "₱" . ($daysLate * $penaltyPerDay);
    }
    return "₱0";
}

// ✅ Current date/time for header
$currentDateTime = date('d/m/Y g:i A'); // e.g. 29/09/2025 3:45 PM
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
  margin-bottom: 5px;
  font-size: 28px;
  color:#333;
}
.datetime {
  text-align:center;
  font-size:16px;
  color:#666;
  margin-bottom:20px;
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
.table-wrapper {
  max-height: 450px;     /* adjust height as needed */
  overflow-y: auto;      /* vertical scroll */
  margin-top: 20px;
  border-radius: 8px;
  box-shadow: 0 3px 6px rgba(0,0,0,0.1);
}

/* Optional: keep table header fixed */
.table-wrapper table thead th {
  position: sticky;
  top: 0;
  background: #4a4a4a;
  z-index: 2;
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
    <div class="s">

    <!-- ✅ Scrollable wrapper -->
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>No.</th>
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
            <td><?= htmlspecialchars($row['book_no']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= $row['borrowed_display'] ?></td>
            <td><?= $row['returned_display'] ?: '<i>Not returned</i>' ?></td>
            <td><?= ucfirst($row['status']) ?></td>
            <td><?= $penalty ?></td>
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

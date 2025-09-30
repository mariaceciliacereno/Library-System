<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ---------- Add book (category, title, No. of books) ---------- */
if (isset($_POST['add_book'])) {
    $category = trim($_POST['category']);
    $title = trim($_POST['title']);
    $number_of_books = isset($_POST['number_of_books']) ? intval($_POST['number_of_books']) : 0;

    if ($category && $title && $number_of_books > 0) {
        // Insert book, always set available_books = 1
        $stmt = $conn->prepare(
            "INSERT INTO books (category, title, number_of_books, available_books) 
             VALUES (?, ?, ?, 1)"
        );
        $stmt->bind_param("ssi", $category, $title, $number_of_books);
        $stmt->execute();
        $stmt->close();
    }
}
/* ---------- Update No. of Books ---------- */
if (isset($_POST['save_books'])) {
    $book_id         = intval($_POST['book_id']);
    $number_of_books = intval($_POST['update_number_of_books']);

    if ($number_of_books > 0) {
        $check = $conn->prepare("SELECT number_of_books, available_books FROM books WHERE id=?");
        $check->bind_param("i", $book_id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();
        $check->close();

        if ($row) {
            $borrowed = $row['number_of_books'] - $row['available_books'];
            $new_available = $number_of_books - $borrowed;
            if ($new_available < 0) $new_available = 0;

            $stmt = $conn->prepare("UPDATE books SET number_of_books=?, available_books=? WHERE id=?");
            $stmt->bind_param("iii", $number_of_books, $new_available, $book_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

/* ---------- Delete book ---------- */
if (isset($_GET['delete_book'])) {
    $book_id = intval($_GET['delete_book']);
    $conn->query("DELETE FROM borrow_requests WHERE book_id = $book_id");
    $conn->query("DELETE FROM books WHERE id = $book_id");
}

/* ---------- Approve borrow ---------- */
if (isset($_GET['approve_borrow'])) {
    $borrow_id = intval($_GET['approve_borrow']);
    $stmt = $conn->prepare("UPDATE borrow_requests SET status='approved' WHERE id=?");
    $stmt->bind_param("i", $borrow_id);
    $stmt->execute();
}

/* ---------- Delete borrow record ---------- */
if (isset($_GET['delete_borrow'])) {
    $borrow_id = intval($_GET['delete_borrow']);
    $conn->query("DELETE FROM borrow_requests WHERE id = $borrow_id");
}

/* ---------- Fetch borrowed books ---------- */
$stmt = $conn->prepare("
    SELECT 
        br.id AS borrow_id,
        b.number_of_books AS book_no,
        b.category,
        b.title,
        u.email,
        br.borrowed_at,                      -- keep raw datetime for any calculations
        br.returned_at,
        DATE_FORMAT(br.borrowed_at, '%d/%m/%Y %h:%i %p')  AS borrowed_display,  -- ✅ formatted
        DATE_FORMAT(br.returned_at,  '%d/%m/%Y %h:%i %p') AS returned_display   -- ✅ formatted
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.id
    JOIN books b ON br.book_id = b.id
    ORDER BY br.borrowed_at DESC
");
$stmt->execute();
$borrowed_books = $stmt->get_result();

/* ---------- Fetch all books ---------- */
$books = $conn->query("SELECT * FROM books ORDER BY category, title");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - Books Management</title>
<style>
body {
    margin:0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: url('magical-library.avif') no-repeat center center fixed;
    background-size: cover;
    color: #fff;
}
.container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px 30px;
    border-radius: 16px;
    background: rgba(255,255,255,0.12);
    box-shadow: 0 8px 32px rgba(31,38,135,0.37);
    backdrop-filter: blur(12px);
    position: relative;
}
h2,h3 {
    text-align:center;
    text-shadow:0 2px 8px rgba(0,0,0,0.7);
    margin-top: 10px;
    margin-bottom: 20px;
}
a.logout {
    position: absolute; top: 20px; right: 20px;
    background: #ff4c4c; color: white;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background 0.3s;
}
a.logout:hover { background: #e60000; }

.form-section {
    background: rgba(255,255,255,0.2);
    color: #fff;
    padding: 15px;
    margin-bottom: 30px;
    border-radius: 10px;
    text-align: center;
}
.form-section input[type=text],
.form-section input[type=number] {
    padding: 8px;
    margin: 8px 6px;
    width: 180px;
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 4px;
}
.form-section input[type=submit] {
    padding: 8px 20px;
    background: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s;
}
.form-section input[type=submit]:hover {
    background: #3e8e41;
}

.table-container {
    max-height: 260px;
    overflow-y: auto;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}
.table-container::-webkit-scrollbar { width: 8px; }
.table-container::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.4);
    border-radius: 4px;
}
table {
    width: 100%;
    border-collapse: collapse;
    min-width: 650px;
}
th, td {
    padding: 10px 12px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.25);
}
th {
    background: rgba(0,0,0,0.6);
    color: #fff;
    position: sticky;
    top: 0;
    font-weight: 600;
    letter-spacing: 0.5px;
}
td {
    background: rgba(255,255,255,0.85);
    color: #222;
}
.action-btn {
    padding: 5px 12px;
    text-decoration: none;
    color: #fff;
    background: #ff4c4c;
    border-radius: 4px;
    transition: background 0.3s;
}
.action-btn:hover { background: #c0392b; }
</style>
</head>
<body>
<div class="container">
    <a href="logout.php" class="logout">Logout</a>
    <h2>📚 Admin Dashboard</h2>

    <!-- Add Book Form -->
    <div class="form-section">
        <h3>Add New Book</h3>
        <form method="post">
            <input type="text" name="category" placeholder="Category" required>
            <input type="text" name="title" placeholder="Title" required>
            <input type="number" name="number_of_books" placeholder="No. of Books" min="1" required>
            <input type="submit" name="add_book" value="Add Book">
        </form>
    </div>

    <!-- All Books Table -->
    <h3>All Books</h3>
    <div class="table-container">
        <table>
            <tr>
                <th>No.</th>
                <th>Category</th>
                <th>Title</th>
                <th>Available</th>
                <th>Action</th>
            </tr>
            <?php while ($book = $books->fetch_assoc()): ?>
            <tr>
                <tr>
                <td><?= $book['number_of_books'] ?></td> <!-- ✅ show the No. of Books -->
                <td><?= htmlspecialchars($book['category']) ?></td>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><?= $book['available_books'] ?></td>
                <td>
                    <a class="action-btn"
                       href="?delete_book=<?= $book['id'] ?>"
                       onclick="return confirm('Delete this book?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Borrowed Books Table -->
<h3>Borrowed Books</h3>
<div class="table-container">
    <table>
        <tr>
            <th>No.</th>
            <th>Category</th>
            <th>Title</th>
            <th>User Email</th>
            <th>Borrowed At</th>
            <th>Returned At</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $borrowed_books->fetch_assoc()): ?>
        <tr>
            <td><?= $row['book_no'] ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <!-- ✅ use formatted date/time -->
            <td><?= $row['borrowed_display'] ?></td>
            <td><?= $row['returned_display'] ?: '<i>Not Returned</i>' ?></td>
            <td>
                <a class="action-btn"
                   href="?delete_borrow=<?= $row['borrow_id'] ?>"
                   onclick="return confirm('Delete this borrow record?')">Delete</a>
            </td>
        </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>

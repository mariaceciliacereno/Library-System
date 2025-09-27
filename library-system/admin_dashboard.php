<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle book addition
if (isset($_POST['add_book'])) {
    $category = trim($_POST['category']);
    $title = trim($_POST['title']);
    if (!empty($category) && !empty($title)) {
        $stmt = $conn->prepare("INSERT INTO books (category, title) VALUES (?, ?)");
        $stmt->bind_param("ss", $category, $title);
        $stmt->execute();
    }
}

// Handle book deletion
if (isset($_GET['delete_book'])) {
    $book_id = intval($_GET['delete_book']);
    $conn->query("DELETE FROM borrow_requests WHERE book_id = $book_id");
    $conn->query("DELETE FROM books WHERE id = $book_id");
}

// Handle borrow approval
if (isset($_GET['approve_borrow'])) {
    $borrow_id = intval($_GET['approve_borrow']);
    $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $borrow_id);
    $stmt->execute();
}

// Handle borrow record deletion
if (isset($_GET['delete_borrow'])) {
    $borrow_id = intval($_GET['delete_borrow']);
    $conn->query("DELETE FROM borrow_requests WHERE id = $borrow_id");
}

// Fetch borrowed books
$stmt = $conn->prepare("
    SELECT br.id AS borrow_id, 
           b.category, 
           b.title, 
           u.email, 
           br.borrowed_at, 
           br.returned_at
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.id
    JOIN books b ON br.book_id = b.id
    ORDER BY br.borrowed_at DESC
");
$stmt->execute();
$borrowed_books = $stmt->get_result();

// Fetch all books
$books = $conn->query("SELECT * FROM books ORDER BY category, title");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Books Management</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('magical-library.avif') no-repeat center center fixed;
            background-size: cover;
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 15px;
            background: rgba(124, 123, 123, 0.12);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(10px);
            color: #fff;
            position: relative;
        }
        h2 {
            text-align: center;
            text-shadow: 1px 1px 5px black;
        }
        /* Logout button style */
        a.logout {
            position: absolute;
            top: 20px;
            right: 20px;
            background: blue;
            color: white;
            padding: 8px 14px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        a.logout:hover {
            background: darkblue;
        }
        .table-container {
          max-height: 300px;
          overflow-y: auto;
          overflow-x: auto;
          margin-top: 10px;
          border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        th, td {
            padding: 10px;
            border: 1px solid rgba(255,255,255,0.3);
            text-align: center;
        }
        th {
            background: rgba(0,0,0,0.8);
            color: whitesmoke;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        td {
            background: rgba(255, 253, 253, 0.8);
            color: black;
        }
        a, button {
            padding: 5px 10px;
            text-decoration: none;
            color: white;
            background: red;
            border: none;
            border-radius: 4px;
        }
        a:hover, button:hover {
            background: darkred;
        }
        .form-section {
            background: rgba(248, 248, 248, 0.8);
            color: black;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
        }
        input[type=text] {
            padding: 8px;
            margin: 5px;
        }
        input[type=submit] {
            padding: 8px 15px;
            background: green;
            color: white;
            border: none;
            border-radius: 4px;
        }
        input[type=submit]:hover {
            background: darkgreen;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- ✅ Logout Button -->
    <a href="logout.php" class="logout">Logout</a>

    <h2>📚 Admin Dashboard</h2>

    <!-- Add Book Form -->
    <div class="form-section">
        <h3>Add New Book</h3>
        <form method="post">
            <input type="text" name="category" placeholder="Category" required>
            <input type="text" name="title" placeholder="Title" required>
            <input type="submit" name="add_book" value="Add Book">
        </form>
    </div>

    <!-- Books List -->
    <h3>All Books</h3>
    <div class="table-container">
        <table>
            <tr>
                <th>Category</th>
                <th>Title</th>
                <th>Action</th>
            </tr>
            <?php while ($book = $books->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($book['category']) ?></td>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><a href="?delete_book=<?= $book['id'] ?>" onclick="return confirm('Delete this book?')">Delete</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Borrowed Books -->
    <h3>Borrowed Books</h3>
    <div class="table-container">
        <table>
            <tr>
                <th>Category</th>
                <th>Title</th>
                <th>User Email</th>
                <th>Borrowed At</th>
                <th>Returned At</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $borrowed_books->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['borrowed_at'] ?></td>
                <td><?= $row['returned_at'] ? $row['returned_at'] : '<i>Not Returned</i>' ?></td>
                <td>
                    <a href="?delete_borrow=<?= $row['borrow_id'] ?>" 
                       onclick="return confirm('Delete this borrow record?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>

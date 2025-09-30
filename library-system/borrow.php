<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['book_id'])) {
    header("Location: books.php");
    exit();
}

$book_id = (int)$_POST['book_id'];
$email = $_SESSION['user'];

// find user id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: books.php?msg=no_user");
    exit();
}
$user_id = (int)$user['id'];

// check availability
$check = $conn->prepare("SELECT available_books FROM books WHERE id = ? LIMIT 1");
$check->bind_param("i", $book_id);
$check->execute();
$row = $check->get_result()->fetch_assoc();
$check->close();

if (!$row || (int)$row['available_books'] <= 0) {
    header("Location: books.php?msg=not_available");
    exit();
}

$conn->begin_transaction();
try {
    // decrease available books by 1
   $u = $conn->prepare("UPDATE books 
    SET available_books = available_books - 1 
    WHERE id = ? AND available_books > 0");
    $u->bind_param("i", $book_id);
    $u->execute();
    $u->close();

    // insert borrow record
    $ins = $conn->prepare("INSERT INTO borrow_requests (user_id, book_id, borrowed_at, status) VALUES (?, ?, NOW(), 'borrowed')");
    $ins->bind_param("ii", $user_id, $book_id);
    $ins->execute();
    $ins->close();

    $conn->commit();
    header("Location: books.php?msg=borrowed");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    header("Location: books.php?msg=error");
    exit();
}

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$email = $_SESSION['user'];
$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && isset($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];
    $user_id = $user['id'];

    // Check stock
    $stock = $conn->prepare("SELECT quantity FROM books WHERE id=?");
    $stock->bind_param("i", $book_id);
    $stock->execute();
    $qty = $stock->get_result()->fetch_assoc()['quantity'];

    if ($qty > 0) {
        // Decrease stock
        $conn->query("UPDATE books SET quantity = quantity - 1 WHERE id = $book_id");
        // Save borrow record
        $ins = $conn->prepare("INSERT INTO borrow_requests (user_id, book_id) VALUES (?, ?)");
        $ins->bind_param("ii", $user_id, $book_id);
        $ins->execute();
    }
}
header("Location: books.php");
exit();

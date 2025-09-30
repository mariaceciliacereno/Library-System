<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['book_id'])) {
    header("Location: books.php");
    exit();
}

$book_id = (int)$_POST['book_id'];
$email   = $_SESSION['user'];

/* ---------- Find the user ID ---------- */
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

$conn->begin_transaction();

try {
    /* ---------- Find active borrow request ---------- */
    $s = $conn->prepare("
        SELECT id
        FROM borrow_requests
        WHERE user_id = ? AND book_id = ? AND status != 'returned'
        ORDER BY id DESC
        LIMIT 1
    ");
    $s->bind_param("ii", $user_id, $book_id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();

    if (!$row) {
        throw new Exception("No active borrow record");
    }

    $borrow_id = (int)$row['id'];

    /* ---------- Mark as returned ---------- */
    $u = $conn->prepare("
        UPDATE borrow_requests
        SET status = 'returned', returned_at = NOW()
        WHERE id = ?
    ");
    $u->bind_param("i", $borrow_id);
    $u->execute();
    $u->close();

    /* ---------- Reset available_books correctly ---------- */
    // Make sure we never exceed number_of_books
    $b = $conn->prepare("
        UPDATE books
        SET available_books = LEAST(number_of_books, available_books + 1)
        WHERE id = ?
    ");
    $b->bind_param("i", $book_id);
    $b->execute();
    $b->close();

    $conn->commit();
    header("Location: books.php?msg=returned");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header("Location: books.php?msg=error");
    exit();
}

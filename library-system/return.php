<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);
    $email   = $_SESSION['user'];

    // Find the user ID
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) {
        header("Location: books.php?msg=user_not_found");
        exit();
    }
    $user_id = $user['id'];

    $conn->begin_transaction();
    try {
        /**
         * 1️⃣ Mark the borrow request as returned.
         *    We only update the most recent non-returned request for this user/book.
         */
        $update = $conn->prepare(
            "UPDATE borrow_requests
             SET status = 'returned', returned_at = NOW()
             WHERE user_id = ? AND book_id = ? AND status IN ('pending','approved')
             ORDER BY id DESC
             LIMIT 1"
        );
        $update->bind_param("ii", $user_id, $book_id);
        $update->execute();

        if ($update->affected_rows === 0) {
            // No active borrow to return
            throw new Exception("No active borrow record found.");
        }

        /** 2️⃣ Increase stock count */
        $inc = $conn->prepare("UPDATE books SET quantity = quantity + 1 WHERE id = ?");
        $inc->bind_param("i", $book_id);
        $inc->execute();

        $conn->commit();
        header("Location: books.php?msg=returned");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: books.php?msg=error");
    }
    exit();
}

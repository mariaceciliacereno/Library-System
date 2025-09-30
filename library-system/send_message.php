<?php
require 'db.php';

$sender   = $_POST['sender']   ?? '';
$receiver = $_POST['receiver'] ?? '';
$message  = $_POST['message']  ?? '';

if ($sender && $receiver && $message) {
    $stmt = $conn->prepare(
        "INSERT INTO messages (sender, receiver, message) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $sender, $receiver, $message);
    $stmt->execute();
}
?>

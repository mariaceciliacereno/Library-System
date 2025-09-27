<?php
require_once __DIR__ . '/../db.php';

$sender   = $_POST['sender']   ?? '';
$receiver = $_POST['receiver'] ?? '';
$message  = $_POST['message']  ?? '';

if (!$sender || !$receiver || !$message) {
    http_response_code(400);
    exit('Missing data');
}

$stmt = $conn->prepare(
    "INSERT INTO messages (sender, receiver, message, created_at)
     VALUES (?, ?, ?, NOW())"
);
$stmt->bind_param('sss', $sender, $receiver, $message);
$stmt->execute();
echo "OK";

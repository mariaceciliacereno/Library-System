<?php
require_once __DIR__ . '/../db.php';

$admin = $_GET['admin'] ?? '';
$user  = $_GET['user']  ?? '';

if (!$admin || !$user) {
    exit;  // nothing to fetch
}

$sql = "SELECT sender, message, created_at
        FROM messages
        WHERE (sender=? AND receiver=?)
           OR (sender=? AND receiver=?)
        ORDER BY created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ssss', $admin, $user, $user, $admin);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $class = $row['sender'] === $admin ? 'admin' : 'user';
    echo "<div class='bubble {$class}'>"
       . htmlspecialchars($row['message'])
       . "</div>";
}

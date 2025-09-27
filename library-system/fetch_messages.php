<?php
// fetch_messages.php - robust version
require 'db.php';            // adjust path if your db.php is elsewhere

$admin = $_GET['admin'] ?? '';
$user  = $_GET['user']  ?? '';

if ($admin === '' || $user === '') {
    // nothing to fetch
    exit;
}

// make sure messages table exists and get its columns
$colsRes = $conn->query("SHOW COLUMNS FROM messages");
if (!$colsRes) {
    die("Table `messages` not found. Create it (see error): " . $conn->error);
}

$columns = [];
while ($c = $colsRes->fetch_assoc()) {
    $columns[] = $c['Field'];
}

// choose a timestamp column (if any)
$candidates = ['sent_at','created_at','created','timestamp','time','date'];
$timestampCol = null;
foreach ($candidates as $cand) {
    if (in_array($cand, $columns, true)) {
        $timestampCol = $cand;
        break;
    }
}

// safe fallback column for ordering
$orderCol = $timestampCol ? $timestampCol : (in_array('id', $columns) ? 'id' : $columns[0]);

// Build SQL using the chosen order column (wrapped in backticks because name is controlled)
$sql = "SELECT sender, receiver, message, `$orderCol` 
        FROM messages
        WHERE (sender=? AND receiver=?) OR (sender=? AND receiver=?)
        ORDER BY `$orderCol` ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error . " — SQL: " . $sql);
}

$stmt->bind_param("ssss", $admin, $user, $user, $admin);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    // If sender equals admin, mark as admin bubble; else user bubble
    $cls = ($row['sender'] === $admin) ? 'admin' : 'user';
    echo "<div class='bubble {$cls}'>" 
         . "<b>" . htmlspecialchars($row['sender']) . ":</b> "
         . nl2br(htmlspecialchars($row['message']))
         . "</div>";
}

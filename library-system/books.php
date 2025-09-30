<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$userEmail = $_SESSION['user'];
// Get user id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$userRow = $stmt->get_result()->fetch_assoc();
$stmt->close();
$user_id = $userRow ? (int)$userRow['id'] : 0;

// Fetch list of book_ids this user has borrowed and NOT returned
$activeBorrowIds = [];
if ($user_id) {
    $b = $conn->prepare("SELECT book_id FROM borrow_requests WHERE user_id = ? AND status != 'returned'");
    $b->bind_param("i", $user_id);
    $b->execute();
    $res = $b->get_result();
    while ($r = $res->fetch_assoc()) {
        $activeBorrowIds[(int)$r['book_id']] = true;
    }
    $b->close();
}

// Fetch all books
$q = $q = "SELECT id, category, title, available_books, number_of_books FROM books ORDER BY id";
$stmt2 = $conn->prepare($q);
if (!$stmt2) { die("DB error: " . $conn->error); }
$stmt2->execute();
$books = $stmt2->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>📚 Library Books</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('mybooks.avif') no-repeat center center fixed;
            background-size: cover;
        }

        .navbar {
    display: flex;
    align-items: center;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 15px 30px;
}

.navbar-title {
    flex: 1; /* pushes icons to the right */
    font-size: 20px;
    cursor: pointer;
}

.navbar-icons {
    display: flex;
    justify-content: flex-end; /* align icons to the right */
    align-items: center;
}

.navbar-icons a {
    margin-left: 20px;
    font-size: 22px;
    color: white;
    text-decoration: none;
}

.navbar-icons a:hover {
    color: #ffdd57;
}

.content {
   max-width: 1000px;
    margin: 50px auto;
    background: rgba(255, 255, 255, 0.15);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.18);
}
.table-wrapper {
            max-height: 400px;
            overflow-y: auto;
            border-radius: 10px;
        }
table {
 width: 100%;
 border-collapse: collapse;
 background-color: rgba(255, 255, 255, 0.75);
 border-top-left-radius: 8px;
border-bottom-left-radius: 8px;
 overflow: hidden;
}

th, td {
padding: 12px 15px;
text-align: center;
border-bottom: 1px solid #070707ff;
}

th {
background-color: rgba(244, 197, 66, 0.8);
}
 
.borrow-btn {
padding: 8px 14px;
background: #ecd92ae5;
border: none;
border-radius: 5px;
cursor: pointer;
font-weight: bold;
}

.borrow-btn:hover {
background-color: #e0a800;
}
.icon-badge {
position: relative;
display: inline-block;
margin-left: 20px;
color: white;
}

.icon-badge svg {
vertical-align: middle;
transition: transform 0.2s ease-in-out;
    }

.icon-badge:hover svg {
transform: scale(1.1);
}

.badge {
position: absolute;
top: -5px;
right: -10px;
background-color: red;
color: white;
font-size: 12px;
font-weight: bold;
padding: 2px 6px;
border-radius: 50%;
box-shadow: 0 0 3px rgba(0,0,0,0.3);

}
.title{
    position: sticky;
    }
</style>
</head>
<body>

<div class="navbar">
    <div class="navbar-title">📚 BOOKS</div>
    <div class="navbar-icons">
        <a href="profile.php" title="Profile">
            <svg  xmlns="http://www.w3.org/2000/svg" width="30" height="30"  
            fill="currentColor" viewBox="0 0 24 24" >
            <!--Boxicons v3.0 https://boxicons.com | License  https://docs.boxicons.com/free-->
            <path d="M12 6c-2.28 0-4 1.72-4 4s1.72 4 4 4 4-1.72 4-4-1.72-4-4-4m0 6c-1.18 0-2-.82-2-2s.82-2 2-2 2 .82 2 2-.82 2-2 2"></path><path d="M12 2C6.49 2 2 6.49 2 12c0 3.26 1.58 6.16 4 7.98V20h.03c1.67 1.25 3.73 2 5.97 2s4.31-.75 5.97-2H18v-.02c2.42-1.83 4-4.72 4-7.98 0-5.51-4.49-10-10-10M8.18 19.02C8.59 17.85 9.69 17 11 17h2c1.31 0 2.42.85 2.82 2.02-1.14.62-2.44.98-3.82.98s-2.69-.35-3.82-.98m9.3-1.21c-.81-1.66-2.51-2.82-4.48-2.82h-2c-1.97 0-3.66 1.16-4.48 2.82A7.96 7.96 0 0 1 4 11.99c0-4.41 3.59-8 8-8s8 3.59 8 8c0 2.29-.97 4.36-2.52 5.82"></path>
            </svg>
        </a>
        <a href="mybooks.php" title="My Books">

            <svg  xmlns="http://www.w3.org/2000/svg" width="30" height="30"  
            fill="currentColor" viewBox="0 0 24 24" >
            <!--Boxicons v3.0 https://boxicons.com | License  https://docs.boxicons.com/free-->
           <path d="M8 6h9v2H8z"></path><path d="M20 2H6C4.35 2 3 3.35 3 5v14c0 1.65 1.35 3 3 3h15v-2H6c-.55 0-1-.45-1-1s.45-1 1-1h14c.55 0 1-.45 1-1V3c0-.55-.45-1-1-1m-6 14H6c-.35 0-.69.07-1 .18V5c0-.55.45-1 1-1h13v12z"></path>
          </svg>
        </a>
           <a href="user_message.php" title="Messages">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30"
                 fill="currentColor" viewBox="0 0 24 24">
                <path d="M4 4h16v12H5.17L4 17.17V4m0-2a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 
                         2 0 0 0-2-2H4z"/>
            </svg>
        </a>
        <a href="settings.php" title="Settings">
            <svg  xmlns="http://www.w3.org/2000/svg" width="30" height="30"  
           fill="currentColor" viewBox="0 0 24 24" >
           <!--Boxicons v3.0 https://boxicons.com | License  https://docs.boxicons.com/free-->
           <path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4m0 6c-1.08 0-2-.92-2-2s.92-2 2-2 2 .92 2 2-.92 2-2 2"></path><path d="m20.42 13.4-.51-.29c.05-.37.08-.74.08-1.11s-.03-.74-.08-1.11l.51-.29c.96-.55 1.28-1.78.73-2.73l-1-1.73a2.006 2.006 0 0 0-2.73-.73l-.53.31c-.58-.46-1.22-.83-1.9-1.11v-.6c0-1.1-.9-2-2-2h-2c-1.1 0-2 .9-2 2v.6c-.67.28-1.31.66-1.9 1.11l-.53-.31c-.96-.55-2.18-.22-2.73.73l-1 1.73c-.55.96-.22 2.18.73 2.73l.51.29c-.05.37-.08.74-.08 1.11s.03.74.08 1.11l-.51.29c-.96.55-1.28 1.78-.73 2.73l1 1.73c.55.95 1.77 1.28 2.73.73l.53-.31c.58.46 1.22.83 1.9 1.11v.6c0 1.1.9 2 2 2h2c1.1 0 2-.9 2-2v-.6a8.7 8.7 0 0 0 1.9-1.11l.53.31c.95.55 2.18.22 2.73-.73l1-1.73c.55-.96.22-2.18-.73-2.73m-2.59-2.78c.11.45.17.92.17 1.38s-.06.92-.17 1.38a1 1 0 0 0 .47 1.11l1.12.65-1 1.73-1.14-.66c-.38-.22-.87-.16-1.19.14-.68.65-1.51 1.13-2.38 1.4-.42.13-.71.52-.71.96v1.3h-2v-1.3c0-.44-.29-.83-.71-.96-.88-.27-1.7-.75-2.38-1.4a1.01 1.01 0 0 0-1.19-.15l-1.14.66-1-1.73 1.12-.65c.39-.22.58-.68.47-1.11-.11-.45-.17-.92-.17-1.38s.06-.93.17-1.38A1 1 0 0 0 5.7 9.5l-1.12-.65 1-1.73 1.14.66c.38.22.87.16 1.19-.14.68-.65 1.51-1.13 2.38-1.4.42-.13.71-.52.71-.96v-1.3h2v1.3c0 .44.29.83.71.96.88.27 1.7.75 2.38 1.4.32.31.81.36 1.19.14l1.14-.66 1 1.73-1.12.65c-.39.22-.58.68-.47 1.11Z"></path>
           </svg>
        </a>
    </div>
</div>

<div class="content">
     <div class="table-wrapper">
    <table>
        <thead>
            <tr>
                      <th>No.</th>
                    <th>Category</th>
                    <th>Title</th>
                    <th>Available</th>
                    <th>Action</th>
            </tr>
        </thead>
        <tbody>
           <?php while ($book = $books->fetch_assoc()): 
                    $bookId = (int)$book['id'];
                    $availableCount = (int)$book['available_books'];
                    $isAvailable = $availableCount > 0;
                    $userHasBorrow = isset($activeBorrowIds[$bookId]);
                ?>
              <tr>
                    <td><?= $book['number_of_books'] ?></td>
                    <td><?= htmlspecialchars($book['category']) ?></td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= $book['available_books'] ?></td>
                    <td>
                       <!-- Borrow form -->
                       <form action="borrow.php" method="post" style="display:inline;">
                         <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                         <button class="borrow-btn" type="submit"
                         <?= $book['available_books'] == 1 ? '' : 'disabled' ?>>Borrow</button>
                      </form>

                       <!-- Return form -->
                      <form action="return.php" method="post" style="display:inline;margin-left:8px;">
                         <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                         <button class="return-btn" type="submit"
                         <?= ($book['available_books'] == 0 && $userHasBorrow) ? '' : 'disabled' ?>>Return</button>
                     </form>
                  </td>
              </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
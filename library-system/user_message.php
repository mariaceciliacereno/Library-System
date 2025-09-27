<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'member') {
    // Only logged-in members can access
    header("Location: login.php");
    exit();
}

$userEmail  = $_SESSION['user'];       // current logged-in user
$adminEmail = "admin@library.com";     // your admin email
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>User Message</title>
<style>
body{
    font-family:Arial, sans-serif;
    background:#f3f3f3;
}
.container{
    width:700px;
    margin:30px auto;
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}
.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.chat-box{
    height:350px;
    overflow-y:auto;
    border:1px solid #ccc;
    padding:10px;
    margin-top:10px;
    display:flex;
    flex-direction:column;
}
.bubble{
    margin:6px 0;
    padding:10px 14px;
    border-radius:16px;
    max-width:70%;
    word-wrap:break-word;
}
.bubble.admin{
    align-self:flex-start;      /* left side for admin */
    background:#d1e7dd;
    border-top-left-radius:0;
}
.bubble.user{
    align-self:flex-end;        /* right side for user */
    background:#f8d7da;
    border-top-right-radius:0;
}
.input-row{
    margin-top:10px;
    display:flex;
    gap:10px;
}
input[type=text]{flex:1;padding:8px;}
button{padding:8px 12px;cursor:pointer;}
.back-btn{
    background:#007bff;
    color:#fff;
    text-decoration:none;
    padding:6px 12px;
    border-radius:4px;
}
.back-btn:hover{background:#0056b3;}
</style>
</head>
<body>
<div class="container">
  <div class="top-bar">
    <h2>User Message</h2>
    <a href="books.php" class="back-btn">⬅ Back to Books</a>
  </div>

  <div id="chat" class="chat-box"></div>

  <div class="input-row">
    <input type="text" id="message" placeholder="Type message">
    <button onclick="send()">Send</button>
  </div>
</div>

<script>
const user  = <?php echo json_encode($userEmail); ?>;
const admin = <?php echo json_encode($adminEmail); ?>;

function loadChat(){
   fetch(`fetch_messages.php?admin=${encodeURIComponent(admin)}&user=${encodeURIComponent(user)}&_=${Date.now()}`)
     .then(r=>r.text())
     .then(html=>{
         document.getElementById('chat').innerHTML = html;
         const box = document.getElementById('chat');
         box.scrollTop = box.scrollHeight; // auto-scroll to bottom
     });
}

function send(){
   const msg = document.getElementById('message').value.trim();
   if(!msg) return;
   fetch('send_message.php',{
       method:'POST',
       headers:{'Content-Type':'application/x-www-form-urlencoded'},
       body:`sender=${encodeURIComponent(user)}&receiver=${encodeURIComponent(admin)}&message=${encodeURIComponent(msg)}`
   }).then(()=>{
       document.getElementById('message').value='';
       loadChat();
   });
}

setInterval(loadChat,2000);
loadChat();
</script>
</body>
</html>

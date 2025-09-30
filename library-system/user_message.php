<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

$userEmail  = $_SESSION['user'];
$adminEmail = "admin@library.com";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>User Message</title>
<style>
/* ===== Global Page Background ===== */
body{
    margin:0;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background: url('mybooks.avif') no-repeat center center fixed; /* <- change image */
    background-size: cover;
}

/* ===== Centered Glass Card ===== */
.container{
    max-width:750px;
    margin:60px auto;
    background: rgba(255,255,255,0.85);
    padding:30px;
    border-radius:20px;
    box-shadow: 0 12px 25px rgba(0,0,0,0.25);
    backdrop-filter: blur(8px);
}

/* ===== Top Bar ===== */
.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom: 10px;
}
.top-bar h2{
    margin:0;
    font-size:26px;
    color:#333;
}
.back-btn{
    background: linear-gradient(135deg,#007bff,#0056b3);
    color:#fff;
    text-decoration:none;
    padding:8px 16px;
    border-radius:30px;
    font-weight:600;
    transition: all 0.25s ease;
}
.back-btn:hover{
    background: linear-gradient(135deg,#0056b3,#003f7f);
    transform: translateY(-2px);
}

/* ===== Chat Box ===== */
.chat-box{
    height:400px;
    overflow-y:auto;
    border:1px solid #ddd;
    padding:15px;
    margin-top:10px;
    display:flex;
    flex-direction:column;
    background: rgba(255,255,255,0.7);
    border-radius:12px;
    box-shadow: inset 0 2px 6px rgba(0,0,0,0.1);
}

/* ===== Bubbles ===== */
.bubble{
    margin:8px 0;
    padding:12px 16px;
    border-radius:18px;
    max-width:70%;
    word-wrap:break-word;
    font-size:15px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}
.bubble.admin{
    align-self:flex-start;
    background: #d1e7dd;
    color:#0f5132;
    border-top-left-radius:0;
}
.bubble.user{
    align-self:flex-end;
    background: #f8d7da;
    color:#842029;
    border-top-right-radius:0;
}

/* ===== Input Row ===== */
.input-row{
    margin-top:15px;
    display:flex;
    gap:10px;
}
input[type=text]{
    flex:1;
    padding:10px 12px;
    border:1px solid #ccc;
    border-radius:30px;
    outline:none;
    font-size:15px;
}
button{
    padding:10px 20px;
    border:none;
    border-radius:30px;
    background:linear-gradient(135deg,#28a745,#218838);
    color:#fff;
    font-weight:600;
    cursor:pointer;
    transition: all 0.25s ease;
}
button:hover{
    background:linear-gradient(135deg,#218838,#1e7e34);
    transform: translateY(-2px);
}
</style>
</head>
<body>
<div class="container">
  <div class="top-bar">
    <h2>💬 User Message</h2>
    <a href="books.php" class="back-btn">Back to Books</a>
  </div>

  <div id="chat" class="chat-box"></div>

  <div class="input-row">
    <input type="text" id="message" placeholder="Type your message...">
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
         box.scrollTop = box.scrollHeight;
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

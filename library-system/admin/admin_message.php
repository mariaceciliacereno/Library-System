<?php
// Assume admin is logged in
$adminEmail = "admin@library.com";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Message</title>
<style>
/* -------- Page Background -------- */
body{
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    margin:0;
    padding:0;
}

/* -------- Main Container -------- */
.container{
    width:700px;
    margin:40px auto;
    background:rgba(255,255,255,0.95);
    padding:25px;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,0.25);
    backdrop-filter: blur(6px);
}

/* -------- Header Bar -------- */
.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}
.top-bar h2{
    margin:0;
    font-size:1.6rem;
    color:#333;
    letter-spacing:0.5px;
}
.back-btn{
    background:#2575fc;
    color:#fff;
    text-decoration:none;
    padding:8px 16px;
    border-radius:6px;
    transition:background 0.3s;
}
.back-btn:hover{background:#1a5ed1;}

/* -------- Chat Box -------- */
.chat-box{
    height:360px;
    overflow-y:auto;
    border:1px solid #ccc;
    padding:15px;
    margin-top:10px;
    margin-bottom:15px;
    display:flex;
    flex-direction:column;
    border-radius:12px;
    background:#f7f9fc;
    box-shadow:inset 0 0 8px rgba(0,0,0,0.05);
}

/* -------- Bubbles -------- */
.bubble{
    margin:8px 0;
    padding:12px 16px;
    border-radius:20px;
    max-width:70%;
    word-wrap:break-word;
    font-size:0.95rem;
}
.bubble.user{
    align-self:flex-start;
    background:#e0e0e0;
    color:#333;
    border-top-left-radius:0;
}
.bubble.admin{
    align-self:flex-end;
    background:#a5d8ff;
    color:#05396b;
    border-top-right-radius:0;
}

/* -------- Input Row -------- */
label{
    font-weight:600;
    color:#333;
}

#userEmail{
    width:100%;
    padding:8px;
    margin-bottom:12px;
    border:1px solid #ccc;
    border-radius:6px;
}
.input-row{
    display:flex;
    gap:10px;
}
input[type=text]{
    flex:1;
    padding:10px;
    border-radius:6px;
    border:1px solid #bbb;
}
button{
    padding:10px 18px;
    background:#2575fc;
    border:none;
    color:#fff;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
    transition:background 0.3s;
}
button:hover{background:#1a5ed1;}
</style>
</head>
<body>
<div class="container">
  <div class="top-bar">
    <h2>Admin Message</h2>
    <a href="index.php" class="back-btn">⬅ Go Back</a>
  </div>

  <label for="userEmail">User Email:</label>
  <input type="text" id="userEmail" placeholder="user@example.com" value="mariacecilia58@gmail.com">

  <div id="chat" class="chat-box"></div>

  <div class="input-row">
    <input type="text" id="message" placeholder="Type message">
    <button onclick="send()">Send</button>
  </div>
</div>

<script>
const admin = "<?php echo $adminEmail; ?>";

function loadChat(){
   const user = document.getElementById('userEmail').value.trim();
   if(!user) return;
   fetch(`fetch_messages.php?admin=${encodeURIComponent(admin)}&user=${encodeURIComponent(user)}&_=${Date.now()}`)
     .then(r=>r.text())
     .then(html=>{
         document.getElementById('chat').innerHTML = html;
         const box = document.getElementById('chat');
         box.scrollTop = box.scrollHeight; // auto-scroll
     });
}
function send(){
   const user = document.getElementById('userEmail').value.trim();
   const msg  = document.getElementById('message').value.trim();
   if(!user || !msg) return;
   fetch('send_message.php',{
       method:'POST',
       headers:{'Content-Type':'application/x-www-form-urlencoded'},
       body:`sender=${encodeURIComponent(admin)}&receiver=${encodeURIComponent(user)}&message=${encodeURIComponent(msg)}`
   }).then(()=>{
       document.getElementById('message').value='';
       loadChat();
   });
}
setInterval(loadChat,2000);
</script>
</body>
</html>

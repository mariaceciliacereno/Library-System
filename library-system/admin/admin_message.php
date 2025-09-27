<?php
// Assume admin is logged in
$adminEmail = "admin@library.com";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Message</title>
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
    margin-bottom:10px;
}
.back-btn{
    background:#007bff;
    color:#fff;
    text-decoration:none;
    padding:6px 12px;
    border-radius:4px;
}
.back-btn:hover{background:#0056b3;}
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
.bubble.user{          /* user message = left side */
    align-self:flex-start;
    background:#e4e4e4;
    border-top-left-radius:0;
}
.bubble.admin{         /* admin message = right side */
    align-self:flex-end;
    background:#cfe9ff;
    border-top-right-radius:0;
}
.input-row{
    margin-top:10px;
    display:flex;
    gap:10px;
}
input[type=text]{flex:1;padding:8px;}
button{padding:8px 12px;cursor:pointer;}
</style>
</head>
<body>
<div class="container">
  <div class="top-bar">
    <h2>Admin Message</h2>
    <!-- Go Back button (adjust href to your admin page, e.g., admin_dashboard.php) -->
    <a href="index.php" class="back-btn">Go Back</a>
  </div>

  <label>User Email:</label>
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
     .then(html=>document.getElementById('chat').innerHTML=html);
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Library Books</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('mybooks.avif') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }

        .box {
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            background: rgba(6, 0, 10, 0.1);
            border-radius: 20px;
            border: 1px solid rgba(184, 132, 214, 0.2);
            box-shadow: 0 8px 32px 0 rgba(10, 10, 10, 0.37);
            padding: 40px;
            width: 400px;
            max-width: 90%;
            margin: 100px auto;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 26px;
            color: white;
        }

        input, select, button {
            padding: 10px;
            margin: 10px 0;
            width: 70%;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            backdrop-filter: blur(5px);
        }

        input::placeholder {
            color: #eee;
        }

        select {
            color: white;
        }

        button {
            background-color: rgba(255, 255, 255, 0.3);
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: rgba(255, 255, 255, 0.5);
        }

        a {
            color: #ddd;
            text-decoration: none;
            font-size: 14px;
        }

        a:hover {
            text-decoration: underline;
        }

        .msg {
            color: #ff4d4d;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2><wbr>Welcome To Library Books</wbr></h2>
        <a href="login.php"><button type="submit">Login</button></a>
         <a href="login.php"><button type="submit">Register</button></a>
    </div>
</body>
</html>
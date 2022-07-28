<?php

session_start();
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] !== true) {
    header("location: domain.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        .topnav {
            overflow: hidden;
            background-color: burlywood;
        }

        .topnav a {
            float: left;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 17px;
        }

        .topnav a:hover {
            background-color: #ddd;
            color: black;
        }

        .topnav a.active {
            background-color: #4CAF50;
            color: white;
        }
    </style>
    <title>content</title>
</head>

<body>
    <div class="topnav">

        <a href="logout.php">Log Out</a>
    </div>
    <h1 style="color: red; text-align:center">WELCOME <?php echo $_GET["email"] ?> </h1>
</body>

</html>
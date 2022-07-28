<?php
$servername = "localhost";
$username = "root";
$password = "";
$conn = new mysqli($servername, $username, $password, "mydb2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

include_once dirname(__FILE__) ."/session.php";
include_once dirname(__FILE__) ."/auth.php";
include_once dirname(__FILE__) ."/url.php";

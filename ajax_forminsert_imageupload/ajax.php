<?php
$servername = "localhost";
$username = "root";
$password = "";
$conn = new mysqli($servername, $username, $password, "mydb2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$fname = $_POST['fname'];
$lname = $_POST['lname'];
$email = $_POST['email'];
$file = $_FILES['file'];
$filename = $file['name'];
$extension = pathinfo($filename, PATHINFO_EXTENSION);
$new_name = rand() . "." . $extension;
$path = "images/$new_name";
move_uploaded_file($file['tmp_name'], $path);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("INSERT into customers (first_name,last_name,email,file_path)values(?,?,?,?)");
    //print_r($conn->error);
    $stmt->bind_param("ssss", $fname, $lname, $email, $path);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}
$res = array("msg" => 'data saved');
echo json_encode($res);

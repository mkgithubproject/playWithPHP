<?php
include "connection.php";
session_start();
if (empty($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: content.php?email=".$_SESSION["user"]);
    exit;
}
$nameErr = $emailErr = $passErr = $regi_info = "";
$name = $email = $password = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!preg_match("/^[a-z0-9_-]{3,16}$/", $_POST["user_name"])) {
    $nameErr = "Invalid user name:<br>
    Character limit between 3 – 16. and Allow only alphanumeric characters and dashes.";
  } else {
    $name = $_POST["user_name"];
    $nameErr = "";
  }
  if (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $_POST["email"])) {
    $emailErr = "Invalid email";
  } else {
    $email = $_POST["email"];
    $emailErr = "";
  }

  if (!preg_match("/^(?=.*[A-Z].*[A-Z])(?=.*[!@#$&*])(?=.*[0-9].*[0-9])(?=.*[a-z].*[a-z].*[a-z]).{8}$/
  ", $_POST["pwd"])) {
    $passErr = "Invalid password";
  } else {
    $password = $_POST["pwd"];
    $passErr = "";
  }
  if ($nameErr == "" && $emailErr == "" && $passErr == "" && $_POST["csrf_token"] == $_SESSION["token"]) {
    $sql = "select email from users where email='$email'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
      $regi_info = "Email already exists";
    } else {
      $sql = "insert into users (name,email,password)values('$name','$email','$password')";
      $conn->query($sql);
      $regi_info = "";
      header("location: login.php");
    }
  }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration</title>
</head>

<body>
  <form action="" method="POST" style="text-align: center;">
    <div class="container">
      <h1>Register</h1>
      <p>Please fill in this form to create an account.</p>
      <label for="name"><b>Name</b></label>
      <input type="text" placeholder="Enter Name" name="user_name" id="name" value="<?php echo $name ?>"><br>
      <span style="color: red;"><?php echo $nameErr; ?> </span><br>
      <label for="email"><b>Email</b></label>
      <input type="text" placeholder="Enter Email" name="email" id="email" value="<?php echo $email ?>"><br>
      <span style="color: red;"><?php echo $emailErr; ?> </span><br>
      <label for="psw"><b>Password</b></label>
      <input type="password" placeholder="Enter Password" name="pwd" id="psw" value="<?php echo $password ?>"><br>
      <span style="color: red;"><?php echo $passErr; ?> </span><br>
      <input type="hidden" name="csrf_token" value="<?php echo $token;?>" />
      <button type="submit">Register</button>
    </div>
    <div class="container signin">
      <span style="color: red;"><?php echo $regi_info; ?></span>
      <p>Already have an account? <a href="login.php">Sign in</a>.</p>
    </div>
    <div style="text-align: center;color:green">
      <p>Ensure password has two uppercase letters.<br>
        Ensure password has one special case <br>
        Ensure password has two digits.<br>
        Ensure password has three lowercase letters<br>
        Ensure password is of length 8.</p>
    </div>
  </form>
</body>

</html>
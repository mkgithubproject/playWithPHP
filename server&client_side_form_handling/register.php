<?php
include "connection.php";
include "session.php";
include "authentic.php";
if(is_authentic()){
  header("location: content.php?email=" . $_SESSION["user"]);
  exit;
}
$nameErr = $emailErr = $passErr = $regi_info = "";
$name = $email = $password = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!preg_match("/^[a-zA-Z]{1,}$/", $conn -> real_escape_string($_POST["user_name"]))) {
    $nameErr = "Invalid user name:Only character allowed";
  } else {
    $name =  $conn -> real_escape_string($_POST["user_name"]);
    $nameErr = "";
  }
  if (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",  $conn -> real_escape_string($_POST["email"]))) {
    $emailErr = "Invalid email";
  } else {
    $email = $conn -> real_escape_string($_POST["email"]);
    $emailErr = "";
  }

  if (!preg_match("/^(?=.*[\d])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*])[\w!@#$%^&*]{8,}$/
  ",  $conn -> real_escape_string($_POST["pwd"]))) {
    $passErr = "must contain 8 character at least one uppercase ,lower case ,numeric,and special character";
  } else {
    $password =  $conn -> real_escape_string($_POST["pwd"]);
    $passErr = "";
  }
  if ($nameErr == "" && $emailErr == "" && $passErr == "" && $_POST["csrf_token"] == $_SESSION["token"]) {
    $stmt = $conn->prepare("select email from users where email=? ");
    $stmt->bind_param("s", $email);
    $executed = $stmt->execute();
    $result = $stmt->get_result();
    if ($executed==true && $result->num_rows > 0) {
      $regi_info = "Email already exists";
    } else {
      $stmt = $conn->prepare("insert into users (name,email,password)values(?,?,?)");
      $stmt->bind_param("sss",$name, $email,$password);
       $stmt->execute();
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
  <!-- <style>
        <?php include 'style.css'; ?>
    </style> -->
    <!-- // or -->
    <link rel="stylesheet" href="style.css">
    <script src="./script/register.js" type="text/javascript">
  </script>
</head>

<body>
  <?php include "header.php";?>
  <form action="" method="POST" style="text-align: center;" onsubmit="return register()">
    <div class="container">
      <h1>Register</h1>
      <p>Please fill in this form to create an account.</p>
      <label for="name"><b>Name</b></label>
      <input type="text" placeholder="Enter Name" name="user_name" id="name" value="<?php echo $name ?>"><br>
      <span style="color: red;"id="nameHelp"><?php echo $nameErr; ?> </span><br>
      <label for="email"><b>Email</b></label>
      <input type="text" placeholder="Enter Email" name="email" id="email" value="<?php echo $email ?>"><br>
      <span style="color: red;" id="emailHelp"><?php echo $emailErr; ?> </span><br>
      <label for="psw"><b>Password</b></label>
      <input type="password" placeholder="Enter Password" name="pwd" id="psw" value="<?php echo $password ?>"><br>
      <span style="color: red;" id="passwordHelp"><?php echo $passErr; ?> </span><br>
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>" />
      <button type="submit">Register</button>
    </div>
    <div class="container signin">
      <span style="color: red;"><?php echo $regi_info; ?></span>
      <p>Already have an account? <a href="login.php">Sign in</a>.</p>
    </div>
    
  </form>
</body>

</html>
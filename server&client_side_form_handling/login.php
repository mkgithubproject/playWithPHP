<?php
include "connection.php";
include "session.php";
include "authentic.php";
if (is_authentic()) {
    header("location: content.php?email=" . $_SESSION["user"]);
    exit;
}
$login_info = $emailError = $passError = "";
$email = $password = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {


    if (empty($_POST["email"])) {
        $emailError = "*All field required.";
    } else {
        $email =  $conn->real_escape_string($_POST["email"]);
        $emailError = "";
    }
    if (empty($_POST["psw"])) {
        $passError = "*All field required.";
    } else {
        $password =  $conn->real_escape_string($_POST["psw"]);
        $passError = "";
    }
    //    echo $sql = "SELECT id,email ,password FROM users where email='$email' AND password='$password' ";
    //     $result = $conn->query($sql);
    // if ($result->num_rows > 0) {
    //             header("location: content.php?email=".$email);
    //         } else {
    //             $login_info = "Not  registered";
    //         }
    //var_dump($conn->error);
    // prepare and bind
    if ($emailError == "" && $passError == "") {
        $stmt = $conn->prepare("SELECT id,email ,password FROM users where email=? AND password=? ");
        $stmt->bind_param("ss", $email, $password);
        $executed = $stmt->execute();
        $result = $stmt->get_result();
        if ($executed == true && ($result->num_rows > 0 && $_POST["csrf_token"] == $_SESSION["token"])) {
            $_SESSION["loggedin"] = true;
            $_SESSION["user"] = $email;
            header("location: content.php");
        } else {
            $login_info = "Not  registered";
        }
    } 
    
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <style>
        <?php include 'style.css'; ?>
    </style>
     <script src="./script/login.js" type="text/javascript">
  </script>
</head>

<body>
    <?php include "header.php"; ?>
    <form action="" method="POST" style="text-align: center;" onsubmit="return login()">
        <div class="container">
            <h1>LOGIN FORM</h1>
            <label for="email"><b>Email</b></label>
            <input type="text" id="email" placeholder="Enter Email" name="email" value="<?php echo $email ?>"><br>
            <small id="emailHelp" style="color: red;" ><?php echo $emailError; ?></small><br>
            <label for="psw"><b>Password</b></label>
            <input type="password" id="psw" placeholder="Enter Password" name="psw" value="<?php echo $password ?>"><br>
            <small id="passwordHelp" style="color: red;" ><?php echo $passError; ?></small><br>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>" /><br>
            <button type="submit">Login</button>
        </div>
        <div class="container signin">
            <br>
            <span style="color: red;"><?php echo $login_info; ?></span>
            <p>Don't have an account? <a href="register.php">Sign up</a>.</p>
        </div>
    </form>
</body>

</html>
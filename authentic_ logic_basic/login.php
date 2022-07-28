<?php
include "connection.php";
session_start();
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: content.php?email=" . $_SESSION["user"]);
    exit;
}
$login_info = "";
$email = $password = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["psw"];
    //    echo $sql = "SELECT id,email ,password FROM users where email='$email' AND password='$password' ";
    //     $result = $conn->query($sql);
    // if ($result->num_rows > 0) {
    //             header("location: content.php?email=".$email);
    //         } else {
    //             $login_info = "Not  registered";
    //         }
    //var_dump($conn->error);
    // prepare and bind
    $stmt = $conn->prepare("SELECT id,email ,password FROM users where email=? AND password=? ");
    $stmt->bind_param("ss", $email, $password);
    $executed = $stmt->execute();
    $result = $stmt->get_result();
    if ($executed == true) {
        if ($result->num_rows > 0) {
            $_SESSION["loggedin"] = true;
            $_SESSION["user"] = $email;
            header("location: content.php?email=" . $email);
        } else {
            $login_info = "Not  registered";
        }
    } else {
        $login_info = "Unexecuted  error ";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
</head>
<body>
    <form action="" method="POST" style="text-align: center;">
        <div class="container">
            <h1>LOGIN FORM</h1>
            <label for="email"><b>Email</b></label>
            <input type="text" id="email" placeholder="Enter Email" name="email" value="<?php echo $email ?>" required>
            <label for="psw"><b>Password</b></label>
            <input type="password" id="psw" placeholder="Enter Password" name="psw" value="<?php echo $password ?>" required>
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
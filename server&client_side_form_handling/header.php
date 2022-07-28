<!-- <?php
// echo '<div class="topnav">
//         <a class="active" href="domain.php">Home</a>
//         <a href="register.php">Sign Up</a>
//         <a href="login.php">Sign In</a>
//         <a href="logout.php">Sign Out</a>
//     </div>'
// if you r using nav bar inside php then need echo else dont use php tag 
    ?> -->
<!--  OR  -->
<?php
include_once("session.php");
?>
<div class="topnav">
        
        <?php
        if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
            echo '<a  href="index.php">Home</a>';
            echo '<a href="logout.php" >Sign Out</a>';
            echo '<a href="content.php">User</a>';
        }else{
            echo '<a  href="index.php">Home</a>';
           echo '<a href="register.php">Sign Up</a>';
           echo '<a href="login.php">Sign In</a>';
        } ?>
       
        
    </div>

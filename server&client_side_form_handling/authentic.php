<?php
include "session.php";
function is_authentic(){
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
      }
      
      if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        return true;
      }
      else{
        return false;
      }
     
}

?>
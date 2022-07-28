<?php  
session_start(); 

 if(!empty($_SESSION)){
    echo "User is: ".$_SESSION["user"];
 }
 else{
     echo 'the session is either empty or doesn\'t exist';
 }
 
?> 
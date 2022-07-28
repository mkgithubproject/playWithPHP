<?php  

 
if(isset($_COOKIE["user"])) {  
    echo "User is: ".$_COOKIE["user"];  
} else {  
    echo 'the cookie is either empty or doesn\'t exist';
}  
?> 
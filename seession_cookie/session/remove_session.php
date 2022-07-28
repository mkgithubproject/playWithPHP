<?php  
session_start();    
session_destroy(); 
if(!empty($_SESSION)){
echo "session is removed"; 
} 
else{
    echo "session is empty";
}
?> 
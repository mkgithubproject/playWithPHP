<?php
include_once "session.php";
function is_authentic(){
	if (empty($_SESSION['sg']['token'])) {
		$_SESSION['sg']['token'] = bin2hex(random_bytes(32));
	}

	if (isset($_SESSION['sg']["loggedin"]) && $_SESSION['sg']["loggedin"] === true) {
		return true;
	}
	else{
		return false;
	}
     
}

?>
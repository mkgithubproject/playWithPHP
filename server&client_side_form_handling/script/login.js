function login() {
    var pw = document.getElementById('email').value;
    var em = document.getElementById('psw').value;
    if (pw.length == 0 || em.length == 0) {
        document.getElementById("emailHelp").innerHTML = "*fill all required field ";
            document.getElementById("passwordHelp").innerHTML = "*fill all required field ";
            document.getElementById("emailHelp").style.color = "red";
            document.getElementById("passwordHelp").style.color = "red";
           
            //event.preventDefault();
    }
    if(pw.length!=0 && em.length!=0){
        return true;
    }
    else{
        return false;
    }
    
}
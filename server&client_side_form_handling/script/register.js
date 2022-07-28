function register() {
  var name = document.getElementById("name").value;
  var em = document.getElementById("email").value;
  var pw = document.getElementById("psw").value;
  var user_info = 0;
  var pass_info = 0;
  var email_info = 0;
  var username = /^[a-zA-Z]{1,}$/;
  var pass = /^(?=.*[\d])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*])[\w!@#$%^&*]{8,}$/;
  var eml = /^[A-Za-z0-9._]{3,}@[A-Za-z]{3,}[.][a-zA-Z]{3,}(.[a-zA-Z]{2,})?$/;
  if (username.test(name)) {
    //document.getElementById("nameHelp").innerHTML = "valid username ";
    document.getElementById("nameHelp").style.display = "none";
    user_info = 1;
  } else {
    document.getElementById("nameHelp").innerHTML = "Invalid username";
    document.getElementById("nameHelp").style.color = "red";
    document.getElementById("nameHelp").style.display = "block";
  }
  if (pass.test(pw)) {
    //document.getElementById("passwordHelp").innerHTML = " valid password";
    document.getElementById("passwordHelp").style.display = "none";
    pass_info = 1;
  } else {
    document.getElementById("passwordHelp").innerHTML =
      "must contain 8 character at least one uppercase ,lower case ,numeric,and special character";
    document.getElementById("passwordHelp").style.color = "red";
    document.getElementById("passwordHelp").style.display = "block";
  }
  if (eml.test(em)) {
    //document.getElementById("emailHelp").innerHTML = "valid email ";
    document.getElementById("emailHelp").style.display = "none";
    email_info = 1;
  } else {
    document.getElementById("emailHelp").innerHTML = "Invalid email";
    document.getElementById("emailHelp").style.color = "red";
    document.getElementById("emailHelp").style.display = "block";
  }
  if (user_info == 1 && email_info == 1 && pass_info == 1) {
    return true;
  } else {
    return false;
  }
}

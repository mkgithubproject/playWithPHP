$(document).ready(function () {
  $("#form-data").on("submit", function (e) {
    e.preventDefault();
    var fname = $("#fname").val();
    var lname = $("#lname").val();
    var email = $("#email").val();
    var filepath = $("#file_upload").val();

    if (fname == "" || email == "" || lname == "" || filepath == "") {
      alert("Please Fill All Fields");
    } else {
      // AJAX Code To Submit Form.
      var formdata = new FormData(this);
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: formdata,
        contentType: false,
        processData: false,
        success: function (data) {
          var obj = JSON.parse(data);
          alert(obj.msg);
        },
        // data:$("#form-data input").serialize(),
        //       }).done(function(msg) {
        //  alert( msg);
      });
    }
  });
});

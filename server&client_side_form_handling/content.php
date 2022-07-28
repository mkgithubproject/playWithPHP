<?php
include "session.php";
include "authentic.php";
if (!is_authentic()) {
    header("location:index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>content</title>
    <style>
        <?php include 'style.css'; ?>
    </style>
</head>
<body>
<?php include "header.php";?>
    <h1 style="color: red; text-align:center">WELCOME <?php echo $_SESSION["user"] ?> </h1>
</body>
</html>
<?php
require_once "connection.php";
$sql = "INSERT INTO student (`first name`, `last name`, phone)
VALUES ('jay', 'singh', '0000000000')";
if ($conn->query($sql) === TRUE) {
  echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
?>
<?php
require_once "connection.php";

$sql = "SELECT `id`, `first name`, `last name`, `phone` FROM `student`" ;
$result = $conn->query($sql);

$sql = "DELETE FROM student WHERE id=3";

if ($conn->query($sql) === TRUE) {
  echo "Record deleted successfully";
} else {
  echo "Error deleting record: " . $conn->error;
}
$conn->close();
?>
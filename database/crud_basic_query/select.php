<?php
require_once "connection.php";
$sql = "SELECT `id`, `first name`, `last name`, `phone` FROM `student`" ;
$result = $conn->query($sql);


// echo "<pre>";
// var_dump($result);
// $result->fetch_assoc();
// var_dump($result);
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
   
    echo "id: " . $row["id"]."<br>". "Full Name: " . $row["first name"]. " " . $row["last name"]."<br>"."phone:".$row["phone"]. "<br>";
    echo "---------------------------------------------------------------------------------------------------------------------<br>";
  }
} else {
  echo "0 results";
}
$conn->close();

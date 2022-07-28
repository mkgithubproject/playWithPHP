<?php
include "connection.php";
$sql="UPDATE student SET `last name`='gangwar' WHERE id=2";
if($conn->query($sql)==true){
 echo "Record updated successfully";
} else {
  echo "Error updating record: " . $conn->error;
}
$conn->close();
?>
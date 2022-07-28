<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, "mydb");
if ($conn->connect_errno) {
    echo "Failed to connect to MySQL: (" . $conn->connect_errno . ") " . $conn->connect_error;
}
$sql = "CREATE TABLE IF NOT EXISTS test (Customer_id INT(12) AUTO_INCREMENT PRIMARY KEY ,
Customer_name VARCHAR(255) NOT NULL
 
);";
if(!$conn->query($sql)){
    echo "table creation failed:(".$conn->error;
}
if (!$conn->query("DROP PROCEDURE IF EXISTS p_insert") ||
    !$conn->query(" CREATE PROCEDURE p_insert(IN name VARCHAR(255)) BEGIN INSERT INTO test(Customer_name) VALUES(name); END;")) {
    echo "Stored procedure creation failed: (" . $conn->errno . ") " . $conn->error;
}
if (!$conn->query("CALL p_insert('Rahul')")) {
    echo "CALL failed: (" . $mysqli->errno . ") " . $mysqli->error;
}

$sql="CREATE PROCEDURE IF NOT EXISTS p_fetch() BEGIN SELECT * from test; END;";
if(!$conn->query($sql)){
    echo "Stored procedure creation failed: (" . $conn->errno . ") " . $conn->error;
}
$result=$conn->query("CALL p_fetch()");
var_dump($result);//return object
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<pre>";
     var_dump($row) ;
  }}
  else {
    echo "0 results";
  }

  $conn->close();


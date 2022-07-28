<?php  
$host = 'localhost';  
$user = 'root';  
$pass = '';  
$conn = new mysqli($host, $user, $pass, "mydb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
//echo 'Connected successfully<br/>';  
  
$sql = 'CREATE Database IF NOT EXISTS mydb';
  
if($conn->query($sql)){  
  //echo "Database mydb created successfully.";  
}else{  
echo "Sorry, database creation failed ".mysqli_error($conn);  
}  


$sql="CREATE TABLE IF NOT EXISTS discounts (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    expired_date VARCHAR(100) NOT NULL,
    amount DECIMAL(10 , 2 ) NULL,
    PRIMARY KEY (id)
    );";
    if ($conn->query($sql) == TRUE) {
        //echo "Table MyGuests created successfully";
      } else {
        echo "Error creating table: " . $conn->error;
      }

      $sql="LOAD DATA INFILE 'C:/Users/Dell 0103/Desktop/Book1.csv'
      INTO TABLE discounts
      FIELDS TERMINATED BY ','
      ENCLOSED BY '\"'
      LINES TERMINATED BY '\n'
      IGNORE 1 ROWS;";
      if($conn->query($sql)==true){
          echo "data has been loaded";
      }
      else{
          echo "loading error".$conn->error;
      }

      $conn->close();
    
?> 
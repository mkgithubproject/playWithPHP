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
$sql1="CREATE TABLE IF NOT EXISTS ConstraintDemoParent
(
       ID INT PRIMARY KEY,
	 Name VARCHAR(50) NULL
);";

$sql2="CREATE TABLE IF NOT EXISTS ConstraintDemoChild
(
        CID INT PRIMARY KEY,
        ID INT ,
        FOREIGN KEY (ID) REFERENCES ConstraintDemoParent(ID)
	
);";
$conn->query($sql1);
$conn->query($sql2);
$sql1="INSERT INTO ConstraintDemoParent (ID,Name)VALUES (1,'John'),(2,'Rohan')";

$sql2="INSERT INTO ConstraintDemoChild (CID,ID)VALUES (1,1)";
$sql3="INSERT INTO ConstraintDemoChild (CID,ID)VALUES (2,4)";
if($conn->query($sql1)==false){
echo $conn->error."<br>";
}
if($conn->query($sql2)==false){
    echo $conn->error;
    }
    if($conn->query($sql3)==false){
        echo $conn->error;
        }
        $sql = "DELETE FROM ConstraintDemoParent WHERE id=1";
        if($conn->query($sql)==false){
           echo $conn->error;
        }

   
    

      $conn->close();
    
?> 
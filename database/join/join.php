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

if ($conn->query($sql)) {
    //echo "Database mydb created successfully.";  
} else {
    echo "Sorry, database creation failed " . mysqli_error($conn);
}
$sql = "CREATE TABLE IF NOT EXISTS customers (Customer_id int NOT NULL  ,
Customer_name VARCHAR(255) NOT NULL,
Email VARCHAR(255) NOT NULL,
Password VARCHAR(255) NOT NULL,
PRIMARY KEY(Customer_id)  
);";
if ($conn->query($sql) == false) {
    echo "customer table is not created.$conn->error";
}
$sql = "CREATE TABLE IF NOT EXISTS orders ( Order_id INT not null  ,Order_name VARCHAR(255) NOT NULL, Customer_id INT ,
Total_amount FLOAT(255,2) NOT NULL,
Status INT NOT NULL,
PRIMARY KEY(Order_id),
FOREIGN KEY (Customer_id) REFERENCES customers(Customer_id) ON UPDATE CASCADE
ON DELETE CASCADE    
);";

if ($conn->query($sql) == false) {
    echo "Order table is not created.$conn->error";
}
$sql = "INSERT INTO Customers (Customer_id,`Customer_name`, `Email`, `Password`)
VALUES (1,'Mohit', 'hackerrank32@gmail.com', '123mk45'),
(2,'Jay','hgsjj@gmail.com','556555'),
(3,'Vijay','vijay@gmail.com','vij2585'),
(4,'surya','suraj@gmail.com','su8555'),(5,'rahul','rahul@gmail.com','su855552') ON  DUPLICATE KEY UPDATE  Customer_name=values(Customer_name),Password=VALUES(Password)
";
if ($conn->query($sql) == false) {
    echo "customer insertion failed" . $conn->error;
}
$sql = "INSERT INTO Orders (Order_id,`Order_name`, Customer_id,`Total_amount`, `Status`)
VALUES (1,'veg thali',1, 250, 1),(2,'non veg thali',1, 150, 1),(3,'banana shake',3, 60, 1),(4,'papaya shake',4, 250, 1),(5,'sweet',5, 500, 2),(6,'papaya shake',3, 250, 0)";
if ($conn->query($sql) == false) {
    //echo "Order insertion failed" . $conn->error;
}
$sql=" select  DISTINCT customers.Customer_id ,Customer_name,Email from customers INNER JOIN orders ON customers.Customer_id=orders.Customer_id AND status=1;";
$result=$conn->query($sql);
echo "Query-1: Get the list of all customers who have placed atleast one order.<br>";
if($result->num_rows > 0){
    echo "Customer Name:<br> ";
    while($row=$result->fetch_assoc()){
        //var_dump($row);
        echo $row["Customer_name"]."<br>";
    }
}
else{
    echo "Zero customer";
}

$sql="select  Customer_name,count( orders.Customer_id) from customers LEFT JOIN orders
ON customers.Customer_id=orders.Customer_id
AND status=1
GROUP BY Customer_name
ORDER BY 2 DESC;";
$result=$conn->query($sql);
echo "Query-2: Get list of customers in descending order based on total number of orders placed. (Customer who have placed the most number of orders should come on top. Customers who haven't placed any order should also be included)<br>";
if($result->num_rows > 0){
    echo "Customer Name:<br> ";
    while($row=$result->fetch_assoc()){
        // echo "<pre>";
        // var_dump($row);
       echo $row["Customer_name"]."<br>";
    }
}
else{
    echo "Zero customer";
}
$sql="select  Customer_name,SUM(Total_amount ) from customers INNER JOIN orders
ON customers.Customer_id=orders.Customer_id
AND status=1
GROUP BY Customer_name
ORDER BY 2 DESC;";
$result=$conn->query($sql);
echo "Query-3 :Get list of customers in descending order based on sum of all order's amount of a customer. (Customer whose sum of order's amount is highest should come on top)<br>";
if($result->num_rows > 0){
    echo "Customer Name:<br> ";
    while($row=$result->fetch_assoc()){
        // echo "<pre>";
        // var_dump($row);
       echo $row["Customer_name"]."<br>";
    }
}
else{
    echo "Zero customer";
}
$sql="select customers. Customer_name 
from  customers
LEFT JOIN orders
ON  customers.Customer_id =orders.Customer_id
AND status=1
WHERE orders.Customer_id IS NULL ";
$result=$conn->query($sql);
echo "Query-4 :Get the list of all customers who havn't placed any order.<br>";
if($result->num_rows > 0){
    echo "Customer Name:<br> ";
    while($row=$result->fetch_assoc()){
        // echo "<pre>";
        // var_dump($row);
       echo $row["Customer_name"]."<br>";
    }
}
else{
    echo "Zero customer";
}
$sql=" select  Customer_name,count( orders.Customer_id) AS order_count from customers LEFT JOIN orders
ON customers.Customer_id=orders.Customer_id
AND status=1 

GROUP BY Customer_name
HAVING order_count>2;";
$result=$conn->query($sql);
echo "Query-5: Get the list of all customers who have placed more than 2 orders.";
if($result->num_rows > 0){
    echo "Customer Name:<br> ";
    while($row=$result->fetch_assoc()){
        // echo "<pre>";
        // var_dump($row);
       echo $row["Customer_name"]."<br>";
    }
}
else{
    echo "<br>Zero customer";
}

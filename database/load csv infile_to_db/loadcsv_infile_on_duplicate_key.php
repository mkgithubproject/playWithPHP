<?php
$arr = [];
$result = array();
$result[0] = array();
$arr[0] = "band";
$arr[1] = "call";
$arr[2] = "freq";
$arr[3] = "mode";
$arr[4] = "operator";
$arr[5] = "rst_rcvd";
$arr[6] = "rst_sent";
$arr[7] = "station_callsign";
$arr[8] = "time_off";
$arr[9] = "time_on";
if ($file = fopen("N4FN (3).ADI", "r")) {
    $index = 0;
    while (!feof(($file))) {
        $line = fgets($file);
        for ($i = 0; $i < 10; $i++) {
            $search = stristr($line, "<" . $arr[$i]);
            $mykey = substr(strtok($search, ":"), 1);
            $search = substr($search, strpos($search, ">") + 1);
            $myvalue = strtok($search, " ");
            if (!empty($mykey)) {
                $result[$index][$mykey] = $myvalue;
            }
        }
        if (strpos($line, "<EOR>") !== false) {
            $index++;
        }
    }
}


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
}else {
    echo "Sorry, database creation failed " . mysqli_error($conn);
}
foreach ($result as $value) {
    $call = $value["call"];
    $band = $value["band"];
    $mode = $value["mode"];
    $time_off = $value["time_off"];
    $time_on = $value["time_on"];
    $freq = $value["freq"];
    $operator = $value["operator"];
    $rst_rcvd = $value["rst_rcvd"];
    $rst_sent = $value["rst_sent"];
    $station_callsign = $value["station_callsign"];
    
        $sql = "INSERT INTO duplicate_key_update (`call`, band, mode,time_off,time_on,freq,operator,rst_rcvd,rst_sent,station_callsign) VALUES ('$call','$band','$mode','$time_off','$time_on','$freq','$operator','$rst_rcvd','$rst_sent','$station_callsign')
        ON DUPLICATE KEY UPDATE band='$band',operator='$operator',rst_rcvd='$rst_rcvd',rst_sent='$rst_sent',station_callsign='$station_callsign',time_off='$time_off',time_on='$time_on'" ;
        $result = $conn->query($sql);
        if ($result == false) {
            echo $conn->error;
        }
        
    }
    echo "data has been saved on duplicate key update";






$conn->close();

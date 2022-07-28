<?php
header('Content-Type: text/plain');
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
print_r($result);

//database
$servername = "localhost";
$username = "root";
$password = "";
$conn = new mysqli($servername, $username, $password, "mydb2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    $sql = "SELECT * from filedata WHERE `call`='$call' AND band='$band' AND mode='$mode' AND time_off='$time_off' AND time_on='$time_on' ";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row["id"];
        $sql = "UPDATE filedata SET freq='$freq', operator='$operator',rst_rcvd='$rst_rcvd',rst_sent='$rst_sent',station_callsign='$station_callsign' WHERE id=$id";
        $result = $conn->query($sql);

        if ($result == false) {
            echo $conn->error;
        }
    } else {
        $sql = "INSERT INTO filedata (`call`, band, mode,time_off,time_on,freq,operator,rst_rcvd,rst_sent,station_callsign) VALUES ('$call','$band','$mode','$time_off','$time_on','$freq','$operator','$rst_rcvd','$rst_sent','$station_callsign')";
        $result = $conn->query($sql);
        if ($result == false) {
            echo $conn->error;
        }
    }
}

echo "File data inserted";
$conn->close();
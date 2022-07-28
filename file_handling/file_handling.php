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
            // if ($s = stristr($line, "<$arr[$i]")) {
            //     sscanf($s, "<$arr[$i]:%d>%s ", $length, $result[$index]["$arr[$i]"]);
            // }
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

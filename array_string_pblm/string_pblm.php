<?php

$str="<CALL:5>HA5KG <QSO_DATE:8>20141005 <TIME_ON:6>181954 <BAND:3>20M <STATION_CALLSIGN:5>TX3X <FREQ:8>14.02400 <CONTEST_ID:11>DX PEDITION <FREQ_RX:8>14.02400 <MODE:2>CW <RST_RCVD:3>599 <RST_SENT:3>599 <OPERATOR:5>HA5AO <CQZ:2>15 <STX:1>3 <APP_N1MM_POINTS:1>1 <APP_N1MM_RADIO_NR:1>1 <APP_N1MM_CONTINENT:2>EU <APP_N1MM_RUN1RUN2:1>1 <APP_N1MM_RADIOINTERFACED:1>0 <APP_N1MM_ISORIGINAL:4>True <APP_N1MM_NETBIOSNAME:13>HA5AONOTEBOOK <APP_N1MM_ISRUNQSO:1>1 <EOR>";
$arr=array();
while(strlen($str)>0){
    $str=substr($str,1);
    $mykey=strtok($str,':');
    $str=substr($str,strpos($str,">")+1);
    $myvalue=strtok($str,' ');
    $str=substr($str,strpos($str,"<"));
    $arr[$mykey]=$myvalue;
}

echo "<pre>";
print_r($arr);
var_dump($arr);
foreach($arr as $x => $x_value) {
  echo   $x . "=>". "'$x_value'";
  echo "<br>";
}

 
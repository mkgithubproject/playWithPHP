<?php
echo "1. how you print current datetime in mysql datetime format?<br>";

date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d H:i:s');
echo $date;

echo "<br>2.  how will you convert date format from mysql date format to user readable format like 14-July-2020 12:53PM?<br>";
$newDate = date("d-M-Y h:i:sA", strtotime($date));
echo $newDate;
echo "<br>3. how will you get only time from the datetime string 14-July-2020 12:53PM?<br>";
$newDate = "14-July-2020 12:53PM";
echo $start_time = date("h:iA", strtotime($newDate));
echo "<br>4. how you will get only date from datetime string 14-July-2020 12:53PM<br>";
echo date('d-M-Y', strtotime($newDate));
echo "<br>5. how will you get the difference between 2 datetime values in seconds<br>";
$now = new DateTime('NOW');
$future = new DateTime('2020/12/11 15:46:00');
$diffSeconds = $now->getTimestamp() - $future->getTimestamp();
echo $diffSeconds;
echo "<br>6.show the difference between 2 datetimes values as: X Days, X Hours, X minutes, X seconds<br>";
$date1 = strtotime("2016-06-01 22:45:00");
$date2 = strtotime("2018-09-21 10:44:01");
$diff = abs($date2 - $date1);
$days = floor($diff / (1 * 24 * 60 * 60));
$hours = floor(($diff - $days * 24 * 60 * 60) / (60 * 60));
$min = floor(($diff - $days * 24 * 60 * 60 - $hours * 60 * 60) / (60));
$sec = floor($diff - $days * 24 * 60 * 60 - $hours * 60 * 60 - $min * 60);
echo $days . "days" . "," . $hours . "hours" . "," . $min . "minutes" . "," . $sec . "seconds";
echo "<br>7. show current datetime in some different timezone like New york, Adelaide <br>";
date_default_timezone_set('America/Anchorage');
$date = date('Y-m-d H:i:s');
echo $date;
echo "<br>" . date_default_timezone_get();
echo "<br>8.print time slots beginning from current datetime to next 48 hours (or some other value entered by user) with each slot of 3.5 hours difference:<br>";
date_default_timezone_set('Asia/Kolkata');
$date = date('d-M-Y h:i:s A');
$date1 = strtotime($date);
$total_sec = $date1 + 48 * 60 * 60;
while ($date1 <= $total_sec) {
  $newDate = date("d-M-Y h:i:sA", $date1);
  echo $newDate;
  echo "<br>";
  $date1 = $date1 + 3.5 * 60 * 60;
}

<?php

 echo "1. how you print current datetime in mysql datetime format?<br>";
 $currentDateTime = new DateTime('NOW',new DateTimeZone('Asia/Kolkata'));
 $ymdNow = $currentDateTime->format('Y-m-d H:i:s');
 echo $ymdNow;
 
 echo "<br>2.  how will you convert date format from mysql date format to user readable format like 14-July-2020 12:53PM?<br>";
 $str="14-July-2020 12:53PM";
 $dateTime = new DateTime($str);
 $Now =  $dateTime ->format('d-M-Y h:iA');
 echo $Now;
 echo "<br>3. how will you get only time from the datetime string 14-July-2020 12:53PM?<br>";
 $newDate="14-July-2020 12:53PM";
 $dateTime = new DateTime($newDate);
 $Now =  $dateTime ->format('h:iA');
 echo $Now;
 echo "<br>4. how you will get only date from datetime string 14-July-2020 12:53PM<br>";
 $newDate="14-July-2020 12:53PM";
 $dateTime = new DateTime($newDate);
 $Now =  $dateTime ->format('d-M-Y');
 echo $Now;
 echo "<br>5. how will you get the difference between 2 datetime values in seconds<br>";
 $now = new DateTime( 'NOW',new DateTimeZone('Asia/Kolkata') );
$future = new DateTime( '2020/12/11 15:46:00',new DateTimeZone('Asia/Kolkata') );
$diffSeconds = $now->getTimestamp() - $future->getTimestamp();
echo $diffSeconds;
 echo "<br>6.show the difference between 2 datetimes values as: X Days, X Hours, X minutes, X seconds<br>";
 $now1 = new DateTime("2016-06-01 22:45:00" );
 $now2 = new DateTime("2018-09-21 10:44:01" );
 $diff = abs($now1->getTimestamp() - $now2->getTimestamp()); 
   $days=floor($diff/(1*24*60*60));
   $hours=floor(($diff-$days*24*60*60)/(60*60));
   $min=floor(($diff-$days*24*60*60-$hours*60*60)/(60));
   $sec=floor($diff-$days*24*60*60-$hours*60*60-$min*60);
   echo $days."days".",".$hours."hours".",".$min."minutes".",".$sec."seconds";
   echo "<br>7. show current datetime in some different timezone like New york, Adelaide <br>";
   $currentDateTime = new DateTime('NOW',new DateTimeZone('America/Anchorage'));
   $ymdNow = $currentDateTime->format('Y-m-d H:i:s');
   echo $ymdNow;
      
      echo "<br>8.print time slots beginning from current datetime to next 48 hours (or some other value entered by user) with each slot of 3.5 hours difference:<br>";
      
      $currentDateTime = new DateTime('NOW',new DateTimeZone('Asia/Kolkata'));
 $date1 = $currentDateTime->getTimestamp();
 $total_sec=$date1+48*60*60;
 while($date1<=$total_sec){
  $currentDateTime = new DateTime('NOW',new DateTimeZone('Asia/Kolkata'));
       $currentDateTime->setTimestamp($date1);
     echo $currentDateTime->format("d-M-Y h:i:sA");


     echo "<br>" ;
     $date1=$date1+3.5*60*60;
 }
 
 
 
  
 
 
 ?>
 


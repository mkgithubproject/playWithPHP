<?php
$open_datetime = "17-03-2019 09:30AM";
$close_datetime = "18-03-2019 02:00PM";

$off_times = array(
    0 => array(
        "from" => "17-03-2019 02:00PM",
        "to" => "17-03-2019 03:00PM"
    ),
    1 => array(
        "from" => "17-03-2019 07:30PM",
        "to" => "18-03-2019 9:30AM"
    )
);

$already_booked_slots = array(
    0 => array(
        "from" => "17-03-2019 10:00AM",
        "to" => "17-03-2019 11:00AM"
    ),
    1 => array(
        "from" => "18-03-2019 11:30AM",
        "to" => "18-03-2019 12:30PM"
    )
);
date_default_timezone_set('Asia/Kolkata');
// $user_from = "17-03-2019 11:30AM";
// $user_to =  "17-03-2019 12:30AM";
// $user_from = "17-03-2019 01:00AM";
// $user_to =  "17-03-2019 03:10AM";
// $user_from = "17-03-2019 01:00AM";
// $user_to =  "17-03-2019 03:10AM";
// $user_from = "17-03-2019 09:40AM";
// $user_to =  "17-03-2019 11:05AM";
// $user_from = "17-03-2019 09:00AM";
// $user_to =  "17-03-2019 09:40AM";
// $user_from = "17-03-2019 09:40AM";
// $user_to =  "17-03-2019 10:20AM";
 $user_from = "17-03-2019 11:00AM";
$user_to =  "17-03-2019 11:30AM";
function booking_user($user_from, $user_to)
{
    global $open_datetime, $close_datetime, $off_times, $already_booked_slots;
    if (strtotime($user_from) < strtotime($open_datetime) || strtotime($user_to) > strtotime($close_datetime)) {
        echo "Cannot book";
    
        return;
        //exit("not requested");
    }
    foreach ($already_booked_slots as $value) {
        if ((strtotime($user_from) >= strtotime($value["from"]) && strtotime($user_from) < strtotime($value["to"])) || (strtotime($user_to) > strtotime($value["from"]) && strtotime($user_to) < strtotime($value["to"])) || (($value["from"] >= $user_from && $value["from"] < $user_to) || ($value["to"] > $user_from && $value["to"] < $user_to))) {

            echo "Cannot book";
       
            return;
        }
    }
    foreach ($off_times as $value) {
        if ((strtotime($user_from) >= strtotime($value["from"]) && strtotime($user_from) < strtotime($value["to"])) || (strtotime($user_to) > strtotime($value["from"]) && strtotime($user_to) < strtotime($value["to"])) || (($value["from"] >= $user_from && $value["from"] < $user_to) || ($value["to"] > $user_from && $value["to"] < $user_to))) {
            echo "Cannot book";
            return;
        }
    }
    echo "Can book";
    return;
}
booking_user($user_from, $user_to);

<?php
   header('Content-Type: text/plain');
echo "1.Program to count number of lowercase alphabets in a text file \n";
$str = file_get_contents("N4FN (3).ADI");
echo preg_match_all("/[a-z]/", $str);

echo "\n2.program to delete a specific line from a file.\n";
$f = "N4FN (3) copy.ADI";
$term = "call";
$arr = file($f);
foreach ($arr as $key=> $line) {

    //removing the line
    if(stristr($line,$term)!== false){unset($arr[$key]);break;}
}
//reindexing array
$arr = array_values($arr);
//writing to file
file_put_contents($f, implode($arr));
echo "deleting successfully\n";

echo "\n3.program to replace a specific line with another text in a file.\n";
$data = file("N4FN (3) copy.ADI"); 
function replace_a_line($data) {
   if (stristr($data, 'created')) {
     return "HRD Logbook \n";
   }
   return $data;
}
$data = array_map('replace_a_line',$data);
file_put_contents("N4FN (3) copy.ADI", implode('', $data));
echo "replacing success\n";

echo "\n4.Program to search in a file\n";
$file = "N4FN (3) copy.ADI";
$searchfor = 'my_name';
$contents = file_get_contents($file);
$pattern = preg_quote($searchfor, '/');
$pattern = "/^.*$pattern.*\$/m";
if(preg_match_all($pattern, $contents, $matches)){
   echo "Found matches:\n";
   echo implode("\n", $matches[0]);
}
else{
   echo "No matches found";
}




        
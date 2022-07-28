<?php

$array = array(
	"P1"=>array(
		"C1"=>array(
			"C11"=>array(),
			"C12"=>array(
				"C121"=>array()
			)
		)
	),
	"P2"=>array(
		"D1"=>array(
			"D11"=>array(
				"D121"=>array(),
				"D122"=>array(
					"D1221"=>array()
				)
			),
			"D12"=>array()
		)
	),
	"P3"=>array()
);
foreach($array as $key => $value) {
    echo $key.' => '. findDeepestChild($value)."<br>";
}
function findDeepestChild(array $array, int $level = 0) {
    $keys = [];
    foreach($array as $key => $value) {
        $keys[$key] = $level;
        if(!is_array($value)) continue;
        if(!empty($value)) return findDeepestChild($value, ++$level);
    }
    arsort($keys );
   
    return array_key_first($keys) ?? '';
}

echo "........................................................................................................,<br>";
$arr=[];
$i=0;
foreach($array as $key=>$value){
    $arr[$i]=$key;
    $i++;
    getkey($value);
}
function getkey(array $array){
    global $arr;
        global $i;
    foreach($array as $key=>$value){
        $arr[$i]=$key;
        $i++;
        if(empty($value)){
            continue;
         }
        if(is_array($value)){
           return getkey($value);
        }
        

    }
    $len=count($arr);
   $str="";
     for($i=0;$i<$len;$i++){
         if(!empty($str)){
            $str.="->";    
         }
         $str=$str.$arr[$i];
        $result=$arr[$i]."=>".$str;
        echo $result."<br>";
         

     }
     $arr=[];
     $i=0;  

    
}
        
    



   




?>
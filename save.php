<?php

session_start();

//print_r ($_POST);

$filename = $_POST['filename']; //2013\05\09\e01a3qd69fo76sjt05k39ab292\4 
$number   = $_POST['number'];   //1

$dir = "C:\\wamp\\www\\recorder\\wav\\";

$file = $dir . $filename;
 
if(file_exists("$file.mat")) {
    rename("$file.mat", "{$file}_$number.mat");
    rename("$file.wav", "{$file}_$number.wav");
}

?>

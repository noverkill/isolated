<?php

$ds = DIRECTORY_SEPARATOR;

session_start();

//print_r ($_POST);

$filename = $_POST['filename']; //2013\05\09\e01a3qd69fo76sjt05k39ab292\4 
$number   = $_POST['number'];   //1

//$dir = "C:\\wamp\\www\\recorder\\wav\\";
$dir = "{$ds}var{$ds}www{$ds}isolated{$ds}wav{$ds}";

$file = $dir . $filename;
 
if(file_exists("$file.mat")) {
    rename("$file.mat", "{$file}_$number.mat");
    rename("$file.wav", "{$file}_$number.wav");
}

?>

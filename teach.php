<?php

session_start();

$fileName = $_POST['filename'];
$number   = $_POST['number'];

$dir = "C:\\wamp\\www\\recorder\\wav\\"; 
 
$wavfile = $dir . $fileName;

$cmd = "python learn.py -i $wavfile -n $number";

$gn = exec($cmd);

print $gn;

?>

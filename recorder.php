<?php

$ds = DIRECTORY_SEPARATOR;

session_start();

//$fileName = $_SERVER['HTTP_X_FILE_NAME'];
 
$input = fopen('php://input', 'r');

$d = explode('.', date('Y.m.d')); 

if(isset($_SESSION['dir'])) $dir = $_SESSION['dir']; 
else $dir = $d[0] . $ds . $d[1] . $ds . $d[2] . $ds . session_id() . $ds;

//$dir2 = "C:\\wamp\\www\\recorder\\wav\\" . $dir; 
$dir2 = "{$ds}var{$ds}www{$ds}isolated{$ds}wav{$ds}" . $dir; 

//$gn = $dir2;

if(!is_dir($dir2)) {
	exec("mkdir -p $dir2");
	//mkdir($dir2,777,true);
}

$wavcount = glob($dir2 . "*.wav") ? count(glob($dir2 . '*.wav')) : 0;
$wavcount++;

$wavfile  = $dir  . $wavcount . '.wav';
$wavfile2 = $dir2 . $wavcount . '.wav';
$wavfile3 = $dir2 . $wavcount;
$wavfile4 = $dir . $wavcount;
 
$output = fopen( $wavfile2, 'w');
 
while ($data = fread($input, 1024))
    fwrite($output, $data);
 
fclose($input);
fclose($output);

$cmd = "python learn.py -i $wavfile3";
 
//$gn = $cmd; 
$gn = exec($cmd);

$user = str_replace($ds, "_", $dir);

print json_encode(array('file' => "$wavfile4", 'guess' => "$gn", "user" => $user));
//print json_encode(array('file' => "$wavfile4", 'guess' => "$cmd"));

?>

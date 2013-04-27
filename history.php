<?php
require('util.php');

$uid = $_GET['uid'];
$grp = $_GET['grp'];

if((255 < strlen($uid))
|| (255 < strlen($grp))
){
	exit;
}

if((preg_match("/^[a-zA-Z0-9_]{1,32}/", $uid) == 0)
|| (preg_match("/^[a-zA-Z0-9_]{1,32}/", $grp) == 0)
){
	exit;
}

$filename = './' . $grp . '-' . $uid . '.csv';
if(!file_exists($filename)){
	exit;
}

$fp = fopen($filename, 'r');
while (!feof($fp)) {
    $line = fgets($fp);
	print $line;
}
fclose($fp);
exit;

?>
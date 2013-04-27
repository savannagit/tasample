<?php
require('util.php');
session_start();

$tag = $_GET['tag'];
$uid = $_GET['uid'];
$grp = $_GET['grp'];
$lat = $_GET['lat'];
$lon = $_GET['lon'];
$chale = $_GET['chale'];

if((255 < strlen($tag))
|| (255 < strlen($uid))
|| (255 < strlen($grp))
|| (255 < strlen($lat))
|| (255 < strlen($lon))
|| (255 < strlen($chale))){
	exit;
}

if(preg_match("/^[a-zA-Z0-9_]{1,32}/", $grp) == 0){
	exit;
}

if((0 < strlen($tag) && preg_match("/^[a-fA-F0-9]{1,64}/", $tag) == 0)
|| (0 < strlen($lat) && preg_match("/^[0-9.]{1,32}/", $lat) == 0)
|| (0 < strlen($lon) && preg_match("/^[0-9.]{1,32}/", $lon) == 0)){
	exit;
}

if ($chale == '') {
	$xordata = $_SESSION['tagchallenge'];
	$dectag = getDecryptedTag($tag, $xordata);
	unset($_SESSION['tagchallenge']);
	if(0 < strlen($dectag) && canAddGroupTag()){
		$setGrp = setGroupTag($dectag, $grp, $lat, $lon);
		if($setGrp==true){
			$timestampmsg = date('Y/m/d H:i:s');
			print 'OK';
		}
		exit;
	}

} else {
	$randomHex = getRandomHex();
	$_SESSION['tagchallenge'] = $randomHex;
	print $randomHex;
};

?>
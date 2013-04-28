<?php
require('util.php');
session_start();

$tag = $_GET['tag'];
$uid = $_GET['uid'];
$grp = $_GET['grp'];
$latexist = isset($_GET['lat']);
$lat = $_GET['lat'];
$lonexist = isset($_GET['lon']);
$lon = $_GET['lon'];
$sid = $_GET['sid'];
$chale = $_GET['chale'];
$plact = $_GET['plact'];
$punch = $_GET['punch'];

if((255 < strlen($tag))
|| (255 < strlen($uid))
|| (255 < strlen($grp))
|| (255 < strlen($lat))
|| (255 < strlen($lon))
|| (255 < strlen($sid))
|| (255 < strlen($chale))
|| (255 < strlen($plact))
|| (255 < strlen($punch))){
	exit;
}

if((preg_match("/^[a-zA-Z0-9_]{1,32}/", $uid) == 0)
|| (preg_match("/^[a-zA-Z0-9_]{1,32}/", $grp) == 0)){
	exit;
}

if((0 < strlen($tag) && preg_match("/^[a-fA-F0-9]{1,64}/", $tag) == 0)
|| (0 < strlen($lat) && preg_match("/^[0-9.]{1,32}/", $lat) == 0)
|| (0 < strlen($lon) && preg_match("/^[0-9.]{1,32}/", $lon) == 0)
|| (0 < strlen($sid) && preg_match("/^[a-zA-Z0-9_]{1,32}/", $sid) == 0)){
	exit;
}

if ($chale == '') {
	$xordata = $_SESSION['punchchallenge'];
	$punchtime = $_SESSION['punchtime'];
	$dectag = getDecryptedTag($tag, $xordata);
	unset($_SESSION['punchchallenge']);
	unset($_SESSION['punchtime']);
	$locationcheck = ($latexist == TRUE && $lonexist == TRUE) && (0 < strlen($lat) && 0 < strlen($lon));
	if($locationcheck){
		$distcheck = checkGroupTagDist($grp, $lat, $lon);
		if(!$distcheck){
			exit;
		}
	} else {
		$distcheck = FALSE;
	}
	$grptag = getGroupTag($grp);
	if($grptag == $dectag && time()-$punchtime < 300){
		setPunchData($punchtime, $uid, $grp, $sid, $plact, $punch, $locationcheck);
		$token = getGoogleToken();
		$punchdatetime = date('Y-m-d\TH:i:s', $punchtime);
		print $punchdatetime."\r\n";
		print $token;
	}

} else {
	$randomHex = getRandomHex();
	$_SESSION['punchchallenge'] = $randomHex;
	$_SESSION['punchtime'] = time();
	print $randomHex;
};

?>
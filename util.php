<?php
function logi($msg){
	$msg .= "\r\n";
	$timestampmsg = date('Y/m/d H:i:s ') . $msg;
	error_log($timestampmsg, 3, './debug.log');
}

function logd($msg,$val){
	$msg .= "=";
	$msg .= $val;
	$msg .= "\r\n";
	$timestampmsg = date('Y/m/s H:i:s ') . $msg;
	error_log($timestampmsg, 3, './debug.log');
}

function getRandomHex($length = 8){
    $charlist = "0123456789abcdef";
    mt_srand();
    $res = "";
    for($i = 0; $i < $length; $i++)
        $res .= $charlist[mt_rand(0, strlen($charlist) - 1)];
    return $res;
}

function getDecryptedTag($tag, $xordata){
    $res = "";
	$j = 0;
	for($i = 0; $i < strlen($tag); $i++){
		$res .= dechex(hexdec($tag[$i]) ^ hexdec($xordata[$j]));
		$j++;
		if(strlen($xordata) <= $j){
			$j = 0;
		}
	}
	return $res;
}

function getGroupTag($grp){
	$res = "";
	if(!file_exists('./grptag.csv')){
		return $res;
	}
	$fp = fopen('./grptag.csv', 'r');
	while (!feof($fp)) {
	    $line = fgets($fp);
		$clms = explode(',', $line, 4);
		if($clms[0] === $grp){
			$res = $clms[1];
		}
	}
	fclose($fp);
	$res = str_replace("\r", "", $res);
	$res = str_replace("\n", "", $res);
	return $res;
}

function checkGroupTagDist($grp, $lat, $lon){
	if(!file_exists('./grptag.csv')){
		return FALSE;
	}
	$fp = fopen('./grptag.csv', 'r');
	while (!feof($fp)) {
	    $line = fgets($fp);
		$clms = explode(',', $line, 4);
		if($clms[0] === $grp){
			$taglat = $clms[2];
			$taglon = $clms[3];
		}
	}
	fclose($fp);
	if($taglat=='' || $taglon ==''){
		return TRUE;
	}
	$dist = getDist($lat, $lon, $taglat, $taglon);
	if($dist < 10){
		return TRUE;
	} else {
		return FALSE;
	}
}

function getDist($lat1 , $lon1 , $lat2 , $lon2) {
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);
    $latave = ($lat1 + $lat2) / 2;
    $latdif = $lat1 - $lat2;
    $londif = $lon1 - $lon2;

    $meridian = 6335439 / sqrt(pow(1 - 0.006694 * sin($latave) * sin($latave), 3));
    $primevertical = 6378137 / sqrt(1 - 0.006694 * sin($latave) * sin($latave));

    $x = $meridian * $latdif;
    $y = $primevertical * cos($latave) * $londif;

    return sqrt($x * $x + $y * $y);
}

function canAddGroupTag(){
	return (filesize('./grptag.csv') < 1024);
}

function setGroupTag($tag, $grp, $lat, $lon){

	$existGrp = getGroupTag($grp);
	if($existGrp != '') {
		return FALSE;
	}

	$fp = fopen('./grptag.csv', 'a');
	$line = $grp . ',' . $tag . ',' . $lat . ',' . $lon;
	$line = mb_convert_encoding($line, 'utf-8', 'auto');
	$line .= "\r\n";
	fwrite($fp, $line);
	fclose($fp);
	return TRUE;
}

function setPunchData($punchtime, $uid, $grp, $sid, $plact, $punch, $locationcheck){
	if($plact != ''){
		return;
	}
	$line = date('Y/m/d,H:i:s', $punchtime) . ',' . $punch . ',';
	if($sid == ''){
		$filename = './' . $grp . '-' . $uid . '.csv';
	} else {
		$filename = './' . $grp . '-' . $sid . '.csv';
		$line .= $uid;
	}
	if($locationcheck){
		$line .= ',' . $locationcheck;
	} else {
		$line .= ',';
	}
	$fp = fopen($filename, 'a');
	$line .= "\n";
	fwrite($fp, $line);
	fclose($fp);
}

function getGoogleToken(){ 
	$params = array( 
		'Email' => 'google acount Email', 
		'Passwd' => 'google acount password', 
		'service' => 'cl',
		'source' => 'my name',
		);
	$request = http_build_query($params); 

	if($fp = fsockopen('ssl://www.google.com',443)){ 
		fputs ($fp, "POST /accounts/ClientLogin HTTP/1.1\r\n"); 
		fputs ($fp, "Host: www.google.com\r\n"); 
		fputs ($fp, "Accept-Charset: UTF-8\r\n"); 
		fputs ($fp, "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n"); 
		fputs ($fp, "Content-Length: ".strlen($request)."\r\n\r\n"); 
		fputs ($fp, $request); 

		$in = ''; 
		while (!feof($fp)) {
			$in = fgets($fp, 4096);
			if (preg_match('/^Auth=.*$/', $in)) break;
		} 
		fclose($fp);
		
		return substr($in, 5);
	}
}
?>
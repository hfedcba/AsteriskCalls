<?php
$_request = array();

foreach($_REQUEST as $element => $value)
{
	$_request[htmlentities($element)] = htmlspecialchars($value);
}

foreach($_GET as $entry => $value) // Kompatibilität zu alter Version
{
	$line = explode(";", $value);
	if(count($line) < 2) continue;
	$_request[$entry] = $line[0];
	for($i = 1; $i < count($line); $i++) {
		$temp = explode("=", $line[$i]);
		$_request[htmlspecialchars(trim($temp[0]))] = htmlspecialchars(trim($temp[1]));
	}
}

$_REQUEST = array();
$_GET = array();
$_POST = array();
?>

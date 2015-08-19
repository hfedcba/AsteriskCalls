<?php
$_request = Array();
foreach($_REQUEST as $element => $value) {
	$_request[htmlentities($element)] = htmlspecialchars($value);
}
$_REQUEST = Array();
?>
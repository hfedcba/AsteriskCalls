<?php
include_once('requestFilter.php');

require_once('config.php');
require_once('mysql.php');
$_config = new \AsteriskCalls\Config();
$_mysql = new \AsteriskCalls\MySql();

if(!isset($_request['method'])) die('Bitte übergeben Sie den Parameter "method".');

if($_request['method'] == 'searchName')
{
	if(!isset($_request['name'])) die('Bitte übergeben Sie den Parameter "name".');
	$names = $_mysql->query('SELECT * FROM phonebook WHERE Name LIKE "%'.$_request['name'].'%" ORDER BY Name ASC');
	echo json_encode($names);
}
else if($_request['method'] == 'editName')
{
	if(!isset($_request['name'])) die('Bitte übergeben Sie den Parameter "name".');
	if(!isset($_request['nummer'])) die('Bitte übergeben Sie den Parameter "nummer".');
	$count = $_mysql->query('SELECT COUNT(*) AS count FROM phonebook WHERE Name="'.$_request['name'].'"');
	if($count[0]['count'] == 0) $_mysql->query('INSERT INTO phonebook (Name, Number, ID) VALUES("'.$_request['name'].'","'.$_request['nummer'].'",NULL)');
	else $_mysql->query('UPDATE phonebook SET Number="'.$_request['nummer'].'" WHERE Name="'.$_request['name'].'"');
	echo json_encode(true);
}
else if($_request['method'] == 'editNumber')
{
	if(!isset($_request['name'])) die('Bitte übergeben Sie den Parameter "name".');
	if(!isset($_request['nummer'])) die('Bitte übergeben Sie den Parameter "nummer".');
	$count = $_mysql->query('SELECT COUNT(*) AS count FROM phonebook WHERE Number="'.$_request['nummer'].'"');
	if($count[0]['count'] == 0) $_mysql->query('INSERT INTO phonebook (Name, Number, ID) VALUES("'.$_request['name'].'","'.$_request['nummer'].'",NULL)');
	else $_mysql->query('UPDATE phonebook SET Name="'.$_request['name'].'" WHERE Number="'.$_request['nummer'].'"');
	echo json_encode(true);
}
else if($_request['method'] == 'deleteName')
{
	if(!isset($_request['name'])) die('Bitte übergeben Sie den Parameter "name".');
	if(!isset($_request['nummer'])) die('Bitte übergeben Sie den Parameter "nummer".');
	$_mysql->query('DELETE FROM phonebook WHERE Name="'.$_request['name'].'" AND Number="'.$_request['nummer'].'"');
	$_mysql->query('UPDATE AsteriskCalls SET FromName="" WHERE FromName="'.$_request['name'].'" AND FromNumber="'.$_request['nummer'].'"');
	$_mysql->query('UPDATE AsteriskCalls SET ToName="" WHERE ToName="'.$_request['name'].'" AND ToNumber="'.$_request['nummer'].'"');
	echo json_encode(true);
}
else if($_request['method'] == 'addName')
{
	if(!isset($_request['name'])) die('Bitte übergeben Sie den Parameter "name".');
	if(!isset($_request['nummer'])) die('Bitte übergeben Sie den Parameter "nummer".');
	$_mysql->query('INSERT INTO phonebook (Name, Number, ID) VALUES("'.$_request['name'].'","'.$_request['nummer'].'",NULL)');
	echo json_encode(true);
}
else if($_request['method'] == 'getCalls')
{
	$sql = 'SELECT Id, Date, Duration, FromNumber, ToNumber, FromName, ToName FROM AsteriskCalls WHERE LENGTH(FromNumber)>'.$_config->MaxInternalNumberLength.' AND FromNumber != ""';
	if(count($_config->ChannelsInOverview) > 0)
	{
		$sql .= ' AND (';
		for($i = 0; $i < count($_config->ChannelsInOverview); $i++)
		{
			$sql .= 'Channel LIKE "'.$_config->ChannelsInOverview[$i].'"';
			if($i < count($_config->ChannelsInOverview) - 1) $sql .= ' OR ';
		}
		$sql .= ')';
	}
	$sql .= ' ORDER BY Date DESC '.' LIMIT '.$_config->CallsInOverview;
	$calls = $_mysql->query($sql);

	// Search for changes in phonebook and format date
	for($i = 0; $i < count($calls); $i++)
	{
		$date = strtotime($calls[$i]['Date']);
		$calls[$i]['Date'] = date($_config->DateFormat, $date);
		$calls[$i]['Time'] = date($_config->TimeFormat, $date);

		// Correct errors from old version
		if($calls[$i]["FromName"] == '""' || $calls[$i]["FromName"] == 'unknown') $calls[$i]["FromName"] = '';
		if($calls[$i]["ToName"] == '""') $calls[$i]["ToName"] = '';

		if($calls[$i]["FromNumber"] == '')
		{
			$calls[$i]["FromName"] = "Unbekannt";
			$calls[$i]["FromNumber"] = "Unbekannt";
		}
		else
		{
			$names = $_mysql->query('SELECT Name FROM phonebook WHERE Number="'.$calls[$i]["FromNumber"].'"');
			$fromName = '';
			if(count($names) > 0) $fromName = $names[0]['Name'];
			if($fromName != "" && $calls[$i]["FromName"] != $fromName)
			{
				$calls[$i]["FromName"] = $fromName;
				$_mysql->query('UPDATE AsteriskCalls SET FromName="'.$fromName.'" WHERE Id="'.$calls[$i]["Id"].'"');
			}
			else if($calls[$i]["FromName"] == "") $calls[$i]["FromName"] = $calls[$i]["FromNumber"];
		}

		if($calls[$i]["ToNumber"] == '')
		{
			$calls[$i]["ToName"] = "Unbekannt";
			$calls[$i]["ToNumber"] = "Unbekannt";
		}
		else
		{
			$names = $_mysql->query('SELECT Name FROM phonebook WHERE Number="'.$calls[$i]["ToNumber"].'"');
			$toName = '';
			if(count($names) > 0) $toName = $names[0]['Name'];
			if($toName != "" && $calls[$i]["ToName"] != $toName)
			{
				$calls[$i]["ToName"] = $toName;
				$_mysql->query('UPDATE AsteriskCalls SET ToName="'.$toName.'" WHERE Id="'.$calls[$i]["Id"].'"');
			}
			else if($calls[$i]["ToName"] == "") $calls[$i]["ToName"] = $calls[$i]["ToNumber"];
		}
	}

	echo json_encode($calls);
}
else if($_request['method'] == 'newVoicemailCounts')
{
	if(!isset($_request['namen'])) die('Bitte übergeben Sie den Parameter "namen".');
	$namen = json_decode($_request['namen']);
	$counts = array();
	foreach($namen as $name)
	{
		$count = $_mysql->query('SELECT COUNT(*) AS count FROM Voicemails WHERE Name="'.$name.'" AND Context="'.$_config->VoicemailContext.'" AND Category="INBOX" AND New=1');
		$counts[$name] = (integer)$count[0]['count'];
	}
	echo json_encode($counts);
}
else if($_request['method'] == 'getVoicemails')
{
	if(!isset($_request['name'])) die('Bitte übergeben Sie den Parameter "name".');
	$voicemails = $_mysql->query('SELECT * FROM Voicemails WHERE Name="'.$_request['name'].'" AND Context="'.$_config->VoicemailContext.'" AND Category="INBOX" ORDER BY ID DESC');

	//Check for updated caller name in phonebook
	foreach($voicemails as $index => $voicemail)
	{
		$voicemails[$index]['Date'] = date($_config->DateFormat, strtotime($voicemail['Date']));
		$voicemails[$index]['Time'] = date($_config->TimeFormat, strtotime($voicemail['Time']));
		if($voicemail['Caller'] != "Unbekannt") {
			$names = $_mysql->query('SELECT Name FROM phonebook WHERE Number="'.$voicemail['Caller'].'"');
			$name = '';
			if(count($names) > 0) $name = $names[0]['Name'];
			if($voicemail['CallerName'] != $name && $name != "") {
				$voicemails[$index]['CallerName'] = $name;
				$_mysql->query('UPDATE Voicemails SET CallerName="'.$name.'" WHERE ID='.$voicemail['ID']);
			}
		}
	}
	echo json_encode($voicemails);
}
else if($_request['method'] == 'deleteVoicemail')
{
	if(!isset($_request['id'])) die('Bitte übergeben Sie den Parameter "id".');
	$voicemail = $_mysql->query('SELECT FullPath FROM Voicemails WHERE ID='.$_request['id']);
	$_mysql->query('DELETE FROM Voicemails WHERE ID='.$_request['id']);
	
	if($voicemail && count($voicemail > 0))
	{
		if(unlink($voicemail[0]["FullPath"]))
		{
			$prefix = mb_substr($voicemail[0]["FullPath"], 0, -3);
			@unlink($prefix."txt");
			@unlink($prefix."wav");
			@unlink($prefix."WAV");
			@unlink($prefix."gsm");
			@unlink($prefix."mp3");
		}
	}
	
	echo json_encode(true);
}
else if($_request['method'] == 'markUnheard')
{
	if(!isset($_request['id'])) die('Bitte übergeben Sie den Parameter "id".');
	$_mysql->query('UPDATE Voicemails SET New=1 WHERE ID='.$_request['id']);
	echo json_encode(true);
}
else if($_request['method'] == 'markHeard')
{
	if(!isset($_request['id'])) die('Bitte übergeben Sie den Parameter "id".');
	$_mysql->query('UPDATE Voicemails SET New=0 WHERE ID='.$_request['id']);
	echo json_encode(true);
}
?>

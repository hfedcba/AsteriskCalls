<?php
namespace AsteriskCalls
{
	class Config
	{
		/* Einstellungen an Installation anpassen */
		var $MySqlHost = 'localhost';
		var $MySqlPort = 3306;
		var $MySqlDatabase = 'asteriskcdrdb';
		var $MySqlUsername = 'asterisk';
		var $MySqlPassword = 'Ajslk319Yjalk1298Auh';
		
		var $DateFormat = 'd.m.Y';
		var $TimeFormat = 'H:i';
		
		// Pfad zum Sprachnachrichtenverzeichnis (mit abschließendem Slash!)
		var $VoicemailPath = '/var/spool/asterisk/voicemail/';
		var $VoicemailContext = 'Laufer';
		var $MaxInternalNumberLength = 4;
		var $CallsInOverview = 200;
	}
}
?>
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
		var $MySqlPassword = 'jlkjyxYXnxk2978Aqiuvmqz';

		var $DateFormat = 'd.m.Y';
		var $TimeFormat = 'H:i';

		var $CountryCode = '+49';

		// Pfad zum Sprachnachrichtenverzeichnis (mit abschließendem Slash!)
		var $VoicemailPath = '/var/spool/asterisk/voicemail/';
		var $VoicemailContext = 'internalsip';
		var $MaxInternalNumberLength = 4;
		// Nur diese Kanäle in der Übersicht anzeigen
		var $ChannelsInOverview = array('PJSIP/telekom_in%', 'PJSIP/gsmgateway%');
		var $CallsInOverview = 200;
	}
}
?>

<?php
namespace AsteriskCalls
{
	require_once('config.php');
	class MySql
	{
		var $_config;
		var $_mysql;
		
		function __construct()
		{
			$this->_config = new Config();
			$this->_mysql = new \mysqli($this->_config->MySqlHost, $this->_config->MySqlUsername, $this->_config->MySqlPassword, $this->_config->MySqlDatabase, $this->_config->MySqlPort);
			if($this->_mysql->connect_errno) die('Verbindung zur Datenbank konnte nicht hergestellt werden('.$this->_mysql->connect_errno.'): '.$this->_mysql->connect_error);
			if(!$this->_mysql->set_charset('utf8')) die('Konnte MySQL-Zeichenkodierung nicht auf UTF-8 setzen.');
			$result = $this->_mysql->query('SELECT 1 FROM AsteriskCalls LIMIT 1');
			if(!$result) $this->_mysql->query('CREATE TABLE `AsteriskCalls` (`Id` int(11) NOT NULL AUTO_INCREMENT, `Date` datetime NOT NULL, `FromNumber` text COLLATE utf8_swedish_ci, `FromName` text COLLATE utf8_swedish_ci, `ToNumber` text COLLATE utf8_swedish_ci, `ToName` text COLLATE utf8_swedish_ci, `UniqueId` text COLLATE utf8_swedish_ci NOT NULL, `Duration` int(11) NOT NULL, `Channel` text COLLATE utf8_swedish_ci, PRIMARY KEY (`Id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci');
			$result = $this->_mysql->query('SELECT 1 FROM Voicemails LIMIT 1');
			if(!$result) $this->_mysql->query('CREATE TABLE `Voicemails` (`ID` int(10) NOT NULL AUTO_INCREMENT, `Context` tinytext NOT NULL, `Name` tinytext NOT NULL, `Category` tinytext NOT NULL, `Path` tinytext NOT NULL, `FullPath` tinytext NOT NULL, `FilePrefix` tinytext NOT NULL, `Caller` varchar(30) NOT NULL, `CallerName` tinytext NOT NULL, `Date` varchar(15) NOT NULL, `Time` varchar(10) NOT NULL, `Duration` int(11) DEFAULT NULL, `New` tinyint(1) DEFAULT NULL, PRIMARY KEY (`ID`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci');
		}
		
		function __destruct()
		{
			if($this->_mysql) $this->_mysql->close();
		}
		
		function query($sql)
		{
			$data = Array();
			$result = $this->_mysql->query($sql);
			if($result === false) die('Konnte SQL-Befehl "'.$sql.'" nicht ausführen: '.$this->_mysql->connect_error);
			else if($result === true) return $data;
			while($row = $result->fetch_array(MYSQLI_ASSOC))
			{
				$data[] = $row;
			}
			$result->free();
			return $data;
		}
	}
}
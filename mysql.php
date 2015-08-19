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
#!/usr/bin/php5
<?php
require_once('config.php');
require_once('mysql.php');

class ProcessVoicemail
{
	private $_mysql;
	private $_config;
	private $_path;
	private $_context;
	private $_name;
	private $_fullPath;
	private $_filename;
	private $_filePrefix;
	private $_error;

	function __construct($context, $name, $filename = "")
	{
		$this->_config = new \AsteriskCalls\Config();
		$this->_mysql = new \AsteriskCalls\MySql();
		
		$this->_context = $context;
		$this->_name = $name;
		$this->_path = $this->_config->VoicemailPath;
		$this->_fullPath = $this->_config->VoicemailPath.$this->_context.'/'.$this->_name.'/INBOX/';
		$this->_filename = $filename;
	}

	function __destruct()
	{
	}

	//Diese Funktion aufrufen:
	public function process()
	{
		if($this->_context != $this->_config->VoicemailContext)
		{
			$this->_error = "Falscher Kontext. Der Kontext kann in \"config.php\" geändert werden.\n";
			return false;
		}
		if($this->_context == "" || $this->_name == "")
		{
			$this->_error = "Argumente \"context\" oder \"name\" fehlen.\n";
			return false;
		}
		if(!$this->getFilename())
		{
			$this->_error = "Sprachnachricht-Infodatei existiert nicht.\n";
			return false;
		}
		$this->voicemailToMP3();
		$voicemail = $this->readVoicemailInfo();
		$this->insertInfoIntoDb($voicemail);
		$this->setRights();
	}

	public function getError()
	{
		return $this->_error;
	}

	private function getFilename() {
		if($this->_filename != "")
		{
			$this->_filePrefix = mb_substr($this->Filename, 0, -4);
			if(!is_file($this->_fullPath.$this->_filePrefix.".txt")) return false;
			return true;
		}
		else
		{
			$files = explode("\n", shell_exec("ls -t \"$this->_fullPath\""));
			foreach($files as $file)
			{
					if(mb_substr($file, -4) == ".wav")
					{
							$this->_filePrefix = mb_substr($file, 0, -4);
							return true;
					}
			}
		}
		return false;
	}

	private function voicemailToMP3()
	{
		if(is_file($this->_fullPath.$this->_filePrefix.'.wav'))
		{
			exec('lame -V2 '.$this->_fullPath.$this->_filePrefix.'.wav '.$this->_fullPath.$this->_filePrefix.'.mp3');
			//exec("rm {$this->FullPath}{$this->FilePrefix}.wav");
		}
	}

	private function readVoicemailInfo()
	{	
		$voicemail = array();
		$voicemail['Context'] = $this->_context;
		$voicemail['Name'] = $this->_name;
		$voicemail['Category'] = 'INBOX';
		$voicemail['Path'] = './v/';
		$voicemail['FullPath'] = './v/'.$this->_context.'/'.$this->_name.'/INBOX/'.$this->_filePrefix.'.mp3';
		$voicemail['FilePrefix'] = $this->_filePrefix;
		$voicemail['Caller'] = '';
		$voicemail['CallerName'] = '';
		$voicemail['Date'] = '';
		$voicemail['Time'] = '';
		$voicemail['Duration'] = 0;
		$fh = fopen($this->_fullPath.$this->_filePrefix.'.txt', 'r');
		$content = fread($fh, filesize($this->_fullPath.$this->_filePrefix.'.txt'));
		fclose($fh);
		$content = explode("\n", $content);
		for($i = 0; $i < count($content); $i++)
		{
			switch(substr($content[$i], 0, 8))
			{
				case "callerid":
					$start = strpos($content[$i], '<', 9);
					if($start === false && strlen($content[$i]) > 10) //Ausschließlich Nummer
					{
						$voicemail['Caller'] = trim(substr($content[$i], 9));
					}
					else
					{
						if ($start === false) {
							$voicemail['Caller'] = "Unbekannt";
							$voicemail['CallerName'] = "Unbekannt";
							break;
						}
						$start++;
						$length = strpos($content[$i], ">", 10) - $start;
						$voicemail['Caller'] = trim(substr($content[$i], $start, $length));
					}
					$names = $this->_mysql->query('SELECT Name FROM phonebook WHERE Number="'.$voicemail['Caller'].'"');
					$voicemail['CallerName'] = '';
					if(count($names) > 0) $voicemail['CallerName'] = $names[0]['Name'];
					if($voicemail['CallerName'] == "") $voicemail['CallerName'] = $voicemail['Caller'];
					break;
				case "origtime":
					$Date = trim(substr($content[$i], 9));
					$voicemail['Date'] = date("Y-m-d", $Date);
					$voicemail['Time'] = date("H:i", $Date);
					break;
				case "duration":
					$voicemail['Duration'] = (integer)trim(substr($content[$i], 9));
					break;
			}
		}
		$voicemail['New'] = 1;
		return $voicemail;
	}

	private function insertInfoIntoDb($voicemail)
	{
		$this->_mysql->query('INSERT INTO Voicemails VALUES(NULL,"'.$voicemail['Context'].'","'.$voicemail['Name'].'","'.$voicemail['Category'].'","'.$voicemail['Path'].'","'.$voicemail['FullPath'].'","'.$voicemail['FilePrefix'].'","'.$voicemail['Caller'].'","'.$voicemail['CallerName'].'","'.$voicemail['Date'].'","'.$voicemail['Time'].'",'.$voicemail['Duration'].','.$voicemail['New'].')');
	}

	private function setRights() {
		exec("/bin/chgrp -R voicemail {$this->_config->VoicemailPath}");
		exec("/bin/chown -R asterisk {$this->_config->VoicemailPath}");
		exec("/bin/chmod -R 775 {$this->_config->VoicemailPath}");
	}
}

if(!isset($argv[2])) {
        echo("\nUsage:\n    postVoicemail.php {Context} {VoicemailName} -f {Optional: Filename}\n\n");
        exit();
}

if(isset($argv[3]) && $argv[3] == "-f" && isset($argv[4])) //Dateiname wurde übergeben
{
        $processVoicemail = new ProcessVoicemail($argv[1], $argv[2], $argv[4]);
}
else
{
        $processVoicemail = new ProcessVoicemail($argv[1], $argv[2]);
}

if (!$processVoicemail->process())
{
        print $processVoicemail->getError();
}
?>


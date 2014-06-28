<?php

class LittleTerminal
{
	private $keepRunning=true;
	private $inputResource=null;
	private $inputBuffer='';
	
	function __construct()
	{
		// Initialize input
		$this->inputResource=fopen("php://stdin","r");
		stream_set_blocking($this->inputResource, 0);
		
		$this->loop();
	}
	
	function shutdown()
	{
	}
	
	function scrollContent($returnToY, $returnToX)
	{
	}
	
	function writeLine($line)
	{
		echo ("\r$line\n");
		$this->displayPrompt();
	}
	
	function getWaitingLine()
	{
		return $this->inputBuffer;
	}
	
	function displayPrompt()
	{
		echo ("\r> ".$this->getWaitingLine());
		print_r($this->inputBuffer);
	}
	
	function dot()
	{
		echo (".");
	}
	
	function getInput()
	{
		// Read anything that has arrived.
		while ($input=fread($this->inputResource, 4096))
		{
			switch ($input)
			{
				default:
					$this->inputBuffer.=$input;
					break;
			}
		}
		
		if ($input!=='') echo "\n\nblah blah got '$input'\n\n";
		
		// Process any complete input
		$EOLPos=strpos($this->inputBuffer, "\n");
		if ($EOLPos !== false)
		{
			$line=substr($this->inputBuffer, 0, $EOLPos);
			// Need to think through double carridge return
			$this->inputBuffer=(strlen($this->inputBuffer) > $EOLPos)?substr($this->inputBuffer, $EOLPos+1):'';
			return $line;
		}
		elseif(strlen($input)) return true;
		else return false;
	}
	
	function stuff()
	{
		$this->writeLine("Ultra stuff version 2.7!!! (with awesome beans)");
		$this->writeLine("This is a line.");
		$this->writeLine("And another one!");
		$this->writeLine("This is really quite nice, isn't it?");
		$this->writeLine("Yup!");
	}
	
	function randomOutput()
	{
		$randomNumber=rand(0,40);
		
		switch ($randomNumber)
		{
			case 0:
				$this->writeLine("Awesummmms!");
				break;
		}
	}
	
	function doExec($line)
	{
		$this->writeLine(`$line`);
	}
	
	function loop()
	{
		$i=0;
		$this->displayPrompt();
		while ($this->keepRunning)
		{
			# $this->dot();
			$line=$this->getInput();
			if ($line===true) $this->displayPrompt();
			elseif ($line)
			{
				$spacePos=strpos($line, " ");
				$command=($spacePos)?substr($line, 0, $spacePos):$line;
				$arguments=(strlen($line) > $spacePos)?substr($line, $spacePos+1):'';
				
				$this->writeLine("> ".$line);
				switch ($command)
				{
					case 'quit':
						$this->shutdown();
						exit();
						break;
					case 'stuff':
						$this->stuff();
						break;
					case 'exec':
						$this->doExec($arguments);
						break;
					case 'cb':
						$this->inputBuffer='';
						break;
				}
			}
			
			$this->randomOutput();
			//$this->displayPrompt();
			if (!$line) usleep(100000);
		}
		
		$this->shutdown();
	}
}

// Bind interupts
#declare(ticks = 1);
#pcntl_signal(SIGTERM, "signalHandeler");
#pcntl_signal(SIGINT, "signalHandeler");

function signalHandeler($signal)
{
	switch ($signal)
	{
		case SIGTERM:
			echo "term\n";
			break;
		case SIGKILL:
			echo "kill\n";
			break;
		case SIGINT:
			echo "int\n";
			break;
		default:
			echo "I don't know this interupt!\n";
			break;
	}
}

$littleTerminal=new LittleTerminal();

?>
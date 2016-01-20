<?php

class LittleTerminal
{
	private $keepRunning=true;
	private $inputResource=null;
	private $inputBuffer='';
	
	function __construct()
	{
		// Initialize display
		ncurses_init();
		$this->fullscreen = ncurses_newwin (0, 0, 0, 0);
		ncurses_mvwaddstr($this->fullscreen, 0, 0, "LittleTerminal started\n");
		ncurses_doupdate();
		
		// Initialize input
		$this->inputResource=fopen("php://stdin","r");
		stream_set_blocking($this->inputResource, 0);
		
		$this->loop();
	}
	
	function shutdown()
	{
		// Clean up
		ncurses_getmaxyx($this->fullscreen,&$a,&$b);
		ncurses_end();
		
		
		
		echo "Width:$b\nHeight:$a\n";
	}
	
	function scrollContent($returnToY, $returnToX)
	{
		#ncurses_move(0, 0);
		#ncurses_insdelln(1);
		ncurses_scrl(-1);
		//ncurses_wrefresh($this->fullscreen);
		#ncurses_move($returnToY, $returnToX);
	}
	
	function writeLine($line)
	{
		ncurses_scrl(1);
		ncurses_getmaxyx($this->fullscreen,&$maxy,&$maxx);
		ncurses_getyx($this->fullscreen, &$y, &$x);
		if ($y > $maxy - 5)
		{
			$this->scrollContent($y, $x);
			ncurses_waddstr($this->fullscreen, "\r$line ($x/$maxx, $y/$maxy) - scroll\n");
		}
		else
		{
			ncurses_waddstr($this->fullscreen, "\r$line ($x/$maxx, $y/$maxy)\n");
		}
		
		ncurses_wrefresh($this->fullscreen);
	}
	
	function displayPrompt()
	{
		// TODO This is a slow way of doing it as the whole line needs to be redrawn
		ncurses_waddstr($this->fullscreen, "\r> ".$this->inputBuffer);
		ncurses_wrefresh($this->fullscreen);
	}
	
	function dot()
	{
		ncurses_waddstr($this->fullscreen, ".");
		ncurses_wrefresh($this->fullscreen);
	}
	
	function getInput()
	{
		// Read anything that has arrived.
		while ($input=fgets($this->inputResource, 4096))
		{
			switch ($input)
			{
				case NCURSES_KEY_BACKSPACE:
					$this->inputBuffer.='deleted';
					break;
				default:
					$this->inputBuffer.=$input;
					break;
			}
		}
		
		// Process any complete input
		$EOLPos=strpos($this->inputBuffer, "\r");
		if ($EOLPos !== false)
		{
			$line=substr($this->inputBuffer, 0, $EOLPos);
			// Need to think through double carridge return
			$this->inputBuffer=(strlen($this->inputBuffer) > $EOLPos)?substr($this->inputBuffer, $EOLPos+1):'';
			return $line;
		}
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
		$randomNumber=rand(0,100);
		
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
		while ($this->keepRunning)
		{
			# $this->dot();
			if ($line=$this->getInput())
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
				}
			}
			
			$this->randomOutput();
			
			$this->displayPrompt();
			if (!$line) usleep(100000);
		}
		
		$this->shutdown();
	}
}

$littleTerminal=new LittleTerminal();

?>
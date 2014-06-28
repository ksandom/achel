<?php

class LittleTerminal
{
	private $keepRunning=true;
	private $inputResource=null;
	private $inputBuffer='';
	private $pos=0;
	private $fullscreen=null;
	private $pad=null;
	
	function __construct()
	{
		// Initialize display
		ncurses_init();
		$this->fullscreen = ncurses_newwin (0, 0, 0, 0);
		ncurses_getmaxyx($this->fullscreen,&$y,&$x);
		$this->pad = ncurses_newpad(100,$x);
		ncurses_mvwaddstr($this->pad, 0, 0, "LittleTerminal started\n");
		//ncurses_update_panels();
		//ncurses_doupdate();
		# TODO lookup ncurses_prefresh, ncurses_pnoutrefresh, ncurses_putp, ncurses_filter
		ncurses_prefresh($this->pad, 0, 0, 0, 0, $y, $x);
		#ncurses_pnoutrefresh($this->pad);
		//ncurses_wrefresh($fullscreen);
		
		
		# TODO lookup ncurses_nl
		//ncurses_attron();
		
		# ncurses_raw(); // Attempting to interpret color codes. This ain't it! (it sends ^C etc straight through, which in itself could be very useful!)
		
		// Initialize input
		$this->inputResource=fopen("php://stdin","r");
		stream_set_blocking($this->inputResource, 0);
		
		$this->loop();
	}
	
	function shutdown()
	{
		// Clean up
		ncurses_getmaxyx($this->pad,&$a,&$b);
		ncurses_end();
		
		
		
		echo "Width:$b\nHeight:$a\n";
	}
	
	function scrollContent($returnToY, $returnToX)
	{
		$this->pos++;
		$this->refresh();
	}
	
	function refresh()
	{
		ncurses_getyx($this->pad, &$y, &$x);
		ncurses_prefresh($this->pad, $this->pos, 0, 0, 0, $y, $x); 
	}
	
	function writeLine($line)
	{
		ncurses_scrl(1);
		ncurses_getmaxyx($this->pad,&$maxy,&$maxx);
		ncurses_getyx($this->pad, &$y, &$x);
		if ($y > $maxy - 5)
		{
			$this->scrollContent($y, $x);
			ncurses_waddstr($this->pad, "\r$line ($x/$maxx, $y/$maxy) - scroll\n");
		}
		else
		{
			ncurses_waddstr($this->pad, "\r$line ($x/$maxx, $y/$maxy)\n");
		}
		
		$this->refresh();
	}
	
	function displayPrompt()
	{
		// TODO This is a slow way of doing it as the whole line needs to be redrawn
		ncurses_waddstr($this->pad, "\r> ".$this->inputBuffer);
		#ncurses_wrefresh($this->pad);
		ncurses_getyx($this->pad, &$y, &$x);
		$this->refresh();
	}
	
	function dot()
	{
		ncurses_waddstr($this->pad, ".");
		ncurses_wrefresh($this->pad);
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
			//sleep(1);
		}
		
		$this->shutdown();
	}
}

$littleTerminal=new LittleTerminal();

?>
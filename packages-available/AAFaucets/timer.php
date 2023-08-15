<?php
# Copyright (c) 2012-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# Adds the ability to put conditions into macros

/*
	There are essentially two variants with one alias and each having their not equivilent
		* ifResultExists
		* ifNotEmptyResult ifResult <-- Most of the time you'll want this one

*/

define('delayBase', 1000);

class Timer extends Module
{
	private $mainLoopRunning=false;
	private $timers=null;
	private $timerState=null;
	private $intervals=null;
	private $intervalMax=null;
	
	function __construct()
	{
		parent::__construct('Timer');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('startMainLoop'), 'startMainLoop', "Start the main loop. This should be called after at least one timer has been created.", array());
				$this->core->registerFeature($this, array('stopMainLoop'), 'stopMainLoop', "Stop the main loop. NOTE that you have to be able to call this from within the loop. So one of your timers must be able to execute features.", array());
				$this->core->registerFeature($this, array('createTimer'), 'createTimer', "Create a timer --createTimer=name,delay . This will trigger the event Timer,name.", array());
				$this->core->registerFeature($this, array('deleteTimer'), 'deleteTimer', "Delete a timer --deleteTimer=name", array());
				$this->core->registerFeature($this, array('reloadTimers'), 'reloadTimers', "Reload the timers (Timer,timers) and intervals (Timer,intervals) used in the main loop.", array());
				
				$this->core->set('Timer', 'timers', array());
				$this->timerState=array();
				$this->core->set('Timer', 'intervals', array( // Set up some sensible delays
					#10000, # TODO Enable this when throttling based on triggerEvent fully works.
					100000,
					200000,
					300000,
					400000));
				
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'startMainLoop':
				$this->mainLoop();
				break;
			case 'stopMainLoop':
				$this->stopMainLoop();
				break;
			case 'createTimer':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2);
				$this->createTimer($parms[0], $parms[1]);
				break;
			case 'deleteTimer':
				$this->deleteTimer($this->core->get('Global', $event));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function createTimer($name, $delay=100000)
	{
		$timers=$this->core->get('Timer', 'timers');
		$this->debug(2, "createTimer: Adding timer $name");
		$timers[$name]=$delay/delayBase;
		$this->timerState[$name]=microtime(true);
		$this->core->set('Timer', 'timers', $timers);
		if ($this->mainLoopRunning) $this->loadTimers();
	}
	
	function deleteTimer($name)
	{
		$timers=$this->core->get('Timer', 'timers');
		$this->debug(2, "deleteTimer: Deleting timer $name");
		unset($timers[$name]);
		unset($this->timerState[$name]);
		$this->core->set('Timer', 'timers', $timers);
		if ($this->mainLoopRunning) $this->loadTimers();
	}
	
	function loadTimers()
	{
		$this->debug(3, "loadTimers: Loaded/reloaded timers");
		$this->timers=&$this->core->get('Timer', 'timers');
		
		foreach ($this->timers as $name=>$delay)
		{
			$this->debug(3, "loadTimers:   $name: $delay");
		}
		
		$this->debug(3, "loadTimers: Mainloop timer intervals");
		$this->intervals=$this->core->get('Timer', 'intervals');
		$this->intervalMax=count($this->intervals)-1;
		
		foreach ($this->intervals as $pos=>$interval)
		{
			$this->debug(3, "loadTimers:   $pos: $interval");
		}
	}
	
	function mainLoop()
	{
		$this->loadTimers();
		
		if (!is_array($this->timers) or count($this->timers)<1)
		{
			$this->debug(1, "mainLoop: No timers created. If the mainLoop was allowed to start, it would be an uncontrollable infinite loop with no purpose.");
			return false;
		}
		
		if ($this->mainLoopRunning)
		{
			$this->debug(1, "mainLoop: The main loop has already been started. Allowing it to be started a second time could do baAaAaAad things!");
			return false;
		}
		
		$intervalPos=0;
		
		$this->mainLoopRunning=true;
		$this->debug(2, "mainLoop: Entering the loop.");
		while ($this->mainLoopRunning)
		{
			$numberOfTimers=count($this->timers);
			$numberOfTriggeredTimers=0;
			
			
			foreach ($this->timers as $name=>$delay)
			{
				$now=microtime(true);
				$difference=$now-$this->timerState[$name];
				
				if ($difference>$delay)
				{
					// echo "+$name, $difference\n";
					if ($this->core->callFeature('triggerEvent', "Timer,$name")) $numberOfTriggeredTimers++;
					$this->timerState[$name]=$now;
				}
				else
				{
					// echo "-$name, $difference\n";
				}
				
			}
			
			if ($numberOfTriggeredTimers and $intervalPos>0) $intervalPos--;
			elseif (!$numberOfTriggeredTimers and $intervalPos<$this->intervalMax) $intervalPos++;
			
			usleep($this->intervals[$intervalPos]); // Sleep long enough to give the CPU enough time to do other stuff, but no so much to make it feel sluggish.
		}
	}
	
	function stopMainLoop()
	{
		$this->mainLoopRunning=false;
		$this->debug(2, "stopMainLoop: Issued stop.");
	}
}

$core=core::assert();
$timer=new Timer();
$core->registerModule($timer);
 
?>
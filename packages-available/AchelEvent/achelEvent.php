<?php
# Copyright (c) 2014, Kevin Sandom under the BSD License. See LICENSE for full details.

# Provides Event faucets.




class AchelEvent extends Faucets
{
	function __construct()
	{
		parent::__construct(__CLASS__);
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('createEventFaucet'), 'createEventFaucet', "Creates an event Faucet that will trigger an event every time there is new data. It will trigger once per block of data, not per line.. --createEventFaucet=faucetName,categoryName[,valueSource] . categoryName is the value for categoryName that will used when triggering the event. eventName will be set to what ever channel the data was recieved on. valueSource is where the value for triggering the event comes from. It can be null, lines or channel. Lines is the number of lines recieved in the data block.", array('network', 'faucet'));
				
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'createEventFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 3, 2);
				$faucet=new EventFaucet($parms[1], $parms[2]);
				$this->environment->currentFaucet->createFaucet($parms[0], 'Event', $faucet);
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

class EventFaucet extends ThroughBasedFaucet
{
	private $categoryName='Unknown';
	private $valueSource='null';
	
	function __construct($categoryName, $valueSource)
	{
		parent::__construct(__CLASS__);
		
		$this->categoryName=$categoryName;
		$this->valueSource=$valueSource;
	}
	
	function preGet()
	{
		$gotSomething=false;
		foreach ($this->input as $channel=>$data)
		{
			if (is_array($data))
			{
				switch ($this->valueSource)
				{
					case '':
					case 'null':
						$value='';
						break;
					case 'lines':
						$value=count($data);
						break;
					case 'channel':
						$value=$channel;
						break;
					default:
						$this->core->debug(1,__CLASS__.'->'.__FUNCTION__.": Unknown valueSource \"{$this->valueSource}\" on ".__CLASS__.". Recieved on channel \"$channel\".");
						$value='unknown';
						break;
				}
				
				$this->clearInput($channel);
				$gotSomething=true;
				
				$this->core->callFeature('triggerEvent', "{$this->categoryName},$channel,$value");
			}
		}
		
		return $gotSomething;
	}
}


$core=core::assert();
$achelEvent=new AchelEvent();
$core->registerModule($achelEvent);

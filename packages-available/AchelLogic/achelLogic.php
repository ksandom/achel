<?php
# Copyright (c) 2016, Kevin Sandom under the BSD License. See LICENSE for full details.

# Flow Logic

/*
	More specific notes here.
*/


class LogicFaucets extends Faucets
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
				$this->core->registerFeature($this, array('createAtLeastFaucet'), 'createAtLeastFaucet', "Create a faucet that will only send anything if if there are at least N channels that have pending information. At that point it sends everything. Previous iterations where the requirement was not met, will be discarded. --createAtLeastFaucet=faucetName,N", array());
				
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'createAtLeastFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$this->environment->currentFaucet->createFaucet($parms[0], 'regex', new AtLeast($parms[1]));
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

class AtLeast extends ThroughBasedFaucet
{
	private $numberOfRequiredChannels=0;
	
	function __construct($numberOfRequiredChannels)
	{
		parent::__construct(__CLASS__);
		
		$this->numberOfRequiredChannels=$numberOfRequiredChannels;
	}
	
	function preGet()
	{
		$gotSomething=false;
		$channelCount=count($this->input);
		
		foreach ($this->input as $channel=>$data)
		{
			if ($channelCount>=$this->numberOfRequiredChannels)
			{
				$this->outFill($data, $channel);
				$gotSomething=true;
			}
			
			$this->clearInput($channel);
		}
		
		return $gotSomething;
	}
}



$core=core::assert();
$core->registerModule(new LogicFaucets());

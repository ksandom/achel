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
				$this->core->registerFeature($this, array('createAtLeastAndAddFaucet'), 'createAtLeastAndAddFaucet', "Create a faucet that will only send anything if if there are at least N channels that have pending information. At that point it sends everything and sends a preset value to a preset channel. Previous iterations where the requirement was not met, will be discarded. --createAtLeastAndAddFaucet=faucetName,N,channel,value", array());
				
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'createAtLeastFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$this->environment->currentFaucet->createFaucet($parms[0], 'regex', new AtLeast($parms[1]));
				break;
			case 'createAtLeastAndAddFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 4);
				$this->environment->currentFaucet->createFaucet($parms[0], 'regex', new AtLeast($parms[1], $parms[2], $parms[3]));
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
	private $channel=false;
	private $value='';
	
	function __construct($numberOfRequiredChannels, $channel=false, $value=false)
	{
		parent::__construct(__CLASS__);
		
		$this->numberOfRequiredChannels=$numberOfRequiredChannels;
		
		if ($channel)
		{
			$this->channel=$channel;
			$this->value=$value;
		}
	}
	
	function preGet()
	{
		$gotSomething=false;
		$channelCount=count($this->input);
		
		if ($channelCount>=$this->numberOfRequiredChannels and $this->channel)
		{
			$this->outFill(array($this->value), $this->channel);
			$gotSomething=true;
		}
		
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

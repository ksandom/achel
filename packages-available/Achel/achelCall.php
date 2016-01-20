<?php
# Copyright (c) 2014, Kevin Sandom under the BSD License. See LICENSE for full details.

# Provides the ability to call Achel features from Faucets.


class CallFaucets extends Faucets
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
				$this->core->registerFeature($this, array('createCallFaucet'), 'createCallFaucet', "Create a faucet to/from a call to a feature. --createCallFaucet=faucetName,feature,argument", array());
				$this->core->registerFeature($this, array('createSemiInlineCallFaucet'), 'createSemiInlineCallFaucet', "Create a faucet to/from a call to a feature, but using a variable as the/an extra parameter.. --createSemiInlineCallFaucet=faucetName,feature,[argument]", array());
				
				$this->core->registerFeature($this, array('createInlineCallFaucet'), 'createInlineCallFaucet', "Create a faucet that will call what ever feature is passed to it. The result will be it's output . --createInlineCallFaucet=faucetName", array());
				
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'createCallFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2, true);
				$this->currentFaucet->createFaucet($parms[0], 'call', new CallFaucet($parms[1], $parms[2]));
				break;
			case 'createSemiInlineCallFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2, true);
				$this->currentFaucet->createFaucet($parms[0], 'call', new CallFaucet($parms[1], $parms[2], true));
				break;
			case 'createInlineCallFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$this->currentFaucet->createFaucet($parms[0], 'inlineCall', new InlineCallFaucet());
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

class CallFaucet extends ThroughBasedFaucet
{
	private $feature='';
	private $argument='';
	private $semiInline=false;
	
	function __construct($feature, $argument, $semiInline=false)
	{
		parent::__construct(__CLASS__);
		$this->feature=$feature;
		$this->argument=$argument;
		$this->semiInline=$semiInline;
	}
	
	function preGet()
	{
		# TODO this function needs refactoring.
		$gotSomething=false;
		foreach ($this->input as $channel=>$data)
		{
			if ($data)
			{
				if ($this->semiInline)
				{
					foreach ($data as $line)
					{
						if (!is_object($line) and !is_array($line))
						{
							$parameter=($this->argument)?"{$this->argument},$line":$line;
							
							$this->core->debug(3, "CallFaucet->preGet: Calling semiInline feature={$this->feature} parameter=$parameter");
							$this->core->callFeature($this->feature, $parameter);
						}
					}
					$this->clearInput($channel);
					$gotSomething=true;
				}
				else
				{
					$this->core->debug(3, "CallFaucet->preGet: Calling feature={$this->feature} parameter={$this->argument}");
					$outData=$this->core->callFeatureWithDataset($this->feature, $this->argument, $data);
					$this->outFill($outData, $channel);
					$this->clearInput($channel);
					$gotSomething=true;
				}
			}
		}
		
		return $gotSomething;
	}
}

class InlineCallFaucet extends ThroughBasedFaucet
{
	/*
		IMPORTANT: Do not expect output from this faucet. Output will come from another source which I will document here soon. Note that you still need to have this Faucet feeding something so that the preGet() call will be made. 
		# TODO document source.
	*/
	
	private $feature='';
	private $argument='';
	
	function __construct()
	{
		parent::__construct(__CLASS__);
	}
	
	function preGet()
	{
		# $this->core->debug(0, "got here 2");
		$gotSomething=false;
		foreach ($this->input as $channel=>$data)
		{
			# $this->core->debug(0, "got here - $channel");
			if (is_array($data))
			{
				foreach ($data as $line)
				{
					if (is_string($line))
					{
						$parts=$this->core->splitOnceOn(' ', $line);
						$returnedResult=$this->core->callFeature($parts[0], $parts[1]);
						$tmpReturnedResult=$returnedResult;
						#print_r($tmpReturnedResult);
						if (is_array($returnedResult)) $this->core->setResultSetNoRef($tmpReturnedResult, __CLASS__." Command={$parts[0]} $parts[1]}");
						# unset($tmpReturnedResult); // This is needed since setResultSet takes a reference. Therefore the next iteration within the current preGet call overwrites result set regardless of whether we want it to or not.
						$this->clearInput($channel);
						$gotSomething=true;
					}
					else
					{
						$this->core->debug(0, "InlineCallFaucet($objectType)->preGet: Was expecting a string, but got ".gettype($line).". Will ignore this entry. This error is likely to repeat as the whole dataset is probably in thie format.");
					}
				}
			}
		}
		
		return $gotSomething;
	}
}






$core=core::assert();
$core->registerModule(new CallFaucets());

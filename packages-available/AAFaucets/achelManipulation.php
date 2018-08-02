<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# One line description here.

/*
	More specific notes here.
*/


class ManipulationFaucets extends Faucets
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
				$this->core->registerFeature($this, array('createRegexFaucet'), 'createRegexFaucet', "Create a faucet that directs flow based on regex rules. Further configuration with --setFaucetConfigItem will be needed for this faucet to be useful. --createRegexFaucet=faucetName", array());
				$this->core->registerFeature($this, array('createDumbReplaceFaucet'), 'createDumbReplaceFaucet', "Create a faucet that sends specified output everytime it recieves input. --createDumbReplaceFaucet=faucetName,thingToInsert", array());
				$this->core->registerFeature($this, array('createDumbInsertFaucetAfter'), 'createDumbInsertFaucetAfter', "Create a faucet that inserts specified output (after the input) everytime it recieves input. --createDumbReplaceFaucet=faucetName,thingToInsert", array());
				$this->core->registerFeature($this, array('createDumbInsertFaucetBefore'), 'createDumbInsertFaucetBefore', "Create a faucet that inserts specified output (before the input) everytime it recieves input. --createDumbReplaceFaucet=faucetName,thingToInsert", array());
				$this->core->registerFeature($this, array('createLabelFaucet'), 'createLabelFaucet', "This faucet prepends 'channelName: ' to each line it recieves and sends everything out to the default channel. --createLabelFaucet=faucetName", array());
				$this->core->registerFeature($this, array('createReplaceFaucet'), 'createReplaceFaucet', "This faucet searches for regex and replaces it with the text you designate. You will also need to add rules to define the search and replace. --createReplaceFaucet=faucetName --addFaucetConfigItemEntry=faucetName,Rules,,good,awesome", array());
				$this->core->registerFeature($this, array('createRegexGetFaucet'), 'createRegexGetFaucet', "This faucet pulls out values from input strings using a regex and places them in a destination store.", array());
				
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'createRegexFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$newFaucet=new RegexFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'regex', $newFaucet);
				break;
			case 'createDumbReplaceFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$dumbReplaceFaucet=new DumbReplaceFaucet($parms[1]);
				$this->environment->currentFaucet->createFaucet($parms[0], 'dumbReplace', $dumbReplaceFaucet);
				break;
			case 'createDumbInsertFaucetAfter':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$newFaucet=new DumbInsertFaucet($parms[1], false);
				$this->environment->currentFaucet->createFaucet($parms[0], 'dumbInsertAfter', $newFaucet);
				break;
			case 'createDumbInsertFaucetBefore':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$newFaucet=new DumbInsertFaucet($parms[1], true);
				$this->environment->currentFaucet->createFaucet($parms[0], 'dumbInsertBefore', $newFaucet);
				break;
			case 'createLabelFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$labelFaucet=new LabelFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'label', $labelFaucet);
				break;
			case 'createReplaceFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$newFaucet=new ReplaceFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'replace', $newFaucet);
				break;
			case 'createRegexGetFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$newFaucet=new RegexGetFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'regexGet', $newFaucet);
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

class RegexFaucet extends ThroughBasedFaucet
{
	function __construct()
	{
		parent::__construct(__CLASS__);
		
		$this->registerConfigItem('rules', '', 'Regex rules to define where data should be sent. --addFaucetConfigItemEntry=faucetName,rules,,ruleName,,matchRegex,destinationRegex', 'array');
		$this->registerConfigItem('onlyFirst', '', 'Send only to the first match. The alternative is to send to every match. Expecting 0 or 1.', 'integer');
		$this->setConfigItem('onlyFirst', '', '1');
		
		$this->registerConfigItem('defaultOut', '', 'If no channels match, this is the channel where the data will be sent.', 'string');
		$this->setConfigItem('defaultOut', '', 'default');
	}
	
	function preGet()
	{
		$gotSomething=false;
		
		$rules=$this->getConfigItem('rules');
			/*
				0	sourceRegex (currently unused)
				1	matchRegex
				2	destinationRegex
			*/
		
		$onlyFirst=$this->getConfigItem('onlyFirst');
		
		$defaultOut=$this->getConfigItem('defaultOut');
		if (is_array($defaultOut)) $defaultOut=$defaultOut[0];
		
		foreach ($this->input as $channel=>$data)
		{
			if ($data)
			{
				if (count($rules))
				{
					foreach ($data as $line)
					{
						$this->core->debug(3, "RegexFaucet->preGet: Delivering line");
						$delivered=false;
						foreach ($rules as $rule)
						{
							if (!preg_match("/{$rule[1]}/", $line)) continue;
							$this->outFill(array($line), $rule[2]);
							$delivered=true;
						}
						
						if (!$delivered) $this->outFill(array($line), $defaultOut);
					}
					$gotSomething=true;
					$this->clearInput($channel);
				}
				else
				{
					$lines=count($data);
					$this->core->debug(3, "RegexFaucet->preGet: no rules set, so sending $lines lines of ".gettype($data)." output to $defaultOut");
					$this->outFill($data, $defaultOut);
					$this->clearInput($channel);
					$gotSomething=true;
				}
			}
		}
		
		return $gotSomething;
	}
}

class DumbReplaceFaucet extends ThroughBasedFaucet
{
	function __construct($thingToInsert=false)
	{
		parent::__construct(__CLASS__);
		
		$this->registerConfigItem('thingToInsert', '', 'What to insert instead of the input.', 'string');
		if ($thingToInsert) $this->setConfigItem('thingToInsert', '', $thingToInsert);
	}
	
	function preGet()
	{
		$gotSomething=false;
		
		$thingToInsert=$this->getConfigItem('thingToInsert');
		foreach ($this->input as $channel=>$data)
		{
			if (!is_array($data)) continue;
			$output=array();
			foreach ($data as $line)
			{
				$output[]=$thingToInsert;
				$gotSomething=true;
			}
			$this->outFill($output, $channel);
			$this->clearInput($channel);
			
		}
		
		return $gotSomething;
	}
}

class DumbInsertFaucet extends ThroughBasedFaucet
{
	private $before=false;
	
	function __construct($thingToInsert=false, $before=false)
	{
		parent::__construct(__CLASS__);
		
		$this->before=$before;
		$actionWord=($before)?'before':'after';
		$this->registerConfigItem('thingToInsert', '', "What to insert $actionWord the input.", 'string');
		if ($thingToInsert) $this->setConfigItem('thingToInsert', '', $thingToInsert);
	}
	
	function preGet()
	{
		$gotSomething=false;
		
		$thingToInsert=$this->getConfigItem('thingToInsert');
		foreach ($this->input as $channel=>$data)
		{
			if (!is_array($data)) continue;
			$output=array();
			foreach ($data as $line)
			{
				if ($this->before)
				{
					$output[]=$thingToInsert;
					$output[]=$line;
				}
				else
				{
					$output[]=$line;
					$output[]=$thingToInsert;
				}
				
				$gotSomething=true;
			}
			$this->outFill($output, $channel);
			$this->clearInput($channel);
			
		}
		
		return $gotSomething;
	}
}

class LabelFaucet extends ThroughBasedFaucet
{
	function __construct()
	{
		parent::__construct(__CLASS__);
	}
	
	function preGet()
	{
		$gotSomething=false;
		
		foreach ($this->input as $channel=>$data)
		{
			$output=array();
			if (is_array($data))
			{
				foreach ($data as $line)
				{
					if (is_string($line))
					{
						$output[]="$channel: $line";
					}
					else
					{
						$this->core->debug(2, "LabelFaucet->preGet: Expected line to be a string, but got ".gettype($line));
					}
				}
				
				$this->outFill($output);
				$this->clearInput($channel);
				$gotSomething=true;
			}
			else
			{
				$this->core->debug(2, "LabelFaucet->preGet: Expected data to be an array, but got ".gettype($data));
			}
		}
		
		return $gotSomething;
	}
}

class ReplaceFaucet extends ThroughBasedFaucet
{
	function __construct()
	{
		parent::__construct(__CLASS__);
		
		/*
			registerConfigItem($settingName, $subcategory, $description, $type='array')
			setConfigItem($settingName, $subcategory, $value)
			addConfigItemEntry($settingName, $subcategory, $entryName, $entryValue)
			getConfigItem($settingName, $subcategory=false)
			getConfig()
		*/
		
		# TODO The subcategory could be used for channel so that a single object can do different rules for different channels.
		$this->registerConfigItem('Rules', 'default', 'Regex rules for search and replace. key=search, value=replace.', 'array');
	}
	
	function preGet()
	{
		$gotSomething=false;
		$rules=$this->getConfigItem('Rules', 'default');
		
		# TODO It would be worth considering if the rebuild should be triggered only when the rules change.
		$search=array();
		$replace=array();
		foreach ($rules as $ruleSearch=>$ruleReplace)
		{
			$search[]="/$ruleSearch/";
			$replace[]=$ruleReplace[0];
		}
		
		foreach ($this->input as $channel=>$data)
		{
			$output=array();
			if (is_array($data))
			{
				foreach ($data as $line)
				{
					if (is_string($line))
					{
						$line=preg_replace($search, $replace, $line);
						$output[]="$line";
					}
					else
					{
						$this->core->debug(2, "ReplaceFaucet->preGet: Expected line to be a string, but got ".gettype($line));
					}
				}
				
				$this->outFill($output, $channel);
				$this->clearInput($channel);
				$gotSomething=true;
			}
			else
			{
				$this->core->debug(2, "ReplaceFaucet->preGet: Expected data to be an array, but got ".gettype($data));
			}
		}
		
		return $gotSomething;
	}
}

class RegexGetFaucet extends ThroughBasedFaucet
{
	function __construct()
	{
		parent::__construct(__CLASS__);
		
		/*
			Here is an example configuration
			
			Category - eg location tracking
				variable - eg instances
					rule0 - The first rule
						regex
							regexValue - The actual regex value
						destination
							destinationValue - Where values should be saved.
						0 - Mapping for the first value found
							valueName
								valueNameValue - What they key should be called. (See note below)
						1 - Mapping for the second value found
							valueName
								valueNameValue - What they key should be called. (See note below)
						2 etc
			
			Note:
				valueName can be any arbitrary name that is a valid variable name. However there are two special cases:
					* key - The value will define the valueName of an entry mapped to "value".
					* value - The item mapped to value to become the value of the entry that was defined by key.
				
				In this example, there should be a line after creating the faucet like this
				
					deliver faucetName,_control,setConfigSrc Category,variable
						# TODO This currently won't work because variable will be interpreted as a separate parameter. Double check this.
			
			In reality this looks something like this
			
			setNested ["AP","stateConfig","rule0","regex","^.*./(.*) = '(.*)'.*$"]
			setNested ["AP","stateConfig","rule0","destination","AP,state"]
			setNested ["AP","stateConfig","rule0","0","key"]
			setNested ["AP","stateConfig","rule0","1","value"]
			
			Note that the regex is slightly mangled in this example so that it doesn't interfere with the PHP comment syntax.
		*/
	}
	
	function preGet()
	{
		$gotSomething=false;
		
		foreach ($this->input as $channel=>$data)
		{
			if ($channel=='_control')
			{
				$this->callControl($data);
				$this->clearInput($channel);
				$gotSomething=true;
				continue;
			}
			
			if (is_array($data))
			{
				foreach ($data as $line)
				{
					if (is_string($line))
					{
						foreach ($this->config as $ruleName=>$rule)
						{
							if (!isset($rule['regex']))
							{
								$this->core->debug(2, "RegexGetFaucet->preGet: Missing regex in rule \"$ruleName\".");
								break;
							}
							if (!isset($rule['destination']))
							{
								$this->core->debug(2, "RegexGetFaucet->preGet: Missing destination in rule \"$ruleName\".");
								break;
							}
							
							$matches=array();
							if (preg_match('/'.$rule['regex'].'/', $line, $matches))
							{
								$this->core->debug(3, "RegexGetFaucet->preGet: Matched rule \"$ruleName\" on line \"$line\".");
								$key=false;
								$value=false;
								
								foreach ($matches as $matchKey=>$match)
								{
									if (!isset($rule[$matchKey]))
									{
										$this->core->debug(4, "RegexGetFaucet->preGet: Skipping match $matchKey since there is nothing defined for it in the rule \"$ruleName\".");
										continue;
									}
									
									$mappingKey=$rule[$matchKey];
									switch ($mappingKey)
									{
										case 'key':
											$key=$match;
											break;
										case 'value':
											$value=$match;
											break;
										default:
											$destination=$rule['destination'].",$channel,$mappingKey";
											$this->core->setNestedViaPath($destination, $match);
											break;
									}
								}
								
								if ($key!==false and $value !==false)
								{
									$destination=$rule['destination'].",$channel,$key";
									$this->core->setNestedViaPath($destination, $value);
									$this->core->debug(3, "RegexGetFaucet->preGet: key=\"$key\" value=\"$value\" destination=\"$destination\"");
								}
							}
							else
							{
								$this->core->debug(3, "Did not match rule \"$ruleName\" ({$rule['regex']}) on line \"$line\".");
							}
						}
					}
					else
					{
						$this->core->debug(2, "RegexGetFaucet->preGet: Expected line to be a string, but got ".gettype($line));
					}
				}
				
				$this->clearInput($channel);
				$gotSomething=true;
			}
			else
			{
				$this->core->debug(2, "RegexGetFaucet->preGet: Expected data to be an array, but got ".gettype($data));
			}
		}
		
		return $gotSomething;
	}
}


$core=core::assert();
$achelManipulation=new ManipulationFaucets();
$core->registerModule($achelManipulation);

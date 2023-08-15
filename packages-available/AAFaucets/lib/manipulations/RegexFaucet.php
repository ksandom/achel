<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# For all the data that comes in. Send out only lines that match the regex.

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
						$this->debug(3, "RegexFaucet->preGet: Delivering line");
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
					$this->debug(3, "RegexFaucet->preGet: no rules set, so sending $lines lines of ".gettype($data)." output to $defaultOut");
					$this->outFill($data, $defaultOut);
					$this->clearInput($channel);
					$gotSomething=true;
				}
			}
		}

		return $gotSomething;
	}
}

?>

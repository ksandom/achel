<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Call an internal feature. And send the resultSet out.

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

							$this->debug(3, "CallFaucet->preGet: Calling semiInline feature={$this->feature} parameter=$parameter");
							$this->core->callFeature($this->feature, $parameter);
						}
					}
					$this->clearInput($channel);
					$gotSomething=true;
				}
				else
				{
					$this->debug(3, "CallFaucet->preGet: Calling feature={$this->feature} parameter={$this->argument}");
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

?>

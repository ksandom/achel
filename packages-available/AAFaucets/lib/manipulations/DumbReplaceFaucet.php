<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Every time we receive some data. Send ____ out instead.

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

?>

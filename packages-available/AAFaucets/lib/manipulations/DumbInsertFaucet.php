<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Prepend/Append some data to each line that passes through.

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

?>

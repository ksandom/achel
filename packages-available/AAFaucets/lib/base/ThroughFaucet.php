<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Take in data on various channels, and put it out on the same channel. This is useful for amalgamating several data sources together and presenting them as one coherent data source.

class ThroughFaucet extends ThroughBasedFaucet
{
	private $isTwoWay=false;

	function __construct($isTwoWay=false)
	{
		$this->isTwoWay=$isTwoWay;
		parent::__construct(__CLASS__);
	}

	function preGet()
	{
		$gotSomething=false;

		if ($this->isTwoWay)
		{
			if (isset($this->input['default']))
			{
				$this->outFill($this->input['default'], 'inside');
				$this->clearInput('default');
				$gotSomething=true;
			}

			if (isset($this->input['inside']))
			{
				$this->outFill($this->input['inside'], 'default');
				$this->clearInput('inside');
				$gotSomething=true;
			}

			foreach (array_keys($this->input) as $channel)
			{
				if (count($this->input[$channel])>0)
				{
					$this->core->debug(1, "The 2WayThroughFaucet does not accept input for channels other than \"default\" and \"inside\". Data was recieved on channel \"$channel\". This will not cause a fatal error, but it certainly indicates a bug in the code you are running.");
					$this->clearInput('inside');
				}
			}
		}
		else
		{
			foreach ($this->input as $channel=>$data)
			{
				$this->outFill($data, $channel);
				$this->clearInput($channel);
				$gotSomething=true;
			}
		}

		return $gotSomething;
	}
}

?>

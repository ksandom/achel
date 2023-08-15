<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Send data to nowhere. Useful for anchoring another faucet.

class NullFaucet extends ThroughBasedFaucet
{
	/*
		Many Faucet actions will only get performed when there is at least one pipe connected to the exit of the faucet. But sometimes we want to create a fully funtioning faucet without connecting it to anything.

		The NullFaucet gives you something to connect it to without causing data to build up anywhere.
	*/

	function __construct()
	{
		parent::__construct(__CLASS__);
	}

	function deconstruct()
	{
	}

	function preGet()
	{
		foreach ($this->input as $channel=>$data)
		{
			if ($data)
			{
				$this->clearInput($channel);
			}
		}

		return false;
	}
}


?>

<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Use the in channel as the label to prepend to each line that comes in.

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
						$this->debug($this->l2, "LabelFaucet->preGet: Expected line to be a string, but got ".gettype($line));
					}
				}

				$this->outFill($output);
				$this->clearInput($channel);
				$gotSomething=true;
			}
			else
			{
				$this->debug($this->l2, "LabelFaucet->preGet: Expected data to be an array, but got ".gettype($data));
			}
		}

		return $gotSomething;
	}
}

?>

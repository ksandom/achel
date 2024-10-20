<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Call an internal feature, and expect any data to be shipped elsewhere.

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
		# $this->debug($this->l0, "got here 2");
		$gotSomething=false;
		foreach ($this->input as $channel=>$data)
		{
			# $this->debug($this->l0, "got here - $channel");
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
						# $this->debug($this->l0, "Result type=".gettype($returnedResult)." count=".count($returnedResult));
						$this->clearInput($channel);
						$gotSomething=true;
					}
					else
					{
						$this->debug($this->l0, "InlineCallFaucet($objectType)->preGet: Was expecting a string, but got ".gettype($line).". Will ignore this entry. This error is likely to repeat as the whole dataset is probably in thie format.");
					}
				}
			}
		}

		return $gotSomething;
	}
}
?>

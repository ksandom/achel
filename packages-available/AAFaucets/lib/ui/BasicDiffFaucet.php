<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# A very, very basic diff of input across different channels.

class BasicDiffFaucet extends ThroughBasedFaucet
{
	private $lineResults=null;

	function __construct()
	{
		parent::__construct(__CLASS__);
	}

	function clearLineResults()
	{
		$this->lineResults=array();
	}

	function processedInput($channel, $value)
	{
		$hash=md5($value);
		if (!isset($this->lineResults[$hash])) $this->lineResults[$hash]=array('value'=>$value, 'channels'=>array());

		$this->lineResults[$hash]['channels'][]=$channel;
		$this->lineResults[$hash]['value']=$value;
	}

	function preGet()
	{
		$gotSomething=false;

		# Find the maximum number of lines we need to deal with in this run.
		# TODO detect channels that have not contributed
		$maxInput=0;
		foreach ($this->input as $channel=>$data)
		{
			$channelCount=count($data);
			if ($channelChoice>$maxInput) $maxInput=$channelCount;
		}

		if ($maxInput) $gotSomethintrue;
		else return false;

		for ($lineNumber=0;$lineNumber<$maxInput;$lineNumber++)
		{
			$this->clearLineResults();
			foreach ($this->input as $channel=>$data)
			{
				$keys=array_keys($data);
				if (!isset($keys[$lineNumber])) continue;
				$value=$data[$keys[$lineNumber]];

				$this->processInput($channel, $value);
			}

			# TODO work out what to do with lineResults
			foreach ($this->lineResults as $hash=>$details)
			{
				$channelSummary=implode('+', $details['channels']);
				$this->outFill($details['value'], $channelSummary);
			}
		}

		foreach ($this->input as $channel=>$data)
		{
			$this->clearInput($channel);
		}

		return $gotSomething;
	}
}


?>

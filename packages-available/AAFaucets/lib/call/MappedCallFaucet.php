<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Call the same feature every time, and map the returned data's keys to channels.

class MappedCallFaucet extends ThroughBasedFaucet
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


	function last($series)
	{
		if (is_array($series))
		{
			$keys=array_keys($series);
			return $series[$keys[count($keys)-1]];
		}
		else
		{
			return $series;
		}
	}

	function buildInput($input)
	{
		$builtInput=array();
		foreach ($input as $key=>$value)
		{
			$builtInput[$key]=$this->last($value);
		}

		# TODO remove this
		# $this->debug(0,"MAPPED: adfasdfwed");
		# print_r($builtInput);
		return $builtInput;
	}

	function preGet()
	{
		$gotSomething=false;
		if ($numberOfChannels=count($this->input))
		{
			if ($numberOfChannels==1 and isset($this->input['default']))
			{
				if (!$this->input['default']) return false;
			}

			$gotSomething=true;



			$this->debug(4, "MappedCallFaucet->preGet: Calling feature={$this->feature} parameter={$this->argument}");
			$builtInput=$this->buildInput($this->input);
			$returnedData=$this->core->callFeatureWithDataset($this->feature, $this->argument, $builtInput);
			foreach ($returnedData as $channel=>$outData)
			{
				$this->outFill(array($outData), $channel);
			}
			$this->clearInput();
			$gotSomething=true;
		}

		return $gotSomething;
	}
}

?>

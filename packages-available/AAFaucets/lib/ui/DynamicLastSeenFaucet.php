<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Track how long ago it was since we last heard from a given channel.

class DynamicLastSeenFaucet extends ThroughBasedFaucet
{
	private $secondsSinceChannel=5;
	private $secondsSinceAnyChannel=30;
	private $secondsSinceUserInput=30;
	private $lastSeen=null;
	private $lookupTable=null;
	private $lookupTableCount=0;
	private $lastOutputLength='';
	private $maximumChannelsToDisplay=3;
	private $lastChannelOffset=0;

	function __construct($secondsSinceChannel=false, $secondsSinceAnyChannel=false)
	{
		parent::__construct(__CLASS__);

		# TODO The timezone needs to be done better
		ini_set('date.timezone', 'UTC');

		if ($secondsSinceChannel!==false) $this->secondsSinceChannel=$secondsSinceChannel;
		if ($secondsSinceAnyChannel!==false) $this->secondsSinceAnyChannel=$secondsSinceAnyChannel;

		# TODO $secondsSinceUserInput

		$this->forgetAll(); # A clean way to initialise it.

		$this->lookupTable=array();
		$this->generateLookupTable(array(10=>'blue', 15=>'cyan', 20=>'green', 25=>'yellow', 30=>'red', 35=>'brightRed', 40=>'brightWhiteHLRed'));
	}

	function deconstruct()
	{
		parent::deconstruct();
	}

	function generateLookupTable($colors)
	{
		$last=0;
		foreach ($colors as $max=>$color)
		{
			for ($index=$last;$index<$max;$index++)
			{
				$this->lookupTable[$index]=$this->core->get('Color', $color);
			}
			$last=$index;
		}
		$this->lookupTableCount=count($this->lookupTable);
	}

	function getSeen($channel)
	{
		if (isset($this->lastSeen[$channel])) return date('U')-$this->lastSeen[$channel];
		else
		{
			$this->setSeen($channel);
			return 0;
		}

	}

	function setSeen($channel, $secondsAgo=false)
	{
		$newTime=($secondsAgo)?date('U')-$secondsAgo:date('U');
		$this->lastSeen[$channel]=$newTime;
	}

	function getUserSummary($input)
	{
		$output='';
		$result=array();
		$resultMask=array();
		$default=$this->core->get('Color', 'default');
		$numberOfInputEntries=count($input);
		$rows=$numberOfInputEntries/$this->maximumChannelsToDisplay;
		if ($rows>intval($rows)) $rows=intval($rows)+1;

		if ($numberOfInputEntries>$this->maximumChannelsToDisplay)
		{
			$start=$this->maximumChannelsToDisplay*$this->lastChannelOffset;
			$finish=$this->maximumChannelsToDisplay*($this->lastChannelOffset+1);
			if ($finish>$numberOfInputEntries) $finish=$numberOfInputEntries;

			$channelIndex=array_keys($input);
			$selectedInput=array();

			for ($selectedChannelNumber=$start;$selectedChannelNumber<$finish;$selectedChannelNumber++)
			{
				$selectedInput[$channelIndex[$selectedChannelNumber]]=$input[$channelIndex[$selectedChannelNumber]];
			}

			$this->lastChannelOffset++;
			if ($this->lastChannelOffset>$rows-1) $this->lastChannelOffset=0;
		}
		else $selectedInput=$input;

		foreach ($selectedInput as $key=>$value)
		{
			$index=($value<$this->lookupTableCount)?$value:$this->lookupTableCount-1;
			$color=$this->lookupTable[$index];
			$result[]="$color$key: $value$default";
			$resultMask[]="$key: $value";
		}

		/*
			Escape codes:
			http://www.isthe.com/chongo/tech/comp/ansi_escapes.html
			http://bluesock.org/~willg/dev/ansi.html#sequences
		*/
		$output="\r\033[1A".implode(', ', $result);
		$outputMask=implode(', ', $resultMask);

		$outputLength=strlen($outputMask);
		$outputPaddingLength=$this->lastOutputLength-$outputLength;

		if ($outputPaddingLength>0) $output.=str_repeat(' ', $outputPaddingLength);

		$this->lastOutputLength=$outputLength;

		return $output;
	}

	function clean()
	{
		$output=str_repeat(' ', $this->lastOutputLength)."\r\033[1A";
	}

	function preGet()
	{
		$gotSomething=false;

		$result=array();

		# TODO Check flow. Do we need to loop through input?
		foreach ($this->lastSeen as $channel=>$rawLastSeenValue)
		{
			$lastSeen=$this->getSeen($channel);

			if ($lastSeen>$this->secondsSinceChannel
				and $channel!='_userInterface'
				and $channel!='_userInput') $result[$channel]=$lastSeen;
		}

		if (count($result))
		{
			# NOTE If the resolution ever gets increased, the test for _userInterface will need to be adapted to stop it come consuming lots of resources.
			if ($this->getSeen('_userInterface')>0  and $this->getSeen('_userInput')>$this->secondsSinceUserInput)
			{
				$gotSomething=true;
				$prefix=($this->lastOutputLength)?'':"\n";
				$this->outFill(array($prefix.$this->getUserSummary($result)));

				$this->setSeen('_userInterface');
			}
		}
		else
		{
			if ($this->lastOutputLength)
			{
				$gotSomething=true;
				$this->outFill(array($this->clean()));
				$this->lastOutputLength=0;
			}
		}

		return $gotSomething;
	}

	function put($data, $channel)
	{ /* Send data out
		Expects an array of strings.
		*/
		if (is_array($data))
		{
			switch ($channel)
			{
				case '_control':
					foreach ($data as $line)
					{
						$parms=$this->core->splitOnceOn(' ', $line);
						$this->control($parms[0], $parms[1]);
					}
					break;
				default:
					$this->setSeen($channel);
					$this->lastOutputLength=0;
					break;
			}
		}
		elseif(is_string($data)) $this->debug(2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings but got a string: \"$line\"");
		else $this->debug(2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but got ".gettype($data));
	}

	function forgetChannel($channel)
	{
		if(isset($this->lastSeen[$channel])) unset($this->lastSeen[$channel]);
	}

	function forgetOlderThan($age)
	{
		foreach ($this->lastSeen as $channel=>$value)
		{
			if ($this->getSeen($channel)>$age)
			{
				$this->forgetChannel($channel);
			}
		}
	}

	function forgetAll()
	{
		$this->lastSeen=array();
		$this->setSeen('_userInterface');
		$this->setSeen('_userInput', $this->secondsSinceUserInput);
	}

	function control($feature, $value)
	{
		switch ($feature)
		{
			case 'forgetChannel':
				$this->debug(2, __CLASS__.'->'.__FUNCTION__.": Issueing forget for channel $value.");
				$this->forgetChannel($value);
				break;
			case 'forgetOlderThan':
				$this->debug(2, __CLASS__.'->'.__FUNCTION__.": Issueing forget for channels older than $value seconds.");
				$this->forgetOlderThan($value);
				break;
			case 'forgetAll':
				$this->debug(2, __CLASS__.'->'.__FUNCTION__.": Issueing forget for all channels.");
				$this->forgetAll();
				break;

			default:
				parent::control($feature, $value);
				return false;
				break;
		}
	}
}


?>

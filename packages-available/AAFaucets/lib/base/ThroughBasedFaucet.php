<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Foundations for faucets that can take in blocks of data, and send out blocks of data. Most faucets are based on this idea.

class ThroughBasedFaucet extends Faucet
{
	protected $input=null;

	function __construct($objectType)
	{
		parent::__construct($objectType);

		$this->input=array();
	}

	function deconstruct()
	{
	}

	private function mergeInChannelData($channel, $data)
	{
		if ($channel=='') $channel="default";

		if (!isset($this->input[$channel]))
		{
			$this->input[$channel]=$data;
		}
		elseif(is_array($data))
		{
			foreach ($data as $key=>$line)
			{
				if (is_numeric($key)) $this->input[$channel][]=$line;
				elseif (isset($this->input[$channel][$key]))
				{
					if (is_array($this->input[$channel][$key]) and is_array($line))
					{
						$this->input[$channel][$key]=array_merge($this->input[$channel][$key], $line);
					}
					else
					{
						$this->core->debug(2, "->storeData: $key already exists in channel $channel and is ".gettype($this->input[$channel][$key])." while the input is ".gettype($line).". Both need to be an array to be merged. Going to replace the existing data. This is very likely not what you want.");
						$this->input[$channel][$key]=$line;
					}
				}
				else $this->input[$channel][$key]=$line;
			}
		}
	}

	function storeData($data, $channel)
	{
		if ($channel=='*')
		{
			if (count($this->input))
			{
				foreach ($data as $channel=>$channelData)
				{
					$this->mergeInChannelData($channel, $channelData);
				}
			}
			else
			{
				$this->input=$data;
			}
		}
		else
		{
			$this->mergeInChannelData($channel, $data);
		}
	}

	function clearInput($channel=false)
	{
		if ($channel)
		{
			unset($this->input[$channel]);
		}
		else
		{
			$this->input=array();
		}
	}

	function put($data, $channel)
	{
		$this->storeData($data, $channel);
	}

	function &getInQues()
	{
		return $this->input;
	}
}


?>

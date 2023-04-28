<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# The foundation functionality for all faucets.

class Faucet
{
	protected $core=null;
	protected $inputBuffer='';
	protected $objectType='';
	private $outChannels=null;
	protected $config=null;
	protected $configRegistry=null;
	protected $faucetInstanceName='unknown';
	protected $parent=null;

	public $pipeDebugLevel=5;

	function __construct($objectType)
	{
		$this->objectType=$objectType;
		$this->core=core::assert();
		$this->outChannels=array();
		$this->config=array();
		$this->configRegistry=array();

		$this->updatePipeDebugLevel();
	}

	function updatePipeDebugLevel()
	{
		$this->pipeDebugLevel=$this->core->get('General', 'pipeDebugLevel');
		if ($this->pipeDebugLevel=='')
		{
			$this->pipeDebugLevel=5;
			$this->core->set('General', 'pipeDebugLevel', $this->pipeDebugLevel);
		}
	}

	function getObjectType()
	{
		return $this->objectType;
	}

	function setParent(&$parent)
	{
		$this->parent=&$parent;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setInstanceName($instanceName)
	{
		$this->faucetInstanceName=$instanceName;
	}

	public function getInstanceName()
	{
		return $this->faucetInstanceName;
	}

	public function beginScopedEvent(&$faucet)
	{
		$environment=FaucetEnvironment::assert();
		$environment->beginScopedEvent($faucet);
	}

	public function endScopedEvent()
	{
		$environment=FaucetEnvironment::assert();
		$environment->endScopedEvent();
	}

	private function mergeOutFillData($outChannel, $data)
	{
		if (!isset($this->outChannels[$outChannel]))
		{
			$this->core->debug(4, "outFill: Created channel $outChannel");
			$this->outChannels[$outChannel]=array();
		}

		if (count($this->outChannels[$outChannel]))
		{ // We need to carefully integrate the new data with the existing data
			if (is_array($data))
			{
				foreach ($data as $key=>$value)
				{
					if (is_numeric($key)) $this->outChannels[$outChannel][]=$value;
					else
					{
						if (isset($this->outChannels[$outChannel][$key]))
						{
							if (is_array($this->outChannels[$outChannel][$key]) and is_array($value))
							{
								$this->outChannels[$outChannel][$key]=array_merge($this->outChannels[$outChannel][$key], $value);
							}
							else
							{
								$this->core->debug(4, "outFill: Data collision. This shouldn't happen, but could if a specific key ($key) is used, and the input (".gettype($value).") and the existing value (".gettype($this->outChannels[$outChannel][$key]).") are not both arrays. In this case, the new value is going to replace the old value.");
								$this->outChannels[$outChannel][$key]=$value;
							}
						}
						else
						{
							$this->core->debug(4, "outFill: Directly saved fresh data as key $key in channel $outChannel. Objecttype {$this->objectType}");
							$this->outChannels[$outChannel][$key]=$value;
						}
					}
				}
			}
			else
			{
				if (is_array($this->outChannels[$outChannel]))
				{
					$this->outChannels[$outChannel][]=$data;
				}
				else
				{
					$this->outChannels[$outChannel]=$data;
				}
			}
		}
		else
		{ // We can simply stick our data there
			$this->core->debug(4, "outFill: Saved fresh data in channel $outChannel. Objecttype {$this->objectType}");
			$this->outChannels[$outChannel]=$data;
		}
	}

	function outFill($data, $channel=false)
	{ // Send output to a particular channel
		if (!$data) return false;

		if ($channel=='*')
		{ // We have been given all of the channels at once. This needs to be done a little differently.
			if (count($this->outChannels))
			{ // We need to merge
				foreach ($data as $dataChannel=>$dataChannelData)
				{
					$this->mergeOutFillData($dataChannel, $dataChannelData);
				}
			}
			else
			{ // Just overwrite it.
				$this->outChannels=$data;
			}
		}
		else
		{ // Normal flow: We have been givin a specific channel.
			$outChannel=($channel!==false)?$channel:'default';
			$this->mergeOutFillData($outChannel, $data);
		}
		return true;
	}

	function &getOutQues()
	{
		return $this->outChannels;
	}

	function get($channel=false)
	{
		$channelChoice=($channel!==false)?$channel:'default';

		if (!isset($this->outChannels[$channelChoice]))
		{
			$this->core->debug(4, __CLASS__."->get: Channel $channelChoice does not exist. It may be that data has not been written to it yet. Objecttype {$this->objectType}");
			return false;
		}

		$output=$this->outChannels[$channelChoice];
		$this->outChannels[$channelChoice]=array();
		return $output;
	}

	function processInputBuffer()
	{
		// Process any complete input. NOTE that this mentality will not work for binary data.
		$EOLPos=strpos($this->inputBuffer, inputLineSeparator);
		if ($EOLPos !== false)
		{
			$this->core->debug(4, "processInputBuffer: New line found");

			$lines=explode(inputLineSeparator, $this->inputBuffer);

			$last=count($lines)-1;
			$this->inputBuffer=$lines[$last];

			unset($lines[$last]);
			return $lines;
		}
		else
		{
			return false;
		}
	}

	function registerConfigItem($settingName, $subcategory, $description, $type='array')
	{
		$chosenSubcategory=($subcategory)?$subcategory:'default';

		# TODO Should config actually be configRegistry
		if (isset($this->config[$settingName][$chosenSubcategory]))
		{
			$this->core->debug(1, "registeronfigItem: Setting $settingName/$chosenSubcategory has already been registered for {$this->objectType}.");
		}
		else
		{
			if (!$type) $type=false;
			$this->configRegistry[$settingName]=array($chosenSubcategory=>array(
				'description'=>$description,
				'type'=>$type));
			$this->config[$settingName]=array($chosenSubcategory=>array());
		}
	}

	function getRegisteredConfigItem($settingName, $subcategory)
	{
		$chosenSubcategory=($subcategory)?$subcategory:'default';

		# TODO Should config actually be configRegistry
		if (isset($this->config[$settingName][$chosenSubcategory]))
		{
			return $this->configRegistry[$settingName][$chosenSubcategory];
		}
		else
		{
			$this->core->debug(1, __CLASS__.'->'.__FUNCTION__.": Setting $settingName/$chosenSubcategory has not been registered on {$this->objectType}.");
			return false;
		}
	}

	function setConfigItem($settingName, $subcategory, $value)
	{
		$chosenSubcategory=($subcategory)?$subcategory:'default';

		if (isset($this->config[$settingName][$chosenSubcategory]))
		{
			$this->config[$settingName][$chosenSubcategory]=$value;
		}
		else
		{
			$this->core->debug(1, "setConfigItem: Setting $settingName/$chosenSubcategory has not been registered for {$this->objectType}.");
		}
	}

	protected function setConfigItemReference($settingName, $subcategory, &$value)
	{
		/*
			The purpose of this is to expose config items of particular Faucets so that MetaFaucets' configItems can manipulate config items of the Faucets it contains.

			Please don't use it for anything else.
		*/

		$chosenSubcategory=($subcategory)?$subcategory:'default';

		if (isset($this->config[$settingName][$chosenSubcategory]))
		{
			# TODO Check this. It may be better to use &= (if that's syntactically correct).
			$this->config[$settingName][$chosenSubcategory]=&$value;
		}
		else
		{
			$this->core->debug(1, "setConfigItemReference: Setting $settingName/$chosenSubcategory has not been registered for {$this->objectType}.");
		}
	}

	function addConfigItemEntry($settingName, $subcategory, $entryName, $entryValue)
	{
		$chosenSubcategory=($subcategory)?$subcategory:'default';

		if (isset($this->config[$settingName][$chosenSubcategory]))
		{
			if ($this->configRegistry[$settingName][$chosenSubcategory]['type']=='array')
			{
				if (!isset($this->config[$settingName][$chosenSubcategory]))
				{
					$this->config[$settingName][$chosenSubcategory]=array();
				}

				$this->config[$settingName][$chosenSubcategory][$entryName]=$entryValue;
			}
			else
			{
				$this->core->debug(1, "addConfigItemEntry: Setting $settingName/$chosenSubcategory for {$this->objectType} is {$this->configRegistry[$settingName][$chosenSubcategory]['type']} when array is required.");
			}
		}
		else
		{
			$this->core->debug(1, "addConfigItemEntry: Setting $settingName/$chosenSubcategory has not been registered for {$this->objectType}.");
		}
	}

	function removeConfigItemEntry($settingName, $subcategory, $entryName)
	{
		$chosenSubcategory=($subcategory)?$subcategory:'default';
		if (isset($this->config[$settingName][$chosenSubcategory]))
		{
			if ($this->configRegistry[$settingName][$chosenSubcategory]['type']=='array')
			{
				if (isset($this->config[$settingName][$chosenSubcategory]))
				{
					$this->core->debug(1, "removeConfigItemEntry($settingName, $subcategory, $entryName)");
					unset($this->config[$settingName][$chosenSubcategory][$entryName]);
				}
			}
		}
	}

	function getConfigItem($settingName, $subcategory=false)
	{
		return $this->getConfigItemByReferece($settingName, $subcategory);
	}

	function &getConfigItemByReferece($settingName, $subcategory=false)
	{
		$chosenSubcategory=($subcategory)?$subcategory:'default';

		if (isset($this->config[$settingName][$chosenSubcategory]))
		{
			return $this->config[$settingName][$chosenSubcategory];
		}
		else
		{
			$this->core->debug(1, "getConfigItem: Setting $settingName/$chosenSubcategory has not been registered for {$this->objectType}.");
			$nothing=false;
			return $nothing;
		}
	}


	function getConfig()
	{
		return $this->config;
	}

	function callControl($lines)
	{
		foreach ($lines as $line)
		{
			$part=explode(' ', $line);
			$value=(isset($part[1]))?$part[1]:'';
			$this->control($part[0], $value);
		}
	}

	function &getReplacementConfig($address)
	{
		# NOTE I've just removed the reference. If quirky behavior appears, this is a good place to look.
		$config=$this->core->getNested(explode(',', $address));
		if (is_array($config)) return $config;
		else
		{
			$result=array();
			$this->core->debug(1, "Could not find valid config in \"$address\"");
			return $result;
		}
	}

	function control($feature, $value)
	{
		switch ($feature)
		{
			case 'setConfigSrc':
				# NOTE I've just removed the reference. If quirky behavior appears, this is a good place to look.
				$this->config=$this->getReplacementConfig($value);
				$this->core->debug(2,"Faucet->control: Overrode config to \"$value\"");
				break;
			case 'setConfigSrcCopy':
				$this->core->debug(2,"Faucet->control: Overrode config to \"$value\"");
				$this->config=$this->getReplacementConfig($value);
				break;
			default:
				$this->core->debug(1, "Control feature $feature not found within ".__CLASS__.". It was called with \"$value\"");
				return false;
				break;
		}
	}
}

?>

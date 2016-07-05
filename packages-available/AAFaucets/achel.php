<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Adds the ability to put conditions into macros

/*
	There are essentially two variants with one alias and each having their not equivilent
		* ifResultExists
		* ifNotEmptyResult ifResult <-- Most of the time you'll want this one

*/

define('fromFaucet', 0);
define('toFaucet', 1);
define('fromChannel', 2);
define('toChannel', 3);
define('context', 4);
define('lineSeparator', "\n");
define('inputLineSeparator', "\n");

define('procOut', 0);
define('procIn', 1);
define('procError', 2);

class FaucetEnvironment
{
	# The environment for containing everything.
	private static $environment=null;
	
	# For tracking nested faucets
	public $currentFaucet=null;
	public $rootFaucet=null;
	public $core=null;
	
	function __construct()
	{
		$this->core=core::assert();
	}
	
	public static function assert()
	{
		if (!isset(self::$environment)) self::$environment=new FaucetEnvironment();
		return self::$environment;
	}
	
	function createEvironment($createEnvironment=true)
	{
		if ($createEnvironment)
		{
			$this->rootFaucet=new MetaFaucet('root');
			$this->currentFaucet=&$this->rootFaucet;
			$this->core->setRef('Achel','currentFaucet', $this->currentFaucet);
		}
		else
		{
			$this->currentFaucet=&$this->core->get('Achel','currentFaucet');
		}
	}
}


class Faucets extends Module
{
	
	function __construct($className=__CLASS__)
	{
		parent::__construct($className);
		
		$core=core::assert();
		$this->setCore($core);
		
		$this->environment=&FaucetEnvironment::assert();
		
		$amIFaucets=($className==__CLASS__);
		$this->environment->createEvironment($amIFaucets);
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('getFaucets'), 'getFaucets', "List all faucets (including objects).", array());
				$this->core->registerFeature($this, array('getFaucetsDetails', 'ls'), 'getFaucetsDetails', "List all faucets with stats.", array());
				
				
				$this->core->registerFeature($this, array('createThroughFaucet'), 'createThroughFaucet', "This faucet is a 1:1 faucet for any channel. Effectively it is useful for connecting stuff in a standardized abstract way. --createThroughFaucet=faucetName", array());
				$this->core->registerFeature($this, array('create2WayThroughFaucet'), 'create2WayThroughFaucet', "This faucet is a 1:1+1:1 pipe. Effectively it is useful for connecting stuff in a standardized abstract way. The difference from --createThroughFaucet is that a single instance of this Faucet can carry both input and output on the default channel, therefore allowing a single instance to be the complete interface for a complicated set of faucets so they can be interfaced in a simple way. However no other channels can be used. The \"default\" channel is used on the external side, and the \"inside\" channel is used on the internal side. --create2WayThroughFaucet=faucetName", array());
				
				$this->registerFaucetCatalogEntry('NullFaucet', 'A black hole for data.', 'achel', 'createNullFaucet,%faucetName%');
				$this->core->registerFeature($this, array('createNullFaucet'), 'createNullFaucet', "Many Faucet actions will only get performed when there is at least one pipe connected to the exit of the faucet. But sometimes we want to create a fully funtioning faucet without connecting it to anything. The NullFaucet gives you something to connect it to without causing data to build up anywhere. --createNullFaucet=faucetName", array());
				
				$this->core->registerFeature($this, array('createRawMetaFaucet', 'mkdir'), 'createRawMetaFaucet', "Create a MetaFaucet that can contain other Faucets. You can use --cf to get inside it and create other faucets etc. --createRawMetaFaucet=faucetName. Normally you will want --createMetaFaucet instead, which allows you to do the nested programming style, which is generally much easier to read.", array());
				
				
				$this->core->registerFeature($this, array('changeFaucet', 'cf', 'cd'), 'changeFaucet', "Used pretty much like cd on the linux command line. Note that you can only --changeFaucet into meta faucets. --changeFaucet=pathToFaucet e.g. --changeFaucet=/faucetName/anotherFaucetName/andAnotherFaucetName . IMPORTANT: When using this within macros, please use changeFaucet or cf. Not cd as that is slang to make the tui more intuitive.", array('metaFaucet'));
				$this->core->registerFeature($this, array('currentFaucet', 'pwd'), 'currentFaucet', "Display the current faucet name to debug. Note that this does not currently show the full path. --currentFaucet", array('metaFaucet'));
				$this->core->registerFeature($this, array('setFaucetConfigItem'), 'setFaucetConfigItem', "Set faucet specific config. --setFaucetConfigItem=faucetName,configName,[configSubcategory],value", array('metaFaucet'));
				$this->core->registerFeature($this, array('getFaucetConfigItem'), 'getFaucetConfigItem', "Get faucet specific config. --getFaucetConfigItem=faucetName,[configName,[configSubcategory]]", array('metaFaucet'));
				$this->core->registerFeature($this, array('addFaucetConfigItemEntry'), 'addFaucetConfigItemEntry', "If the faucet config item is an array, you can add an entry to it. --addFaucetConfigItemEntry=faucetName,configName,[configSubcategory],entryName,value", array('metaFaucet'));
				$this->core->registerFeature($this, array('removeFaucetConfigItemEntry'), 'removeFaucetConfigItemEntry', "If the faucet config item is an array, you can remove an entry from it. --removeFaucetConfigItemEntry=faucetName,configName,[configSubcategory],entryName,value", array('metaFaucet'));
				$this->core->registerFeature($this, array('bindFaucetConfigItem'), 'bindFaucetConfigItem', "Within the current metaFaucet, make a reference to one of the faucets it contains. This allows you to manipulate the config of sub-faucets directly from the metaFaucet that contains them. --bindFaucetConfigItem=faucetName,configName,[configSubcategory],metaFaucetConfigName,[metaFaucetConfigSubcategory]", array('metaFaucet'));
				$this->core->registerFeature($this, array('generateFaucetCatalogEntry'), 'generateFaucetCatalogEntry', "Generate the details needed to recreate a named metaFaucet. --generateFaucetCatalogEntry[=name] . If name is omitted the current meta faucet is assumed.", array('metaFaucet'));
				
				
				$this->core->registerFeature($this, array('deleteFaucet'), 'deleteFaucet', "Delete a faucet to/from a terminal.", array());
				
				$this->core->registerFeature($this, array('setFaucetAs'), 'setFaucetAs', "Use core->setRef to set a named faucet. The purpose I'm currently intending to use this for is setting sending output from mass macros through achel pipes. You could do that in this form --setFaucetAs=faucetName,Category,valueName like this --setFaucetAs=terminal,General,echoObject", array());
				$this->core->registerFeature($this, array('getFaucetAliases'), 'getFaucetAliases', "List all faucet aliases.", array());
				$this->core->registerFeature($this, array('createFaucetAlias'), 'createFaucetAlias', "Alias a faucet to give it a standard name. --aliasFaucet=aliasName,originalName", array());
				$this->core->registerFeature($this, array('replaceFaucetAlias'), 'replaceFaucetAlias', "Alias a faucet (whether it exists already or not) to give it a standard name. --replaceFaucetAlias=aliasName,originalName", array());
				$this->core->registerFeature($this, array('deleteFaucetAlias'), 'deleteFaucetAlias', "Delete a faucet alias. --deleteFaucetAlias=aliasName", array());
				
				$this->core->registerFeature($this, array('getPipes'), 'getPipes', "List all pipes.", array());
				$this->core->registerFeature($this, array('createPipe'), 'createPipe', "Create a one way pipe between two faucets. --createPipe=fromFaucet,toFaucet[,fromChannel,toChannel,parm1,parm2] . parm1 and 2 are specific to the destination faucet and help it know what to do with the data in the context of this pipe. fromChannel and toChannel are used by some faucets that need to send and/or recieve input from multiple sources.", array());
				$this->core->registerFeature($this, array('deletePipe'), 'deletePipe', "Delete a one way pipe between two faucets. --deletePipe=fromFaucet,toFaucet", array());
				$this->core->registerFeature($this, array('tracePipes'), 'tracePipes', "List out all the possible combinations for getting from one faucet,channel to another. --tracePipes=fromFaucet,toFaucet[,fromChannel[,toChannel[,depthLimit]]]", array());
				
				$this->core->registerFeature($this, array('deliver'), 'deliver', "Deliver text directly to a faucet. --deliver=faucetName,channel,textToSend", array());
				$this->core->registerFeature($this, array('deliverAll'), 'deliverAll', "Invoke every pipe to check for contents from it's fromFaucet and deliver any contents to it's toFaucet. This will become very inefficient as the number of faucets grows. --deliverAll[=maximumRevolutions] . maximumRevolutions defaults to 10.", array());
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'getFaucets':
				return $this->environment->currentFaucet->getFaucets();
				break;
			case 'getFaucetsDetails':
				return $this->environment->currentFaucet->getFaucetsDetails();
				break;
			case 'createThroughFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$this->environment->currentFaucet->createFaucet($parms[0], 'through', new ThroughFaucet());
				break;
			case 'create2WayThroughFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$this->environment->currentFaucet->createFaucet($parms[0], 'through', new ThroughFaucet(true));
				break;
			case 'createNullFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$this->environment->currentFaucet->createFaucet($parms[0], 'null', new NullFaucet());
				break;
			case 'createRawMetaFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$metaFaucet=new MetaFaucet($parms[0]);
				$metaFaucet->setStructure($this->environment->rootFaucet, $this->environment->currentFaucet);
				$this->environment->currentFaucet->createFaucet($parms[0], 'meta', $metaFaucet);
				break;
			
			
			case 'changeFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$this->changeFaucet($parms[0]);
				$this->core->setRef('Achel','currentFaucet', $this->environment->currentFaucet);
				break;
			case 'currentFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 0);
				$this->getCurrentFaucet($parms);
				break;
			case 'setFaucetConfigItem':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 3, 4, true);
				if ($faucet=&$this->environment->currentFaucet->getFaucet($parms[0], $event))
				{
					// --setFaucetConfigItem=faucetName,configName,[configSubcategory],value
					$faucet['object']->setConfigItem($parms[1], $parms[2], $parms[3]);
				}
				break;
			case 'addFaucetConfigItemEntry':
				// addConfigItemEntry($settingName, $subcategory, $entryName, $entryValue)
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 5, false);
				if ($faucet=&$this->environment->currentFaucet->getFaucet($parms[0], $event))
				{
					$faucet['object']->addConfigItemEntry($parms[1], $parms[2], $parms[3], $parms[4]);
				}
				break;
			case 'removeFaucetConfigItemEntry':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 4, false);
				if ($faucet=&$this->getFaucet($parms[0], $event))
				{
					$faucet['object']->removeConfigItemEntry($parms[1], $parms[2], $parms[3]);
				}
				break;
			case 'getFaucetConfigItem':
				// --getFaucetConfigItem=faucetName,[configName,[configSubcategory]]
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 3, 1);
				if ($faucet=&$this->environment->currentFaucet->getFaucet($parms[0], $event))
				{
					if ($parms[1])
					{
						return $faucet['object']->getConfigItem($parms[1], $parms[2]);
					}
					else
					{
						return $faucet['object']->getConfig();
					}
				}
				break;
			case 'bindFaucetConfigItem':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 5, 4, false);
				// --bindFaucetConfigItem=faucetName,configName,[configSubcategory],metaFaucetConfigName,[metaFaucetConfigSubcategory]
				$this->environment->currentFaucet->bindConfigItem($parms[0], $parms[1], $parms[2], $parms[3], $parms[4]);
				break;
			case 'generateFaucetCatalogEntry':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1);
				return $this->environment->currentFaucet->generateFaucetCatalogEntry($parms[0]);
				break;
			
			
			
			case 'deleteFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1);
				$this->environment->currentFaucet->deleteFaucet($parms[0]);
				break;
			
			
			case 'setFaucetAs':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2);
				$this->setFaucetAs($parms[0], $parms[1], $parms[2]);
				break;
			case 'getFaucetAliases':
				# TODO migrate this
				return $this->aliases;
				break;
			case 'createFaucetAlias':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2);
				$this->environment->currentFaucet->createAlias($parms[0], $parms[1]);
				break;
			case 'replaceFaucetAlias':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2);
				$this->environment->currentFaucet->createAlias($parms[0], $parms[1], true);
				break;
			case 'deleteFaucetAlias':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1);
				$this->environment->currentFaucet->deleteAlias($parms[0]);
				break;
			case 'getPipes':
				# TODO migrate this
				return $this->environment->currentFaucet->getPipes();
				break;
			case 'createPipe':
				$this->environment->currentFaucet->createPipe($this->core->get('Global', $event));
				break;
			case 'deletePipe':
				$this->environment->currentFaucet->deletePipe($this->core->get('Global', $event));
				break;
			case 'tracePipes':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 5, 2);
				return $this->environment->currentFaucet->tracePipes($parms[0], $parms[2], $parms[1], $parms[3], $parms[4]);
				break;
				
			case 'deliver':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2, true);
				$this->environment->currentFaucet->deliver($parms[0], $parms[1], $parms[2]);
				break;
			case 'deliverAll':
				return $this->environment->rootFaucet->deliverAll($this->core->get('Global', $event));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	
	function setFaucetAs($faucetName, $category, $valueName)
	{
		if ($faucet=&$this->environment->currentFaucet->getFaucet($faucetName))
		{
			$this->core->debug(2, "setFaucetAs: Putting Faucet \"$faucetName\" into $category,$valueName");
			$this->core->setRef($category, $valueName, $faucet['object']);
		}
		else
		{
			$this->core->debug(2, "setFaucetAs: Could not find a faucet named \"$faucetName\".");
		}
	}
	
	function getCurrentFaucet($parameters)
	{
		# TODO Refactor this to 
		# * take arguments,and set the result in the specified location in ncessary.
		# * find the whole path.
		
		if (isset($parameters[1]))
		{
			$this->core->set($parameters[0], $parameters[1], $this->environment->currentFaucet->getFullPath());
		}
		else
		{
			$this->core->debug(0, __CLASS__.'->'.__FUNCTION__.': '.$this->environment->currentFaucet->getFullPath());
		}
	}
	
	function changeFaucet($faucetPath)
	{
		$debugLevel=1;
		
		if ($faucetPath=='/') $faucetPath=''; // A simple way to make sure that / gets processed only once.
		
		$pathParts=explode('/', $faucetPath);
		
		foreach ($pathParts as $partKey=>$part)
		{
			$origin=$this->environment->currentFaucet->getName();
			
			switch ($part)
			{
				case '':
					/*
						This represents root (/) but matches '' as / is our delimiter. Therefore if the path begins with a / or if there is a double / (ie //), then it will go to root from that point on.
						
						This is intended behavior. If later it is desired to ignore double slashes, then this test will help ($partKey==0)
					*/
					$this->environment->currentFaucet=&$this->environment->rootFaucet;
					break;
				case 'root':
					$this->environment->currentFaucet=&$this->environment->rootFaucet;
					break;
				case '.':
					// We're already here. We don't need to do anything.
					break;
				case '..':
					// Note that .. is allowed part-way through a sequence.
					if ($parentFaucet=&$this->environment->currentFaucet->getParentFaucet())
					{
						$this->environment->currentFaucet=&$this->environment->currentFaucet->getParentFaucet();
					}
					else
					{
						$this->core->debug(1, "Can not .. beyond root.");
					}
					break;
				default:
					$totalFaucet=$this->environment->currentFaucet->getFaucet($part);
					if ($totalFaucet)
					{
						$this->core->debug($debugLevel, __CLASS__.'->'.__FUNCTION__.": \"$faucetPath\" => $partKey=>\"$part\" Entered \"$part\"");
						$this->environment->currentFaucet=&$totalFaucet['object'];
						unset($totalFaucet);
					}
					else
					{
						$this->core->debug($debugLevel, __CLASS__.'->'.__FUNCTION__.": \"$faucetPath\" => $partKey=>\"$part\" Could not find faucet named. \"$part\"");
					}
					break;
			}
			
			$destination=$this->environment->currentFaucet->getName();
			$this->core->debug($debugLevel, __CLASS__.'->'.__FUNCTION__.": $origin->$destination ($part)");
		}
	}
	
	function registerFaucetCatalogEntry($faucetName, $description, $package, $source)
	{
		if ($imposter=$this->core->get('FaucetCatalog', $faucetName))
		{
			$this->core->debug(1, __CLASS__.'->'.__FUNCTION__.": $faucetName already exists with description \"{$imposter['description']}\", source is \"$source\" and it's from package \"{$imposter['package']}\". This is certainly a bug between the package \"$package\" and \"{$imposter['package']}\"");
			return false;
		}
		
		$entry=$this->environment->currentFaucet->getFaucetCatalogTemplate($faucetName, $source, 'macro');
		$entry['config']['description']=$description;
		$entry['config']['package']=$package;
		
		$this->core->set('FaucetCatalog', $faucetName, $entry);
	}
}



class Faucet
{
	protected $core=null;
	protected $inputBuffer='';
	protected $objectType='';
	private $outChannels=null;
	protected $config=null;
	protected $configRegistry=null;
	protected $faucetInstanceName='unknown';
	
	function __construct($objectType)
	{
		$this->objectType=$objectType;
		$this->core=core::assert();
		$this->outChannels=array();
		$this->config=array();
		$this->configRegistry=array();
	}
	
	function getObjectType()
	{
		return $this->objectType;
	}
	
	public function setInstanceName($instanceName)
	{
		$this->faucetInstanceName=$instanceName;
	}
	
	public function getInstanceName()
	{
		return $this->faucetInstanceName;
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
			$this->control($part[0], $part[1]);
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

class StreamBasedFaucet extends Faucet
{
	function __construct($objectType)
	{
		parent::__construct($objectType);
	}
	
	function deconstruct()
	{
		if ($this->resource) fclose($this->resource);
	}
}








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

class MetaFaucet extends ThroughBasedFaucet
{
	# Faucet structure
	private $rootFaucet=null;
	private $parentFaucet=null;
	private $myName='';
	
	# Stuff that connects to each other
	private $pipes=null;
	private $faucets=null;
	private $aliases=null;
	
	private $bindings=null;
	
	private $debugID=0;
	
	function __construct($name)
	{
		$this->debugID=rand(1,100000);
		
		parent::__construct(__CLASS__);
		
		$this->myName=$name;
		$this->bindings=array();
		
		$this->registerConfigItem('description', '', 'Describe what the metaFaucet does.', 'string');
		$this->setConfigItem('description', '', 'unknown');
		
		$this->registerConfigItem('package', '', 'Which package does the metaFaucet live in.', 'string');
		$this->setConfigItem('package', '', 'unknown');
		
		$this->registerConfigItem('source', '', 'What generated the metaFaucet. filePath|macroName|userBuilt', 'string');
		$this->setConfigItem('source', '', 'userBuilt');
		
		$this->registerConfigItem('sourceType', '', 'What type of source is it? file|macro|generated.', 'string');
		$this->setConfigItem('sourceType', '', 'generated');
	}
	
	function deconstruct()
	{
	}
	
	function setStructure(&$rootFaucet, &$parentFaucet)
	{
		$this->environment->rootFaucet=&$rootFaucet;
		$this->parentFaucet=&$parentFaucet;
	}
	
	function &getRootFaucet()
	{
		return $this->parentFaucet;
	}
	
	function &getParentFaucet()
	{
		return $this->parentFaucet;
	}
	
	function preGet()
	{
		return $this->deliverAll();
		
		/*foreach ($this->input as $channel=>$data)
		{
			if ($data)
			{
				$this->clearInput($channel);
			}
		}*/
	}
	
	
	
	
	function getName()
	{
		return $this->myName;
	}
	
	function getFullPath()
	{
		if ($this->myName == 'root')
		{
			return '';
		}
		else
		{
			return $this->parentFaucet->getFullPath().'/'.$this->myName;
		}
	}
	
	function createFaucet($faucetName, $type, &$faucetObject)
	{
		if (!$faucetName)
		{
			$this->core->debug(1, "createFaucet: No faucetName given. $type faucet will not be created.");
			return false;
		}
		
		$faucetObject->setInstanceName($faucetName);
		
		$this->core->debug(2, "createFaucet ({$this->debugID}): Created faucet $faucetName.");
		$this->faucets[$faucetName]=array(
			'name'=>$faucetName,
			'type'=>"$type",
			'instance'=>'# TODO generate this',
			'object'=>&$faucetObject);
	}
	
	function deleteFaucet($faucetName)
	{
		if (isset($this->faucets[$faucetName]))
		{
			if (is_array($this->faucets[$faucetName]))
			{
				$this->faucets[$faucetName]['object']->deconstruct();
				unset($this->faucets[$faucetName]);
			}
			else $this->core->debug(2, "deleteFaucet: Faucet $faucetName was not an array.");
			
		}
		else
		{
			$this->core->debug(2, "deleteFaucet: Faucet $faucetName does not exist.");
		}
	}
	
	function &getFaucet($faucetName, $event='unknown')
	{
		if (!$faucetName) return $this;
		
		$actualFaucetName=$this->findRealFaucetName($faucetName);
		if ($actualFaucetName and isset($this->faucets[$actualFaucetName])) return $this->faucets[$actualFaucetName];
		else 
		{
			$this->core->debug(2, "getFaucet ($event): Faucet $faucetName does not exist.");
			$result=false;
			return $result;
		}
	}
	
	function getFaucets()
	{
		return $this->faucets;
	}
	
	function getFaucetsDetails()
	{
		$faucets=array();
		
		foreach ($this->faucets as $faucet)
		{
			$outFaucet=array(
				'name'=>$faucet['name'],
				'type'=>$faucet['type'],
				'in'=>array(),
				'out'=>array()
			);
			
			if (method_exists($faucet['object'], 'getOutQues'))
			{
				$outFaucet['out']=$this->quesToStats($faucet['object']->getOutQues());
			}
			
			if (method_exists($faucet['object'], 'getInQues'))
			{
				$outFaucet['in']=$this->quesToStats($faucet['object']->getInQues());
			}
			
			$faucets[]=$outFaucet;
		}
		
		return $faucets;
	}
	
	function quesToStats($ques)
	{
		$stats=array();
		
		foreach ($ques as $key=>$que)
		{
			$stats[$key]=count($que);
		}
		
		return $stats;
	}
	
	function findRealFaucetName($faucetName)
	{
		if (isset($this->faucets[$faucetName])) return $faucetName;
		elseif ($faucetName=='.') return $faucetName;
		elseif (isset($this->aliases[$faucetName])) return $this->faucets[$this->aliases[$faucetName]]['name'];
		else
		{
			$this->core->debug(2, "findRealFaucetName ({$this->debugID}): Could not find a faucet named \"$faucetName\".");
			return false;
		}
	}
	
	
	
	function createAlias($aliasName, $originalName, $replace=false)
	{
		if (!isset($this->faucets[$originalName]))
		{
			$this->core->debug(1, "createAlias: Faucet $originalName does not exist.");
			# TODO re-evaluate whether we should really abort now. 
			return false;
		}
		
		if (!isset($this->aliases[$aliasName]))
		{
			$this->core->debug(2, "createAlias: Creating alias $aliasName pointing to $originalName");
			$this->aliases[$aliasName]=$originalName;
		}
		elseif ($replace)
		{
			$this->core->debug(2, "createAlias: Replacing alias $aliasName pointing to {$this->aliases[$aliasName]} with $originalName");
			$this->aliases[$aliasName]=$originalName;
		}
		else
		{
			$this->core->debug(2, "createAlias: Alias $aliasName is already pointing to {$this->aliases[$aliasName]}");
		}
	}
	
	function deleteAlias($aliasName)
	{
		if (!isset($this->aliases[$aliasName]))
		{
			$this->core->debug(2, "deleteAlias: Alias $aliasName does not exist.");
		}
		else
		{
			$this->core->debug(1, "deleteAlias: Deleting alias $aliasName.");
			unset ($this->aliases[$this->aliasName]);
		}
	}
	
	function createPipe($key)
	{
		$parms=$this->core->interpretParms($key, 6, 2);
		$newRecord=array(
			'fromFaucet'=>$this->findRealFaucetName($parms[fromFaucet]),
			'fromChannel'=>$parms[fromChannel],
			'toFaucet'=>$this->findRealFaucetName($parms[toFaucet]),
			'toChannel'=>$parms[toChannel],
			'context'=>$parms[context]);
		
		if (!isset($this->faucets[$newRecord['fromFaucet']]) and $newRecord['fromFaucet']!='.')
		{
			$this->core->debug(1, "createPipe: fromFaucet {$newRecord['fromFaucet']}(aliased from {$parms[fromFaucet]}) does not exist in $key.");
			return false;
		}
		
		if (!isset($this->faucets[$newRecord['toFaucet']]) and $newRecord['toFaucet']!='.')
		{
			$this->core->debug(1, "createPipe: toFaucet {$newRecord['toFaucet']}(aliased from {$parms[toFaucet]}) does not exist in $key.");
			return false;
		}
		
		if ($newRecord['fromChannel']=='') $newRecord['fromChannel']='default';
		if ($newRecord['toChannel']=='') $newRecord['toChannel']='default';
		
		if (!isset($this->pipes[$newRecord['fromFaucet']])) $this->pipes[$parms[fromFaucet]]=array();
		if (!isset($this->pipes[$newRecord['fromFaucet']][$newRecord['fromChannel']])) $this->pipes[$newRecord['fromFaucet']][$newRecord['fromChannel']]=array();

		if (!isset($this->pipes[$newRecord['fromFaucet']][$newRecord['fromChannel']][$key]))
		{
			# TODO This looks a lot like 2 lines just above. Do these need to be here?
			if ($newRecord['fromChannel']=='') $newRecord['fromChannel']='default';
			if ($newRecord['toChannel']=='') $newRecord['toChannel']='default';
			
			$this->pipes[$newRecord['fromFaucet']][$newRecord['fromChannel']][$key]=$newRecord;
			
			
			$this->core->debug(2, "createPipe: Created pipe from {$newRecord['fromFaucet']} to {$newRecord['toFaucet']} using key $key.");
		}
		else
		{
			$this->core->debug(2, "createPipe: Pipe $key already exists.");
		}
	}
	
	function deletePipe($key)
	{
		$parms=$this->core->interpretParms($key, 5, 2);
		$oldRecord=array(
			'fromFaucet'=>$this->findRealFaucetName($parms[fromFaucet]),
			'fromChannel'=>$parms[fromChannel],
			'toFaucet'=>$this->findRealFaucetName($parms[toFaucet]),
			'toChannel'=>$parms[toChannel],
			'context'=>$parms[context]);
		
		if (!$oldRecord['fromChannel']) $oldRecord['fromChannel']='default';
		if (!$oldRecord['toChannel']) $oldRecord['toChannel']='default';
		
		if (!isset($this->pipes[$oldRecord['fromFaucet']]))
		{
			$this->core->debug(1, "deletePipe: There are no pipes from {$oldRecord['fromFaucet']}");
			return false;
		}
		
		# TODO This needs to be updated for the new structure
		
		if (!isset($this->pipes[$oldRecord['fromFaucet']][$oldRecord['fromChannel']][$key]))
		{
			$this->core->debug(1, "deletePipe: Pipe $key does not exist.");
		}
		else
		{
			$this->core->debug(2, "deletePipe: Deleting Pipe $key.");
			unset ($this->pipes[$oldRecord['fromFaucet']][$oldRecord['fromChannel']][$key]);
			if (!count($this->pipes[$oldRecord['fromFaucet']][$oldRecord['fromChannel']]))
			{
				if (!count($this->pipes[$oldRecord['fromFaucet']]))
				{
					$this->core->debug(2, "deletePipe: No pipes remained for {$parms[fromFaucet]}. Deleting section.");
					unset ($this->pipes[$oldRecord['fromFaucet']]);
				}
// 				unset ($this->pipes[$oldRecord['fromFaucet']][$oldRecord['fromChannel']]);
			}
		}
	}
	
	function getPipes()
	{
		return $this->pipes;
	}
	
	function deliver($dstFaucet, $dstChannel, $input)
	{
		if ($dstFaucet!='.')
		{
			$actuallyToFaucet=$this->findRealFaucetName($dstFaucet);
			if (!is_array($input)) $input=array($input);
		}
		else $actuallyToFaucet=null;
		
		
		$this->core->debug(4, "deliver: Delivering ".gettype($input)." to $actuallyToFaucet,$dstChannel.");
		
		if ($dstFaucet=='.') $this->outFill(array($input), $dstChannel);
		elseif ($actuallyToFaucet)
		{
			$this->faucets[$actuallyToFaucet]['object']->put($input, $dstChannel);
		}
	}
	
	function deliverAll($maximumRevolutions=3)
	{
		if (!$maximumRevolutions) $maximumRevolutions=10;
		# TODO consider refactoring to be non-blocking so that timers will also work
		$this->core->debug(4, "deliverAll: About to deliver anything that needs to be delivered..");
		$returnValue=false;
		$resultValue=true;
		
		if (!$this->pipes)
		{
			$this->core->debug(1, "deliverAll: No pipes???");
			return false;
		}
		
		for ($revolution=0; $revolution<$maximumRevolutions; $revolution++)
		{
			$resultValue=$this->processPipes();
			
			if ($resultValue)
			{
				$returnValue=true;
			}
			else
			{
				return $this->core->getResultSet();
			}
		}
		
		return $this->core->getResultSet();
	}

	function last($series)
	{
		if (is_array($series))
		{
			if (count($series) < 1)
			{
				return false;
			}
			else
			{
				$keys=array_keys($series);
				return $series[$keys[count($keys)-1]];
			}
		}
		else
		{
			return $series;
		}
	}
	
	private function bendInput($input, $preBentInput=false)
	{
		if ($preBentInput) return $preBentInput;
		
		$bentInput=array();
		foreach ($input as $key=>$value)
		{
			$bentInput[$key]=$this->last($value);
		}
		
		return array($bentInput);
	}
	
	private function processPipes()
	{
		$pipeDebugLevel=$this->core->get('General', 'pipeDebugLevel');
		if ($pipeDebugLevel=='')
		{
			$pipeDebugLevel=5;
			$this->core->set('General', 'pipeDebugLevel', $pipeDebugLevel);
		}
		
		
		$resultValue=true;
		foreach ($this->pipes as $fromFaucet=>$fromFaucetPipes)
		{
			if ($fromFaucet=='.')
			{
				$fromFaucetName='.';
				if (!count($this->input)) continue;  // Skip if there is no data.
			}
			elseif (!$fromFaucetName=$this->findRealFaucetName($fromFaucet))
			{
				$this->core->debug(2, "processPipes: Can not find $fromFaucet. Removing pipes for $fromFaucet.");
				unset($this->pipes[$fromFaucet]);
				continue; // Skip if the faucet doesn't exist
			}
			else
			{
				if (!$this->faucets[$fromFaucet]['object']->preGet()) continue; // Skip if the faucet has no new data
			}
			
			foreach ($fromFaucetPipes as $fromChannel=>$channelPipes)
			{
				$bentData=false;
				
				# Figure out as much of the input now as we can
				if ($fromFaucet=='.')
				{
					if ($fromChannel=='*')
					{
						if (count($this->input))
						{
							$input=$this->input;
							$this->clearInput();
						}
						else
						{
							$this->core->debug(4,__CLASS__.": No Data!?");
							$input=false;
						}
					}
					elseif ($fromChannel=='~*')
					{
						if (count($this->input))
						{
							$input=$this->bendInput($this->input, $bentData);
							$this->clearInput();
						}
						else
						{
							$input=false;
						}
					}
					else
					{
						if (isset($this->input[$fromChannel]))
						{
							$input=$this->input[$fromChannel];
							$this->clearInput($fromChannel);
							$this->core->debug(4, "Got input from $fromChannel");
						}
						else
						{
							$input=false;
						}
					}
				}
				else
				{
					# TODO figure out what this should be. Probably get needs to take an empty value for retrieving everything.
					if ($fromChannel=='*')
					{
						$input=$this->faucets[$fromFaucetName]['object']->getOutQues();
					}
					elseif ($fromChannel=='~*')
					{
						
						$input=$this->faucets[$fromFaucetName]['object']->getOutQues();
						if (count($input))
						{
							$input=$this->bendInput($input, $bentData);
						}
						else
						{
							$input=false;
						}
					}
					else
					{
						$input=$this->faucets[$fromFaucetName]['object']->get($fromChannel);
						
					}
				}
				
				
				if (count($input) and $input!==false)
				{
					$isVerboseEnough=$this->core->isVerboseEnough($pipeDebugLevel);
					
					$resultValue=true;
					foreach ($channelPipes as $key=>$pipe)
					{
						if (!$toFaucetName=$this->findRealFaucetName($pipe['toFaucet']))
						{
							$this->core->debug(2, "deliverAll: Can not find {$pipe['toFaucet']}. Removing pipe from $fromFaucetName to $toFaucetName.");
							$this->deletePipe($key);
							continue;
						}
						
						if ($isVerboseEnough)
						{
							$debugData=json_encode($input);
							$numberOfItems=count($input);
							$this->core->debug($pipeDebugLevel, "deliverAll: Delivering ".gettype($input)." $numberOfItems entries from $fromFaucetName,$fromChannel to $toFaucetName,{$pipe['toChannel']} using context {$pipe['context']} and key $key. Data=$debugData");
						}
						
						if ($toFaucetName=='.') $this->outFill($input, $pipe['toChannel']);
						else $this->faucets[$toFaucetName]['object']->put($input, $pipe['toChannel']);
					}
				}
			}
		}
		
		return $resultValue;
	}
	
	function tracePipes($fromFaucet, $fromChannel, $toFaucet, $toChannel, $depthLimit=10)
	{
		$output=array();
		
		$actuallyFrom=$this->findRealFaucetName($fromFaucet);
		$actuallyTo=$this->findRealFaucetName($toFaucet);
		
		if (!$toChannel) $toChannel='default';
		if (!$fromChannel) $fromChannel='default';
		if (!$depthLimit) $depthLimit=10;
		
		$this->core->debug(2, "tracePipes: Tracing pipes from $fromFaucet,$fromChannel to $toFaucet,$toChannel");
		
		$this->doTraceFaucets($output, array(), $actuallyFrom, $fromChannel, $actuallyTo, $toChannel, 'begin point', $depthLimit-1);
		
		return $output;
	}
	
	function doTraceFaucets(&$output, $progress, $fromFaucet, $fromChannel, $toFaucet, $toChannel, $key, $depthLimit)
	{
		// This is a recursive function intended to be used by traceFaucets
		
		$actuallyFrom=$this->findRealFaucetName($fromFaucet);
		$actuallyTo=$this->findRealFaucetName($toFaucet);
		
		if ($depthLimit < 1)
		{
			$this->core->debug(2, "doTraceFaucets: depthLimit($depthLimit) has fallen below 1. Trace will go no further.");
			return false;
		}
		else
		{
			$this->core->debug(2, "doTraceFaucets: depthLimit($depthLimit) is still not below 1. Trace will continue.");
		}
		
		if (!isset($this->pipes[$actuallyFrom])) //[$fromChannel]
		{
			$this->core->debug(2, "doTraceFaucets: No more pipes for this route ($actuallyFrom,$fromChannel to $actuallyTo,$toChannel). Ended at $actuallyFrom,$fromChannel of \"$key\"");
			return false;
		}
		
		foreach ($this->pipes[$actuallyFrom] as $fromChannel=>$channelPipes)
		{
			foreach ($this->pipes[$actuallyFrom][$fromChannel] as $newKey=>$pipe)
			{
				$this->core->debug(2, "doTraceFaucets: testing $newKey");
				if ($pipe['toFaucet']==$actuallyTo)// and $pipe['toChannel']==$toChannel)
				{
					$this->core->debug(2, "doTraceFaucets: found pipe $newKey");
					$progressContinued=$progress;
					$progressContinued[]=$pipe;
					
					$output[]=$progressContinued;
				}
				else
				{
					$this->core->debug(2, "doTraceFaucets: Diving deeper into {$pipe['toFaucet']},{$pipe['toChannel']},$actuallyTo,$toChannel");
					$progressContinued=$progress;
					$progressContinued[]=$pipe;
					$this->doTraceFaucets($output, $progressContinued, $pipe['toFaucet'], $pipe['toChannel'], $actuallyTo, $toChannel, $newKey, $depthLimit-1);
				}
			}
		}
	}
	
	function bindConfigItem($faucetName, $settingName, $subcategory, $asSettingName, $asSubcategory)
	{
		if ($faucet=$this->getFaucet($faucetName))
		{
			$this->core->debug(4, __CLASS__.'->'.__FUNCTION__."($faucetName, $settingName, $subcategory, $asSettingName, $asSubcategory)");
			# TODO test for failure (get)
			if ($configDetails=$faucet['object']->getRegisteredConfigItem($settingName, $subcategory))
			{
				# TODO Store enough that information that this can be derived again by generateFaucetCatalogEntry()
				if (!$subcategory) $asSubcategory='default';
				$this->bindings["$asSettingName-$asSubcategory"]=array(
					'faucetName'=>$faucetName,
					'settingName'=>$settingName,
					'subcategory'=>$subcategory,
					'asSettingName'=>$asSettingName,
					'asSubcategory'=>$asSubcategory,
					'description'=>$configDetails['description'],
					'type'=>$configDetails['type']);
				
				$this->registerConfigItem($settingName, $subcategory, $configDetails['description'], $configDetails['type']);
				$this->setConfigItemReference($settingName, $subcategory, $faucet['object']->getConfigItemByReferece($settingName, $subcategory));
			}
			else
			{
				$this->core->debug(1, __CLASS__.'->'.__FUNCTION__.": Could not find config item $settingName,$subcategory in $faucetName.");
			}
		}
		else
		{
			$this->core->debug(1, __CLASS__.'->'.__FUNCTION__.": faucet $faucetName does not exist.");
		}
	}
	
	/*
	# TODO Write this
	function unBindConfigItem($asSettingName, $asSubcategory)
	{
	}
	*/
	
	
	function getFaucetCatalogTemplate($faucetName, $source, $type)
	{
		$entry=array(
			'faucetName'=>$faucetName,
			'source'=>$source,
			'sourceType'=>$type,
			'faucets'=>array(),
			'pipes'=>array(),
			'configBindings'=>array(),
			'config'=>array(),
		);
		
		return $entry;
	}
	
	function getUniqueConfig($config, $bindings)
	{
		$output=array();
		foreach ($config as $categoryKey=>$category)
		{
			if (!is_array($category)) continue;
			
			foreach ($category as $subcategoryKey=>$subcategory)
			{
				if (!isset($bindings["$categoryKey-$subcategoryKey"]))
				{
					if (!isset($output[$categoryKey])) $output[$categoryKey]=array();
					if (!isset($output[$categoryKey][$subcategoryKey])) $output[$categoryKey][$subcategoryKey]=array();
					$output[$categoryKey][$subcategoryKey]=$subcategory;
				}
			}
		}
		
		return $output;
	}
	
	function generateFaucetCatalogEntry($name)
	{
		if (!$name) $obj=&$this;
		elseif ($obj=&$this->getFaucet($name));
		else
		{
			$this->debug(1, __CLASS__.'->'.__FUNCTION__.": Did not recieve a faucet named $name within metaFaucet \"".$this->myName."\"");
			return false;
		}
		
		$name=$obj->myName;
		$package=$obj->getConfigItem('package', '');
		$description=$obj->getConfigItem('description', '');
		
		$entry=$this->getFaucetCatalogTemplate($name, 'unknown', 'generated');
		
		$entry['faucets']=$obj->getFaucets();
		$entry['pipes']=$obj->getPipes();
		$entry['configBindings']=$this->bindings;
		#$entry['config']=$this->getUniqueConfig($this->getConfig(), $entry['configBindings']);
		$entry['config']=$this->getConfig();
		
		foreach ($entry['faucets'] as &$faucet)
		{
			$faucet['config']=$faucet['object']->getConfig();
		}
		
		/*
			#'faucetName'=>$faucetName,
			#'pacakge'=>$package,
			#'description'=>$description,
			#'source'=>$source,
			#'sourceType'=>$type,
			#'faucets'=>array(),
			#'pipes'=>array(),
			#'configBindings'=>array(),
			#'config'=>array(),
		*/
		
		return $entry;
	}
}

/*
# TODO This isn't working correctly yet. The CTRL C seems to be continuing even after it has been handeled and the shell/ssh session is getting killed also.

declare(ticks = 1);

pcntl_signal(SIGINT, "captureBreak");

function captureBreak()
{
	$core=core::assert();
	$core->callFeature('break');
}
*/

$core=core::assert();
$core->registerModule(new Faucets());
 
?>

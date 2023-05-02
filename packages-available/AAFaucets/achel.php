<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
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

include 'lib/base/FaucetEnvironment.php';
include 'lib/base/Faucet.php';
include 'lib/base/ThroughBasedFaucet.php';
include 'lib/base/StreamBasedFaucet.php';
include 'lib/base/ThroughFaucet.php';
include 'lib/base/NullFaucet.php';
include 'lib/base/MetaFaucet.php';

class Faucets extends Module
{

	function __construct($className=__CLASS__)
	{
		parent::__construct($className);

		$core=core::assert();
		$this->setCore($core);

		$this->environment=FaucetEnvironment::assert();

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
				$this->core->registerFeature($this, array('deliverUnitTests'), 'deliverUnitTests', "Deliver until all unit tests have returned something. --deliverUnitTests[=timeOutSeconds].", array());
				$this->core->registerFeature($this, array('preGet'), 'preGet', "Invoke preGet() on a the specified faucet. This is strictly for unit testing to make sure that something specifically gets polled before something is manually sent. --preGet=faucetName .", array());
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
				$throughFaucet=new ThroughFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'through', $throughFaucet);
				break;
			case 'create2WayThroughFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$this->environment->currentFaucet->createFaucet($parms[0], 'through', new ThroughFaucet(true));
				break;
			case 'createNullFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$nullFaucet=new NullFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'null', $nullFaucet);
				break;
			case 'createRawMetaFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$metaFaucet=new MetaFaucet($parms[0]);
				$metaFaucet->setStructure($this->environment->rootFaucet, $this->environment->currentFaucet);
				$this->environment->currentFaucet->createFaucet($parms[0], 'meta', $metaFaucet);
				break;


			case 'changeFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 0);
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
			case 'deliverUnitTests':
				return $this->environment->rootFaucet->deliverUnitTests($this->core->get('Global', $event));
				break;
			case 'preGet':
				return $this->environment->currentFaucet->doPreGet($this->core->get('Global', $event));
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

		if (isset($parameters[1]) && $parameters[1])
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
		$debugLevel=2;

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
						$this->core->debug($debugLevel+1, __CLASS__.'->'.__FUNCTION__.": \"$faucetPath\" => $partKey=>\"$part\" Entered \"$part\"");
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
$achelF=new Faucets();
$core->registerModule($achelF);

?>

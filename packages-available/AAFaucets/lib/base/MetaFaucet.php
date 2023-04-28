<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# The container for faucets. Able to be nested. Think of it like a directory in a filesystem.

class MetaFaucet extends ThroughBasedFaucet
{
	# Faucet structure
	private $parentFaucet=null;
	private $myName='';
	private $fullPath='';

	# Stuff that connects to each other
	private $pipes=null;
	private $faucets=null;
	private $aliases=null;

	private $bindings=null;

	private $debugID=0;

	private $environment=null;


	function __construct($name)
	{
		$this->debugID=rand(1, 100000);

		parent::__construct(__CLASS__);

		$this->environment=FaucetEnvironment::assert();

		$this->myName=$name;
		$this->bindings=array();

		$this->faucets=array();

		$this->registerConfigItem('description', '', 'Describe what the metaFaucet does.', 'string');
		$this->setConfigItem('description', '', 'unknown');

		$this->registerConfigItem('package', '', 'Which package does the metaFaucet live in.', 'string');
		$this->setConfigItem('package', '', 'unknown');

		$this->registerConfigItem('source', '', 'What generated the metaFaucet. filePath|macroName|userBuilt', 'string');
		$this->setConfigItem('source', '', 'userBuilt');

		$this->registerConfigItem('sourceType', '', 'What type of source is it? file|macro|generated.', 'string');
		$this->setConfigItem('sourceType', '', 'generated');


		$this->fromColour=$this->core->get('Color', 'cyan');
		$this->toColour=$this->core->get('Color', 'brightPurple');
		$this->keyColour=$this->core->get('Color', 'brightBlack');
		$this->dataColour=$this->core->get('Color', 'brightBlue');
		$this->contentColour=$this->core->get('Color', 'green');
		$this->pathColour=$this->core->get('Color', 'yellow');

		$this->defaultColour=$this->core->get('Color', 'default');
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
		$this->updatePipeDebugLevel();

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
		if ($this->fullPath)
		{
			return $this->fullPath;
		}

		if ($this->myName == 'root')
		{
			$this->fullPath='';
		}
		else
		{
			$this->fullPath=$this->parentFaucet->getFullPath().'/'.$this->myName;
		}

		return $this->fullPath;
	}

	function createFaucet($faucetName, $type, &$faucetObject)
	{
		if (!$faucetName)
		{
			$this->core->debug(1, "createFaucet: No faucetName given. $type faucet will not be created.");
			return false;
		}

		$faucetObject->setInstanceName($faucetName);
		$faucetObject->setParent($this);

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
		$this->path=$this->pathColour.$this->getFullPath().$this->defaultColour;

		$parms=$this->core->interpretParms($key, 6, 2);
		$newRecord=array(
			'fromFaucet'=>$this->findRealFaucetName($parms[fromFaucet]),
			'fromChannel'=>$parms[fromChannel],
			'toFaucet'=>$this->findRealFaucetName($parms[toFaucet]),
			'toChannel'=>$parms[toChannel],
			'context'=>$parms[context]);

		$this->core->debug($this->pipeDebugLevel,"createPipe ".$this->path.':'.implode(", ", $newRecord));

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


		$this->core->debug($this->pipeDebugLevel, "deliver: Delivering ".gettype($input)."(".count($input).") to $actuallyToFaucet,$dstChannel.");

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

	function deliverUnitTests($timeoutSeconds)
	{
		$startTime=time();
		$currentTime=$startTime;
		$registeredTests=$this->core->getCategoryModule('FaucetTestRegistrations');
		$count=count($registeredTests);

		while ($count>0 and $currentTime-$startTime<$timeoutSeconds)
		{
			$resultValue=$this->processPipes();

			$registeredTests=$this->core->getCategoryModule('FaucetTestRegistrations');
			$count=count($registeredTests);
			$currentTime=time();
		}
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

	private function cycleAllSubFaucets()
	{
		if (!is_array($this->faucets)) return false;
		foreach ($this->faucets as $key=>&$faucet)
		{
			$this->core->debug($this->pipeDebugLevel, "cycleAllSubFaucets: Calling preGet() on $key.");
			$faucet['object']->preGet();
		}
	}

	private function processPipes()
	{
		$this->path=$this->pathColour.$this->getFullPath().$this->defaultColour;
		# $this->cycleAllSubFaucets();

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
				$this->core->debug($this->pipeDebugLevel, "processPipes: Can not find $fromFaucet. Removing pipes for $fromFaucet.");
				unset($this->pipes[$fromFaucet]);
				continue; // Skip if the faucet doesn't exist
			}
			else
			{
				# TODO move this to cycleAllSubFaucets.
				if (!$this->faucets[$fromFaucet]['object']->preGet()) continue; // Skip if the faucet has no new data
			}


			foreach ($fromFaucetPipes as $fromChannel=>$channelPipes)
			{
				# Figure out as much of the input now as we can
				if ($fromFaucet=='.')
				{ // From the parent meta faucet.
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
				{ // Non-meta faucets.
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

					$this->clearInput();
				}


				if (is_array($input) and $input!==false)
				{
					if (count($input))
					{
						$isVerboseEnough=$this->core->isVerboseEnough($this->pipeDebugLevel);

						$resultValue=true;
						foreach ($channelPipes as $key=>$pipe)
						{
							if (!$toFaucetName=$this->findRealFaucetName($pipe['toFaucet']))
							{
								$this->core->debug($this->pipeDebugLevel, "deliverAll: Can not find {$pipe['toFaucet']}. Removing pipe from $fromFaucetName to $toFaucetName.");
								$this->deletePipe($key);
								continue;
							}

							if ($isVerboseEnough)
							{
								$debugData=json_encode($input);
								$numberOfItems=count($input);
								$this->core->debug($this->pipeDebugLevel, "deliverAll {$this->path}: {$this->contentColour}".gettype($input)."*$numberOfItems {$this->fromColour}$fromFaucetName,$fromChannel {$this->keyColour}--> {$this->toColour}$toFaucetName,{$pipe['toChannel']} {$this->keyColour}context={$this->defaultColour}{$pipe['context']} {$this->keyColour}key={$this->defaultColour}$key. {$this->keyColour}Data={$this->dataColour}$debugData");
							}

							if ($toFaucetName=='.') $this->outFill($input, $pipe['toChannel']);
							else $this->faucets[$toFaucetName]['object']->put($input, $pipe['toChannel']);
						}
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

?>

<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

define('valueSeparator', ',');
define('storeValueBegin', '~!');
define('storeValueEnd', '!~');

define('resultVarBegin', '~%');
define('resultVarEnd', '%~');

define('categoryMarker', 'CategoryMark'); // When a category is marked as changed, it's recorded here.

define('resultVarsDefaultMaxRecusion', 50); // Prevent a stack overflow. We could go many times deeper than this, but if we get this far, sompething is very likely wrong.
define('resultVarsDefaultRecusionWarn', 25); // If we get to this many (arbitrary) levels of recusion, something is probably wrong
define('resultVarsDefaultWarnDebugLevel', 2);
define('resultVarsDefaultSevereDebugLevel', 1);

define('localScopeVarName', 'Local');
define('nestedPrivateVarsName', 'Me');
define('isolatedNestedPrivateVarsName', 'Isolated');

define('workAroundIfBug', true); // See doc/bugs/ifBug.md

define ('cleanupArgs', true); // Turn this off if you strike code that relies on arguments set for other macros. This is a bad way of programming and should serve as a severe warning that the code needs to be updated. This option will be removed soon.

# TODO Once Achel better handels boolean values, these can be actual boolean values.
define ('achelTrue', 'true');
define ('achelFalse', 'false');


/*
	Debug levels
		0 Default - Don't use this normally
		1 Important
		2 Warning
		3 Good to know
		4 
		5 Mother in law
*/

class core extends Module
{
	private $store;
	private $module;
	private static $singleton;
	private $verbosity=0;
	private $initMap=array();
	private $lastMessage=array('value'=>'', 'count'=>0);
	
	function __construct($verbosity=0)
	{
		$this->store=array();
		$this->module=array();
		
		$this->setRef('Core', 'modules', $this->module);
		
		$this->verbosity=$verbosity;
		$this->set('Verbosity', 'level', $verbosity);
		
		# This is for setting default default result set so that functions definitely get the data type they are expecting.
		#$defaultResultSet=array();
		#$this->setResultSet($defaultResultSet, 'start up');
		
		parent::__construct('Core');
		$this->set('Core', 'serial', intval(rand()));
		$this->set('Core', 'categoryMarker', categoryMarker);
		$this->registerModule($this);
	}
	
	public function dumpState()
	{
		return array('Store'=>$this->store, 'Module'=>$this->module);
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->registerFeature($this, array('registerTags'), 'registerTags', 'Register tags to a feature. --registerTags=featureName'.valueSeparator.'tag1['.valueSeparator.'tag2['.valueSeparator.'tag3'.valueSeparator.'...]]');
				$this->registerFeature($this, array('aliasFeature'), 'aliasFeature', 'Create an alias for a feature. Eg aliasing --help to -h and -h1 would be done by --aliasFeature=help'.valueSeparator.'h'.valueSeparator.'h1');
				$this->registerFeature($this, array('setFeatureAttribute'), 'setFeatureAttribute', 'Set a feature attribute. --setFeatureAttribute=featureName,attributeName,attributeValue');
				# $this->registerFeature($this, array('get'), 'get', 'Get a value. --get=category'.valueSeparator.'variableName', array('storeVars'));
				$this->registerFeature($this, array('getToResult', 'get'), 'get', 'Get a value and put it in an array so we can do stuff with it. --get=category'.valueSeparator.'variableName', array('storeVars'));
				$this->registerFeature($this, array('getNested'), 'getNested', 'Get a value and put it in an array so we can do stuff with it. --getNested=category'.valueSeparator.'variableName', array('storeVars'));
				$this->registerFeature($this, array('set'), 'set', 'set a value. All remaining values after the destination go into a string. --set=category'.valueSeparator.'variableName'.valueSeparator.'value', array('storeVars'));
				$this->registerFeature($this, array('makeMeAvailable'), 'makeMeAvailable', 'Make a Me variable available to the calling scope. This is useful if you want to send data back via a variable which the calling scope is expecting you to initialise. Eg --makeMeAvailable=variableName would make Me,variableName available to the calling scope. You typically do this at the BEGINING of a macro.', array('storeVars'));
				$this->registerFeature($this, array('makeLocalAvailable'), 'makeLocalAvailable', 'Make a Local variable available to the calling scope. This is useful if you want to send data back via a variable which the calling scope is expecting you to initialise. Eg --makeLocalAvailable=variableName would make Local,variableName available to the calling scope. You need to do this at the END of a macro.', array('storeVars'));
				$this->registerFeature($this, array('setNested'), 'setNested', 'set a value into a nested array, creating all the necessary sub arrays. The last parameter is the value. All the other parameters are the keys for each level. --setNested=StoreName'.valueSeparator.'category'.valueSeparator.'subcategory'.valueSeparator.'subsubcategory'.valueSeparator.'value. In this case an array would be created in StoreName,category that looks like this subcategory=>array(subsubcategory=>value)', array('storeVars'));
				$this->registerFeature($this, array('setArray'), 'setArray', 'set a value. All remaining values after the destination go into an array. --set=category'.valueSeparator.'variableName'.valueSeparator.'value', array('storeVars'));
				$this->registerFeature($this, array('setIfNotSet', 'setDefault'), 'setIfNotSet', 'set a value if none has been set. --setIfNotSet=category'.valueSeparator.'variableName'.valueSeparator.'defaultValue', array('storeVars'));
				$this->registerFeature($this, array('setIfNothing'), 'setIfNothing', 'set a value if none has been set or evaluates(PHP) to false. --setIfNothing=category'.valueSeparator.'variableName'.valueSeparator.'defaultValue', array('storeVars'));
				$this->registerFeature($this, array('unset'), 'unset', 'un set (delete) a value. --unset=category'.valueSeparator.'variableName['.valueSeparator.'variableName['.valueSeparator.'variableName]] . Note that the extra optional variableNames are for nesting, not for deleting multiple variables in one command.', array('storeVars'));
				$this->registerFeature($this, array('getCategory'), 'getCategory', 'Get an entire store into the result set. --getCategory=moduleNam', array('storeVars', 'store', 'dev'));
				$this->registerFeature($this, array('setCategory'), 'setCategory', 'Set an entire store to the current state of the result set. --setCategory=category', array('storeVars', 'store', 'dev'));
				$this->registerFeature($this, array('unsetCategory'), 'unsetCategory', 'Un set/delete an entire store. --unsetCategory=category', array('storeVars', 'store', 'dev'));
				$this->registerFeature($this, array('copyCategory'), 'copyCategory', 'Copy an entire store to another store. --copyCategory=fromModuleName,toModuleName', array('storeVars', 'store', 'dev'));
				$this->registerFeature($this, array('copy'), 'copy', "Copy the contents of any nested variable to any nested location. --copy='[\"Path,to,variable\",\"Path,to,destination,variable\"]' or in a macro: copy [\"Path,to,variable\",\"Path,to,destination,variable\"]", array('storeVars', 'store', 'dev'));
				$this->registerFeature($this, array('stashResults'), 'stashResults', 'Put the current result set into a memory slot. --stashResults=category'.valueSeparator.'variableName');
				$this->registerFeature($this, array('retrieveResults'), 'retrieveResults', 'Retrieve a result set that has been stored. This will replace the current result set with the retrieved one --retrieveResults=category'.valueSeparator.'variableName');
				$this->registerFeature($this, array('getPID'), 'getPID', 'Save the process ID to a variable. --getPID=category'.valueSeparator.'variableName');
				
				$this->registerFeature($this, array('setJson'), 'setJson', 'Take a json encoded array from jsonValue and store the arrary in category'.valueSeparator.'variableName. --setJson=category'.valueSeparator.'variableName'.valueSeparator.'jsonValue');
				$this->registerFeature($this, array('outNow'), 'outNow', 'Execute the output now.', array('dev'));
				
				$this->registerFeature($this, array('callFeature', 'callFeatureReturn'), 'callFeatureReturn', "Call a feature. This essentially allows you to execute what ever is in a variable. Take a lot of care to make sure your variables contain what you think they do as you could introduce a lot of pain here. --callFeature=feature[,parm1[,parm2..etc]]", array('dangerous'));
				$this->registerFeature($this, array('callFeatureNoReturn', 'isolate'), 'callFeatureNoReturn', "Call a feature but don't return the results. There's two main uses for this. 1) Call something that would normally interfere with the result set. 2) Execute what ever is in a variable without affecting what is in the resultSet. Take a lot of care to make sure your variables contain what you think they do as you could introduce a lot of pain here. This can also be used to isolate a feature so that it doesn't affect the following feature calls. --callFeatureNoReturn=feature[,parm1[,parm2..etc]]", array('dangerous'));
				
				
				$this->registerFeature($this, array('getFileList'), 'getFileList', "Get a simple list of items in a given directory. --getFileList=fullDirectoryPath .", array('filesystem'));
				$this->registerFeature($this, array('getFileTree'), 'getFileTree', "Get a direcotry tree of items in a given directory. --getFileTree=fullDirectoryPath,[withAttributes] . withAttributes can be true or false (default).", array('filesystem'));
				
				$this->registerFeature($this, array('dump'), 'dump', 'Dump internal state.', array('debug', 'dev'));
				$this->registerFeature($this, array('debug'), 'debug', 'Send parameters to stdout. --debug=debugLevel,outputText eg --debug=0,StuffToWriteOut . DebugLevel is not implemented yet, but 0 will be "always", and above that will only show as the verbosity level is incremented with -v or --verbose.', array('debug', 'dev'));
				$this->registerFeature($this, array('verbose', 'v', 'verbosity'), 'verbose', 'Increment/set the verbosity. --verbose[=verbosityLevel] where verbosityLevel is an integer starting from 0 (default)', array('debug', 'dev'));
				$this->registerFeature($this, array('lessVerbose', 'V'), 'V', 'Decrement verbosity.', array('debug', 'dev'));
				$this->registerFeature($this, array('ping'), 'ping', 'Useful for debugging.', array('debug', 'dev'));
				$this->registerFeature($this, array('cleanResults'), 'cleanResults', 'Cleans keys. Converts any objects to arrays.', array('resultSet'));
				$this->registerFeature($this, array('parameters'), 'parameters', "Map the input parameters to more meaningful names. In the simplest for, this looks like --parameters=parameterName1,parameterName2,parameterName3,etc . But using json will give you access to all the features. See parameters.md", array('resultSet'));
				$this->registerFeature($this, array('#'), '#', 'Comment.', array('systemInternal'));
				$this->registerFeature($this, array('pass'), 'pass', "It's a place holder meaning that you will not get a message like \"Could not find macro 'default'. This can happen if you haven't asked me to do anything.\"", array('systemInternal'));
				$this->registerFeature($this, array('	'), '	', 'Internally used for nesting.', array('systemInternal'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			#case 'get': # TODO Is this still useful?
			#	$parms=$this->interpretParms($this->get('Global', 'get'));
			#	return $this->get($parms[0], $parms[1]);
			#	break;
			
			# TODO Oops! Somehow I missed this. It should use already existing functionality.
			#case 'registerTags':
			#	break;
			case 'aliasFeature':
				$parms=$this->interpretParms($this->get('Global', $event));
				$this->aliasFeature($parms[0], $parms);
				break;
			case 'setFeatureAttribute':
				$parms=$this->interpretParms($this->get('Global', $event), 2, 3, true);
				$this->setFeatureAttribute($parms[0], $parms[1], $parms[2]);
				break;
			case 'get':
				$parms=$this->interpretParms($this->get('Global', $event));
				$value=array($this->get($parms[0], $parms[1]));
				return $value;
				break;
			case 'getNested':
				$parms=$this->interpretParms($this->get('Global', $event));
				return array($this->getNested($parms));
				break;
			case 'set':
				$parms=$this->interpretParms($this->get('Global', $event), 2, 2, true);
				$this->set($parms[0], $parms[1], $parms[2]);
				break;
			case 'makeMeAvailable':
				$parms=$this->interpretParms($this->get('Global', $event), 1, 1, true);
				$this->makeMeAvailable($parms[0]);
				break;
			case 'makeLocalAvailable':
				$parms=$this->interpretParms($this->get('Global', $event), 1, 1, true);
				$this->makeLocalAvailable($parms[0]);
				break;
			case 'setNested':
				$this->setNestedFromInterpreter($this->get('Global', $event));
				break;
			case 'setArray':
				$parms=$this->interpretParms($this->get('Global', $event), 2, 2, false);
				$this->set($parms[0], $parms[1], $parms[2]);
				break;
			case 'setIfNotSet':
				$originalParms=$this->get('Global', $event);
				$parms=$this->interpretParms($originalParms, 2);
				$this->requireNumParms($this, 3, $event, $originalParms, $parms);
				$this->setIfNotSet($parms[0], $parms[1], $parms[2]);
				break;
			case 'setIfNothing':
				$originalParms=$this->get('Global', $event);
				$parms=$this->interpretParms($originalParms);
				$this->requireNumParms($this, 3, $event, $originalParms, $parms);
				$this->setIfNotSet($parms[0], $parms[1], $parms[2], true);
				break;
			case 'unset':
				$parms=$this->interpretParms($this->get('Global', $event), 0, 2, false);
				$this->doUnset($parms);
				break;
			case 'getCategory':
				return $this->getCategoryModule($this->get('Global', $event));
				break;
			case 'setCategory':
				$this->setCategoryModule($this->get('Global', $event), $this->getResultSet());
				break;
			case 'unsetCategory':
				$this->unsetCategoryModule($this->get('Global', $event));
				break;
			case 'copyCategory':
				$parms=$this->interpretParms($this->get('Global', $event), 2);
				$this->setCategoryModule($parms[1], $this->getCategoryModule($parms[0]));
				break;
			case 'copy':
				# TODO Do this
				$originalParms=$this->get('Global', $event);
				$parms=$this->interpretParms($originalParms);
				$this->debug(4,"copy: get={$parms[1]} set={$parms[0]}");
				$this->setNestedViaPath($parms[1], $this->getNested(explode(',',$parms[0])));
				break;
			case 'stashResults':
				$originalParms=$this->get('Global', 'stashResults');
				$parms=$this->interpretParms($originalParms);
				if ($this->requireNumParms($this, 2, $event, $originalParms, $parms))
				{
					$this->setNestedViaPath($parms, $this->core->getResultSet());
				}
				break;
			case 'retrieveResults':
				$originalParms=$this->get('Global', 'retrieveResults');
				$parms=$this->interpretParms($originalParms);
				# TODO this should be in an If condition like stashResults
				$this->requireNumParms($this, 2, $event, $originalParms, $parms);
				return $this->getNested($parms);
				break;
			case 'setJson':
				$parms=$this->interpretParms($this->get('Global', $event), 2, 2, true);
				$this->set($parms[0], $parms[1], json_decode($parms[2], 1));
				break;
			case 'dump':
				return $this->dumpState();
				break;
			case 'callFeatureReturn':
				$parms=$this->splitOnceOn(valueSeparator, $this->get('Global', $event));
				return $this->callFeature($parms[0], $parms[1]);
				break;
			case 'callFeatureNoReturn':
				$parms=$this->splitOnceOn(valueSeparator, $this->get('Global', $event));
				$this->callFeature($parms[0], $parms[1]);
				break;
			case 'debug':
				$parms=$this->interpretParms($this->get('Global', $event), 1, 1, true);
				$this->debug($parms[0], $parms[1]);
				break;
			case 'verbose':
				$original=$this->get('Global', $event);
				$this->verbosity($original);
				break;
			case 'V':
				$this->verbosity('-');
				break;
			case 'getPID':
				$this->getPID($this->interpretParms($this->get('Global', $event)));
				break;
			case 'ping':
				echo "Pong.\n";
				break;
			case 'cleanResults':
				return $this->objectToArray($this->getResultSet());
				break;
			case 'outNow':
				$this->out($this->getResultSet());
				break;
			case 'parameters':
				$parms=$this->interpretParms($this->get('Global', $event));
				$this->parameters($parms);
				break;
			case 'getFileList':
				return $this->getFileList($this->get('Global', $event));
				break;
			case 'getFileTree':
				$parms=$this->interpretParms($this->get('Global', $event), 2, 1, true);
				return $this->getFileTree($parms[0], $parms[1]);
				break;
			case 'pass':
				break;
			case '#':
				break;
			default:
				$this->complain($this, 'Unknown event', $event);
				break;
		}
	}

	public static function assert($verbosity=0)
	{
		if (!self::$singleton) self::$singleton=new core($verbosity);
		return self::$singleton;
	}
	
	function separateStringAppendedToJson($input)
	{
		$firstChar=substr($input, 0, 1);
		$lastChar=substr($input, -1, 1);
		
		if (($firstChar=='[' or $firstChar=='{') and ($lastChar==']') or $lastChar=='}')
		{ # There is no extra string. Just return the json as is
			
			return array('firstPart'=>$input, 'lastPart'=>null);
		}
		else
		{ # We have some work to do
			# Find where the split is
			if ($firstChar=='[') $searchFor='],';
			elseIf ($firstChar=='{') $searchFor='},';
			
			$lastFoundPos=0;
			$tmpFound=0;
			while ($tmpFound!==false)
			{
				$tmpFound=strpos($input, $searchFor, $lastFoundPos+1);
				if ($tmpFound) $lastFoundPos=$tmpFound;
			}
			
			# Split it
			$lastFoundPos++;
			$firstPart=substr($input, 0, $lastFoundPos);
			$lastPart=substr($input, $lastFoundPos+1, strlen($input)-strlen($lastFoundPos)-1);
			
			return array('firstPart'=>$firstPart, 'lastPart'=>$lastPart);
		}
	}
	
	function interpretParms($parms, $limit=0, $require=null, $reassemble=true)
	{
		if (is_array($parms)) return $parms;
		
		if (strlen($parms)<1)
		{ // No parms$require
			# TODO Potentially some detection could happen here to allow atomic failure.
			if ($require>0) $this->debug(0, "Expected $require parameters, but got nothing. Bad things could happen if execution had been allowed to continue. Parms=$parms");
			$parts=array();
		}
		else
		{
			
			
			$firstChar=substr($parms, 0, 1);
			if ($firstChar=='[' or $firstChar=='{')
			{ // Json
				$mixedParts=$this->separateStringAppendedToJson($parms);
				$parts=json_decode($mixedParts['firstPart'], 1);
				if (!count($parts))
				{
					$this->debug(0, "interpretParms: Got 0 parts back from json \"${mixedParts['firstPart']}\" originalInput=$parms which usually means invalid json.");
					
					# TODO Do we want to do some other clean up here.
					$parts=array(); // Prevent stuff from breaking since they always expect an array.
				}
				
				if ($mixedParts['lastPart']!='') $parts[]=$mixedParts['lastPart'];
			}
			else
			{ // Legacy comma separated format
				$parts=explode(valueSeparator, $parms);
			}
		}
		
		#if ($require===null) $require=$limit;
		
		$partsCount=count($parts);
		
		if ($partsCount<$limit)
		{
			if ($partsCount<$require) $this->debug(0, "Expected $require parameters, but got $partsCount. Bad things could happen if execution had been allowed to continue. Parms=$parms");
			
			$output=$parts;
			while (count($output)< $limit) $output[]='';
			return $output;
		}
		
		for ($i=$partsCount;$i<$limit;$i++) $parts[$i]=false;
		
		if ($limit)
		{
			# Return the split array, but once we reach the limit, dump any remaining parms into one remaining parm
			$output=array();
			for ($i=0;$i<$limit;$i++)
			{
				if (isset($parts[$i]))
				{
					# $this->core->debug(2, "interpretParms: Added main part $i => {$parts[$i]}");
					$output[]=$parts[$i];
				}
				else break;
			}
			
			$outputParts=array();
			$stop=count($parts);
			
			for ($j=$i;$j<$stop;$j++)
			{
				if (is_array($parts[$j])) $outputParts[]=json_encode($parts[$j]);
				else $outputParts[]=$parts[$j];
				# $this->core->debug(2, "interpretParms: Added remaining part $j => {$parts[$j]}");
			}
			
			# Reassemble=true sets a string. False sets an array.
			if ($reassemble) $output[]=implode(valueSeparator, $outputParts);
			else $output[]=$outputParts;
			
			while (count($output)< $limit)
			{
				$this->debug(0, "add one");
				$output[]='';
			}
			
			return $output;
		}
		else return $parts;
	}
	
	function loadCache($path)
	{
		$fileList=$this->getFileList($path);
		
		foreach ($fileList as $fileName=>$filePath)
		{
			$nameParts=explode('.', $fileName);
			$categoryName=$nameParts[0];
			$cacheContents=json_decode(file_get_contents($filePath), 1);
			$this->setCategoryModule($categoryName, $cacheContents);
			
			$cacheSize=count($cacheContents);
			$this->setRef('CacheStats', $categoryName, $cacheSize);
			$this->debug(4,"Loaded cache \"$categoryName\" ($cacheSize)");
			
			if ("$fileName" == 'Features.cache.json')
			{
				$this->set('General', 'complainAboutDuplicateMacros', false);
			}
		}
	}
	
	function getFileList($path)
	{
		$index=md5($path);
		$contents = $this->get('FileListCache', $index);
		if (is_null($contents))
		{
			$contents=array();
		}
		
		if (count($contents)<1)
		{
			$listCache=array_keys($this->getCategoryModule('FileListCache'));
			$contents = $this->doGetFileList($path);
			$this->set('FileListCache', $index, $contents);
			$misses=$this->get('CacheStats', 'FileListMisses')+1;
			$this->set('CacheStats', 'FileListMisses', $misses);
			$this->set('CacheStats', 'FileListChanged', 'true');
		}
		else
		{
			$this->set('CacheStats', 'FileListHits', $this->get('CacheStats', 'FileListHitsHits')+1);
		}
		
		return $contents;
	}
	
	function doGetFileList($path)
	{
		# TODO This can be done much better internally in PHP
		if (is_file($path))
		{
			$pathParts=explode('/', $path);
			$fileName=$pathParts[count($pathParts)-1];
			$output=array($fileName=>$path);
		}
		else
		{
			if (file_exists($path))
			{
				$output=array();
				$files=explode("\n", `ls -1 "$path" 2> /dev/null`);
				foreach ($files as $file)
				{
					$trimmedFile=trim($file);
					if ($trimmedFile) $output[$trimmedFile]="$path/$trimmedFile";
				}
			}
			else return false;
		}
		return $output;
	}
	
	function getFileList2($path, $withAttributes=false)
	{
		@$result=scandir($path, SCANDIR_SORT_ASCENDING);
		
		$prePendPath=($path=='/')?'/':$path.'/';
		
		if ($result)
		{
			$output=array();
			foreach ($result as $file)
			{
				if ($file != '.' and $file != '..')
				{
					$output[$file]=$prePendPath.$file;
				}
			}
			
			return $output;
		}
		else return array();
	}
	
	function getFileTree($path, $withAttributes=false)
	{
		$entries=$this->getFileList($path);
		if (!$entries) return false;
		
		foreach ($entries as $filename=>&$entry)
		{
			if (!is_file($entry))
			{
				$subEntries=$this->getFileTree($entry, $withAttributes);
				if ($withAttributes)
				{
					$entry=array(
						'path' => $entry,
						'type' => 'directory',
						'subEntries' => $subEntries
						);
				}
				else
				{
					$entry=$subEntries;
				}
			}
			else
			{
				if ($withAttributes)
				{
					$entry=array(
						'path' => $entry,
						'type' => 'file'
						);
				}
			}
		}
		
		return $entries;
	}
	
	function getModules($path)
	{ // get all the module paths from a path
		return $this->getFileList($path);
	}
	
	function setResultSetNoRef($value, $src='unknown')
	{
		$this->setResultSet($value, 'setResultSetNoRef: '.$src);
	}
	
	function setResultSet(&$value, $src='unknown')
	{
		$valueText=(is_string($value))?$value:'Type='.gettype($value);
		$this->debug(5, "setResultSet(value=$valueText, src=$src)");
		if (is_array($value)) # ($value!=null and $value!==false)
		{
			$numberOfEntries=count($value);
			if ($numberOfEntries==1)
			{
				$keys=array_keys($value);
				$firstValue=$value[$keys[0]];
				if (!$firstValue)
				{
					$this->core->debug(5, __CLASS__.'->'.__FUNCTION__.": resultSet is an array with a single empty value. Not setting.");
					# return false;
				}
			}
			
			$nesting=$this->get('Core', 'nesting');
			if ($this->isVerboseEnough(5))
			{
				$arrayString=($numberOfEntries==1)?json_encode($value):'NA';
				$this->debug(5, "setResultSet(value=$valueText($numberOfEntries), src=$src)/$nesting - is_array == true. VALUE WILL BE SET json=$arrayString");
				if ($this->isVerboseEnough(6)) 
				{
					print_r($value);
					$this->debug(6, "setResultSet(value=$value($numberOfEntries), src=$src)/$nesting - exiting from var dump.");
				}
				$serial=$this->get('Core', 'serial');
			}
			#$this->setNestedViaPath(array('Core', 'resultSet', $nesting), $value);
			$this->set('Core', 'shared'.$nesting, $value);
			
			return true;
		}
		else return false;
	}
	
	function &getResultSet()
	{
		$nesting=$this->get('Core', 'nesting');
		/*$resultSet=$this->getNested(array('Core', 'resultSet', $nesting));
		$resultSetDiag=count($resultSet);
		if ($this->isVerboseEnough(5))
		{
			$serial=$this->get('Core', 'serial');
			$this->debug(5, "getResultSet/$nesting count=$resultSetDiag serial=$serial");
			#print_r($resultSet);
		}*/
		# return $resultSet;
		return $this->get('Core', 'shared'.$nesting);
	}
	
	function &getParentResultSet()
	{
		$nesting=$this->get('Core', 'nesting');
		$nestingSrc=$nesting-1;
		if ($nestingSrc<1 or !is_numeric($nestingSrc)) $nestingSrc = 1; # TODO check this
		#$resultSet=$this->getNested(array('Core', 'resultSet', $nesting));
		$resultSet=&$this->get('Core', 'shared'.$nestingSrc);
		
		if ($this->isVerboseEnough(5))
		{
			$serial=$this->get('Core', 'serial');
			$resultSetDiag=count($resultSet);
			$this->debug(5, "getParentResultSet $nestingSrc->$nesting/$resultSetDiag/$serial");
		}
		return $resultSet;
	}
	
	function makeParentShareMemoryCurrent()
	{
		$this->debug(5, "makeParentShareMemoryCurrent/");
		$this->setResultSet($this->getParentResultSet());
	}
	
	function callFeatureWithDataset($argument, $value, $dataset)
	{
		// Increment nesting
		$this->incrementNesting();
		
		// set resultSet to dataset
		$this->setResultSet($dataset);
		
		// call feature
		$output=$this->callFeature($argument, $value);
		
		// Decrement nesting (WITHOUT pulling the resultSet)
		$this->decrementNesting();
		return $output;
	}
	
	private function setStackEntry($nesting, $argument, $value)
	{
		$this->setNestedViaPath(array("Core", "stack", $nesting), array(
			'feature'=>$argument,
			'value'=>$value,
			'nesting'=>$nesting
			));
	}
	
	private function unsetStackEntry($nesting)
	{
		$this->doUnset(array("Core", "stack", $nesting));
	}
	
	function callFeature($argument, $value='')
	{
		$nesting=$this->get('Core', 'nesting');
		
		if (is_string($argument) and $argument and $argument != '#' and $argument != '//')
		{ // Only process non-white space
			$obj=&$this->core->get('Features', $argument);
			
			if (!is_array($obj))
			{
				if (!$this->assertAvailableMacro($argument, 'callFeature')) return false;
				$obj=&$this->core->get('Features', $argument);
			}
			
			# NOTE This has been done this way for performance. However it may be worth abstracting it out into setNestedViaPath.
			$this->store[isolatedNestedPrivateVarsName][$nesting]['featureName']=$argument;
			
			$indentation=str_repeat('  ', $nesting);
			$valueIn=$this->processValue($value);
			
			$this->debug(4, "INVOKE-Enter {$indentation}{$obj['name']}/$nesting value={$value}, valueIn=$valueIn");
			
			$this->setStackEntry($nesting, $argument, $valueIn);
			
			if ($this->isVerboseEnough(5))
			{
				$this->debugResultSet($obj['name']);
			}
			
			$numberOfArgs=$this->makeArgsAvailableToTheScript($obj['name'], $valueIn);
			$result=$this->module[$obj['provider']]->event($obj['name']);
			
			if (isset($obj['featureType']))
			{
				$this->core->debug(4, "callFeature: ".$obj['featureType']);
				if ($outDataType=$this->getNested(array('Semantics', 'featureTypes', $obj['featureType'], 'outDataType')))
				{
					if ($dataType=$this->getNested(array('Semantics', 'dataTypes', $outDataType)))
					{
						$semanticsTemplate=$this->get('Settings', 'semanticsTemplate');
						$this->core->debug(4, "callFeature: Applying --{$dataType['action']}={$dataType[$semanticsTemplate]}");
						$this->callFeature($dataType['action'], $dataType[$semanticsTemplate]);
						$dataType['chosenTemplate']=$dataType[$semanticsTemplate];
						$this->set('SemanticsState', 'currentDataType', $dataType);
						$this->set('SemanticsState', 'currentFeatureType', $obj['featureType']);
					}
					else $this->core->debug(4, "callFeature: Could not find dataType $outDataType");
				}
				else $this->core->debug(4, "callFeature: Could not find featureType ".$obj['featureType']);
			}
			
			if (cleanupArgs)
			{
				$this->removeArgs($obj['name'], $numberOfArgs);
			}
			
			$this->unsetStackEntry($nesting);
			
			if ($this->isVerboseEnough(4))
			{
				$resultCount=count($result);
				$nesting=$this->get('Core', 'nesting');
				$isArray=is_array($result)?'True':'False';;
				$this->debug(4, "INVOKE-Exit  {$indentation}{$obj['name']}/$nesting value={$value}, valueIn=$valueIn resultCount=$resultCount is_array=$isArray smCount=".$this->getResultSetCount());
				# $this->debugResultSet($obj['name']);
			}
			return $result;
		}
		else $this->debug(3,"Core->callFeature: Non executable code \"$argument\" sent. We shouldn't have got this.");
		return false;
	}
	
	function parameters($args)
	{
		if ($this->isVerboseEnough(4))
		{
			$this->debug(4,"Parameters: ".json_encode($args));
		}
		// localScopeVarName vs nestedPrivateVarsName
		$categoryForParameters=localScopeVarName;
		$categoryForFeedBack=localScopeVarName;
		$categoryForKnowledge=isolatedNestedPrivateVarsName;
		if ($categoryForParameters=='Local') $scopeKey=$this->getScopeForCategory($categoryForParameters);
		else $scopeKey=$this->getScopeForCategory($categoryForParameters, 1);
		
		$nesting=$this->get('Core', 'nesting');
		if (isset($this->store[$categoryForKnowledge][$nesting-1]['featureName']))
		{
			$lastMacro=$this->store[$categoryForKnowledge][$nesting-1]['featureName'];
			$this->debug(4, "parameters: lastMacro=$lastMacro nesting=$nesting");
		}
		else
		{
			$this->debug(1, "parameters: Could not find lastMacro. This likely happened because the parameters command was called nested in some code. It should be called just under the comments section of the macro.");
			return false;
		}
		
		$argsToUse=(is_array($args))?$args:array($args);
		if (!isset($this->store[$categoryForParameters][$scopeKey])) $this->store[$categoryForParameters][$scopeKey]=array();
		if (!is_array($this->store[$categoryForParameters][$scopeKey])) $this->store[$categoryForParameters][$scopeKey]=array();
		
		if (!isset($this->store[$categoryForFeedBack][$scopeKey])) $this->store[$categoryForFeedBack][$scopeKey]=array();
		if (!is_array($this->store[$categoryForFeedBack][$scopeKey])) $this->store[$categoryForFeedBack][$scopeKey]=array();
		
		$this->store[$categoryForFeedBack][$scopeKey]['pass']=achelTrue;
		$argKeys=array_keys($args);
		foreach ($argKeys as $position => $details)
		{
			if (is_array($args[$details]))
			{ // More flexible json/array based configuration
				if (is_numeric($details))
				{
					$key=$args[$details];
					$value=$this->core->get('Global',"$lastMacro-$details");
				}
				else
				{
					$key=$details;
					$value=$this->core->get('Global',"$lastMacro-$position");
				}
				
				$variableResult=$this->processVariableDefinition($details, $value, $args[$details]);
				$this->store[$categoryForParameters][$scopeKey][$key]=$variableResult['value'];
				if (!$variableResult['pass']) $this->store[$categoryForFeedBack][$scopeKey]['pass']=achelFalse;
				
				if ($variableResult['pass'])
				{
					$this->core->debug(3, "parameters: Parameter \"$key\" set to \"{$variableResult['value']}\"");
				}
				else
				{
					$this->core->debug(1, "parameters: Parameter \"$key\" failed a test with message \"{$variableResult['message']}\"");
				}
			}
			else
			{ // Simple position to name assignment.
				if (is_numeric($details))
				{ // Basic position->name assignment
					$key=$args[$details];
					$value=$this->core->get('Global',"$lastMacro-$details");
					# TODO This was false. Double check this change hasn't broken any assumptions.
					$default=$value;
					$this->debug(4,"parameters: Simple numeric. [$categoryForParameters][$scopeKey][$key] value=$value    key=$key nesting=$nesting globalKey=$lastMacro-$details");
					
					# print_r($this->core->getCategoryModule('Global'));
				}
				else
				{ // Basic name assignment
					$key=$details;
					$value=$this->core->get('Global',"$lastMacro-$position");
					$default=$args[$details];
					$this->debug(4,"parameters: Simple name. [$categoryForParameters][$scopeKey]  [$key] value=$value nesting=$nesting default=$default");
				}
				$this->store[$categoryForParameters][$scopeKey][$key]=($value!==false and $value!='')?$value:$default;
				$this->debug(3, "   key=$key value=".$this->store[$categoryForParameters][$scopeKey][$key]);
			}
		}
	}
	
	function processVariableDefinition($key, $value, $args)
	{
		$pass=true;
		$message='';
		
		if (!isset($args['type'])) $args['type']='string';
		switch ($args['type'])
		{
			case 'string':
				$length=strlen($value);
				// Tests
				$maxLengthAllowed=(isset($args['maxLengthAllowed']))?$args['maxLengthAllowed']:false;
				if ($maxLengthAllowed and $length>$maxLengthAllowed)
				{
					$pass=false;
					$message="Length ($length) greater than allowed ($maxLengthAllowed).";
				}
				
				$minLengthAllowed=(isset($args['minLengthAllowed']))?$args['minLengthAllowed']:false;
				if ($minLengthAllowed and $length<$minLengthAllowed)
				{
					$pass=false;
					$message="Length ($length) less than allowed ($minLengthAllowed).";
				}
				
				if (!$length and isset($args['default'])) $value=$args['default'];
				
				// Manipulations
				$maxLength=(isset($args['maxLength']))?$args['maxLength']:false;
				if ($maxLength and $length>$maxLength) $value=substr($value, 0, $maxLength);
				
				$minLength=(isset($args['minLength']))?$args['minLength']:false;
				if ($minLength and $length<$minLength)
				{
					$difference=$minLength-$length;
					$padding=str_pad(' ',$difference);
					$value=$value.$padding;
				}
				break;
			case 'number':
				if (is_numeric($value))
				{
					// Tests
					$maxAllowed=(isset($args['maxAllowed']))?$args['maxAllowed']:false;
					if ($maxAllowed and $value>$maxAllowed)
					{
						$pass=false;
						$message="Value ($value) greater than allowed ($maxAllowed).";
					}
					
					$minAllowed=(isset($args['minAllowed']))?$args['minAllowed']:false;
					if ($minAllowed and $value<$minAllowed)
					{
						$pass=false;
						$message="Value ($value) less than allowed ($minAllowed).";
					}
					
					// Manipulations
					$max=(isset($args['max']))?$args['max']:false;
					if (($max !== false) and $value>$max) $value=$max;
					
					$min=(isset($args['min']))?$args['min']:false;
					if (($min !== false) and $value<$min) $value=$min;
				}
				else
				{
					if (isset($args['default'])) $value=$args['default'];
					else $pass=false;
					$message="Not a number.";
				}
				break;
			case 'boolean':
				// Just assert that we have a boolean. Anything that is not 0, '', or 'false' will resolve to true.
				if ($value=='' and isset($args['default'])) $value=$args['default'];
				else $value=($value and $value!=='false')?achelTrue:achelFalse;
				break;
			default:
				$this->debug(1,"processVariableDefinition: Unknown type \"{$args['type']}\" in definition for \"$key\".");
				break;
		}
		
		return array('value'=>$value, 'pass'=>$pass, 'message'=>$message);
	}
	
	function durableGet($key, $array)
	{
		return (isset($array[$key]))?$array[$key]:false;
	}
	
	function makeArgsAvailableToTheScript($featureName, $args)
	{
		$this->set('Global', $featureName, $args);
		$argParts=$this->interpretParms($args);
		foreach ($argParts as $key=>$arg)
		{
			$this->set('Global', "$featureName-$key", $arg);
		}
		
		return count($argParts);
	}
	
	function removeArgs($featureName, $numberOfArgs)
	{
		for ($key=0; $key<$numberOfArgs; $key++)
		{
			$this->doUnset(array('Global', "$featureName-$key"));
		}
	}
	
	function splitOnceOn($needle, $haystack)
	{
		if ($pos=strpos($haystack, $needle))
		{
			$first=substr($haystack, 0, $pos);
			$remaining=substr($haystack, $pos+strlen($needle));
			
			return array($first, $remaining);
		}
		else return array($haystack, '');

	}
	
	private function findAndProcessVariables($input, &$iterations=0)
	{
		$debugLevel=5;
		$output=$input;
		
		$this->core->debug($debugLevel, "findAndProcessVariables: Enter \"$output\"");
		
		while (strpos($output, storeValueBegin)!==false and $iterations<100)
		{
			$startPos=strpos($output, storeValueBegin)+2;
			$nextStartPos=strpos($output, storeValueBegin, $startPos)+2;
			$endPos=strpos($output, storeValueEnd, $startPos);
			
			if ($startPos>$endPos)
			{
				$oldStartPos=$startPos;
				$beginLen=strlen(storeValueBegin);
				if (substr($output, 0, $beginLen)== storeValueBegin)
				{
					$startPos=$beginLen-1;
					$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos oldStartPos=$oldStartPos Reset startPos");
				}
				else
				{
					$endPos=strpos($output, storeValueEnd, $startPos);
					$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Exended endPos (A)");
				}
			}
			
			$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Initial find from $output. ".storeValueBegin.' -> '.storeValueEnd);
			
			
			# This allows us to have nested variables.
			while ($nextStartPos<$endPos and $nextStartPos>$startPos)
			{
				$lastStart=$startPos;
				$startPos=strpos($output, storeValueBegin, $startPos)+2;
				$nextStartPos=strpos($output, storeValueBegin, $startPos)+2;
				
				# if ($startPos>$endPos) $startPos=$lastStart;
				if ($startPos>$endPos)
				{
					$endPos=strpos($output, storeValueEnd, $startPos);
					$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Exended endPos (B)");
				}
				# $this->core->debug(0, "Progressing $startPos $nextStartPos $endPos $output");
				$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Progressing using $output");
			}
			
			$length=$endPos-$startPos;
			
			$varDef=substr($output, $startPos, $length);
			$varParts=explode(',', $varDef);
			$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Trying to lookup $varDef from $output");
			
			if (isset($varParts[1]))
			{
				if (count($varParts)==2)
				{
					/*
						This will slowly become irrelevant as new features take advantage of nesting deeper than two levels. But until then it's still a huge performance saver.
					*/
					$varValue=$this->get($varParts[0], $varParts[1], false, 0);
				}
				else $varValue=$this->getNested($varParts);
				
				if (is_array($varValue))
				{
					$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Got array");
				}
				else
				{
					$this->core->debug($debugLevel, "findAndProcessVariables: start=$startPos next=$nextStartPos end=$endPos Got $varValue");
				}
				
				if (!is_array($varValue)) $output=implode($varValue, explode(storeValueBegin.$varDef.storeValueEnd, $output));
				else 
				{
					if (count($varValue))
					{
						$varValue=json_encode($varValue, JSON_FORCE_OBJECT);
						$output=implode($varValue, explode(storeValueBegin.$varDef.storeValueEnd, $output));
					}
					else $output=implode('', explode(storeValueBegin.$varDef.storeValueEnd, $output));
				}
			}
			else
			{
				$this->core->debug(0, "findAndProcessVariables: Probable syntax error in ".storeValueBegin."$varDef".storeValueEnd." . It should have had an extra value. Perhaps like this: ".storeValueBegin."Thing,{$varParts[0]}".storeValueEnd."");
			}
			
			$iterations++;
		}
		
		if ($iterations==100)
		{
			$this->debug(1, "Still finding \"".storeValueBegin."\" in \"".$output."\"");
		}
		
		$this->core->debug($debugLevel, "findAndProcessVariables: Return \"$output\"");
		
		return $output;
	}
	
	function processValue($value)
	{ // Substitute in an variables
		$output=$value;
		
		$iterations=0;
		
		$output=$this->findAndProcessVariables($output, $iterations);
		
		# This is the first go at escaping
		foreach (array(
			'~\!'=>'~!', 
			'!\~'=>'!~', 
			'~\%'=>'~%', 
			'%\~'=>'%~') as $from=>$to)
		{
			$output=implode($to, explode($from, $output));
		}
		
		$output=$this->findAndProcessVariables($output, $iterations);
		
		if ($iterations>20) $this->debug(0, "processValue: $iterations reached while processing variables in \"$value\". The result achieved is \"$output\". This is probably a big."); # NOTE 10 is very strict, yet unlikely to be reached in any legitimate situation I can think of at the moment. The warning limit may need to be reconsidered in the future.
		
		return $output;
	}
	
	function assertAvailableMacro($macroName, $context, $lineNumber=false)
	{
		if ($macroPath=$this->get('MacroListCache', $macroName))
		{
			if ($originName=$this->get('FeatureAliases', $macroName))
			{
				$this->incrementNesting();
				$this->callFeature('loadMacro', $originName);
				$this->decrementNesting();
			}
			else
			{
				$this->incrementNesting();
				$this->callFeature('loadMacro', $macroName);
				$this->decrementNesting();
			}
			return true;
		}
		else
		{
			$macroDetails=($lineNumber)?"$macroName:$lineNumber":"$macroName";
			$this->complain(null, "Could not find a module to match '$macroName' in $macroDetails", $context.'/assertAvailableMacro');
			
			return false;
		}
	}
	
	function addAction($argument, $value=null, $macroName='default', $lineNumber=false)
	{
		$this->debug(4, "addAction: Adding $argument,$value to $macroName");
		
		if (!$argument) return false;
		
		if (!isset($this->store['Macros'])) $this->store['Macros']=array();
		if (!isset($this->store['Macros'][$macroName])) $this->store['Macros'][$macroName]=array();
		
		$obj=&$this->core->get('Features', $argument);
		if (!is_array($obj))
		{
			if (!$this->assertAvailableMacro($argument, 'addAction', $lineNumber)) return false;
			$obj=&$this->core->get('Features', $argument);
			
			if (!is_array($obj))
			{
				$this->complain($this, "Failed to assert macro $argument.", 'addAction');
				return false;
			}
		}
		
		// Add the the action to the macro.
		$this->store['Macros'][$macroName][]=array(
			'obj'=>&$obj, 
			'name'=>$obj['name'], 
			'value'=>$value, 
			'lineNumber'=>$lineNumber);
		
		// Stats.
		if (!isset($this->store['Features'][$argument]['referenced'])) $this->store['Features'][$argument]['referenced']=0;
		$this->store['Features'][$argument]['referenced']++;

	}
	
	function getRealFeatureName($featureName)
	{
		$feature=$this->core->get('Features', $featureName);
		if (!$feature)
		{
			$this->assertAvailableMacro($featureName, 'getRealFeatureName');
			$feature=$this->core->get('Features', $featureName);
		}
		
		return $feature['name'];
	}
	
	function debugResultSet($label='undefined')
	{
		if ($this->isVerboseEnough(4))
		{
			$nesting=$this->get('Core', 'nesting');
			$serial=$this->get('Core', 'serial');
			for ($i=$nesting;$i>-1;$i--)
			{
				$resultSet=$this->getNested(array('Core', 'resultSet', $i));
				$this->debug(4, "debugResultSet $label/$i count=".count($resultSet)." serial=$serial");
			}
		}
	}
	
	function debug($verbosityLevel, $output)
	{
		if ($this->isVerboseEnough($verbosityLevel))
		{
			$title="debug$verbosityLevel";
			# TODO These lookups can be optimized!
			$code=$this->get('Codes', $title, false);
			$default=$this->get('Codes', 'default', false);
			$dim=$this->get('Codes', 'dim', false);
			$eol=$this->get('General', 'EOL', false); # TODO This can be improved
			
			$scopeName=$this->get('General', 'scopeName');
			if ($this->verbosity> 0)
			{
				$output="$dim$scopeName:$default ".$output;
			}
			
			if ($output!=$this->lastMessage['value'])
			{
				if ($this->lastMessage['count']>0) $this->debugOutput("$eol");
				$this->debugOutput("[$code$title$default] $output$eol");
				$this->lastMessage['value']=$output;
				$this->lastMessage['count']=0;
			}
			elseIf ($this->lastMessage['count']==0)
			{
				$this->debugOutput("  {$code}Repeat{$default} ");
				$this->lastMessage['count']++;
			}
			else
			{
				$this->debugOutput(".");
				$this->lastMessage['count']++;
			}
			# return false;
		}
	}
	
	private function debugOutput($message)
	{
		fwrite(STDERR, $message);
	}
	
	function isVerboseEnough($verbosityLevel=0)
	{
		return ($this->verbosity >= $verbosityLevel);
	}
	
	function verbosity($level=0, $announce=true)
	{
		if (is_numeric($level))
		{
			$this->verbosity=intval($level);
			$verbosityName=$this->get('VerbosityLevels', $this->verbosity);
			if ($announce) $this->core->debug($this->verbosity, "verbosity: Set verbosity to \"$verbosityName\" ({$this->verbosity})");
		}
		elseif ($level=='-')
		{
			$this->verbosity=$this->verbosity-1;
			$verbosityName=$this->get('VerbosityLevels', $this->verbosity);
			if ($announce) $this->core->debug($this->verbosity, "verbosity: Decremented verbosity to \"$verbosityName\" ({$this->verbosity})");
		}
		else
		{
			$this->verbosity=$this->verbosity+1;
			$verbosityName=$this->get('VerbosityLevels', $this->verbosity, false);
			if ($announce) $this->core->debug($this->verbosity, "verbosity: Incremented verbosity to \"$verbosityName\" ({$this->verbosity})");
		}
		
		$this->set('Verbosity', 'level', $this->verbosity); // NOTE that changes to this variable will not affect the practicle verbosity. setRef coiuld be used, in which case changes would affect it. However we would then lack safety controls and events.
		
		if ($this->get('Features', 'triggerEvent')) $this->callFeature('triggerEvent', 'Verbosity,changed');
		else $this->debug(0, __CLASS__.'.'.__FUNCTION__.": triggerEvent is not defined. Perhaps an event handeler is not installed. It could be that it hasn't loaded yet.");
	}
	
	
	function incrementNesting()
	{
		# TODO Check: There may need to be some cleaning done here!
		$srcNesting=$this->get('Core', 'nesting');
		$nesting=(is_numeric($srcNesting))?$srcNesting+1:1;
		$this->set('Core', 'nesting', $nesting);
		$this->debug(5, "Incremented nesting to $nesting");
		$this->makeParentShareMemoryCurrent();
		return $nesting;
	}
	
	function decrementNesting()
	{
		$srcNesting=$this->get('Core', 'nesting');
		
		$nesting=(is_numeric($srcNesting))?$srcNesting-1:1;
		
		$this->returnMeVariables($srcNesting, $nesting);
		
		$this->delete(nestedPrivateVarsName, $srcNesting);
		$this->delete(isolatedNestedPrivateVarsName, $srcNesting);
		if ($nesting<1) $nesting=1;
		$this->set('Core', 'nesting', $nesting);
		$this->debug(5, "Decremented nesting to $nesting count=*disabled for performance*"); //.$this->getResultSetCount());
		return $nesting;
	}
	
	function getResultSetCount()
	{
		return count($this->getResultSet());
	}
	
	function &go($macroName='default')
	{
		$emptyResult=null;
		
		if (!isset($this->store['Macros'][$macroName]))
		{
			$this->assertAvailableMacro($macroName, 'go');
		}
		
		if (isset($this->store['Macros'][$macroName]))
		{
			if (count($this->store['Macros'][$macroName]))
			{
				# Set our shared memory location (this allows us to run macros within macros) and manage the scope
				$nesting=$this->incrementNesting();
				
				if (isset($this->store['Features'][$macroName]['source']))
				{
					if ($this->store['Features'][$macroName]['source']!='nesting')
					{
						$oldOldScope=$this->get('General', 'previousScopeName');
						$oldScope=$this->get('General', 'scopeName');
						$scopeName="$macroName-$nesting";
						$this->set('General', 'scopeName', $scopeName);
						$this->set('General', 'previousScopeName', $oldScope);
						$this->debug(3, "go: Scope enter $oldScope -> $scopeName,  $oldOldScope -> $oldScope");
					}
				}
				if (!isset($oldScope))
				{
					$oldScope=false;
					$scopeName=$this->get('General', 'scopeName');
				}
				
				# Iterate through the actions to be taken
				foreach ($this->store['Macros'][$macroName] as $actionItem)
				{
					$entryNesting=$this->get('Core', 'nesting');
					
					$returnedValue1=$this->callFeature($actionItem['name'], $actionItem['value']);
					if (is_array($returnedValue1)) $returnedValue=$returnedValue1;
					
					$exitNesting=$this->get('Core', 'nesting');
					$this->setResultSet($returnedValue);
					if ($entryNesting!=$nesting) $this->debug(0, "go: WARNING entryNesting($entryNesting) != exitNesting($exitNesting) for macro $macroName/{$actionItem['name']}. This is certainly a bug! $macroName began with $nesting.");
				}
				$resultSet=$this->getResultSet();
				
				$this->unsetStackEntry($nesting);
				
				# Set the shared memory and scope back to the previous nesting level
				$this->decrementNesting();
				
				if ($oldScope)
				{
					# TODO a more long term soltion needs to be found to this. The correct way breaks because it is double handeling the special scope case.
					#$this->doUnset(array(localScopeVarName, $scopeName));
					unset ($this->store[localScopeVarName][$scopeName]);
					$this->set('General', 'scopeName', $oldScope);
					$this->set('General', 'previousScopeName', $oldOldScope);
					$this->debug(4, "go: Scope exit $scopeName -> $oldScope,  $oldScope -> $oldOldScope");
				}
				
				# If this is the default macro, we need to run the cleanup stuff
				if ($macroName=='default')
				{
					$this->callFeature('triggerEvent', 'Achel,finishEarly');
					$this->callFeature('triggerEvent', 'Achel,finishGeneral');
					$this->callFeature('triggerEvent', 'Achel,finishLate');
				}
				
				return $resultSet;
			}
			else
			{
				$obj=&$this->get('Features', 'helpDefault');
				if (is_object($obj))
				{
					$this->complain($this, "hmmmm, I don't think you asked me to do anything...");
					$this->module[$obj['provider']]->event('helpDefault');
					return $emptyResult;
				}
				else
				{
					$this->complain($this, "hmmmm, I don't think you asked me to do anything... But I'm also unable to load help. Alllllll BYYYY MY selllllllf...");
					return $emptyResult;
				}
			}
		}
		else
		{
			$obj=&$this->getFeature('helpDefault', 'go');
			
			if ($obj)
			{
				$this->complain($this, "Could not find macro '$macroName'. This can happen if you haven't asked me to do anything.");
				
				$this->module[$obj['provider']]->event('helpDefault');
			}
			else
			{
				$this->complain($this, "Could not find macro '$macroName' and couldn't find helpDefault. This generally begins if you haven't asked me to do anything, but to not find either suggests something is wrong with the cache. Clearing it may help.");
			}
			return $emptyResult;
		}
	}
	
	function &getFeature($featureName, $context='na/getFeature')
	{
		# To be used in more generic situations than assertAvailableMacro which is only for macros.
		$obj=&$this->get('Features', 'helpDefault');
		if ($obj) return $obj;
		else
		{
			if ($this->assertAvailableMacro($featureName, $context.'/getFeature'))
			{
				$obj=&$this->get('Features', 'helpDefault');
				return $obj;
			}
			else
			{
				$result=false;
				return $result;
			}
		}
	}
	
	function &getCategoryModule($category)
	{
		if (isset($this->store[$category])) return $this->store[$category];
		else
		{
			$output=array();
			return $output;
		}
	}
	
	function setCategoryModule($category, $contents)
	{
		$this->store[$category]=$contents;
		$this->markCategory($category);
	}
	
	function unsetCategoryModule($category)
	{
		unset($this->store[$category]);
	}
	
	function &get($category, $valueName, $debug=true, $nestingOffset=0)
	{
		if ($debug) $this->debug(5,"get($category, $valueName, false)");
		if (isset($this->store[$category]))
		{
			# TODO Can I use the key to refactor?
			if ($key=$this->getScopeForCategory($category, $nestingOffset))
			{
				$this->core->debug(4, "get: ($category, $valueName) key=$key nestingOffset=$nestingOffset");
				if ($category==nestedPrivateVarsName)
				{
					# TODO Is this really still needed?
					$nesting=$this->get('Core', 'nesting')+$nestingOffset;
					if (isset($this->store[$category])) 
					{
						for ($i=$nesting;$i>0;$i--)
						{
							if (isset($this->store[$category][$i][$valueName]))
							{
								$result=$this->store[$category][$i][$valueName];
								break;
							}
							else $result=null;
						}
					}
					else $result=null;
					$this->core->debug(5,"get (public): [$category][$nesting][$valueName] ".json_encode($result, JSON_FORCE_OBJECT));
				}
				else
				{
					$this->core->debug(4,"get ($key): [$category][$key][$valueName]");
					if (isset($this->store[$category][$key]))
					{
						if (isset($this->store[$category][$key][$valueName]))
						{
							$result=$this->store[$category][$key][$valueName];
						}
						else $result=null;
					}
					else $result=null;
				}
			}
			else
			{
				if (isset($this->store[$category][$valueName])) return $this->store[$category][$valueName];
				else
				{
					$result=null;
				}
			}
			
			if ($this->isVerboseEnough(5))
			{
				$this->core->debug(5,"get: [$category][$key][$valueName] ".json_encode($result));
			}
		}
		else
		{
			$result=null;
		}
		
		return $result;
	}
	
	function getMeVariableLevel($variableName, $startLevel)
	{
		// Returns the nesting level that the variable was found at and the value associated with it.
		// Returns false on failure.
		
		for ($i=$startLevel;$i>0;$i--)
		{
			if (isset($this->store[nestedPrivateVarsName][$i][$variableName]))
			{
				return array('level'=>$i, 'value'=>$this->store[nestedPrivateVarsName][$i][$variableName]);
			}
		}
		
		return false;
	}
	
	function returnMeVariables($srcNestingLevel, $dstNestingLevel)
	{
		/*
			$srcNestingLevel will typically be larger than $dstNestingLevel. There might be a useful exception to this, but I haven't thought of it yet.
		*/
		
		$haveSrc=false;
		$haveDst=false;
		
		// What do we have to work with?
		if (isset($this->store[nestedPrivateVarsName][$srcNestingLevel]))
		{
			$haveSrc=is_array($this->store[nestedPrivateVarsName][$srcNestingLevel]);
		}
		
		if (isset($this->store[nestedPrivateVarsName][$dstNestingLevel]))
		{
			$haveDst=is_array($this->store[nestedPrivateVarsName][$dstNestingLevel]);
		}
		
		
		if ($haveSrc)
		{
			# Loop through each variable at current nesting.
			foreach ($this->store[nestedPrivateVarsName][$srcNestingLevel] as $variableName=>$variableValue)
			{
				# Do we have it from an earlier nesting.
				if ($mePosition=$this->getMeVariableLevel($variableName, $srcNestingLevel-1))
				{
					# Is it different?
					if ($mePosition['value']!=$this->store[nestedPrivateVarsName][$srcNestingLevel][$variableName])
					{
						# Save it back.
						$this->store[nestedPrivateVarsName][$mePosition['level']][$variableName]=$this->store[nestedPrivateVarsName][$srcNestingLevel][$variableName];
					}
				}
			}
		}
		
		return true;
		
		
		// Let's do something with it.
		if ($haveSrc and $haveDst)
		{ // Need to merge.
			
		}
		elseif ($haveSrc and !$haveDst)
		{ // Need to replace.
			$this->store[nestedPrivateVarsName][$dstNestingLevel]=$this->store[nestedPrivateVarsName][$srcNestingLevel];
		}
		elseif ($haveDst and !$haveSrc)
		{ // Can ignore.
		}
	}
	
	function markCategory($category)
	{
		if ($category!=categoryMarker) $this->set(categoryMarker, $category, 'true');
	}
	
	function setIfNotSet($category, $valueName, $value, $orNothing=false)
	{
		$existingValue=$this->get($category, $valueName);
		
		if (
			(!$existingValue and $orNothing == true) or # setIfNothing
			(!$existingValue and $orNothing == false and $existingValue===null) # setIfNotSet
			)
		{
			$this->set($category, $valueName, $value);
			return true;
		}
		else return false;
	}
	
	function makeMeAvailable($variableName)
	{
		$nesting=$this->get('Core', 'nesting')-1;
		$variableDetials=$this->getMeVariableLevel($variableName,$nesting);
		
		if (!is_array($variableDetials))
		{ // It's not in scope, we need to make it so.
			$this->store[nestedPrivateVarsName][$nesting][$variableName]=false;
		}
	}
	
	function makeLocalAvailable($variableName)
	{
		$value=$this->get(localScopeVarName, $variableName);
		if ($value!=null)
		{
			$key=$this->getScopeForCategory(localScopeVarName);
			$this->doUnSet(array(localScopeVarName, $key, $variableName));
			$this->set(localScopeVarName, $variableName, $value, true);
			$this->core->debug(4,"makeLocalAvailable: name=$variableName key/scope=$key");
		}
	}
	
	function getScopeForCategory($category, $nestingOffset=0)
	{
		if ($category==nestedPrivateVarsName or $category==isolatedNestedPrivateVarsName or $category==localScopeVarName)
		{
			if ($category==localScopeVarName)
			{
				if ($nestingOffset==true) return $this->get('General', 'previousScopeName');
				else return $this->get('General', 'scopeName');
			}
			else return $this->get('Core', 'nesting')-$nestingOffset;
		}
		else return false;
	}
	
	function set($category, $valueName, $args, $nestingOffset=0)
	{ // set a variable for a module
		if ($this->isVerboseEnough(5))
		{
			$argsDisplay=(is_numeric($args) or is_string($args))?$args:gettype($args);
			$this->debug(5,"set($category, $valueName, $argsDisplay)");
		}
		
		if (!isset($this->store[$category])) $this->store[$category]=array();
		
		if ($key=$this->getScopeForCategory($category, $nestingOffset))
		{
			$this->core->debug(5,"set: [$category][$key][$valueName]");
			if (!isset($this->store[$category][$key])) $this->store[$category][$key]=array();
			$this->store[$category][$key][$valueName]=$args;
		}
		else
		{
			$this->store[$category][$valueName]=$args;
		}
		
		$this->markCategory($category);
	}

	function setRef($category, $valueName, &$args, $nestingOffset=0)
	{ // set a variable for a module
		$argString=(is_string($args))?$args:'[non-string]';
		$this->debug(5,"setRef($category, $valueName, $argString)");
		if (!isset($this->store[$category])) $this->store[$category]=array();
		
		if ($key=$this->getScopeForCategory($category, $nestingOffset))
		{
			if (!isset($this->store[$category][$key])) $this->store[$category][$key]=array();
			$this->store[$category][$key][$valueName]=&$args;
			$this->core->debug(5,"setRef: [$category][$key][$valueName]");
		}
		else
		{
			$this->store[$category][$valueName]=&$args;
		}
		
		$this->markCategory($category);
	}
	
	function doUnSet($deleteList)
	{
		return $this->doUnsetNested($this->store, $deleteList);
	}
	
	function doUnsetNested(&$currentScope, $deleteList, $position=0)
	{
		// Handel Local, Me and Isolated.
		$listKeys=array_keys($deleteList);
		if ($key=$this->getScopeForCategory($deleteList[$listKeys[0]]))
		{
			$category=$deleteList[$listKeys[0]];
			$deleteList[$listKeys[0]]=$key;
			
			return $this->doUnsetNested($this->store[$category], $deleteList);
		}
		
		// Handel everything else.
		if (is_string($deleteList))
		{
			$this->debug(0, "doUnsetNested: WARNING! Converted string to array. The string was \"$deleteList\", which was expected to be an absolute path in the form of an array. You can do this in PHP like so: array('CategoryName', 'subCategory', 'variable'). Ideally execution should stop here, but it is being allowed incase there is still some old code relying on this behavior (PHP would have complained bitterly). This decision will be reversed very soon, so please fix the bug if it is yours. If you think the bug is in Achel, please get in contact via github.");
			$deleteList=explode(',', $deleteList);
		}
		
		if (!isset($currentScope[$deleteList[$position]]))
		{
			$fullChain=implode(',', $deleteList);
			$failedChain='';
			
			foreach ($deleteList as $item)
			{
				$failedChain=($failedChain)?"$failedChain,$item":$item;
			}
			
			$this->debug(3, "doUnsetNested: Could not find \"{$deleteList[$position]}\" in $fullChain. Successfully got to \"$failedChain\". This is unlikely to be a problem, but it worth considering when debugging.");
			return false;
		}
		
		$fullChain=implode(',', $deleteList);
		$newPosition=$position+1;
		if (count($deleteList)>$newPosition)
		{ // Recurse into the currentScope
			return $this->doUnsetNested($currentScope[$deleteList[$position]], $deleteList, $newPosition);
		}
		else
		{ // Time to delete
			$this->debug(4, "doUnsetNested: Deleting {$deleteList[$position]} in $fullChain");
			unset($currentScope[$deleteList[$position]]);
			return true;
		}
	}
	
	function getNested($values)
	{
		$output=$this->store;
		
		// Handel variables with special scope.
		$valueKeys=array_keys($values);
		$category=$values[$valueKeys[0]];
		if ($key=$this->getScopeForCategory($category))
		{
			if ($this->isVerboseEnough(3))
			{
				$this->core->debug(3,"getNested: $category,$key,".implode(',', $values));
			}
			if (isset($output[$category][$key])) $output=$output[$category][$key];
			
			# TODO There must be a better way to remove a key. unset() didn't work because it left the empty key and the index started at 1 instead of 0. Possibly the second part is actually what mattered.
			$oldValues=$values;
			$numberOfPathParts=count($values);
			$values=array();
			for ($i=1;$i<$numberOfPathParts;$i++)
			{
				$values[]=$oldValues[$i];
			}
		}
		
		// Do the normal nested lookup.
		foreach ($values as $value)
		{
			if (isset($output[$value]))
			{
				$output=$output[$value];
			}
			else
			{
				$this->core->debug(4, "getNested: Could not find \"$value\" using key ".implode(',', $values));
				$output=null;
				return $output;
			}
		}
		
		return $output;
	}
	
	
	
	
	/*
		setNested traditionaly has quite a messey input structure as it was a transition from the two level addressing to the multi-layer addressing.
		
		Therefore, there needs to be a little mess to tidy everything up.
		
		Short term
			setNestedViaPath
				The new, future-proof way of doing it. Any new code should use this, and old code should be ported to this.
			setNested
				The behavior is DEPRECATED. This function will be repurposed. For quickly getting things working without a debug0, use setNestedOldWay. But please update the code to use setNestedViaPath as soon as possible.
			setNestedJFDI - DEPRECATED
				This was a stop-gap to get past the limitatons of the old way and is therefore no longer needed. Please port to setNestedViaPath.
			setNestedFromInterpreter
				This is potentially short term and should not be relied on. It's intended for taking input directly from the interpreter.
			setNestedOldWay - DEPRECATED
				This takes input using the old way and converts it to input for the new way. It is _HEAVY_. Please consider this a very short term fix to get rid of the debug0. You should port to setNestedViaPath.
		
		Long term
			setNestedViaPath
				As above.
			setNested
				Will point to setNestedViaPath.
			setNestedFromInterpreter
				May still be in use.
		
	*/
	
	
	function setNested($store, $category, $values)
	{
		# TODO This currently points to the old way of doing it, which is deprecated. Once the old way is removed it will point to setNestedViaPath.
		$this->debug(0, "DEPRECATED: This code is using a deprecated version of setNested() which will soon be removed. Please update the code to point to setNestedViaPath() (which will point to setNested once the old version is removed.)");
		$this->setNestedOldWay($store, $category, $values);
	}
	
	function setNestedJFDI($path, $value=null) # DEPRECATED
	{
		# TODO port this to the new setNested.
		
		/* 
		setNested has a funny input structure which it has inherited from the historical strictly 2 layer Store/variable structure. Eventually this will be removed. In the mean time setNestedJFDI removes that complication.
		
		If $value is absent, it will be assumed to be part of $path. If there are fewer than 2 total parameters between $path and $value, interpretParms will complain.
		*/
		
		$fullInput=($value!==null)?"$path,$value":$path;
		
		$parms=$this->interpretParms($fullInput, 2, 3, false);
		$this->setNested($parms[0], $parms[1], $parms[2]);
	}
	
	
	function setNestedViaPath($path, $value)
	{
		$this->setNestedStart($path, $value);
	}
	
	private function setNestedFromInterpreter($allValues)
	{
		$parts=$this->interpretParms($allValues);
		if (count($parts)==2)
		{
			$this->setNestedViaPath($parts[0], $parts[1]);
		}
		else
		{
			$path=$parts;
			$lastPosition=count($path)-1;
			if ($lastPosition==-1)
			{
				$this->debug(1, "setNestedFromInterpreter: Could not interpret the path provied (\"$allValues\") at all. This is likely a syntax error in the address.");
				return false;
			}
			
			if (isset($path[$lastPosition]))
			{
				$value=$path[$lastPosition];
				unset($path[$lastPosition]);
				
				
				$this->setNestedStart($path, $value);
				return true;
			}
			else
			{
				$this->debug(1, "setNestedFromInterpreter: Could not interpret the path provied (\"$allValues\"). This suggests that interpretParms was unable to interpret it.");
				return false;
			}
		}
	}
	
	function setNestedOldWay($store, $category, $values) # DEPRECATED
	{
		$this->debug(1, "DEPRECATED: This code is using a deprecated version of setNested() which will soon be removed. Please update the code to point to setNestedViaPath() (which will point to setNested once the old version is removed.)");
		
		$traditionalAddress=array($store, $category);
		$remainingStuff=(is_array($values))?$values:explode(',', $values);
		
		$lastPosition=count($remainingStuff)-1;
		$value=$remainingStuff[$lastPosition];
		
		unset($remainingStuff[$lastPosition]);
		
		$fullAddress=array_merge($traditionalAddress, $remainingStuff);
		
		$this->setNestedViaPath($fullAddress, $value);
		$this->markCategory($store);
	}
	
	private function setNestedStart($path, $value)
	{
		/*
		path can be either a string like Example,folder1,folder2 or an array like ('Example', 'folder1', 'folder2')
		value is what ever you want to set at the end.
		*/
		
		$pathParts=(is_array($path))?$path:explode(',', $path);
		
		# $this->core->debug(0, implode(',', $pathParts));
		# TODO Add shortcuts
		
		// Handel variables with special scope.
		$pathKeys=array_keys($pathParts);
		$category=$pathParts[$pathKeys[0]];
		if ($key=$this->getScopeForCategory($category))
		{
			if (!isset($this->store[$category][$key])) $this->store[$category][$key]=array();
			
			# TODO There must be a better way to remove a key. unset() didn't work because it left the empty key and the index started at 1 instead of 0. Possibly the second part is actually what mattered.
			$oldPathPaths=$pathParts;
			$numberOfPathParts=count($pathParts);
			$pathParts=array();
			for ($i=1;$i<$numberOfPathParts;$i++)
			{
				$pathParts[]=$oldPathPaths[$i];
			}
			
			if ($this->isVerboseEnough(4))
			{
				$this->core->debug(4,"setNestedStart: Used $category,$key,".implode(',', $pathParts)).' '.json_encode($pathParts);
			}
			$output=&$this->store[$category][$key];
		}
		else $output=&$this->store;

		$this->setNestedWorker($output, $pathParts, $value, count($pathParts));
		
		$this->markCategory($pathParts[0]);
	}
	
	private function setNestedWorker(&$initialValue, $path, &$value, $count=0, $position=0)
	{
		if ($position<$count-1)
		{
			$this->debug(5, "setNestedWorker: processing $position/$count {$path[$position]}");
			
			# Make sure we have a sane place to continue
			$lastkey=$path[$position];
			if (!isset($initialValue[$path[$position]]))
			{
				if ($path[$position]==='')
				{
					$initialValue[]=array();
					$keys=array_keys($initialValue);
					$lastkey=$keys[count($keys)-1];
				}
				else $initialValue[$path[$position]]=array();
			}
			elseif (!is_array($initialValue[$path[$position]]))
			{
				if ($path[$position]==='')
				{
					$initialValue[]=array();
					$keys=array_keys($initialValue);
					$lastkey=$keys[count($keys)-1];
				}
				else $initialValue[$path[$position]]=array();
			}
			
			$this->setNestedWorker($initialValue[$lastkey], $path, $value, $count, $position+1);
		}
		else
		{
			# set the value
			if ($path[$position]==='') $initialValue[]=$value;
			else $initialValue[$path[$position]]=$value;
			
			#print_r($value);
			
			# TODO looks like some optimisation can be done here.
			/*
			$tmpValue=$this->getNested($path);
			$vcount=count($value);
			$tcount=count($tmpValue);
			
			$destination=implode(',', $path);
			
			
			$this->debug(4, "setNestedWorker: Setting value $position/$count {$path[$position]}. vcount=$vcount tcount=$tcount destination=$destination");
			*/
		}
	}
	
	
	
	
	
	function addItemsToAnArray($category, $valueName, $items)
	{
		$currentList=$this->get($category, $valueName);
		if (is_array($currentList)) $output=array_merge($currentList, $items);
		else $output=$items;
		
		$this->set($category, $valueName, $output);
		return $output;
	}
	
	function delete($category, $valueName)
	{
		if (isset($this->store[$category]))
		{
			if (isset($this->store[$category][$valueName])) 
			{
				unset($this->store[$category][$valueName]);
				$this->debug(4,"delete($category, $valueName) - deleted");
			}
			else  $this->debug(5,"delete($category, $valueName) - valueName not found");
		}
		else $this->debug(5,"delete($category, $valueName) - category not found");
	}
	
	function getStore()
	{ # Note that this returns a COPY of the store. It is not intended as a way of modifying the store.
		$this->debug(5,"getStore()");
		return $this->store;
	}
	
	function registerModule(&$obj)
	{
		$name=$obj->getName();
		if (isset($this->module[$name]))
		{
			echo "Module $name is already loaded.\n";
			return false;
		}
		
		$this->module[$name]=&$obj;
		$this->module[$name]->setCore($this);
		$this->setNestedViaPath("Modules,$name,path", $this->get('Modules', 'currentModulePath'));
		return true;
	}
	
	function registerSubModule(&$obj)
	{
		$obj->setCore($this);
		
		$subModule=array(
			'name'=>$obj->getName(),
			'description'=>$obj->getDescription(),
			'category'=>$obj->getCategory(),
			'obj'=>&$obj,
			);
		
		$this->debug(4, "registerSubModule: name={$subModule['name']} category={$subModule['category']}");
		$this->setRef($subModule['category'], $subModule['name'], $subModule);
	}
	
	
	function registerFeature(&$obj, $flags, $name, $description, $tags=false,$isMacro=false, $source='unknown')
	{
		$this->core->debug(4, "registerFeature name=$name");
		$arrayTags=(is_array($tags))?$tags:explode(',', $tags);
		if (!count($arrayTags))
		{
			$arrayTags[]='undefined';
		}
		// $arrayTags[]=$name; # I'm not convinced this is a good item. It means we are going to have a stupid amount of tags that are only used once.
		$arrayTags[]='all';
		$arrayTags[]=$obj->getName();
		$this->registerTags($name, $arrayTags);
		$tagString=implode(',', $arrayTags);
		
		# TODO Remove the tag string from descriptoin once we have proper integration with help
		$entry=array('flags'=>$flags, 'name'=>$name, 'description'=>$description, 'tagString'=>$tagString, 'provider'=>$obj->getName(), 'isMacro'=>$isMacro, 'source'=>$source, 'referenced'=>0);
		
		foreach ($flags as $flag)
		{
			if (!isset($this->store['Features'][$flag]))
			{
				$this->setRef('Features', $flag, $entry);
			}
			else
			{
				if ($this->get('General', 'complainAboutDuplicateMacros'))
				{
					$existing=$this->get('Features', $flag);
					$existingName=$existing['obj']->getName();
					$this->complain($obj, "Feature $flag has already been registered by $existingName");
				}
			}
		}
	}
	
	function aliasFeature($feature, $flags)
	{
		$entry=&$this->get('Features', $feature);
		$macroCacheEntry=$this->get('MacroListCache', $feature);
		
		foreach ($flags as $flag)
		{
			if (!isset($this->store['Features'][$flag]))
			{
				$this->core->debug(4, "Aliasing $flag => $feature");
				$this->setRef('Features', $flag, $entry);
				$entry['flags'][]=$flag;
				$this->setRef('FeatureAliases', $flag, $entry['name']);
			}
			elseif ($flag==$feature)
			{}
			else
			{
				if ($this->get('General', 'complainAboutDuplicateMacros')!==false)
				{
					$existing=$this->get('Features', $flag);
					$this->complain($this, "Feature $flag has already been registered by {$existing['provider']}({$existing['source']})");
				}
			}
			
			if ($macroCacheEntry)
			{
				$this->set('MacroListCache', $flag, $macroCacheEntry);
			}
		}
	}
	
	function setFeatureAttribute($featureName, $attributeName, $attributeValue)
	{
		$entry=&$this->get('Features', $featureName);
		$entry[$attributeName]=$attributeValue;
	}
	
	function registerTags($name, $tags)
	{
		$arrayTags=(is_array($tags))?$tags:explode(',', $tags);
		foreach ($arrayTags as $tag)
		{
			if ($tag)
			{
				$names=$this->get('Tags', $tag);
				if (!is_array($names)) $names=array();
				
				$names[]=$name;
				$this->set('Tags', $tag, $names);
			}
		}
	}
	
	function callInits($event='init')
	{
		# TODO The initMap is not working. Symptom: the Packages module (and potentially others) get inited twice. I've worked around this by putting in a condition around the $callInits variable in the loadModules function. This situation arises when calling loadModules from the packages.php module. 
		if (!isset($this->initMap[$event])) $this->initMap[$event]=array();
		foreach ($this->module as $name=>&$obj)
		{
			if (!isset($this->initMap[$event][$name]))
			{
				$obj->event($event);
				$this->initMap[$event][$name]=true;
			}
		}
	}
	
	function complain($obj, $message, $specific='', $fatal=false)
	{
		$output=($specific)?"$specific: $message":"$message.";
		if ($obj) $output=$obj->getName().': '.$output;
		
		if ($fatal) die("$output\n");
		else $this->debug(0, "$output");
	}
	
	function requireNumParms($obj, $numberRequried, $event, $originalParms, $interpretedParms=false)
	{
		$parmsToCheck=($interpretedParms)?$interpretedParms:$this->interpretParms($originalParms);
		$actualParms=count($parmsToCheck);
		
		if ($numberRequried>$actualParms) 
		{
			$this->complain($obj, "Required $numberRequried parameters but got $actualParms. Original parms were \"$originalParms\" for", $event, true);
			return false;
		}
		else return true;
	}
	
	function getRequireNumParmsOrComplain($obj, $featureName, $numberRequried)
	{
		$originalParms=$this->get('Global', $featureName);
		$interpretedParms=$this->interpretParms($originalParms);
		
		if ($this->requireNumParms($obj, $numberRequried, $featureName, $originalParms, $interpretedParms))
		{
			return $interpretedParms;
		}
		else
		{
			return false;
		}
	}
	
	function out($output)
	{
		if (isset($this->store['General']['outputObject']))
		{
			$this->store['General']['outputObject']->out($output);
		}
		else
		{
			if (is_string($output)) echo programName."/noOut: $output\n";
			else
			{
				echo programName."/noOut: print_r output follows:\n";
				print_r($output);
			}
		}
	}
	
	function echoOut($output)
	{
		if (isset($this->store['General']['echoObject']))
		{
			$this->store['General']['echoObject']->put(array($output), 'default');
		}
		else
		{
			if (is_string($output)) echo programName."/noEcho: $output\n";
			else
			{
				echo programName."/noEcho: print_r output follows:\n";
				print_r($output);
			}
		}
	}
	
	function now()
	{
		return 'I need to implement this!';
	}
	
	function getPID($parms)
	{
		$this->set($parms[0], $parms[1], strval(getmypid()));
	}
	
	function objectToArray($data)
	{
		if (!is_array($data) and !is_object($data)) return $data;
		$result = array();
		
		$data = (array) $data;
		foreach ($data as $key => $value)
		{
			$newKey=$this->cleanKey($key);
			if (is_object($value) or is_array($value)) $result[$newKey] = $this->objectToArray($value);
			else $result[$newKey] = $value;
		}
		
		return $result;
	}
	
	function cleanKey($key)
	{
		$result=str_replace("\0", '', $key);
		$result=preg_replace(array('/^\*_/'), array(''), $result);
		return $result;
	}
}

function loadModules(&$core, $sourcePath, $callInits=true)
{
	$loadStartTime=microtime(true);
	
	foreach ($core->getModules($sourcePath) as $path)
	{
		$path=$path;
		if (file_exists($path))
		{
			#echo "Loading $path\n";
			$filenameParts=explode('.', $path);
			$numParts=count($filenameParts);
			$lastPos=($numParts>1)?$numParts-1:0;
			
			if ($filenameParts[$lastPos]=='php'or $filenameParts[$lastPos]=='module')
			{
				$core->set('Modules', 'currentModulePath', $path);
				include ($path);
				$core->doUnset(array('Modules', 'currentModulePath'));
			}
		}
		else
		{
			echo "Didn't find $path\n";
		}
	}
	
	$initStartTime=microtime(true);
	
	if ($callInits)
	{
		$core->callInits(); // Basic init only
		$core->callInits('followup'); // Any action that needs to be taken once all modules are loaded.
		$core->callInits('last'); // Any action that needs to be taken once all modules are loaded.
	}
	
	$finishTime=microtime(true);
	
	$loadTime=$initStartTime-$loadStartTime;
	$initTime=$finishTime-$initStartTime;
	$total=$finishTime-$loadTime;
	
	$core->debug(4, "Loaded modules in $loadTime. Initalised modules in $initTime. Total=$total.");
}




class Module
{
	private $category=''; 
	protected $core=null;
	
	/*
		A note about name vs category
			For a module it makes sense that these are the same thing, because a module's memory space is reporesented by the name of that module. Therefore the variables are mapped like this
			
				getName		$this->category
	*/
	
	# TODO consider refactoring the name/category relationship in Module. This is likely to have a lot of stuff refering to it, and probably isn't worth while.
	
	function __construct($name)
	{
		$this->category=$name;
	}
	
	function getName()
	{
		return $this->category;
	}
	
	function setCore(&$core)
	{
		$this->core=&$core;
	}
}

class SubModule extends Module
{
	protected $name='unknown';
	protected $description='Who am I?';
	protected $category='Unknown';
	
	/*
		A note about name vs category
			For a module it makes sense that these are the same thing, because a module's memory space is reporesented by the name of that module.
			
			For a submodule this doesn't make sense anymore because it doesn't currently have its own designated memory space and name is needed as well as category. Therefore the variables are mapped like this
			
				getName		$this->name
				getDescription	$this->description
				getCategory	$this->category
	*/
	
	function __construct($category)
	{
		$this->category=$category;
	}
	
	function getName()
	{
		return $this->name;
	}
	
	function getDescription()
	{
		return $this->description;
	}
	
	function getCategory()
	{
		return $this->category;
	}
	
	function setIdentity($name, $description)
	{
		$this->name=$name;
		$this->description=$description;
	}
}
 
?>

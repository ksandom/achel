<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.

# Useful stuff for manipulating the core
define('macroLineTerminator', ';');

class Macro extends Module
{
	private $lastCreatedMacro=null;

	function __construct()
	{
		parent::__construct('Macro');
	}

	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('singleLineMacro'), 'singleLineMacro', 'Define and run a macro. --macro=macroName:"command1=blah;command2=wheee"');
				$this->core->registerFeature($this, array('macro'), 'macro', 'Define and run a macro. --macro=macroName:"command1=blah\ncommand2=wheee"');
				$this->core->registerFeature($this, array('defineSingleLineMacro'), 'defineSingleLineMacro', 'Define a macro. --defineMacro=macroName:"command1=blah;command2=wheee"');
				$this->core->registerFeature($this, array('defineMacro'), 'defineMacro', 'Define a macro. --defineMacro=macroName:"command1=blah\ncommand2=wheee"');
				$this->core->registerFeature($this, array('runMacro'), 'runMacro', 'Run a macro. --runMacro=macroName');
				$this->core->registerFeature($this, array('listMacros'), 'listMacros', 'List all macros');
				$this->core->registerFeature($this, array('function'), 'function', 'Create a function. --function=functionName,functionCode . functionCode is normally indented on the next line. See Syntax/readme.md for a full breakdown of how to use it.');
				$this->core->registerFeature($this, array('loop', 'loopMacro'), 'loop', 'Loop through a resultSet. The current iteration of the resultSet is accessed via STORE variables under the category Result. The key for a given iteration will be stored in Result,key and if the value is a string it will be stored in Result,line. Otherwise all array entries will be directly accessile directly in Result. See loopMacro.md for more information. --loop=[Category,]macroName . Category is where the data for current iteration of the loop will be stored. If omitted, it will be Result. Note that if you specify a Category from a macro, you should have a trailing comma before the indented code block; like this: loop Example,', array('loop', 'iterate', 'resultset')); # TODO This should probably move to a language module
				$this->core->registerFeature($this, array('loopLite'), 'loopLite', 'Loop through a resultSet without passing the whole source resultSet arround in each iteration. For most use-cases, this will be what you want. The current iteration of the resultSet is accessed via STORE variables under the category Result. See loopMacro.md for more information. --loop=macroName[,parametersForTheMacro]', array('loop', 'iterate', 'resultset'));
				$this->core->registerFeature($this, array('forEach'), 'forEach', "For each result in the resultSet, run this command. The whole resultSet will temporarily be set to the result in the current iteration, and the resultSet of that iteration will replace the original result in the original resultSet. Basically it's a way to work with nested results and be able to send their results back. --foreEach=feature,value", array('loop', 'iterate', 'resultset')); # TODO This should probably move to a language module

				$this->core->registerFeature($this, array('getProgressKey'), 'getProgressKey', "Progress information is now stored in a unique location for each level nesting so that nested loops in the same macro can operate without interfereing with each others' progress information.", array('loop', 'iterate', 'resultset'));
				$this->core->registerFeature($this, array('loadMacro'), 'loadMacro', "Load a macro from disk. This is not currenly for general use.", array('hidden'));
				$this->core->registerFeature($this, array('loadAllMacros'), 'loadAllMacros', "Load all macros from disk. This is not currenly for general use.", array('hidden'));
				$this->core->registerFeature($this, array('loadMacroFromFile'), 'loadMacroFromFile', "Load a acros from a file. --loadMacroFromFile=pathToFile . This is not currenly for general use.", array('hidden'));
				break;
			case 'singleLineMacro':
				$this->defineMacro($this->core->get('Global', $event), true);
				return $this->runMacro($this->lastCreatedMacro);
				break;
			case 'macro':
				$this->defineMacro($this->core->get('Global', $event));
				return $this->runMacro($this->lastCreatedMacro);
				break;
			case 'defineSingleLineMacro':
				$this->defineMacro($this->core->get('Global', $event), true);
				break;
			case 'defineMacro':
				$this->defineMacro($this->core->get('Global', $event));
				break;
			case 'runMacro':
				return $this->runMacro($this->core->get('Global', $event));
			case 'function':
				break;
			case 'listMacros':
				return $this->listMacros();
				break;
			case 'loop':
				$parms=$this->core->interpretParms($this->core->get('Global', $event));
				return $this->loopMacro($this->core->getResultSet(), $parms);
			case 'loopLite':
				$parms=$this->core->interpretParms($this->core->get('Global', $event));
				return $this->loopMacro($this->core->getResultSet(), $parms, true);
			case 'forEach':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 1);
				return $this->doForEach($this->core->getResultSet(), $parms[0], $parms[1]);
			case 'followup':
				$this->followup();
				break;
			case 'getProgressKey':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$this->core->set($parms[0], $parms[1], $this->getProgressKey('previousScopeName'));
				break;
			case 'loadMacro':
				$macroName=$this->core->get('Global', $event);
				$this->loadMacro($macroName);
				break;
			case 'loadAllMacros':
				$this->loadSavedMacros(true);
				break;
			case 'loadMacroFromFile':
				$this->loadMacroFromFile($this->core->get('Global', $event));
				break;
			case 'last':
				break;
			default:
				return $this->runMacro($event);
				break;
		}
	}

	function defineMacro($macro, $useSemiColon=false, $macroName=false, $quiet=false)
	{
		# This case happens when we load all macros in a cached environment.
		if ($this->core->get('MacroRawContents', $macroName) and $quiet) return true;

		# Get macroName
		if (!$macroName)
		{
			$endOfName=strPos($macro, ':');
			$macroName=trim(substr($macro, 0, $endOfName));
			$actualMacro=trim(substr($macro, $endOfName+1));
		}
		else $actualMacro=$macro;
		$this->lastCreatedMacro=$macroName;

		if (!is_array($actualMacro)) $actualMacro=explode("\n", $actualMacro);

		$preCompile=array();

		if ($useSemiColon)
		{
			# Strip out new line characters and split into lines using ;
			$lines=explode(';', implode('', $actualMacro));
		}
		else
		{
			# Split into lines using \n
			$lines=$actualMacro;
		}

		# Precompile macro into a nested array of commands.
		$obj=null; # TODO check this. It may be needed in more places.
		$lineNumber=0;
		foreach ($lines as $line)
		{
			$lineNumber++;

			if (!trim($line)) continue;

			$lineWithoutIndentation=ltrim($line);
			$offset=strlen($line) - strlen($lineWithoutIndentation) + 1;
			$endOfArgument=strPos($line, ' ', $offset);
			if ($endOfArgument)
			{
				$argument=substr($line, 0, $endOfArgument);
				$value=trim(substr($line, $endOfArgument+1));
			}
			else
			{
				$argument=$line;
				$value='';
			}


			switch ($argument)
			{
				case '#':
					break;
				case '':
					break;
				case '#onDefine':
					$parts=$this->core->splitOnceOn(' ', $value);
					$this->debug(3, "#onDefine {$parts[0]}={$parts[1]}");
					$this->core->callFeature($parts[0], $parts[1]);
					break;
				case '#onLoaded':
					$parts=$this->core->splitOnceOn(' ', $value);
					$this->debug(3, "#onLoaded {$parts[0]}={$parts[1]}");
					$this->core->callFeature("registerForEvent", "Macro,allLoaded,$parts[0],$parts[1]");
					break;
				default:
					//$this->core->addAction($argument, $value, $macroName);
					$preCompile[]=array(
						'argument'=>$argument,
						'value'=>$value,
						'nesting'=>array(),
						'macroName'=>$macroName,
						'lineNumber'=>$lineNumber
						);
					break;
			}
		}

		if ($this->core->get("Macro", "lazyLoaded")==$macroName)
		{
			$this->core->registerFeature($this, array($macroName), $macroName, "Macro loaded at runtime for external scripts.", array('dontCacheThis'));
		}

		if (!$this->compileFromArray($macroName, $preCompile))
		{
			$this->debug(0, "defineMacro($macroName): Failed to define the macro.");
		}
	}

	function compileFromArray($macroName, $inputArray)
	{
		$outputArray=array();
		$nesting=new Nesting($macroName, $this->core);

		# Figure out nesting
		$lastRootKey=null;
		foreach($inputArray as $key=>$action)
		{
			$nestingLevel=$nesting->getNestingForLine($action['argument']);
			if ($nesting->isBroken())
			{
				$this->debug(0, "compileFromArray($macroName:${action['lineNumber']}): Got a nesting error. Not going to try any further with macro $macroName.");

				return false;
			}

			$firstCharacter=substr($action['argument'], 0, 1);
			if ($nestingLevel)
			{
				if (!is_null($lastRootKey))
				{ // We have indentation. Remove 1 layer of indentation, and nest the argument.
					$this->debug(4, "compileFromArray($macroName:${action['lineNumber']}): Nested feature inside $macroName:{$outputArray[$lastRootKey]['lineNumber']}:{$outputArray[$lastRootKey]['argument']}: \"${action['argument']} ${action['value']}\"");
					$action['argument']=$nesting->removeOneIndent($action['argument']);
					$outputArray[$lastRootKey]['nesting'][]=$action;
				}
				else
				{ // We have indentation, but no argument to nest it in. This is fatal.
					$this->debug(0, "compileFromArray($macroName:${action['lineNumber']}): Syntax error: Indentation (level=$nestingLevel) without any features beforehand. The derived line was \"${action['argument']} ${action['value']}\"");

					return false;
				}
			}
			elseif ($firstCharacter=='#')
			{
			}
			else
			{
				$this->debug(4, "compileFromArray($macroName:${action['lineNumber']}): Root feature \"${action['argument']} ${action['value']}\"");
				$lastRootKey=$key;
				$outputArray[$lastRootKey]=$action;
			}
		}

		# Compile
		foreach($outputArray as $key=>$action)
		{
			$obj=&$this->core->get('Features', $action['argument']);

			# Handle any nesting
			if (count($action['nesting']))
			{
				$subName="$macroName--{$action['lineNumber']}";

				$macroPath=$this->core->get('MacroListCache', $macroName);
				$this->core->set('MacroListCache', $subName, $macroPath);

				$subAliases=array($subName);
				if ($action['argument']=='function')
				{
					$parts=$this->core->splitOnceOn(',', $action['value']);
					$subAliases[]=$parts[0];
				}

				$this->core->registerFeature($this, $subAliases, $subName, "Derived macro for $macroName", "$macroName,hidden", true, 'nesting');
				if (!$outputArray[$key]['nesting']=$this->compileFromArray($subName, $action['nesting']))
				{
					$this->debug(0, "Macro/compileFromArray/$macroName/$key: Received failure from nested code.");
					return false;
				}

				$this->core->addAction(trim($action['argument']), $action['value'].$subName, $macroName, $action['lineNumber']);
			}
			else
			{
				# TODO follow through to automatically load the macro if it doesn't exist.
				$this->core->addAction(trim($action['argument']), $action['value'], $macroName, $action['lineNumber']);
			}
		}

		$this->debug(4, "Macro/compileFromArray/$macroName: Ready to return the macro.");
		return $outputArray;
	}

	function runMacro($macroName)
	{
		return $this->core->go($macroName);
	}

	function listMacros()
	{
		$store=$this->core->getStore();
		$output=array();
		if (!isset($store['Macros'])) return $output;
		foreach ($store['Macros'] as $macroName=>$macro)
		{
			$output[]=$macroName;
		}
		return $output;
	}

	function loopMacro($input, $paramaters, $clearResultSetOnStart=false)
	{
		if (isset($paramaters[1]))
		{
			$category=$paramaters[0];
			$feature=$paramaters[1];
		}
		else
		{
			$category='Result';
			$feature=$paramaters[0];
		}

		$output=array();
		$firstComma=strpos($feature, ',');
		if ($firstComma!==false)
		{
			$macroName=substr($feature, 0, $firstComma);
			$macroParms=substr($feature, $firstComma+1);;
		}
		else
		{ // We haven't been passed any custom variables
			$macroName=$feature;
			$macroParms='';
		}

		if (!$macroName)
		{
			$this->core->complain($this, "No macro specified.");
			return false;
		}

		if (is_array($input))
		{
			if ($clearResultSetOnStart)
			{
				$this->core->setResultSetNoRef(array(), 'loop');
			}


			# TODO track previous and next
			$shouldUpdateProgress=$this->initProgress($input);
			$keys=array_keys($input);
			foreach ($keys as $position=>$key)
			{
				$in=$input[$key];
				if ($shouldUpdateProgress) $this->updateProgress();
				$this->debug(5, "loopMacro iterated for key $key");

				# Create Result category for referencing the current position in the resultSet.
				if (is_array($in)) $this->core->setCategoryModule($category, $in);
				else
				{
					$this->core->setCategoryModule($category, array());
					$this->core->set($category, 'line', $in);
				}

				# Add the current key
				$this->core->set($category, 'key', $key);

				# Add the surrounding keys
				if (isset($keys[$position-1])) $this->core->set($category, 'previousKey', $keys[$position-1]);
				if (isset($keys[$position+1])) $this->core->set($category, 'nextKey', $keys[$position+1]);

				# The environment is setup. Let's do the work.
				$this->core->callFeature($macroName, $macroParms);
				$result=$this->core->getCategoryModule($category);
				if (count($result)==1) $single=(isset($result['line']));
				else $single=false;

				# This data is unlikely to be useful in most situations. So we shouldn't polute arbitrary data with it.
				if (isset($result['previousKey'])) unset ($result['previousKey']);
				if (isset($result['nextKey'])) unset ($result['nextKey']);

				# Put the data back ready to be sent back to the ResultSet
				if ($single)
				{
					$output[$key]=$result['line'];
				}
				else
				{
					if (count($result)) $output[$key]=$result;
					else $this->debug(4, "loopMacro: Skipped key \"$key\" since it looks like it has been unset.");
				}
			}
			if ($shouldUpdateProgress) $this->removeProgress();

			# TODO remove Result
		}
		else $this->debug(5, "loopMacro: No input!");

		return $output;
	}

	function getProgressKey($scopeNameKey='scopeName')
	{
		return 'progress-'.$this->core->get('General', $scopeNameKey);
	}

	function doForEach($data, $feature, $value)
	{
		$output=array();

		$shouldUpdateProgress=$this->initProgress($data);
		foreach ($data as $line)
		{
			if ($shouldUpdateProgress) $this->updateProgress();
			if ($returnValue=$this->core->callFeatureWithDataset($feature, $value, $line))
			{
				$output[]=$returnValue;
			}
			else $output[]=$line;
		}
		if ($shouldUpdateProgress) $this->removeProgress();

		return $output;
	}

	function isInitialised($progressKey)
	{
		$data=$this->core->get('ProgressData', $progressKey);

		return (is_array($data));
	}

	function initProgress(&$data)
	{
		if (!$this->isInitialised($this->getProgressKey()))
		{
			$this->core->set('ProgressData', $this->getProgressKey(), array('position'=>0, 'total'=>count($data), 'remaining'=>0));
			return true;
		}
		else return false;
	}

	function updateProgress()
	{
		$progressKey=$this->getProgressKey();
		$progress=$this->core->get('ProgressData', $progressKey);

		# Work around corner cases which cause progress data to be missing.
		if (!$progress) # TODO It would be better to find all the cases where this is needed, as this will currently leave to unusual progress in those cases.
		{
			$emptyData=array();
			$this->initProgress($emptyData);
			$progress=$this->core->get('ProgressData', $progressKey);
			$progress['notes']="Fudged progress data to cover corner cases. Grep for this string to find it.";
		}

		$progress['position']++;
		$progress['remaining']=$progress['total']-$progress['position'];
		$this->core->set('ProgressData', $progressKey, $progress);
	}

	function removeProgress()
	{
		$this->core->doUnset(array('ProgressData', $this->getProgressKey()));
	}

	function getFileList()
	{
		$profile=$this->core->get('General', 'profile');
		$fileList=$this->core->addItemsToAnArray('Core', 'macrosToLoad', $this->core->getFileList($this->core->get('General', 'configDir')."/profiles/$profile/macros"));

		return $fileList;
	}

	function getJustMacros($fileList)
	{
		$output=array();

		foreach ($fileList as $fileName=>$fullPath)
		{
			if($fileName=='*') break;

			$nameParts=explode('.', $fileName);
			if ($nameParts[1]=='achel' or $nameParts[1]=='macro') // Only invest further time if it actually is a macro.
			{
				$macroName=$nameParts[0];
				$output[$macroName]=array(
					'fileName'=>$fileName,
					'macroName'=>$macroName,
					'fullPath'=>$fullPath);
			}
		}

		return $output;
	}

	function getMacroNameDetailss($fullPath)
	{
		# Get fileName
		$pathParts=explode('/', $fullPath);
		$fileName=$pathParts[count($pathParts)-1];

		# Get original macroName
		$nameParts=explode('.', $fileName);
		$originalName=$nameParts[0];
		if (isset($nameParts[1]))
		{
			$extention=$nameParts[1];
		}
		else
		{
			$extention="achel";
		}

		return array(
			'fileName' => $fileName,
			'originalName' => $originalName,
			'extention' => $extention
		);
	}

	function loadMacro($macroName)
	{
		$this->debug(4, "Loading macro $macroName.");

		$macroPath=$this->core->get('MacroListCache', $macroName);
		if (!$macroPath)
		{
			$this->debug(0, "Could not find $macroName in the cache.");
			return false;
		}

		$macroDetails=$this->getMacroNameDetailss($macroPath);
		$this->loadMacroRegisterFeature($macroDetails['fileName'], $macroPath, $macroDetails['originalName']);
		$contentsParts=$this->core->get("MacroRawContents", $macroName);
		$this->defineMacro($contentsParts, false, $macroName);
	}

	function loadMacroFromFile($fileName, $macroName='')
	{
		if (!$macroName)
		{
			$macroName=md5($fileName);
		}

		$this->core->set("MacroListCache", $macroName, $fileName);
		$this->loadMacro($macroName);
		$this->debug(0, "$fileName -> $macroName");
	}

	function lazyLoadMacroFromFile($fileName)
	{
		$this->core->set("Macro", "lazyLoad", "$fileName");
	}

	function loadMacroRegisterFeature($fileName, $fullPath, $macroName, $quiet=false)
	{
		# This case happens when we load all macros in a cached environment.
		if ($this->core->get('MacroRawContents', $macroName) and $quiet) return true;

		$contents=file_get_contents($fullPath);
		$contentsParts=explode("\n", $contents);
		$this->core->set("MacroRawContents", $macroName, $contentsParts);
		$firstNativeLine=0;

		if (substr($contentsParts[0], 0, 2)=='#!') $firstNativeLine=1;

		if (substr($contentsParts[$firstNativeLine], 0, 2)=='# ')
		{
			$firstLine=substr($contentsParts[$firstNativeLine], 2);
			$firstLineParts=explode('~', $firstLine);
			#$description=$firstLine;
			$description=$firstLineParts[0];
			$tags=(isset($firstLineParts[1]))?'macro,'.trim($firstLineParts[1]):'';
			$this->core->registerFeature($this, array($macroName), $macroName, $description, $tags, true, $fullPath);
		}
		else $this->core->complain($this, "$fullPath appears to be a macro, but doesn't have a helpful comment on the first line begining with a # .");
	}

	function followup()
	{
		$cachedMacroList=$this->core->getCategoryModule('MacroListCache');
		if (!(count($cachedMacroList)>1))
		{
			$this->debug(4, "Loading macros from scratch.");
			$this->loadSavedMacros();
		}
	}

	private function complainAboutDuplicates($shouldComplain=true)
	{
		$this->core->setRef('General', 'complainAboutDuplicateMacros', $shouldComplain);
	}

	function loadSavedMacros($quiet=false)
	{
		$loadStart=microtime(true);

		$this->complainAboutDuplicates(!$quiet);
		$fileList=$this->getFileList();

		# Pre-register all macros so that they can be nested without issue.
		$macroList=$this->getJustMacros($fileList);
		foreach ($macroList as $macroName=>$details)
		{
			$fileName=$details['fileName'];
			$fullPath=$details['fullPath'];

			$this->core->set("MacroListCache", $macroName, $fullPath);

			$this->loadMacroRegisterFeature($fileName, $fullPath, $macroName, $quiet);
		}

		# Interpret and define all macros.
		foreach ($macroList as $macroName=>$details)
		{
			$fullPath=$details['fullPath'];

			$contentsParts=$this->core->get("MacroRawContents", $macroName);

			if (is_array($contentsParts))
			{
				if (substr($contentsParts[0], 0, 2)=='# ')
				{
					$this->defineMacro($contentsParts, false, $macroName, $quiet);
				}
				else
				{
					$this->core->complain($this, "${details['fullPath']} appears to be a macro, but doesn't have a helpful comment on the first line begining with a # .");
					$this->debug(0, "${details['fullPath']} appears to be a macro, but doesn't have a helpful comment on the first line begining with a # .");
				}
			}
			else
			{
				$this->debug(0, "Something went very wrong trying to load macro $macroName.");
			}
		}

		$this->doLazyLoad();

		$this->core->callFeature('triggerEvent', 'Macro,allLoaded');
		$loadFinish=microtime(true);
		$loadTime=$loadFinish-$loadStart;
		$this->debug(4, "Loaded macros in $loadTime seconds. start=$loadStart fimish=$loadFinish");

		$this->complainAboutDuplicates(false);
	}

	function doLazyLoad()
	{
		$lazyLoad=$this->core->get("LazyLoad", "file");
		if ($lazyLoad)
		{
			$this->debug(1, "LazyLoad: $lazyLoad");
			$this->loadMacroFromFile($lazyLoad, $this->core->get("LazyLoad", "macro"));
		}
		else
		{
			$this->debug(1, "LazyLoad: _nothing_");
		}
	}
}

class Nesting
{
	private $core=null;

	private $tab="	";
	private $space=" ";
	private $character='';

	private $allowedCharacters=null;

	private $currentNesting=0;

	private $configured=false;
	private $size="2";
	private $broken=false;

	function __construct($macroName, &$core)
	{
		$this->macroName=$macroName;
		$this->core=&$core;

		$this->allowedCharacters=array('tab'=>$this->tab, 'space'=>$this->space);
	}

	public function getNestingForLine($line)
	{ // Figure out the nesting level of a given line, and use it to configure the macro if it's the first indentation.

		$this->currentNesting=0;

		$indentationCharacters=$this->getIndentationCharacters($line);

		if (!$this->configured)
		{ // Let's see if we can configure it.
			$indentationCharacterWidth=strlen($indentationCharacters);

			if ($indentationCharacterWidth>0)
			{ // We have characters.
				$this->size=$indentationCharacterWidth;
				$this->character=substr($indentationCharacters, 0, 1);
				$this->configured=true;

				$this->core->debug(4, "Macro/Nesting/{$this->macroName}: Character=\"{$this->character}\" Size=\"{$this->size}\"");

				if (!$this->onlyHasAllowedCharacters($indentationCharacters))
				{
					$this->giveError("Unexpected characters in the indentation of line:$line");
				}
			}
		}

		if ($this->configured)
		{
			if (!$this->indentationIsClean($indentationCharacters))
			{
				$this->giveError("Context for the above errors is the line:$line");
			}
		}

		return $this->currentNesting;
	}

	public function removeOneIndent($line)
	{
		return substr($line, $this->size);
	}

	public function getNestingCharacter()
	{
		return $this->character;
	}

	public function getLastError()
	{
		return $this->lastError;
	}

	public function isBroken()
	{
		return $this->broken;
	}

	private function lineIsIndented($line)
	{
		$firstCharacter=substr($line, 0, 1);
		return ($firstCharacter==$this->space or $firstCharacter==$this->tab);
	}

	private function getIndentationCharacters($line)
	{ // A really fast way to get the indentation characters.
		$indentationCharacterWidth=strlen($line)-strlen(ltrim($line));
		return substr($line, 0, $indentationCharacterWidth);
	}

	private function indentationIsClean($characters)
	{ // Make sure there is nothing unexpected in the indentation.

		$clean=true;

		// Test that the indentation width is a multiple of the configured indentation size.
		$characterWidth=strlen($characters);
		$this->currentNesting=$characterWidth/$this->size;

		if ($this->currentNesting != intval($this->currentNesting))
		{
			$this->giveError("Indentation width ({$this->currentNesting}) should be a multiple of the macro's indentation size ({$this->size}).");
			$clean = false;
		}

		// Test that we only have expected characters.
		$withoutCorrectCharacters=str_replace($this->character, '', $characters);
		if (strlen($withoutCorrectCharacters)>0)
		{
			$this->giveError("Indentation contains unexpected characters. A mix of tabs and spaces, maybe?");
			$clean = false;
		}

		return $clean;
	}

	private function onlyHasAllowedCharacters($characters)
	{ // Check to make sure that only allowed characters are being used for indentation.

		// Remove everything that is allowed.
		foreach ($this->allowedCharacters as $character)
		{
			$characters=str_replace($character, '', $characters);
		}

		// There should be nothing left.
		return (strlen($characters)==0);
	}

	private function giveError($errorText)
	{
		$this->core->debug(0, "Macro/Nesting/$this->macroName: $errorText");
		$this->broken=true;
	}
}

$core=core::assert();
$macro=new Macro();
$core->registerModule($macro);

?>

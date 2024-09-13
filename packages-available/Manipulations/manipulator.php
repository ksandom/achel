<?php
# Copyright (c) 2012-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# Manipulate output
class Manipulator extends Module
{
	private $dataDir=null;

	function __construct()
	{
		parent::__construct('Manipulator');
	}

	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('toString'), 'toString', 'Convert array of arrays into an array of strings. eg --toString="blah file=%hostName% ip=~%externalIP%~"', array('array', 'string', 'Manipulations'));
				$this->core->registerFeature($this, array('split'), 'split', "Split each string-able thing into it's parts based on a specified string. --split=[stringToSplitOn] . If stringToSplitOn is not specified, \\n is assumed.", array('array', 'string', 'new','line','new line', 'Manipulations'));
				$this->core->registerFeature($this, array('f', 'flatten'), 'flatten', 'Flatten an array of arrays into a keyed array of values. --flatten[=limit] (default:-1). Note that "limit" specifies how far to go into the nesting before simply returning what ever is below. Choosing a negative number specifies how many levels to go in before beginning to flatten. Choosing 0 sets no limit.', array('array', 'string', 'Manipulations'));
				$this->core->registerFeature($this, array('flattenSubItems'), 'flattenSubItems', 'Just like --flatten. But will preserve the first layer regardless of the limit that you specify. This is probably the feature that you want.', array('array', 'string', 'Manipulations'));
				$this->core->registerFeature($this, array('finalFlatten'), 'finalFlatten', 'To be used after a --flatten as gone as far as it can.', array('array', 'string', 'Manipulations'));
				$this->core->registerFeature($this, array('replace'), 'replace', 'Replace a pattern matching a regular expression and replace it with something defined. --replace=searchRegex,replacement', array('array', 'string', 'Manipulations'));
				$this->core->registerFeature($this, array('replaceInString'), 'replaceInString', "Replace a string within a string. --replaceInString=Category,variable,search,replace,inputString", array('Manipulations'));
				$this->core->registerFeature($this, array('replaceRegexInString'), 'replaceRegexInString', "Replace a regex matched string within a string. --replaceRegexInString=Category,variable,search,replace,inputString", array('Manipulations'));

				$this->core->registerFeature($this, array('unique'), 'unique', 'Only keep unique entries. The exception is non-string values will simply be kept without being compared.', array('array', 'string', 'Manipulations'));
				$this->core->registerFeature($this, array('requireEach', 'refine'), 'requireEach', 'Require each entry to match this regular expression. --requireEach=regex', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('recursiveRequireEach', 'recursiveRefine'), 'recursiveRequireEach', 'Require each entry to match this regular expression somewhere in its dataset. --requireEach=regex', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('requireItem'), 'requireItem', 'Require a named entry in each of the root entries. A regular expression can be supplied to provide a more precise match. --requireItem=entryKey[,regex]', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('excludeEach', 'exclude'), 'excludeEach', 'The counterpart of --requireEach. Excludes any item that contains an entry that matches the regular expression. --requireEach=regex', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('recursiveExcludeEach', 'recursiveExclude'), 'recursiveExcludeEach', 'The counterpart of --recursiveRequireEach. Excludes any item that contains an entry that matches the regular expression somewhere in the dataset. --requireEach=regex', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('excludeItem'), 'excludeItem', 'The counterpart of --requireItem. Excludes any items wherre a named entry matches the specified regex. --excludeItem=entryKey[,regex]', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('manipulateEach'), 'manipulateEach', 'Call a feature for each entry in the result set that contains an item matching this regular expression. --manipulateEach=regex,feature featureParameters', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('manipulateItem'), 'manipulateItem', 'Call a feature for each entry that contains an item explicity matching the one specified. --manipulateItem=entryKey,regex,feature featureParameters', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('chooseFirst'), 'chooseFirst', 'Choose the first non-empty value and put it into the destination variable. --chooseFirst=dstVarName,srcVarName1,srcVarName2[,srcVarName3[,...]]', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('chooseFirstSet'), 'chooseFirstSet', "Choose the first set whose key has a non-empty value and put each item in the set into the it's destination variable. --chooseFirstSet=setSize,srcVarName1,dstVarName1,srcVarName2,dstVarName2[,srcVarName3,dstVarName3[,...]] . The setSize determines how many src/dst pairs are in each set. eg --chooseFirstSet=3,x,y,z,a1,b1,c1,a2,b2,c2 . In this example, we define that the setSize is 3. Therefore we can take a,b and c from set 1 and 2 and put it into x, y, and z. In each case 'a' is the variable that will be tested. So if a1 is empty, a2 will be tested. If that succeeds then a2, b2 and c3 will be put into x, y and z.", array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('chooseFirstSetIfNotSet'), 'chooseFirstSetIfNotSet', "See --chooseFirstSet for full help. This variant will only take action on each result that doesn't have x set.", array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('resultSet'), 'resultSet', 'Set a value in each result item. --setResult=dstVarName,value . Note that this has no counter part as you can already retrieve results with ~%varName%~ and many to one would be purely random.', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('resultSetIfNotSet'), 'resultSetIfNotSet', 'Set a value in each result item only if it is not already set. --resultSetIfNotSet=dstVarName,value . Note that this has no counter part as you can already retrieve results with ~%varName%~ and many to one would be purely random.', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('resultUnset'), 'resultUnset', 'Delete a value in each result item. --resultUnset=dstVarName.', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('addSlashes'), 'addSlashes', 'Put extra backslashes before certain characters to escape them to allow nesting of quoted strings. --addSlashes=srcVar,dstVar', array('array', 'escaping', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('cleanUnresolvedResultVars'), 'cleanUnresolvedResultVars', 'Clean out any result variables that have not been resolved. This is important when a default should be blank.', array('array', 'escaping', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('take'), 'take', 'Take only a single key from each entry in a result set --take=key.', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('takeSubResult'), 'takeSubResult', "Take only a single entry in a result set and make that the entrie resultSet. --takeSubResult=key . Note that it will looks it's containing array entry. If you want that, use --chooseSubResult instead.", array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('chooseSubResult'), 'chooseSubResult', 'Take only a single entry in a result set and make that the entrie resultSet, but keep the original containing array entry. --chooseSubResult=key.', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('duplicate', 'dup'), 'duplicate', 'Duplicate the result set. --duplicate[=numberOfTimesToDuplicate]Eg --duplicate=3 would take a result set of [a,b,c] and give [a,b,c,a,b,c,a,b,c]. The original intended use for this was to open extra terminals for each host when using --term or --cssh. Note that --dup --dup is not the same as --dup=2 !', array('array', 'result', 'Manipulations'));
				$this->core->registerFeature($this, array('count'), 'count', 'Replace the reseulSet with the count of the resultSet. --count', array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('countToVar'), 'countToVar', 'Count the number of results and stick the answer in a variable. --countToVar=CategoryName,variableName', array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('pos'), 'pos', 'Insert the position of each result to that result. This can be used simply to track results as they get processed in other ways, or for creating an inprovised unique number for each result (NOTE that that number will not necessarily stay with the same result on subsequent runs if the input result set has changed). --pos[=resultVariableName[,offset]] . resultVariableName defaults to "pos" and offset defaults to "0"', array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('chooseBasedOn'), 'chooseBasedOn', 'For each item in the result set, choose the value of an array based on the modulous of a named value in the result set and the number of items in the array. This would naturally work well with --pos. --chooseBasedOn=inputValueName,outputValueName,inputCategory[,inputValueName,[subInputValueName,[etc,[etc]]]]', array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('escape'), 'escape', "Escape a string to be used is something like manually created json. --escape=Category,variable,value . Note that this currently doesn't handle a comma (,) very well.", array('Manipulations'));


				$this->core->registerFeature($this, array('firstResult', 'firstResults', 'first'), 'firstResult', "Take the first x results, where x is one if not specified. --firstResult[=x]", array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('lastResult', 'lastResults', 'last'), 'lastResult', "Take the last x results, where x is one if not specified. --lastResult=x", array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('offsetResult', 'offsetResults'), 'offsetResult', "After x results, take the first y results. --offsetResult=x,y . If y is negative, The results will be taken from the end rather than the beginning. In this case x therefore is an offset from the end, not the beginning.", array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('resetKeys'), 'resetKeys', "Resets the keys of the resultSet to an integer starting at 0. Use this when you want to access something by position. --resetKeys", array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('keyOn'), 'keyOn', "Key items in the resultSet using a named value from each item in the resultSet. --keyOn=itemKey1[,itemKey2[,itemKey3[,...]]]", array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('keyOnPreserve'), 'keyOnPreserve', "Key items in the resultSet using a named value from each item in the resultSet. --keyOn=itemKey1[,itemKey2[,itemKey3[,...]]] . Preseve keys. Clashes will take the last value. Use this where you need to match up keys from multiple sources.", array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('keyValueOn'), 'keyValueOn', "Key items in the value of each item in the resultSet using a named value from each item inside that item in the resultSet. If this sounds confusing, just think of it as running --keyOn inside a value inside each item in the result set. --keyValueOn=valueName,subValueName", array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('lessThan'), 'lessThan', "Restrict the resultset to items where a named result value is less than a specified value. --lessThan=valueName,valueToTest", array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('greaterThan'), 'greaterThan', "Restrict the resultset to items where a named result value is greater than a specified value. --greaterThan=valueName,valueToTest", array('result', 'Manipulations'));
				$this->core->registerFeature($this, array('between'), 'between', "Restrict the resultset to items where a named result value is between two specified values. --between=valueName,smallValue,largeValue", array('result', 'Manipulations'));

				$this->core->registerFeature($this, array('sortOnKey'), 'sortOnKey', "Sort items by the key of each result in the result set. This is not to be confused with --sortOnItemKey which is slower, but probably what you want.", array('result', 'sort', 'Manipulations'));
				$this->core->registerFeature($this, array('sortOnValue'), 'sortOnValue', "Sort items by the value of each result in the result set. This will not work with resultSets containing arrays as the results. For that use --sortOnItemKey . --sortOnValue may not perform well with larger resultSets.", array('result', 'sort', 'Manipulations'));
				$this->core->registerFeature($this, array('sortOnItemKey'), 'sortOnItemKey', "Sort items by a named item. You can sort on multiple fields. --sortOnItemKey=itemKey1[,itemKey2[,itemKey3[,...]]]", array('result', 'sort', 'Manipulations'));
				$this->core->registerFeature($this, array('lower'), 'lower', "Return the lower case representation of a provided string. --lower=Category,var,inputString", array('result', 'string', 'Manipulations'));
				$this->core->registerFeature($this, array('upper'), 'upper', "Return the upper case representation of a provided string. --upper=Category,var,inputString", array('result', 'string', 'Manipulations'));

				#$this->core->registerFeature($this, array('cleanUnresolvedStoreVars'), 'cleanUnresolvedStoreVars', 'Clean out any store variables that have not been resolved. This is important when a default should be blank.', array('array', 'escaping', 'result'));

				$this->core->registerFeature($this, array('createOneResult'), 'createOneResult', 'Replaces the resultSet with a single entry that can then be manipulated using features like --resultSet.', array('array', 'result', 'Manipulations'));

				$this->core->registerFeature($this, array('getKeysNumericallyIndexed'), 'getKeysNumericallyIndexed', 'Replaces the resultSet numerically indexed set of keys from the previous resultSet. This is the faster cousin of --getKeys .', array('array', 'result', 'Manipulations'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'split':
				return $this->splitItems($this->core->getResultSet(), $this->core->get('Global', $event));
				break;
			case 'requireEach':
				return $this->requireEach($this->core->getResultSet(), $this->core->get('Global', $event));
				break;
			case 'recursiveRequireEach':
				return $this->requireEach($this->core->getResultSet(), $this->core->get('Global', $event), false, true, true);
				break;
			case 'requireItem':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 1);
				return $this->requireEntry($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'excludeEach':
				return $this->requireEach($this->core->getResultSet(), $this->core->get('Global', $event), false, false);
				break;
			case 'recursiveExcludeEach':
				return $this->requireEach($this->core->getResultSet(), $this->core->get('Global', $event), false, false, true);
				break;
			case 'excludeItem':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 1);
				return $this->requireEntry($this->core->getResultSet(), $parms[0], $parms[1], false, false);
				break;
			case 'manipulateEach':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 2);
				return $this->requireEach($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'manipulateItem':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 3);
				return $this->requireEntry($this->core->getResultSet(), $parms[0], $parms[1], $parms[2]);
				break;
			case 'toString':
				return $this->toString($this->core->getResultSet(), $this->core->get('Global', $event));
				break;
			case 'flatten':
				$limitIn=$this->core->get('Global', $event);
				if ($limitIn == null) $limit=-1;
				elseif ($limitIn==0) $limit=false;
				else $limit=$limitIn;
				return $this->flatten($this->core->getResultSet(), $limit);
				break;
			case 'flattenSubItems':
				$limitIn=$this->core->get('Global', $event);
				if ($limitIn == null) $limit=-1;
				elseif ($limitIn==0) $limit=false;
				else $limit=$limitIn;
				return $this->flattenSubItems($this->core->getResultSet(), $limit);
				break;
			case 'finalFlatten':
				return $this->finalFlatten($this->core->getResultSet());
				break;
			case 'replace':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				return $this->replaceUsingRegex($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'replaceInString':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 5, 5);
				$this->core->set($parms[0], $parms[1], str_replace($parms[2], $parms[3], $parms[4]));
				break;
			case 'replaceRegexInString':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 5, 5);
				$this->core->set($parms[0], $parms[1], $this->replaceUsingRegex($parms[4], $parms[2], $parms[3]));
				break;
			case 'unique':
				return $this->unique($this->core->getResultSet());
				break;
			case 'chooseFirst':
				return $this->chooseFirst($this->core->getResultSet(), $this->core->interpretParms($this->core->get('Global', $event)));
				break;
			case 'chooseFirstSet':
				return $this->chooseFirstSet($this->core->getResultSet(), $this->core->interpretParms($this->core->get('Global', $event)));
				break;
			case 'chooseFirstSetIfNotSet':
				return $this->chooseFirstSet($this->core->getResultSet(), $this->core->interpretParms($this->core->get('Global', $event)), false);
				break;
			case 'resultSet':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				$this->core->requireNumParms($this, 2, $event, $originalParms, $parms);
				return $this->resultSet($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'resultSetIfNotSet':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				$this->core->requireNumParms($this, 2, $event, $originalParms, $parms);
				return $this->resultSet($this->core->getResultSet(), $parms[0], $parms[1], false);
				break;
			case 'resultUnset':
				return $this->resultUnset($this->core->getResultSet(), explode(',', $this->core->get('Global', $event)));
				break;
			case 'cleanUnresolvedResultVars':
				return $this->cleanUnresolvedVars($this->core->getResultSet(), resultVarBegin, resultVarEnd);
				break;
			case 'take':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				return $this->take($parms[0], $this->core->getResultSet());
				break;
			case 'takeSubResult':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				return $this->takeSubResult($parms[0], $this->core->getResultSet());
				break;
			case 'chooseSubResult':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				return array($parms[0]=>$this->takeSubResult($parms[0], $this->core->getResultSet()));
				break;
			case 'addSlashes':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				$this->core->requireNumParms($this, 2, $event, $originalParms, $parms);
				return $this->addResultSlashes($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'duplicate':
				return $this->duplicate($this->core->getResultSet(), $this->core->get('Global', $event));
				break;
			case 'count':
				return array($this->countResultSet());
				break;
			case 'countToVar':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2);
				$this->core->set($parms[0], $parms[1], $this->countResultSet());
				break;
			case 'pos':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2);
				return $this->assignPos($this->core->getResultSet(), $parms[0], $parms[1]);;
				break;
			case 'chooseBasedOn':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 3, true);
				return $this->chooseBasedOn($this->core->getResultSet(), $parms[0], $parms[1], $parms[2]);;
				break;

			case 'escape':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3, true);
				$this->core->set($parms[0], $parms[1], $this->escape($parms[2]));
				break;

			case 'firstResult':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 1, 0);
				return $this->offsetResult($this->core->getResultSet(), 0, $parms[0]);
				break;
			case 'lastResult':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 1, 0);
				$number=($parms[0])?$parms[0]*-1:-1;
				return $this->offsetResult($this->core->getResultSet(), 0, $number);
				break;
			case 'offsetResult':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2);
				return $this->offsetResult($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'createOneResult':
				return array(array());
				break;
			case 'resetKeys':
				return $this->resetKeys($this->core->getResultSet());
				break;
			case 'keyOn':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 1, 1);
				return $this->keyOn($this->core->getResultSet(), $parms);
				break;
			case 'keyOnPreserve':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 1, 1);
				return $this->keyOn($this->core->getResultSet(), $parms, false);
				break;
			case 'keyValueOn':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2);
				return $this->keyValueOn($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'sortOnKey':
				return ksort($this->core->getResultSet());
				break;
			case 'sortOnValue':
				$resultSet=$this->core->getResultSet();
				sort($resultSet);
				return $resultSet;
				break;
			case 'sortOnItemKey':
				return $this->sortOnItemKey($this->core->getResultSet(), $this->core->interpretParms($this->core->get('Global', $event)));
				break;
			case 'lessThan':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2);
				return $this->lessThan($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'greaterThan':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2);
				return $this->greaterThan($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'between':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3);
				return $this->between($this->core->getResultSet(), $parms[0], $parms[1], $parms[2]);
				break;
			case 'lower':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3);
				$this->core->set($parms[0], $parms[1], strtolower($parms[2]));
				break;
			case 'upper':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3);
				$this->core->set($parms[0], $parms[1], strtoupper($parms[2]));
				break;

			case 'getKeysNumericallyIndexed':
				return array_keys($this->core->getResultSet());

			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

	function countResultSet()
	{
		$resultSet=$this->core->getResultSet();
		if (is_null($resultSet))
		{
			return 0;
		}
		else
		{
			if (is_string($resultSet))
			{
				if (strlen($resultSet) > 0) return 1;
				else return 0;
			}
			else
			{
				return count($resultSet);
			}
		}
	}

	function splitItems($resultSet, $stringToSplitOn="\n")
	{
		if ($stringToSplitOn==='') $stringToSplitOn="\n";
		$output=array();
		if (!is_array($resultSet))
		{
			$this->debug(3, __CLASS__.'.'.__FUNCTION__.": resultSet is not an array. I can not do anything with this.");
			return $resultSet;
		}

		foreach ($resultSet as $key=>$resultItem)
		{
			if (is_string($resultItem))
			{
				$output[$key]=explode($stringToSplitOn, $resultItem);
			}
			else
			{
				$this->debug(3, __CLASS__.'.'.__FUNCTION__.": Key \"$key\" is not a string. Just add back in as is.");
				$output[$key]=$resultItem;
			}
		}

		return $output;
	}

	function replace($input, $search, $replace)
	{
		if (is_object($search) or is_object($replace)) return $input;
		$output=implode($replace, explode($search, $input));;
		$this->debug(4, "replace: Search=$search Replace=$replace Input=\"$input\" Output=\"$output\"");
		return $output;
	}

	function toString($input, $template)
	{
		$output=array();

		if (!is_array($input))
		{
			$this->debug(3, "Manipulator->toString: Input was not an array. Quite possibly there was no input. Try using --nested to find out what data you are getting at this point.");
			return $input;
		}
		foreach ($input as $line)
		{
			if (is_array($line))
			{
				$outputLine=$this->core->processValue($template);
				$outputLine=$this->processResultVarsInString($line, $outputLine);
				$output[]=$outputLine;
			}
			else
			{
				$output[]=$this->replace($this->core->processValue($template), resultVarBegin.'value'.resultVarEnd, $line);
			}
		}

		return $output;
	}

	function escape($value)
	{
		return addslashes($value);
	}

	function processResultVarsInString($input, $string)
	{
		# TODO I just re-read this code. This is an incredibly slow way to do it. Instead, do look through the demplate and resolve the variables. Note that this will require a bit more care to code to handle nested variables.It may be possible to reuse the code the parses normal variables.
		# TODO This really needs to recursively go through the result set since it can be nested.
		$outputLine=$string;;


		$iterations=50;
		$previousValue='';
		while (strpos($outputLine, '~%')!==false and $iterations>0 and $previousValue!=$outputLine)
		{
			$previousValue=$outputLine;
			$iterations--;
			foreach ($input as $key=>$value)
			{
				if (!is_array($value)) $outputLine=$this->replace($outputLine, resultVarBegin."$key".resultVarEnd, $value);
				else $this->debug(4, "processResultVarsInString: value for key $key is an array, so the replace has not been attempted.");
			}
		}

		return $outputLine;
	}

	function cleanUnresolvedVars($input, $begin, $end)
	{
		if (is_array($input))
		{
			$output=array();
			foreach ($input as $key=>$value) $output[$key]=$this->cleanUnresolvedVars($value, $begin, $end);
			return $output;
		}
		else
		{
			return $this->cleanUnresolvedVarsFromString($input, $begin, $end);
		}


	}

	function cleanUnresolvedVarsFromString($input, $begin, $end)
	{
		$start=strpos($input, $begin);
		if (!$start) return $input;
		$finish=strpos($input, $end)+strlen($end);
		$termite=substr($input, $start, $finish-$start);
		$output=$this->replace($input, $termite, '');

		if (strpos($output, $begin)!==false) return $this->cleanUnresolvedVarsFromString($output, $begin, $end);
		else return $output;
	}

	function flatten($input, $limit, $nesting=0, $prefix='')
	{
		if (!is_array($input)) return $input;

		$output=array();
		$clashes=array();
		if (is_numeric($limit) and $limit<0)
		{
			foreach ($input as $key=>$line)
			{
				if ($prefix)
				{
					$effectivePrefix="{$prefix}_{$key}";
					$effectiveKey="{$effectivePrefix}";
				}
				else
				{
					$effectivePrefix="{$key}";
					$effectiveKey="$key";
				}

				$newLimit=($limit<-1)?$limit+1:false;
				$output[$effectiveKey]=$this->flatten($line, $newLimit, $nesting+1, $effectivePrefix);
			}
		}
		else $this->getArrayNodes($output, $input, $clashes, $limit, $nesting);

		return $output;
	}

	function flattenSubItems($input, $limit, $nesting=0)
	{
		if (!is_array($input)) return $input;

		$output=array();

		foreach ($input as $key => $entry)
		{
			$output[$key] = $this->flatten($entry, $limit, $nesting, $key);
		}

		return $output;
	}

	function finalFlatten($dataIn)
	{
		$output=array();

		foreach ($dataIn as $line)
		{
			if (is_array($line))
			{
				foreach ($line as $subline)
				{
					$output[]=$subline;
				}
			}
			else $output[]=$line;
		}

		return $output;
	}

	function replaceUsingRegex($dataIn, $search, $replace)
	{
		$searchArray=array("/$search/");
		$replaceArray=array($replace);
		$output=array();

		if (is_array($dataIn))
		{
			foreach ($dataIn as $line)
			{
				$output[]=preg_replace($searchArray, $replaceArray, $line);
			}

			return $output;
		}
		else
		{
			return preg_replace($searchArray, $replaceArray, $dataIn);
		}
	}

	function unique($dataIn)
	{
		$output=array();

		foreach ($dataIn as $line)
		{
			if (is_string($line))
			{
				$output[md5($line)]=$line;
			}
			else $output[]=$line;
		}

		return $output;
	}

	private function getArrayNodes(&$output, $input, &$clashes, $limit, $nesting, $prefix='')
	{
		foreach ($input as $key=>$value)
		{
			if ($prefix)
			{
				$effectivePrefix="{$prefix}_{$key}";
				$effectiveKey="{$effectivePrefix}";
			}
			else
			{
				$effectivePrefix="{$key}";
				$effectiveKey="$key";
			}

			if (is_array($value) and !(is_numeric($limit) and (($nesting>=$limit))))
			{
				$this->getArrayNodes($output, $value, $clashes, $limit, $nesting+1, $effectivePrefix);
			}
			else
			{
				$output[$effectiveKey]=$value;
			}
		}
	}

	private function mixResults($matching, $notMatching, $feature)
	{
		$featureParts=$this->core->splitOnceOn(' ', $feature);
		$processed=$this->core->callFeatureWithDataset($featureParts[0], $featureParts[1], $matching);

		return array_merge($processed, $notMatching);
	}

	private function requireEach($input, $search, $feature=false, $shouldMatch=true, $shouldRecurse=false)
	{
		$outputMatch=array();
		$outputNoMatch=array();

		# This could techinically be done with return array();, but would be prone to bugs if the default value of one of these arrays changes in the future.
		if (!is_array($input)) return ($shouldMatch)?$outputMatch:$outputNoMatch;

		foreach ($input as $key=>$line)
		{
			$processed=false;

			if (is_string($line))
			{
				if (preg_match('/'.$search.'/', $line))
				{
					$this->debug(2, "requireEach: Matched \"$search\" in \"$line\"");
					$outputMatch[$key]=$line;
				}
				else $outputNoMatch[$key]=$line;
			}
			elseif (is_array($line))
			{
				$matched=false;
				foreach ($line as $subline)
				{
					$matched=false;
					if (is_string($subline))
					{
						if (preg_match('/'.$search.'/', $subline))
						{
							$outputMatch[$key]=$line;
							$matched=true;
							break;
						}
					}
					elseif ($shouldRecurse and is_array($subline))
					{
						$subResult=$this->requireEach($subline, $search, $feature, true, true);
						if (count($subResult))
						{
							$outputMatch[$key]=$line;
							$matched=true;
							break;
						}
					}
				}
				if (!$matched) $outputNoMatch[$key]=$line;
			}
			else $outputNoMatch[$key]=$line;
		}

		if ($feature)
		{
			if ($this->core->isVerboseEnough(3))
			{
				$this->debug(3, 'requireEach: Matched '.count($outputMatch).". Didn't match ".count($outputNoMatch.". For search $search"));
			}
			return $this->mixResults($outputMatch, $outputNoMatch, $feature);
		}
		else
		{
			if ($shouldMatch) return $outputMatch;
			else return $outputNoMatch;
		}
	}

	private function requireEntry($input, $neededKey, $neededRegex, $feature=false, $shouldMatch=true)
	{
		$outputMatch=array();
		$outputNoMatch=array();

		if (!is_array($input)) return false; # TODO double check what this should be.

		foreach ($input as $key=>$line)
		{
			if ($neededKey)
			{
				if (isset($line[$neededKey]))
				{
					if ($neededRegex)
					{
						if (preg_match('/'.$neededRegex.'/', $line[$neededKey])) $outputMatch[$key]=$line;
						else $outputNoMatch[$key]=$line;
					}
					else $outputMatch[$key]=$line;
				}
				else $outputNoMatch[$key]=$line;
			}
			else
			{
				if (is_array($line))
				{
					if (count($this->requireEach($line, $neededRegex))) $outputMatch[$key]=$line;
					else $outputNoMatch[$key]=$line;
				}
				else $outputNoMatch[$key]=$line;
			}
		}

		if ($feature)
		{
			$this->debug(3, 'requireEntry: Matched '.count($outputMatch).". Didn't match ".count($outputNoMatch).". For search $neededKey=$neededRegex"); # TODO Optimise this so that the counts are not done if the debugging isn't going to be seen
			return $this->mixResults($outputMatch, $outputNoMatch, $feature);
		}
		else
		{
			if ($shouldMatch) return $outputMatch;
			else return $outputNoMatch;
		}
	}

	function chooseFirst($input, $parms)
	{
		# Choose the first non-empty value and put it into the destination variable. --chooseFirst=dstVarName,srcVarName1,srcVarName2[,srcVarName3[,...]]

		$dstVarName=$parms[0];
		$totalParms=count($parms);
		$output=array();

		foreach ($input as $line)
		{
			//$line[$dstVarName]='unset'; # Do we want this?
			for ($i=1;$i<$totalParms;$i++)
			{
				$value=(isset($line[$parms[$i]]))?$line[$parms[$i]]:'';
				if ($value)
				{
					$line[$dstVarName]=$value;
					break;
				}
			}

			$output[]=$line;
		}

		return $output;
	}

	function chooseFirstSet($dataIn, $parms, $overwrite=true)
	{
		# TODO write this
		/*
			build an array of sets
			test each set for each inputItem
				assign results
			return result
		*/

		$stop=count($parms);
		$width=$parms[0];
		$sets=array(0=>array());
		$setID=-1;
		$output=array();
		$destination=0;


		for ($inputKey=1;$inputKey<$stop;$inputKey++)
		{
			if ($inputKey%$width==1)
			{
				$setID++;
				$sets[$setID]=array();
			}

			$sets[$setID][]=$parms[$inputKey];
		}

		foreach ($dataIn as $line)
		{
			if ($overwrite or !isset($line[$sets[0][0]]))
			{
				for ($setToTest=1;$setToTest<=$setID;$setToTest++)
				{
					$value=(isset($line[$sets[$setToTest][0]]))?$line[$sets[$setToTest][0]]:'';
					if ($value)
					{
						foreach ($sets[0] as $key=>$destinationField)
						{
							$valueToCopy=(isset($line[$sets[$setToTest][$key]]))?$line[$sets[$setToTest][$key]]:'';
							$line[$sets[0][$key]]=$line[$sets[$setToTest][$key]];
						}
						break;
					}
				}
			}
			$output[]=$line;
		}

		return $output;
	}

	function resultSet($input, $key, $value, $overwrite=true)
	{
		$output=$input;
		foreach ($output as &$line)
		{
			if ($overwrite or !isset($line[$key])) $line[$key]=$this->processResultVarsInString($line, $value);
		}

		return $output;
	}

	function resultUnset($input, $keys)
	{
		$output=$input;
		foreach ($output as &$line)
		{
			foreach ($keys as $key)
			{
				if (isset($line[$key])) unset($line[$key]);
			}
		}

		return $output;
	}

	function addResultSlashes($input, $src, $dst)
	{
		$output=$input;
		foreach ($output as &$line)
		{
			$line[$dst]=addslashes($line[$src]);
		}

		return $output;
	}

	function take($key, $resultSet)
	{
		$output=array();

		foreach ($resultSet as $line)
		{
			if (isset($line[$key]))
			{
				if (false) //(is_array($line[$key]))
				{ # TODO I don't think this is correct!
					foreach ($line[$key] as $subline)
					{
						$output[]=$subline;
					}
				}
				else $output[]=$line[$key];
			}
		}

		return $output;
	}

	function takeSubResult($key, $resultSet)
	{
		if (isset($resultSet[$key])) return $resultSet[$key];
		else return array(); # Failure should empty the resultSet
	}

	function duplicate($input, $numberOfTimesToDuplicate=1)
	{
		$output=array();

		$actualNumberOfTimesToDuplicate=($numberOfTimesToDuplicate)?$numberOfTimesToDuplicate:1;

		for($duplication=0;$duplication<=$actualNumberOfTimesToDuplicate;$duplication++)
		{
			foreach ($input as $line)
			{
				$output[]=$line;
			}
		}

		return $output;
	}

	function assignPos($resultSet, $resultVariableName='pos', $offset=0)
	{
		if (!$resultVariableName) $resultVariableName='pos';
		if (!$offset) $offset=0;

		$pos=$offset;
		foreach ($resultSet as $key=>&$result)
		{
			$result[$resultVariableName]=$pos;
			$pos++;
		}

		return $resultSet;
	}

	function chooseBasedOn($resultSet, $inputValueName, $outputValueName, $inputArrayName)
	{
		if ($inputArray=$this->core->getNested($this->core->interpretParms($inputArrayName)))
		{
			if (is_array($inputArray))
			{
				$index=array_keys($inputArray);
				$count=count($inputArray);

				foreach ($resultSet as $key=>&$item)
				{
					if (!isset($item[$inputValueName])) continue; # TODO Do we need debugging on this? Probably yes.
					if (!is_numeric($item[$inputValueName])) continue; # TODO Do we need debugging on this? Probably yes.

					# TODO test/finish this
					$arrayPos=$item[$inputValueName]%$count;
					#$item[$outputValueName]=$inputArray[$index[$arrayPos]];
					$item[$outputValueName]=$index[$arrayPos];
				}



				return $resultSet;
			}
			else
			{
				$this->debug(3, "Manipulator->chooseBasedOn: $inputArrayName was not an array. It's ".gettype($inputArray));
				return false;
			}
		}
		else
		{
			$this->debug(3, "Manipulator->chooseBasedOn: $inputArrayName did not exist.");
			return false;
		}
	}

	function offsetResult($resultSet, $offset, $max)
	{
		if (!$max) $max=1;

		$output=array();
		$keys=array_keys($resultSet);
		$keyCount=count($keys);
		$absMax=abs($max);
		$totalRequested=$offset+$absMax;

		if ($totalRequested > $keyCount) $stop=$keyCount;
		else $stop=$totalRequested;

		if ($max<0)
		{
			$oldOffset=$offset;
			$offset=$keyCount-$stop;
			$stop=$keyCount-$oldOffset;
		}

		for ($i=$offset; $i<$stop; $i++)
		{
			$key=$keys[$i];
			$output[$key]=$resultSet[$key];
		}

		return $output;
	}

	function resetKeys($data)
	{
		$output=array();
		$newKey=0;

		foreach ($data as $value)
		{
			$output[$newKey]=$value;
			$newKey++;
		}

		return $output;
	}

	function keyOn($resultSet, $keysToKeyOn, $unique=true)
	{
		$output=array();
		$separator='_';

		if (!is_array($resultSet))
		{
			$this->debug(1, "Expected an array, but got ".gettype($resultSet).". Here's a stacktrace:");
			$this->core->stackTrace();
			return false;
		}

		foreach ($resultSet as $oldKey=>$item)
		{
			if ($unique)
			{
				$key='';
				foreach ($keysToKeyOn as $keyPart)
				{
					$sep=($key=='')?'':$separator;
					$key.=(isset($item[$keyPart]))?$sep.$item[$keyPart]:$sep.$oldKey;
				}

				$this->debug(3, "keyOn: Derived key $key");
			}
			else
			{
				$keyKeys=array_keys($keysToKeyOn);
				if (isset($item[$keysToKeyOn[0]]))
				{
					$key=$item[$keysToKeyOn[0]];
				}
				else
				{
					$key=$oldKey;
				}
			}
			$output[$key]=$item;
		}

		return $output;
	}

	function keyValueOn($resultSet, $valueName, $subValueName)
	{
		foreach ($resultSet as $oldKey=>&$item)
		{
			if (isset($item[$valueName]))
			{
				$item[$valueName]=$this->keyOn($item[$valueName], $subValueName);
			}
		}

		return $resultSet;
	}

	function sortOnItemKey($resultSet, $keysToKeyOn)
	{
		# Key on keys
		$output=$this->keyOn($resultSet, $keysToKeyOn);

		# Sort
		ksort($output);
		return $output;
	}

	function findPoint($resultSet, $method, $valueName, $value)
	{ // Divide and conquer to find an approximate value.

		if (!is_array($resultSet)) return $resultSet;

		$keys=array_keys($resultSet);
		$min=0;
		$total=count($keys);
		$max=$total-1;
		$interations=0;
		$half=intval(($max-$min)/2);

		while ($interations<$total)
		{
			if (!isset($resultSet[$keys[$half]][$valueName]))
			{
				$this->debug(1, "findPoint: Result with key \"{$keys[$half]}\" does not have a value named $valueName. This could be a bug in the macro, or corrupted data.");
				$half++;
				if ($half > $max)
				{
					$this->debug(3, "findPoint: half ($half) > max $max, method is $method and there is nowhere left to go.");
					return null;
				}
				continue;
			}

			$iterationValue=$resultSet[$keys[$half]][$valueName];
			$maxValue=$resultSet[$keys[$max]][$valueName];
			$minValue=$resultSet[$keys[$min]][$valueName];
			$this->debug(3, "findPoint: Iteration $interations min=$min half=$half max=$max");

			if ($iterationValue == $value and $method == '==') return $half;
			elseif ($max==$min or $max==$half) # TODO potentially we don't need $max==$min
			{
				switch ($method)
				{
					case '==':
						return $half;
					case '>':
						if ($iterationValue<=$value)
						{
							$nextIndex=$half+1;
							if (isset($keys[$nextIndex]))
							{
								$this->debug(3, "findPoint: $iterationValue<=$value and method is $method so returning previous value {$keys[$nextIndex]}($nextIndex).");
								return $nextIndex;
							}
							else
							{
								$this->debug(3, "findPoint: $iterationValue<=$value, method is $method and we don't have anything less to return.");
								return null;
							}
						}
						else
						{
							$this->debug(3, "findPoint: Normal exit with method $method and half $half and iterationValue $iterationValue.");
							return $half;
						}
					case '<':
						# This is some protection to make sure the value actually is less than the specified value. This code can almost certainly be optimised further.
						if ($iterationValue>=$value)
						{
							$previousIndex=$half-1;
							if (isset($keys[$previousIndex]))
							{
								$this->debug(3, "findPoint: $iterationValue>=$value and method is $method so returning previous value {$keys[$previousIndex]}($previousIndex).");
								return $previousIndex;
							}
							else
							{
								$this->debug(3, "findPoint: $iterationValue>=$value, method is $method and we don't have anything less to return.");
								return null;
							}
						}
						else
						{
							$this->debug(3, "findPoint: Normal exit with method $method and half $half and iterationValue $iterationValue.");
							return $half;
						}
				}
			}
			elseif ($iterationValue>$value)
			{
				$this->debug(3, "findPoint: ($iterationValue>$value) Set max to $half");
				$max=$half;
			}
			else
			{
				$this->debug(3, "findPoint: (else) Set min to $half");
				$min=$half;
			}

			$difference=$max-$min;
			if ($difference>1) $half=intval($difference/2)+$min;
			else $half=$max;
			$interations++;
		}
		$this->debug(2, "findPoint: Finished having done $interations iterations.");
		return $half;
	}

	function getRange($resultSet, $start, $stop)
	{
		if ($start===null or $stop===null)
		{
			if ($start===null)
			{
				$end='start';
				$ending='starting';
			}
			else
			{
				$end='stop';
				$ending='stopping';
			}
			$this->debug(3, "getRange: $end is false. This suggests that a valid $ending point was not found. This is completely healthy if findPoint was unable to find a point that fullfilled the request (ie no results for the search).");
			return array();
		}
		if (!is_array($resultSet)) return $resultSet;

		if ($stop=='' and !is_numeric($stop))
		{
			$stop=count($resultSet)-1;
			$this->debug(3, "getRange: Guessed absent stop value to be $stop.");
		}

		$keys=array_keys($resultSet);
		$output=array();
		$this->debug(3, "getRange(---, $start, $stop)");

		if (count($resultSet))
		{
			for ($i=$start;$i<=$stop;$i++)
			{
				$output[$keys[$i]]=$resultSet[$keys[$i]];
			}
		}

		return $output;
	}

	function lessThan($resultSet, $valueName, $value)
	{
		$this->debug(3, "lessThan(---, $valueName, $value)");
		$point=$this->findPoint($resultSet, '<', $valueName, $value);
		$range=$this->getRange($resultSet, 0, $point);
		return $range;
	}

	function greaterThan($resultSet, $valueName, $value)
	{
		$this->debug(3, "greaterThan(---, $valueName, $value)");
		$point=$this->findPoint($resultSet, '>', $valueName, $value);
		$range=$this->getRange($resultSet, $point, false);
		return $range;
	}

	function between($resultSet, $valueName, $smallValue, $largeValue)
	{
		$this->debug(3, "between(---, $valueName, $smallValue, $largeValue)");
		$startPoint=$this->findPoint($resultSet, '>', $valueName, $smallValue);
		$stopPoint=$this->findPoint($resultSet, '<', $valueName, $largeValue);
		$range=$this->getRange($resultSet, $startPoint, $stopPoint);
		return $range;
	}
}

$core=core::assert();
$manipulator=new Manipulator();
$core->registerModule($manipulator);

?>

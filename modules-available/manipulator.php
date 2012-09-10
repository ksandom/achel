<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

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
				$this->core->registerFeature($this, array('toString'), 'toString', 'Convert array of arrays into an array of strings. eg --toString="blah file=%hostName% ip=%externalIP%"', array('array', 'string'));
				$this->core->registerFeature($this, array('f', 'flatten'), 'flatten', 'Flatten an array of arrays into a keyed array of values. --flatten[=limit] (default:-1). Note that "limit" specifies how far to go into the nesting before simply returning what ever is below. Choosing a negative number specifies how many levels to go in before beginning to flatten. Choosing 0 sets no limit.', array('array', 'string'));
				$this->core->registerFeature($this, array('finalFlatten'), 'finalFlatten', 'To be used after a --flatten as gone as far as it can.', array('array', 'string'));
				$this->core->registerFeature($this, array('requireEach'), 'requireEach', 'Require each entry to match this regular expression. --requireEach=regex', array('array', 'result'));
				$this->core->registerFeature($this, array('requireItem'), 'requireItem', 'Require a named entry in each of the root entries. A regular expression can be supplied to provide a more precise match. --requireItem=entryKey[,regex]', array('array', 'result'));
				$this->core->registerFeature($this, array('manipulateEach'), 'manipulateEach', 'Call a feature for each entry in the result set that contains an item matching this regular expression. --manipulateEach=regex,feature featureParameters', array('array', 'result'));
				$this->core->registerFeature($this, array('manipulateItem'), 'manipulateItem', 'Call a feature for each entry that contains an item explicity matching the one specified. --manipulateItem=entryKey,regex,feature featureParameters', array('array', 'result'));
				$this->core->registerFeature($this, array('chooseFirst'), 'chooseFirst', 'Choose the first non-empty value and put it into the destination variable. --chooseFirst=dstVarName,srcVarName1,srcVarName2[,srcVarName3[,...]]', array('array', 'result'));
				$this->core->registerFeature($this, array('resultSet'), 'resultSet', 'Set a value in each result item. --setResult=dstVarName,value . Note that this has no counter part as you can already retrieve results with ~%varName%~ and many to one would be purely random.', array('array', 'result'));
				$this->core->registerFeature($this, array('resultUnset'), 'resultUnset', 'Delete a value in each result item. --resultUnset=dstVarName.', array('array', 'result'));
				$this->core->registerFeature($this, array('addSlashes'), 'addSlashes', 'Put extra backslashes before certain characters to escape them to allow nesting of quoted strings. --addSlashes=srcVar,dstVar', array('array', 'escaping', 'result'));
				$this->core->registerFeature($this, array('cleanUnresolvedResultVars'), 'cleanUnresolvedResultVars', 'Clean out any result variables that have not been resolved. This is important when a default should be blank.', array('array', 'escaping', 'result'));
				$this->core->registerFeature($this, array('take'), 'take', 'Take only a single key from a result set --take=key.', array('array', 'result'));
				#$this->core->registerFeature($this, array('cleanUnresolvedStoreVars'), 'cleanUnresolvedStoreVars', 'Clean out any store variables that have not been resolved. This is important when a default should be blank.', array('array', 'escaping', 'result'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'requireEach':
				return $this->requireEach($this->core->getResultSet(), $this->core->get('Global', 'requireEach'));
				break;
			case 'requireItem':
				$parms=$this->core->interpretParms($this->core->get('Global', 'requireItem'), 2, 1);
				return $this->requireEntry($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'manipulateEach':
				$parms=$this->core->interpretParms($this->core->get('Global', 'manipulateEach'), 1, 2);
				return $this->requireEach($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'manipulateItem':
				$parms=$this->core->interpretParms($this->core->get('Global', 'manipulateItem'), 2, 3);
				return $this->requireEntry($this->core->getResultSet(), $parms[0], $parms[1], $parms[2]);
				break;
			case 'toString':
				return $this->toString($this->core->getResultSet(), $this->core->get('Global', 'toString'));
				break;
			case 'flatten':
				$limitIn=$this->core->get('Global', 'flatten');
				if ($limitIn == null) $limit=-1;
				elseif ($limitIn==0) $limit=false;
				else $limit=$limitIn;
				
				echo "dfghjjklhgfasyufighnbhh".gettype($limitIn)."\n";
				
				return $this->flatten($this->core->getResultSet(), $limit);
				break;
			case 'finalFlatten':
				return $this->finalFlatten($this->core->getResultSet());
				break;
			case 'chooseFirst':
				return $this->chooseFirst($this->core->getResultSet(), $this->core->interpretParms($this->core->get('Global', 'chooseFirst')));
				break;
			case 'resultSet':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				$this->core->requireNumParms($this, 2, $event, $originalParms, $parms);
				return $this->resultSet($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			case 'resultUnset':
				return $this->resultUnset($this->core->getResultSet(), explode(',', $this->core->get('Global', 'resultUnset')));
				break;
			case 'cleanUnresolvedResultVars':
				return $this->cleanUnresolvedVars($this->core->getResultSet(), resultVarBegin, resultVarEnd);
				break;
			case 'take':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				return $this->take($parms, $this->core->getResultSet());
				break;
			case 'addSlashes':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', 'addSlashes'));
				$this->core->requireNumParms($this, 2, $event, $originalParms, $parms);
				return $this->addResultSlashes($this->core->getResultSet(), $parms[0], $parms[1]);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function replace($input, $search, $replace)
	{
		return implode($replace, explode($search, $input));
	}
	
	function toString($input, $template)
	{
		$output=array();
		
		foreach ($input as $line)
		{
			if (is_array($line))
			{
				# TODO It would be nice to make this recursive.
				$outputLine=$this->core->processValue($template);
				foreach ($line as $key=>$value)
				{
					$outputLine=$this->processResultVarsInString($line, $outputLine);
				}
				$output[]=$outputLine;
			}
			else
			{
				$output[]=$this->replace($this->core->processValue($template), resultVarBegin.'value'.resultVarEnd, $line);
			}
		}
		
		return $output;
	}
	
	function processResultVarsInString($input, $string)
	{
		# TODO This really needs to recursively go through the result set since it can be nested.
		$outputLine=$string;;
		
		foreach ($input as $key=>$value)
		{
			if (!is_array($value)) $outputLine=$this->replace($outputLine, resultVarBegin."$key".resultVarEnd, $value);
			else $this->core->debug(3, "processResultVarsInString: value for key $key is an array, so the replace has not been attempted.");
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
		$finish=strpos($input, $end)+strlen($end);
		$termite=substr($input, $start, $finish-$start);
		$output=$this->replace($input, $termite, '');
		
		if (strpos($output, $begin)!==false) return $this->cleanUnresolvedVarsFromString($output, $begin, $end);
		else return $output;
	}
	
	function flatten($input, $limit, $nesting=0)
	{
		if (!is_array($input)) return $input;
		
		$output=array();
		$clashes=array();
		if (is_numeric($limit) and $limit<0)
		{
			foreach ($input as $key=>$line)
			{
				$newLimit=($limit<-1)?$limit+1:false;
				$output[$key]=$this->flatten($line, $newLimit, $nesting+1);
			}
		}
		else $this->getArrayNodes($output, $input, $clashes, $limit, $nesting);
		
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
	
	private function getArrayNodes(&$output, $input, &$clashes, $limit, $nesting)
	{
		foreach ($input as $key=>$value)
		{
			if (is_array($value) and !(is_numeric($limit) and (($nesting>=$limit))))
			{
				$this->getArrayNodes($output, $value, $clashes, $limit, $nesting+1);
			}
			else
			{
				if (is_numeric($key)) $output[]=$value;
				else
				{
					if (!isset($output[$key])) $output[$key]=$value;
					else
					{
						# work out new key based on clashes
						$clashes[$key]=(isset($clashes[$key]))?$clashes[$key]+1:1;
						$newKey="$key{$clashes[$key]}";
						$output[$newKey]=$value;
					}
					
				}
			}
		}
	}
	
	private function mixResults($matching, $notMatching, $feature)
	{
		$featureParts=$this->core->splitOnceOn(' ', $feature);
		$processed=$this->core->callFeatureWithDataset($featureParts[0], $featureParts[1], $matching);
		
		return array_merge($processed, $notMatching);
	}
	
	private function requireEach($input, $search, $feature=false)
	{
		//print_r($input);
		$outputMatch=array();
		$outputNoMatch=array();
		if (!is_array($input)) return $output;
		
		foreach ($input as $key=>$line)
		{
			$processed=false;
			
			if (is_string($line))
			{
				if (preg_match('/'.$search.'/', $line))
				{
					$outputMatch[]=$line;
				}
				else $outputNoMatch[]=$line;
			}
			elseif (is_array($line))
			{ # TODO make this work recursively
				foreach ($line as $subline)
				{
					$matched=false;
					if (is_string($subline) && preg_match('/'.$search.'/', $subline))
					{
						$outputMatch[]=$line;
						$matched=true;
						break;
					}
				}
				if (!$matched) $outputNoMatch[]=$line;
			}
			else $outputNoMatch[]=$line;
		}
		
		if ($feature)
		{
			$this->core->debug(3, 'requireEach: Matched '.count($outputMatch).". Didn't match ".count($outputNoMatch.". For search $search")); # TODO Optimise this so that the counts are not done if the debugging isn't going to be seen
			return $this->mixResults($outputMatch, $outputNoMatch, $feature);
		}
		else return $outputMatch;
	}
	
	private function requireEntry($input, $neededKey, $neededRegex, $feature=false)
	{
		$outputMatch=array();
		$outputNoMatch=array();
		
		if (!is_array($input)) return $output;
		
		foreach ($input as $line)
		{
			if ($neededKey)
			{
				if (isset($line[$neededKey]))
				{
					if ($neededRegex)
					{
						if (preg_match('/'.$neededRegex.'/', $line[$neededKey])) $outputMatch[]=$line;
						else $outputNoMatch[]=$line;
					}
					else $outputMatch[]=$line;
				}
				else $outputNoMatch[]=$line;
			}
			else
			{
				if (is_array($line))
				{
					if (count($this->requireEach($line, $neededRegex))) $outputMatch[]=$line;
					else $outputNoMatch[]=$line;
				}
				else $outputNoMatch[]=$line;
			}
		}
		
		if ($feature)
		{
			$this->core->debug(3, 'requireEntry: Matched '.count($outputMatch).". Didn't match ".count($outputNoMatch.". For search $neededKey=$neededRegex")); # TODO Optimise this so that the counts are not done if the debugging isn't going to be seen
			return $this->mixResults($outputMatch, $outputNoMatch, $feature);
		}
		else return $outputMatch;
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
	
	function resultSet($input, $key, $value)
	{
		$output=$input;
		foreach ($output as &$line)
		{
			# TODO There is something wrong happening here.
			$line[$key]=$this->processResultVarsInString($line, $value);
			#$line[$key]=$value;
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
			if (isset($line[$key[0]]))
			{
				if (false) //(is_array($line[$key]))
				{ # TODO I don't think this is correct!
					foreach ($line[$key] as $subline)
					{
						$output[]=$subline;
					}
				}
				else $output[]=$line[$key[0]];
			}
		}
		
		return $output;
	}
}

$core=core::assert();
$core->registerModule(new Manipulator());
 
?>
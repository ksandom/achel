<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# Facilities for merging data together.

class Merge extends Module
{
	function __construct()
	{
		parent::__construct(__CLASS__);
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('mergeTakeFirst'), 'mergeTakeFirst', 'Use with --dataSet for merging data. TakeFirst resolves conflicts by taking the first value.', array('merge', 'MergeAlgorithm'));
				$this->core->registerFeature($this, array('mergeTakeLast'), 'mergeTakeLast', 'Use with --dataSet for merging data. TakeLast resolves conflicts by taking the last value.', array('merge', 'MergeAlgorithm'));
				$this->core->registerFeature($this, array('mergeCombineBiasFirst'), 'mergeCombineBiasFirst', 'Use with --dataSet for merging data. CombineBiasFirst resolves conflicts by combining conflicting arrays into a single array and keeping the first values within that array when there are sub conflicts.', array('merge', 'MergeAlgorithm'));
				$this->core->registerFeature($this, array('mergeCombineBiasLast'), 'mergeCombineBiasLast', 'Use with --dataSet for merging data. CombineBiasLast resolves conflicts by taking the first value.', array('merge', 'MergeAlgorithm'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			
			# These can all use the same handeler.
			case 'mergeTakeFirst':
			case 'mergeTakeLast':
			case 'mergeCombineBiasFirst':
			case 'mergeCombineBiasLast':
				$result=$this->simpleMerge($this->core->get('Merge', 'resultSet'), $this->core->getResultSet(), $event);
				$this->saveResult($result);
				return $result;
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function saveResult($result)
	{
		$this->core->set('Merge', 'resultSet', $result);
	}
	
	function simpleMerge($resultSet1, $resultSet2, $algorithm)
	{
		# Some time saving stuff
		if (!is_array($resultSet1))
		{
			if (is_array($resultSet2))
			{
				return $resultSet2;
			}
			else
				return false;
		}
		
		# Create a starting point.
		$result=$resultSet1;
		
		# Merge them together
		foreach ($resultSet2 as $key=>$value)
		{
			if (!isset($result[$key]))
			{
				$result[$key]=$value;
			}
			else
			{
				switch ($algorithm)
				{
					case "mergeTakeFirst":
						# We already have the first. Do nothing.
						break;
					case "mergeTakeLast":
						# Simply take the new value.
						$result[$key]=$value;
						break;
					case "mergeCombineBiasFirst":
						$result[$key]=$this->simpleMerge($resultSet1[$key], $resultSet2[$key], 'mergeTakeFirst');
						break;
					case "mergeCombineBiasLast":
						$result[$key]=$this->simpleMerge($resultSet1[$key], $resultSet2[$key], 'mergeTakeLast');
						break;
					default:
						$this->core->complain($this, "Merge could not find selection method.", $algorithm);
						break;
				}
			}
		}
		
		return $result;
	}
}

$core=core::assert();
$merge=new Merge();
$core->registerModule($merge);

?>
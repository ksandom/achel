<?php
# Copyright (c) 2014, Kevin Sandom under the BSD License. See LICENSE for full details.

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
				$this->core->registerFeature($this, array('mergeTakeFirst'), 'mergeTakeFirst', 'Use with --dataSet for merging data. TakeFirst resolves conflicts by taking the first value.', array('yaml'));
				$this->core->registerFeature($this, array('mergeTakeLast'), 'mergeTakeLast', 'Use with --dataSet for merging data. TakeLast resolves conflicts by taking the last value.', array('yaml'));
				$this->core->registerFeature($this, array('mergeCombineBiasFirst'), 'mergeCombineBiasFirst', 'Use with --dataSet for merging data. CombineBiasFirst resolves conflicts by combining conflicting arrays into a single array and keeping the first values within that array when there are sub conflicts.', array('yaml'));
				$this->core->registerFeature($this, array('mergeCombineBiasLast'), 'mergeCombineBiasLast', 'Use with --dataSet for merging data. CombineBiasLast resolves conflicts by taking the first value.', array('yaml'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'mergeTakeFirst':
				# return $this->core->getResultSet();
				break;
			case 'mergeTakeLast':
				# return $this->core->getResultSet();
				break;
			case 'mergeCombineBiasFirst':
				# return $this->core->getResultSet();
				break;
			case 'mergeCombineBiasLast':
				# return $this->core->getResultSet();
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function saveResult($result)
	{
		# $this->
	}
	
	function simpleMerge($resultSet1, $resultSet2)
	{
		
	}
}

$core=core::assert();
$core->registerModule(new Merge());

?>
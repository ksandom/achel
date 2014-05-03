<?php
# Copyright (c) 2014 Kevin Sandom under the BSD License. See LICENSE for full details.

class AchelIterator extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct(__CLASS__);
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('iterator'), 'iterator', 'Create a list of numbers for iterating through and stick them in the resultSet. --iterator=start,incrementor,stop . Common usage would look like this --iterator=0,1,9 . This would give a list of values from 0-9 inclusive. If you then use --loop, the values can be found in Result,line.', array('maths'));
				break;
			case 'followup':
				break;
			case 'last':
				break;

			case 'iterator':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				if ($this->core->requireNumParms($this, 3, $event, $originalParms, $parms))
				{
					$returnValue=$this->basicMaths($parms[0], $parms[1], $parms[2]);
					return $returnValue;
				}
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function genericIterator($start, $incrementor, $stop)
	{
		$result=array();
		for ($i=$start;$i<=$stop;$i+=$incrementor)
		{
			$result=$i;
		}
		
		return $result;
	}
}

$core=core::assert();
$core->registerModule(new AchelIterator());
 
?>
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
					$returnValue=$this->genericIterator($parms[0], $parms[1], $parms[2]);
					return $returnValue;
				}
				else
				{
					$this->core->complain($this, "Insufficient parameters. Got ".implode(','.$parms), $event);
				}
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function genericIterator($start, $incrementor, $stop)
	{
		// Sanity checks
		if (!$incrementor)
		{
			$this->core->debug(1, "genericIterator: Invalid incrementor \"$incrementor\".");
			return false;
		}
		
		if (($stop>$start and $incrementor<0) or ($stop<$start and $incrementor>0))
		{
			$this->core->debug(1, "genericIterator: Incrementor (\"$incrementor\") is in the opposite direction to start (\"$start\") and stop (\"$stop\").");
			return false;
		}
		
		if ($start=='' or $stop=='')
		{
			$this->core->debug(1, "genericIterator: Start (\"$start\") or Stop (\"$stop\") is empty.");
			return false;
		}
		
		
		// Do it
		$result=array();
		if ($stop>$start)
		{
			for ($i=$start;$i<=$stop;$i+=$incrementor)
			{
				$result[]=$i;
			}
		}
		else
		{
			for ($i=$start;$i>=$stop;$i+=$incrementor)
			{
				$result[]=$i;
			}
		}
		
		return $result;
	}
}

$core=core::assert();
$achelIterator=new AchelIterator();
$core->registerModule($achelIterator);

?>
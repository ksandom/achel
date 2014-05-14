<?php
# Copyright (c) 2014 Kevin Sandom under the BSD License. See LICENSE for full details.

class CSV extends Module
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
				$this->core->registerFeature($this, array('loadCSV'), 'loadCSV', 'Loads a CSV file into the resultSet. --CSV=filename', array('data','csv'));
				break;
			case 'followup':
				break;
			case 'last':
				break;

			case 'loadCSV':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				if ($this->core->requireNumParms($this, 1, $event, $originalParms, $parms))
				{
					$returnValue=$this->loadCSV($parms[0]);
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
	
	function loadCSV($fileName, $interpretHeader=true)
	{
		if (file_exists($fileName))
		{
			$input=file($fileName);
			$output=array();
			
			// Interpret the header if requested
			if (isset($input[0]) and $interpretHeader)
			{
				$map=str_getcsv($input[0]);
				$start=1;
			}
			else
			{
				$map=array();
				$start=0;
			}
			
			// Interpret the remainder of the file
			$totalLines=count($input);
			for ($lineNumber=$start;$lineNumber<$totalLines;$lineNumber++)
			{
				$line=str_getcsv($input[$lineNumber]);
				
				if ($interpretHeader)
				{
					$outputLine=array();
					foreach ($line as $key=>$value)
					{
						if (isset($map[$key])) $destinationKey=$map[$key];
						else $destinationKey=$key;
						$outputLine[$destinationKey]=$value;
					}
					
					$output[]=$outputLine;
				}
				else
				{
					$output[]=$line;
				}
			}
			
			return $output;
		}
		else
		{
			$this->core->complain($this, 'File not found.', $fileName);
			return false;
		}
	}
}

$core=core::assert();
$core->registerModule(new CSV());
 
?>
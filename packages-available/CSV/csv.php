<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

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
				$this->core->registerFeature($this, array('loadCSV'), 'loadCSV', 'Loads a CSV file into the resultSet. --loadCSV=filename', array('data','csv'));
				$this->core->registerFeature($this, array('saveCSVHeavyLifting'), 'saveCSVHeavyLifting', 'Does the heavy lifting for saving a CSV.', array('data','csv','hidden'));
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
			case 'saveCSVHeavyLifting':
				return $this->saveCSVHeavyLifting();
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

	private function saveCSVHeavyLifting()
	{
			$headings=$this->core->get('Local','headings');
			$resultSet=$this->core->getResultSet();
			$lines=array();

			$lines[]=$this->csvLine($headings, $headings);

			foreach ($resultSet as $key=>$lineData)
			{
				$lines[]=$this->csvLine($headings, $lineData);
			}

			$combined=implode("\n", $lines)."\n";

			return array(0=>$combined);
	}

	private function csvLine($headings, $lineData)
	{
		$output='';
		foreach ($headings as $key=>$value)
		{
			if (isset($lineData[$key]))
			{
				$prefix=($output=='')?'"':'","';
				$output.=$prefix.str_replace('"', '\"', $lineData[$key]);
			}
		}

		return $output.'"';
	}
}

$core=core::assert();
$csv=new CSV();
$core->registerModule($csv);

?>

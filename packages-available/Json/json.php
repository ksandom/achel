<?php
# Copyright (c) 2015, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage command line options

class JsonGeneral extends Module
{
	private $forceObject=true;
	
	function __construct()
	{
		parent::__construct(__CLASS__);
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('fromJson'), 'fromJson', 'Convert the resultSet from a Json string into the data it represents. This is the counterpart of --toJson', array('json'))
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'fromJson':
				return $this->fromJson($this->core->getResultSet());
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function fromJson($resultSet)
	{
		$output=array();
		if (!is_array($resultSet))
		{
			$this->core->debug(2, "fromJson: resultSet is not an array. Returning nothing.");
		}
		else
		{
			foreach ($resultSet as $key=>$line)
			{
				if (is_string($line))
				{
					$output[$key]=json_decode($line);
				}
				else
				{
					$this->core->debug(2, "fromJson: Skipped line that was not a string.");
				}
			}
		}
		return $output;
	}
}

$core=core::assert();
$core->registerModule(new JsonGeneral());
 
?>
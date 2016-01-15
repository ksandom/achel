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
				$this->core->registerFeature($this, array('toJsons'), 'toJsons', 'Convert the resultSet from an array of variables (ideally arrays) to Json strings. This is the counterpart of --fromJsons', array('json'));
				$this->core->registerFeature($this, array('fromJsons'), 'fromJsons', 'Convert the resultSet from an array of Json strings into the data it represents. This is the counterpart of --toJsons', array('json'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'fromJsons':
				return $this->fromJsons($this->core->getResultSet());
				break;
			case 'toJsons':
				return $this->toJsons($this->core->getResultSet());
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function toJsons($resultSet)
	{
		$output=array();
		if (!is_array($resultSet))
		{
			$this->core->debug(2, "toJson: resultSet is not an array. Returning nothing.");
		}
		else
		{
			foreach ($resultSet as $key=>$line)
			{
				$output[$key]=json_encode($line, JSON_FORCE_OBJECT);
			}
		}
		return $output;
	}
	
	function fromJsons($resultSet)
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
					$output[$key]=json_decode($line, 1);
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
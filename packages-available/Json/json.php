<?php
# Copyright (c) 2015-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

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
				$this->core->registerFeature($this, array('jsonify'), 'jsonify', "Convert the source variable into a string of json and put it into the destination variable. --jsonify=[\"Category,sourceVar,srcSubVar,srcSubVar2,etc\",\"Category,destinationVar,dstSubVar,dstSubVar2,etc\"] . Counterpart: --unJsonify.", array('json'));
				$this->core->registerFeature($this, array('unJsonify'), 'unJsonify', "Convert the source variable from a string of json and put it into the destination variable as the datastructure that the json represents. --unJsonify=[\"Category,sourceVar,srcSubVar,srcSubVar2,etc\",\"Category,destinationVar,dstSubVar,dstSubVar2,etc\"] . Counterpart: --jsonify.", array('json'));
				$this->core->registerFeature($this, array('toJsons'), 'toJsons', 'Convert the resultSet from an array of variables (ideally arrays) to Json strings. This is the counterpart of --fromJsons', array('json'));
				$this->core->registerFeature($this, array('fromJsons'), 'fromJsons', 'Convert the resultSet from an array of Json strings into the data it represents. This is the counterpart of --toJsons', array('json'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'jsonify':
				$parms=$this->core->interpretParms($this->core->get('Global', $event));
				$this->jsonify($parms[0], $parms[1]);
				break;
			case 'unJsonify':
				$parms=$this->core->interpretParms($this->core->get('Global', $event));
				$this->jsonify($parms[0], $parms[1], true);
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
	
	function jsonify($from, $to, $backwards=false)
	{
		$value=$this->core->getNested(explode(',', $from));
		$type=gettype($value);
		$output=null;
		
		if (!$backwards)
		{ // jsonify
			$output=json_encode($value, JSON_FORCE_OBJECT);
		}
		else
		{ // unJsonify
			if ($type=='string')
			{
				$output=json_decode($value, 1);
			}
			else
			{
				$this->core->debug(1, "unJsonify expected a string. Got $type. If it was represented in json, it would look like ".json_encode($value).". Note that this is just a representation of the data recieved.");
			}
		}
		
		if ($output) $this->core->setNestedViaPath(explode(',', $to), $output);
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
$jsonGeneral=new JsonGeneral();
$core->registerModule($jsonGeneral);
 
?>
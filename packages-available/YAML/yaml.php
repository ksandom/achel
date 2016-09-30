<?php
# Copyright (c) 2014, Kevin Sandom under the BSD License. See LICENSE for full details.

# YAML conversions
# Most/all of this implementation will map directly to the PHP code.

class YAML extends Module
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
				$this->core->registerFeature($this, array('toYAML'), 'toYAML', 'Convert the current resultSet to YAML. --toYAML', array('yaml'));
				$this->core->registerFeature($this, array('toYAMLFile'), 'toYAMLFile', 'Convert the current resultSet to YAML and save it to a file. --toYAMLFile=fullPathToFile', array('yaml','file', 'export'));
				$this->core->registerFeature($this, array('fromYAML'), 'fromYAML', 'Read in YAML and convert it into data for the resultSet. --fromYAML', array('yaml'));
				$this->core->registerFeature($this, array('fromYAMLFile'), 'fromYAMLFile', 'Read in YAML from a file and convert it into data for the resultSet. --fromYAMLFile=fullPathToFile', array('yaml','file', 'import'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'toYAML':
				return array(yaml_emit($this->core->getResultSet()));
				break;
			case 'toYAMLFile':
				return yaml_emit_file($this->core->get('Global', $event), $this->core->getResultSet());
				break;
			case 'fromYAML':
				return $this->interpretAchelYAML($this->core->getResultSet());
				break;
			case 'fromYAMLFile':
				return yaml_parse_file($this->core->get('Global', $event));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	
	function interpretAchelYAML($dataIn)
	{
		if (is_string($dataIn)) return yaml_parse($dataIn);
		elseIf (is_array($dataIn))
		{
			$result=array();
			foreach ($dataIn as $key=>$value)
			{
				if (is_array($value))
				{
					$result[$key]=yaml_parse($vaule);
				}
				else
				{
					$this->core->complain($this, "Expected either a single string, or array. ", gettype($value));
				}
			}
			
			return $result;
		}
		else
		{
			$this->core->complain($this, "Expected either a single string (YAML), or array (lots of YAML). ", gettype($dataIn));
		}
	}
}

$core=core::assert();
$yaml=new YAML();
$core->registerModule($yaml);

?>
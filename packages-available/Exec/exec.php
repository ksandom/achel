<?php
# Copyright (c) 2015-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# Exec

class ExExec extends Module
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
				$this->core->registerFeature($this, array('exExec'), 'exExec', "Execute an external command. If it returns json, make that the resultSet. If it's a string, return it as a single entry in the resultSet.", array('execute', 'system', 'external'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'exExec':
				return $this->runAndReturn($this->core->get('Global', $event));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function runAndReturn($command)
	{
		# Sanity checks
		if (!$command)
		{
			$this->core->debug(1,"runAndReturn(\"$command\") got no command.");
			return false;
		}
		
		$this->core->debug(3,"Running runAndReturn(\"$command\")");
		$output=`$command`;
		
		if (strlen($output)==0)
		{
			$this->core->debug(2,"runAndReturn(\"$command\") got no output.");
			return false;
		}
		
		$firstChar=substr($output, 0, 1);
		
		switch ($firstChar)
		{
			case '[':
			case '{':
				return json_decode($output, 1);
				break;
			default:
				return array($output);
				break;
		}
	}
}

$core=core::assert();
$exExec=new ExExec();
$core->registerModule($exExec);

?>
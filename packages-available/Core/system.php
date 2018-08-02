<?php
# Copyright (c) 2013-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# Extra functionality that doesn't need to be in the core at startup.

class System extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('System');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				# TODO This may be obsolete.
				$this->core->registerFeature($this, array('internalExec'), 'internalExec', 'Call a feature. This is useful if you want to execute a variable. You should think very carefully before using this as poorly written code would allow an attacker to executre arbitrary code.', array('feature'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'internalExec':
				$parts=$this->core->splitOnceOn(' ', $this->core->get('Global', $event));
				return $this->core->callFeature($parts[0], $parts[1]);;
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
}

$core=core::assert();
$asystem=new System();
$core->registerModule($asystem);
 
?>
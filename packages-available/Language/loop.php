<?php
# Copyright (c) 2012-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# Adds the ability to put more advanced loops into macros.

class Loop extends Module
{
	function __construct()
	{
		parent::__construct('Loop');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('while'), 'while', "Loop while something is true. --while=thing1,comparison,thing2,doStuff . The first 3 fields match up to a normal --if condition. doStuff is what you want to do inside the loop. It would usually be an indented new line followed by your code.", array('language'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'notIfResultExists':
				return $this->ifResultExists($this->core->getResultSet(), $this->core->get('Global', 'notIfResultExists'), false);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}


}

$core=core::assert();
$loop=new Loop();
$core->registerModule($loop);

?>
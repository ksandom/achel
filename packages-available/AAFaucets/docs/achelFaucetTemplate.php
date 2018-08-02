<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# One line description here.

/*
	More specific notes here.
*/


class NetworkFaucets extends Faucets
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
				
				break;
			case 'followup':
				break;
			case 'last':
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}







$core=core::assert();
$core->registerModule(new NetworkFaucets());

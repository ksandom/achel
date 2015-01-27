<?php
# Copyright (c) 2015, Kevin Sandom under the BSD License. See LICENSE for full details.

class GenerateStrings extends Module
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
				$this->core->registerFeature($this, array('generateChars'), 'generateChars', 'Generate a string consisting of a repeating character. --generateChars=Category,variable,length,character', array('userExtra'));

				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'generateChars':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 4);
				$this->core->set($parms[0], $parms[1], $this->generateStringFromChar($parms[2], $parms[3]));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function generateStringFromChar($length, $char)
	{
		return str_pad('', $length, $char);
	}
}

$core=core::assert();
$core->registerModule(new GenerateStrings());
 
?>
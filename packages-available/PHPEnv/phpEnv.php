<?php
# Copyright (c) 2015-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

class PHPEnv extends Module
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
				$this->core->registerFeature($this, array('getPHPEnv'), 'getPHPEnv', 'Get the PHP environment variables. --getEnv=Category,variable', array('env','dev'));
				$this->core->registerFeature($this, array('getEnv', 'getPHPServer'), 'getPHPServer', 'Get the PHP server variables. --getSystem. --getServer=Category,variable', array('env','dev'));
				$this->core->registerFeature($this, array('getPHPRequest'), 'getPHPRequest', 'Get the PHP request variables. For Achel, this is very much an edge case. You probably want one of the other Env variables such as --getEnv --getSystem. --getRequest=Category,variable', array('env','dev'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'getPHPEnv':
				$parms=$this->core->interpretParms($this->core->get('Global', $event),2 ,2);
				$this->core->set($parms[0], $parms[1], $_ENV);
				break;
			case 'getPHPServer':
				$parms=$this->core->interpretParms($this->core->get('Global', $event),2 ,2);
				$this->core->set($parms[0], $parms[1], $_SERVER);
				break;
			case 'getPHPRequest':
				$parms=$this->core->interpretParms($this->core->get('Global', $event),2 ,2);
				$this->core->set($parms[0], $parms[1], $_REQUEST);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

$core=core::assert();
$phpEnv=new PHPEnv();
$core->registerModule($phpEnv);
 
?>
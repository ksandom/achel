<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

class Trig extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct(__CLASS__);
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('tan'), 'tan', 'Calculate the tangent of an arc in radians. This directly calls http://php.net/tan . --tan=Category,variable,angle . The counterpart is --atan.', array('trig', 'Maths'));
				$this->core->registerFeature($this, array('atan'), 'atan', 'Calculate the arc tangent in radians. This directly calls http://php.net/atan . --atan=Category,variable,value . The counterpart is --tan.', array('trig', 'Maths'));
				
				$this->core->registerFeature($this, array('sin'), 'sin', 'Calculate the sine of an arc in radians. This directly calls http://php.net/sin . --sin=Category,variable,angle . The counterpart is --asin.', array('trig', 'Maths'));
				$this->core->registerFeature($this, array('asin'), 'asin', 'Calculate the arc sine in radians. This directly calls http://php.net/asin . --asin=Category,variable,value . The counterpart is --sin.', array('trig', 'Maths'));
				
				$this->core->registerFeature($this, array('cos'), 'cos', 'Calculate the cosine of an arc in radians. This directly calls http://php.net/cos . --cos=Category,variable,angle . The counterpart is --atan.', array('trig', 'Maths'));
				$this->core->registerFeature($this, array('acos'), 'acos', 'Calculate the arc cosine in radians. This directly calls http://php.net/acos . --acos=Category,variable,value . The counterpart is --cos.', array('trig', 'Maths'));
				
				$this->core->registerFeature($this, array('radiansToDegrees'), 'radiansToDegrees', 'Convert radians to degrees. This directly calls http://php.net/rad2deg . --radiansToDegrees=Category,variable,value . The counterpart is --degreesToRadians.', array('trig', 'Maths'));
				$this->core->registerFeature($this, array('degreesToRadians'), 'degreesToRadians', 'Convert radians to degrees. This directly calls http://php.net/rad2deg . --degreesToRadians=Category,variable,value . The counterpart is --radiansToDegrees.', array('trig', 'Maths'));
				
				break;
			case 'followup':
				break;
			case 'last':
				break;
			
			case 'sin':
				if ($parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3))
				{
					if (is_numeric($parms[2])) $this->core->set($parms[0], $parms[1], sin($parms[2]));
					else $this->core->debug(0, "$event: Expected a number but got this value \"{$parms[2]}\"");
				}
				break;
			case 'asin':
				if ($parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3))
				{
					if (is_numeric($parms[2])) $this->core->set($parms[0], $parms[1], asin($parms[2]));
					else $this->core->debug(0, "$event: Expected a number but got this value \"{$parms[2]}\"");
				}
				break;
			
			case 'cos':
				if ($parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3))
				{
					if (is_numeric($parms[2])) $this->core->set($parms[0], $parms[1], cos($parms[2]));
					else $this->core->debug(0, "$event: Expected a number but got this value \"{$parms[2]}\"");
				}
				break;
			case 'acos':
				if ($parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3))
				{
					if (is_numeric($parms[2])) $this->core->set($parms[0], $parms[1], acos($parms[2]));
					else $this->core->debug(0, "$event: Expected a number but got this value \"{$parms[2]}\"");
				}
				break;
			
			case 'tan':
				if ($parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3))
				{
					if (is_numeric($parms[2])) $this->core->set($parms[0], $parms[1], tan($parms[2]));
					else $this->core->debug(0, "$event: Expected a number but got this value \"{$parms[2]}\"");
				}
				break;
			case 'atan':
				if ($parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3))
				{
					if (is_numeric($parms[2])) $this->core->set($parms[0], $parms[1], atan($parms[2]));
					else $this->core->debug(0, "$event: Expected a number but got this value \"{$parms[2]}\"");
				}
				break;
			
			case 'radiansToDegrees':
				if ($parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3))
				{
					if (is_numeric($parms[2])) $this->core->set($parms[0], $parms[1], rad2deg($parms[2]));
					else $this->core->debug(0, "$event: Expected a number but got this value \"{$parms[2]}\"");
				}
				break;
			case 'degreesToRadians':
				if ($parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3))
				{
					if (is_numeric($parms[2])) $this->core->set($parms[0], $parms[1], deg2rad($parms[2]));
					else $this->core->debug(0, "$event: Expected a number but got this value \"{$parms[2]}\"");
				}
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

$core=core::assert();
$trig=new Trig();
$core->registerModule($trig);



?>
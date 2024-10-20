<?php
# Copyright (c) 2015-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# Hash stuff
class AchelHash extends Module
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
				$this->core->registerFeature($this, array('crcResultVar', 'crc'), 'crcResultVar', "For each item in the result set, calculate the CRC of a specified value and set a specified value to that CRC. --crcResultVar=inputValueName,outputValueName . Please see the warning on http://uk3.php.net/crc32 for information about it's acuracy. You may want to check out --positiveCRCResultVar which is good enough for what I want.", array('result', 'crc', 'Manipulations'));
				$this->core->registerFeature($this, array('positiveCRCResultVar', 'positiveCRC'), 'positiveCRCResultVar', "For each item in the result set, calculate the CRC of a specified value and set a specified value to that CRC. --positiveCRCResultVar =inputValueName,outputValueName . If the result is negative, take the absolute value. Please see the warning on http://uk3.php.net/crc32 for why this is useful. Note that this output may not be consistent with other applications generating a CRC. If you need that consistency, then you probably want --crc.", array('result', 'crc', 'Manipulations'));
				$this->core->registerFeature($this, array('crcVar'), 'crcVar', "Calculate the CRC of a specified value. --crcResultVar=Category,variable,inputValue . Please see the warning on http://uk3.php.net/crc32 for information about it's acuracy. You may want to check out --positiveCRCResultVar which is good enough for what I want.", array('result', 'crc', 'Manipulations'));
				$this->core->registerFeature($this, array('positiveCRCVar'), 'positiveCRCVar', "Calculate the CRC of a specified value. --positiveCRCVar=Category,variable,inputValue . If the result is negative, take the absolute value. Please see the warning on http://uk3.php.net/crc32 for why this is useful. Note that this output may not be consistent with other applications generating a CRC. If you need that consistency, then you probably want --crc.", array('result', 'crc', 'Manipulations'));
				
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'crcResultVar':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2, true);
				return $this->crc($this->core->getResultSet(), $parms[0], $parms[1], false);;
				break;
			case 'positiveCRCResultVar':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 2, 2, true);
				return $this->crc($this->core->getResultSet(), $parms[0], $parms[1], true);;
				break;
			case 'crcVar':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3, true);
				$this->core->set($parms[0], $parms[1], crc32($parms[2]));
				break;
			case 'positiveCRCVar':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3, true);
				$this->core->set($parms[0], $parms[1], crc32($parms[2]));
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function crc($resultSet, $inputValueName, $outputValueName)
	{
		foreach ($resultSet as $key=>&$item)
		{
			if (isset($item[$inputValueName]))
			{
				$item[$outputValueName]=abs(crc32($item[$inputValueName]));
			}
			else $this->debug($this->l3, __CLASS__."->crc(<dataset>, $inputValueName, $outputValueName): Inputvalue $inputValueName did not exist in row with key $key.");
		}
		
		return $resultSet;
	}
}

$core=core::assert();
$hash=new AchelHash();
$core->registerModule($hash);
 
?>
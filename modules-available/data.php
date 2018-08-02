<?php
# Copyright (c) 2012-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# Manage confiuration

class Data extends Module
{
	private $storageDir=null;
	
	function __construct()
	{
		parent::__construct('Data');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('saveStoreToConfig'), 'saveStoreToConfig', 'Save all store values for a particular module name. Will be auto-loaded. --saveStoreToConfig=storeName');
				$this->core->registerFeature($this, array('loadStoreFromConfig'), 'loadStoreFromConfig', 'Load all store values for a particular module name. --loadStoreFromConfig=storeName');
				$this->core->registerFeature($this, array('deleteData', 'dataDelete'), 'deleteData', 'Deletes the json file storing the data for a particular store. --deleteData=StoreName . NOTE This will not ask for confirmation. Use care that you are deleting the right thing.');
				$this->core->registerFeature($this, array('deleteConfig'), 'deleteConfig', 'Deletes the json file storing the config for a particular store. --deleteConfig=StoreName . NOTE This will not ask for confirmation. Use care that you are deleting the right thing.');
				
				$this->core->registerFeature($this, array('dataExists'), 'dataExists', 'Returns whether a named data store exists in the data directory. --dataExists=Category,variable,namedStore .', array('data', 'exists', 'filesystem'));
				$this->core->registerFeature($this, array('configExists'), 'configExists', 'Returns whether a named config store exists in the config directory. --configExists=Category,variable,namedStore .', array('data', 'exists', 'filesystem'));
				
				$this->core->registerFeature($this, array('saveStoreToData'), 'saveStoreToData', 'Save all store values for a particular module name. --saveStoreToData=storeName');
				$this->core->registerFeature($this, array('loadStoreFromData'), 'loadStoreFromData', 'Load all store values for a particular module name. --loadStoreFromData=storeName');
				$this->core->registerFeature($this, array('loadStoreFromDataDir'), 'loadStoreFromDataDir', 'Load all store values for a particular module name using a directory of json files. --loadStoreFromDataDir=storeName,dirName (dirName is automatically prefixed with the mass data directory, so you would put in something like --loadStoreFromDataDir=Hosts,1LayerHosts)');
				
				$this->core->registerFeature($this, array('loadStoreVariableFromFile'), 'loadStoreVariableFromFile', 'Load the contents of a json file into a store variable. --loadStoreVariableFromFile=fileName,StoreName,variableName . This is basically the same as--loadStoreFromFile=filename except for the destination. Note that the file name MUST be in the form storeName.config.json where storeName is the destination name of the store that you want to save.');
				$this->core->registerFeature($this, array('loadStoreFromFile'), 'loadStoreFromFile', 'Load all store values for a particular name from a file. Note that the file name MUST be in the form storeName.config.json where storeName is the destination name of the store that you want to save. This can be useful for importing config. --loadStoreFromFile=filename[,StoreName]');
				$this->core->registerFeature($this, array('saveStoreToFile'), 'saveStoreToFile', 'Save all store values for a particular module name to a file. This can be useful for exporting data to other applications. --saveStoreToFile=fullPathToFilename[,StoreName]');
				$this->core->registerFeature($this, array('assertFileExists'), 'assertFileExists', "Assert that a file exists. If it doesn't an empty file will be created.", array('file'));

				$this->storageDir=$this->core->get('General', 'storageDir');
				$this->loadConfig();
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'saveStoreToConfig':
				$this->saveStoreEntry($this->core->get('Global', $event), 'config');
				break;
			case 'loadStoreFromConfig':
				$this->loadStoreEntryFromName($this->core->get('Global', $event), 'config');
				break;
			case 'deleteData':
				$this->deleteData($this->core->get('Global', $event), 'data');
				break;
			case 'deleteConfig':
				$this->deleteData($this->core->get('Global', $event), 'config');
				break;
			
			case 'dataExists':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 3, 3);
				$this->dataExists($parms[0], $parms[1], $parms[2], 'data');
				break;
			case 'configExists':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 3, 3);
				$this->dataExists($parms[0], $parms[1], $parms[2], 'config');
				break;
			
			case 'saveStoreToData':
				$this->saveStoreEntry($this->core->get('Global', $event), 'data');
				break;
			case 'loadStoreFromData':
				$this->loadStoreEntryFromName($this->core->get('Global', $event), 'data');
				break;
			case 'loadStoreFromDataDir':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$this->loadStoreEntryFromDataDir($parms[0], $parms[1]);
				break;
			case 'loadStoreFromFile':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 1);
				$this->loadStoreEntryFromFilename($parms[0], $parms[1]);
				break;
			
			case 'loadStoreVariableFromFile':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 3, 1);
				$this->loadStoreEntryFromFilename($parms[0], $parms[1], $parms[2]);
				break;
			case 'saveStoreToFile':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$this->saveStoreEntryToFilename($parms[0], $parms[1]);
				break;
			case 'assertFileExists':
				$this->assertFileExists($this->core->get('Global', $event));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function loadStoreEntry($storeName, $filename, $variableName=false, $source='config')
	{
		$filenameTouse=false;
		if (file_exists($filename)) $filenameTouse=$filename;
		elseif (file_exists($this->storageDir."/$source/$filename")) $filenameTouse=$this->storageDir."/$filename";
		else
		{
			$this->core->debug(3, "loadStoreEntry: Could not find $filename.");
			return false;
		}
		
		$config=json_decode(file_get_contents($filenameTouse), 1);
		if ($variableName) $this->core->set($storeName, $variableName, $config);
		else $this->core->setCategoryModule($storeName, $config);
	}
	
	function loadStoreEntryFromFilename($filename, $storeName=false, $variableName=false)
	{
		# expecting storeName.config.json or /path/to/achelHome/config/storeName.config.json
		
		// Strip off any path that may be there
		$fullFilenameParts=explode('/', $filename);
		$noPath=$fullFilenameParts[count($fullFilenameParts)-1];
		
		// Strip off just the store name
		$filenameParts=explode('.', $noPath);
		if (!$storeName) $storeName=$filenameParts[0];
		
		return $this->loadStoreEntry($storeName, $filename, $variableName);
	}
	
	function saveStoreEntryToFilename($fileName, $storeName=false, $variableName=false)
	{
		# TODO Is it really config? Or data?
		# expecting storeName.config.json or /path/to/achelHome/config/storeName.config.json
		
		// Strip off any path that may be there
		$fullFilenameParts=explode('/', $fileName);
		$noPath=$fullFilenameParts[count($fullFilenameParts)-1];
		
		// Strip off just the store name
		$filenameParts=explode('.', $noPath);
		if (!$storeName) $storeName=$filenameParts[0];
		
		$stuffToSave=($variableName)?$this->core->get($storeName,$variableName):$this->core->getCategoryModule($storeName);
		
		if (!file_put_contents($fileName, json_encode($stuffToSave)))
		{
			$this->core->debug(0, "Was unable to save $fileName. Two common causes are permissions and the destination directory not existing. If you previously got a message \"No cache found. A re-install may be required.\" in this output, please re-install.");
		}
	}
	
	function loadStoreEntryFromName($storeName, $source='config')
	{
		$filename=$this->storageDir."/$source/$storeName.$source.json";
		return $this->loadStoreEntry($storeName, $filename);
	}
	
	function loadStoreEntryFromDataDir($storeName, $dirName)
	{
		$fileList=$this->core->getFileList($this->storageDir.'/data/'.$dirName);
		$result=array();
		foreach ($fileList as $fileName)
		{
			# TODO this needs to be made more resilient to failure
			$preResult=json_decode(file_get_contents($fileName), 1);
			
			$result=array_merge($result, $preResult);
		}
		
		$this->core->setCategoryModule($storeName, $result);
		return true;
	}

	function loadConfig()
	{
		$configFiles=$this->core->getFileList($this->storageDir.'/config');
		foreach ($configFiles as $filename=>$fullPath)
		{
			$this->loadStoreEntryFromFilename($fullPath);
		}
	}
	
	function saveStoreEntry($storeName, $source='config')
	{
		if ($config=$this->core->getCategoryModule($storeName))
		{
			$fullPath="{$this->storageDir}/$source/$storeName.$source.json";
			file_put_contents($fullPath, json_encode($config));
		}
	}
	
	function assertFileExists($fileName)
	{
		if (!file_exists($fileName)) file_put_contents($fileName, '');
	}
	
	function deleteData($storeName, $source='config')
	{
		$fullPath="{$this->storageDir}/$source/$storeName.$source.json";
		if (file_exists($fullPath))
		{
			if (!unlink($fullPath))
			{
				$this->core->debug(1, "deleteData: Failed to delete \"$fullPath\" with error \"$result\".");
			}
		}
	}
	
	function dataExists($Category, $variable, $storeName, $source='config')
	{
		$fullPath="{$this->storageDir}/$source/$storeName.$source.json";
		$result=(file_exists($fullPath))?achelTrue:achelFalse;
		$this->core->set($Category, $variable, $result);
	}
}

$core=core::assert();
$data=new Data();
$core->registerModule($data);
 
?>
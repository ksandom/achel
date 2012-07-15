<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

# Manage hosts

class Hosts extends Module
{
	private $dataDir=null;
	
	function __construct()
	{
		parent::__construct('Hosts');
	}
	
	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('search'), 'search', 'List/Search host entries. ', array('search'));
				$this->core->registerFeature($this, array('searchOld'), 'searchOld', 'Deprecated. List/Search host entries. ', array('deprecated', 'search'));
				$this->core->registerFeature($this, array('importFromHostsFile'), 'importFromHostsFile', 'Import host entries from a hosts file.', array('import'));
				$this->core->registerFeature($this, array('reloadOldStyleHosts'), 'reloadOldStyleHosts', 'Import host entries from a hosts file.', array('hosts', 'src'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'search':
				return $this->listHosts();
				break;
			case 'searchOld':
				return $this->oldListHosts();
				break;
			case 'reloadOldStyleHosts':
				return $this->loadOldStyleHostDefinitions();
				break;
			case 'importFromHostsFile':
				return $this->importFromHostsFile();
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
	function hostMatches($host, $search)
	{
		if (!$search) return true; # If no search, return all results.
		
		foreach ($host as $key=>$detail)
		{
			if (is_string($detail))
			{
				if (preg_match('/'.$search.'/', $detail) or !$search)
				{
					$this->core->debug(5, "hostMatches: Matched search=\"$search\", detail=\"$detail\"");
					return true;
				}
				else
				{
					$this->core->debug(3, "hostMatches: Did not match on search=$search key=$key value=$detail");
				}
			}
			elseif(is_array($detail))
			{
				$this->core->debug(3, "hostMatches: Nested into array key=$key");
				if ($this->hostMatches($detail, $search)) return true;
			}
			else
			{
				$this->core->debug(5, "hostMatches: What is this? key=$key type=".gettype($detail));
			}
		}
	}
	
	function assertHostDefinitionsLoaded($folderName='1LayerHosts', $destination="hostDefinitions")
	{
		if (!$this->core->get('Hosts', $destination)) 
		{
			$this->core->debug(4, "assertHostDefinitionsLoaded: Need to load $destination.");
			$this->loadHostDefinitions($folderName, $destination);
		}
		else $this->core->debug(4, "assertHostDefinitionsLoaded: NO need to load $destination.");
	}
	
	function loadHostDefinitions($folderName='1LayerHosts', $destination="hostDefinitions")
	{
		$this->dataDir=$this->core->get('General', 'configDir').'/data';
		$hostFiles=$this->core->getFileList($this->dataDir."/$folderName");
		$allHostDefinitions=array();
		$this->core->debug(3, "loadHostDefinitions: Loading folder $folderName into $destination.");
		foreach ($hostFiles as $filename=>$hostFile)
		{
			$allHostDefinitions[$filename]=json_decode(file_get_contents($hostFile));
			$this->core->debug(4, "loadHostDefinitions:   Loaded $hostFile into $destination.");
		}
		
		$this->core->set('Hosts', $destination, $allHostDefinitions);
	}

	function listHosts()
	{
		$this->assertHostDefinitionsLoaded();
		$output=array();
		
		$search=$this->core->get('Global', 'search');
		$allHostDefinitions=$this->core->get('Hosts', 'hostDefinitions');
		foreach ($allHostDefinitions as $filename=>$fileDetails)
		{
			$this->core->debug(4, "listHosts: $filename");
			$this->processCategory($output, $search, $fileDetails, $filename, 'default');
		}
		
		return $output;
	}

	function oldListHosts()
	{
		$this->assertHostDefinitionsLoaded('hosts', 'oldStyleHostDefinitions');
		$output=array();
		
		$search=$this->core->get('Global', 'searchOld');
		$allHostDefinitions=$this->core->get('Hosts', 'oldStyleHostDefinitions');
		foreach ($allHostDefinitions as $filename=>$fileDetails)
		{
			foreach ($fileDetails as $categoryName=>$categoryDetails)
			{
				$this->processCategory($output, $search, $categoryDetails, $filename, $categoryName);
			}
		}
		
		return $output;
	}
	
	function processCategory(&$output, $search, $categoryDetails, $filename, $categoryName='unknown')
	{
		if ($categoryDetails)
		{
			$this->core->debug(5, "processCategory: categoryDetails is ".gettype($categoryDetails));
			foreach ($categoryDetails as $hostName=>$hostDetails)
			{
				if ($this->hostMatches($hostDetails, $search))
				{
					$iip=(isset($hostDetails->internalIP))?$hostDetails->internalIP:false;
					$eip=(isset($hostDetails->externalIP))?$hostDetails->externalIP:false;
					$ifqdn=(isset($hostDetails->internalFQDN))?$hostDetails->internalFQDN:false;
					$efqdn=(isset($hostDetails->externalFQDN))?$hostDetails->externalFQDN:false;
					if (is_numeric($hostName) and isset($hostDetails->hostname)) $hostName=$hostDetails->hostname;
					
					$output[]=array('filename'=>$filename, 'categoryName'=>$categoryName, 'hostName'=>$hostName, 'internalIP'=>$iip, 'externalIP'=>$eip, 'internalFQDN'=>$ifqdn, 'externalFQDN'=>$efqdn);
				}
				else $this->core->debug(4, "Did not match $hostName");
			}
		}
		else
		{
			$this->core->debug(1, "processCategory: categoryDetails is ".gettype($categoryDetails).". This might be a problem. Here is other stuff we know: file=$filename, cat=$categoryName, search=$search");
		}
	}
	
	function importFromHostsFile()
	{
		if (file_exists('/etc/hosts'))
		{
			if ($contents=file_get_contents('/etc/hosts')) return $this->processHostsFile($contents);
			else $this->core->complain($this, "Didn't get any contents from /etc/hosts. Permissions?");
		}
		else $this->core->complain($this, "Could not find /etc/hosts. Are you on a real computer?");
	}
	
	function processHostsFile($fileContents)
	{
		# TODO make this work for more types of host file
		/*
			This is a first stab at reading the hosts file. Feel free to add your own situations, but please keep it generic enough that it doesn't break the common situations.
		*/
		
		$output=array();
		$lines=explode("\n", $fileContents);
		foreach ($lines as $line)
		{
			$trimmedLine=trim($line);
			if ($trimmedLine)
			{
				if (substr($trimmedLine,0, 1)!='#')
				{
					# TODO one of the ranges of regex functions is deprecated. Check this isn't one.
					$line=preg_replace('/\ +/', "\t", $line);
					$line=preg_replace('/\#.*$/', "\t", $line);
					
					$parts=explode("\t", $line);
					$numberOfParts=count($parts);
					if ($numberOfParts>1)
					{
						$lineOutput=(isset($output[$parts[1]]))?$output[$parts[1]]:array();
						if (!isset($lineOutput['hostNameMap']))
						{
							$lineOutput['hostNameMap']=array();
							$lineOutput['hostNameCount']=0;
						}
						
						$ipKey=(strpos($parts[0], '.'))?'internalIP':'internalIPv6';
						$lineOutput[$ipKey]=$parts[0];
						$lineOutput['hostName']=$parts[1];
						
						for ($i=0; $i<$numberOfParts; $i++)
						{
							if (!(isset($lineOutput['hostNameMap'][$parts[$i]])) and $parts[$i]!=$lineOutput['hostName'] and (trim($parts[$i])))
							{
								$lineOutput['hostNameMap'][$parts[$i]]=$parts[$i];
								$lineOutput['hostNameCount']++;
								$lineOutput['hostName'.$lineOutput['hostNameCount']]=$parts[$i];
							}
						}
						
						$output[$lineOutput['hostName']]=$lineOutput;
					}
				}
			}
		}
		
		return $output;
	}
}

$core=core::assert();
$core->registerModule(new Hosts());
 
?>
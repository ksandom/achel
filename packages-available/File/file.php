<?php
# Copyright (c) 2014, Kevin Sandom under the BSD License. See LICENSE for full details.

# File operations

class FileSystem extends Module
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
				$this->core->registerFeature($this, array('listFiles'), 'listFiles', 'List files in a given directory. --listFiles=path . path is the absolute path to the directory. eg /usr/lib/puppet/yaml', array('file', 'files'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'listFiles':
				return $this->core->getFileList($this->core->get('Global', $event));
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
	
}

$core=core::assert();
$file=new FileSystem();
$core->registerModule($file);

?>
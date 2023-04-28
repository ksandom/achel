<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Foundations for streaming to/from the filesystem.

class FileBasedFaucet extends Faucet
{
	protected $inResource=null;
	protected $outResource=null;
	private $fileTailHack=false;

	function __construct($objectType)
	{
		parent::__construct($objectType);
	}

	function deconstruct()
	{
		if ($this->inResource) @fclose($this->inResource);
		if ($this->outResource) @fclose($this->outResource);
	}

	function preGet()
	{
		$resourceType=gettype($this->inResource);

		if ($resourceType=='object') return $this->outFill($this->inResource->get());
		else
		{
			return $this->outFill($this->getResource());
		}
	}

	function getResource()
	{
		if (!$this->inResource)
		{
			$this->core->debug(3, "getResource: No data for class {$this->objectType}. # TODO put some useful info about exactly what instance is generating this error.");
			return false;
		}

		$this->inputBuffer='';


		while ($input=fgets($this->inResource, 4096))
		{
			$this->inputBuffer.=$input;
		}

		if ($processedInput=$this->processInputBuffer()) return $processedInput;
		else
		{
			$this->core->debug(4, "processInputBuffer: we have data");
			if ($this->fileTailHack) fseek($this->inResource, -1);
			return false;
		}
	}

	function useFileTailHack()
	{
		$this->fileTailHack=true;
	}
}


?>

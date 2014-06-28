<?php
# Copyright (c) 2014, Kevin Sandom under the BSD License. See LICENSE for full details.

# Provides General IO Faucets. Eg file, terminal etc

/*
	FileFaucet
	TermFaucet
	ProcFaucet
*/


class GeneralIOFaucets extends Faucets
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
				$this->core->registerFeature($this, array('createFileFaucet'), 'createFileFaucet', "Create a faucet to/from a file. --createFileFaucet=faucetName,fileName", array());
				$this->core->registerFeature($this, array('createTermFaucet'), 'createTermFaucet', "Create a faucet to/from a terminal. --createTermFaucet=faucetName", array());
				$this->core->registerFeature($this, array('createProcFaucet'), 'createProcFaucet', "Create a faucet to/from a process. --createProcFaucet=faucetName,command", array());
				
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'createFileFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$this->currentFaucet->createFaucet($parms[0], 'file', new FileFaucet($parms[1]));
				break;
			case 'createTermFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$this->currentFaucet->createFaucet($parms[0], 'term', new TermFaucet());
				break;
			case 'createProcFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$this->currentFaucet->createFaucet($parms[0], 'proc', new ProcFaucet($parms[1]));
				break;
			
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

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

class FileFaucet extends FileBasedFaucet
{
	function __construct($fileName)
	{
		parent::__construct(__CLASS__);
		$this->outResource=fopen("$fileName","a+");
		$this->inResource=$this->outResource;
		stream_set_blocking($this->inResource, 0);
		#$this->useFileTailHack();
	}
	
	function put($data, $channel)
	{ /* Send data out
		Expects an array of strings.
		*/
		
		if (is_array($data))
		{
			foreach ($data as $line)
			{
				if (is_string($line)) fputs($this->outResource, "$line\n");
				else $this->core->debug(2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but struck ".gettype($line)." in the array.");
			}
		}
		else $this->core->debug(2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but got ".gettype($data));
	}
}

class TermFaucet extends FileBasedFaucet
{
	function __construct()
	{
		parent::__construct(__CLASS__);
		$this->inResource=fopen("php://stdin","r");
		stream_set_blocking($this->inResource, 0);
	}
	
	function put($data, $channel)
	{ /* Send data out
		Expects an array of strings.
		*/
		
		if (is_array($data))
		{
			foreach ($data as $line)
			{
				if (is_string($line)) echo "$line\n";
				elseif (!is_bool($line)) $this->core->debug(2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but struck ".gettype($line)." in the array.");
			}
		}
		elseif(!is_bool($data)) $this->core->debug(2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but got ".gettype($data));
	}
}

class ProcFaucet extends Faucet
{
	private $procFaucets=null;
	private $procDescriptors=null;
	private $proc=null;
	private $cmd='';
	
	function __construct($cmd)
	{
		parent::__construct(__CLASS__);
		
		$this->cmd=$cmd;
		$this->procDescriptors=array(
			procOut=>array('pipe', 'r'),
			procIn=>array('pipe', 'w'),
			procError=>array('file', '/tmp/error', 'a'),
			);
		
		$this->proc=proc_open($cmd, $this->procDescriptors, $this->procFaucets, '/tmp', null);
		
		stream_set_blocking($this->procFaucets[procIn], 0);
	}
	
	function deconstruct()
	{
		# TODO faucets got caught in the terminology migration. It needs to be pipes
		foreach ($this->procFaucets as &$faucet) fclose($faucet);
		proc_close($this->proc);
	}
	
	function getResource($resouceID)
	{
		# TODO procFaucets got caught in the terminology migration. It needs to be procPipes
		while ($input=stream_get_contents($this->procFaucets[$resouceID]))
		{
			$this->inputBuffer.=$input;
		}
		
		$result=$this->inputBuffer;
		$this->inputBuffer='';
		return $result;
	}
	
	function preGet()
	{
		if ($input=$this->getResource(procIn))
		{
			$output=explode(inputLineSeparator, $input);
			
			// Removing the last entry is necessary since every line is terminated with a newline character, and that character is the delimiter for splitting the string into an array. Therefore there is an extra blank entry that we don't want.
			$last=count($output)-1;
			if (!$output[$last]) unset($output[$last]);
			return $this->outFill($output);
		}
		else return false;
	}
	
	function control($feature, $value)
	{
		switch ($feature)
		{
			case 'c':
				# TODO put in semantics for this. It should be defined when the object is created.
				$this->control('code', $feature);
				break;
			case 'code':
				# TODO put in semantics for this. It should be defined when the object is created.
				$this->core->debug(2, "Sending control $value to {$this->cmd}");
				$code="\033$value";
				# TODO BUG This is sending ^]$code, not ^$code
				fwrite($this->procFaucets[procOut], "$code");
				break;
			case 'localBreak':
				# TODO put in semantics for this. It should be defined when the object is created.
				$this->control('kill', 1);
				break;
			case 'localKill':
				# TODO put in semantics for this. It should be defined when the object is created.
				$signal=($value)?$value:15;
				// NOTE proc_terminate($this->proc, $signal) does not behave in a useful manner at this time. Therefore the following work-around is in place.
				
				$status=proc_get_status($this->proc);
				print_r($status);
				if (posix_kill($status['pid'], $signal)) $this->core->debug(2, "Success sent signal $signal to {$this->cmd}");
				else $this->core->debug(2, "Failure sending signal $signal to {$this->cmd}");
				break;
			default:
				return parent::control($feature, $value);
				break;
		}
	}
	
	function put($data, $channel)
	{ /* Send data out
		Expects an array of strings.
		*/
		if (is_array($data))
		{
			switch ($channel)
			{
				case '_control':
					foreach ($data as $line)
					{
						$parms=$this->core->splitOnceOn(' ', $line);
						$this->control($parms[0], $parms[1]);
					}
					break;
				default:
					foreach ($data as $line)
					{
						if (is_string($line)) fwrite($this->procFaucets[procOut], "$line\n");
						else $this->core->debug(2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but struck ".gettype($line)." in the array.");
					}
					break;
			}
		}
		elseif(is_string($data)) $this->core->debug(2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings but got a string: \"$line\"");
		else $this->core->debug(2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but got ".gettype($data));
	}
}






$core=core::assert();
$core->registerModule(new GeneralIOFaucets());

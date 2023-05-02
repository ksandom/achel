<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# For interacting with a process.

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
		# TODO this is almost certainly unnecessary.
		# flush();
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
				$this->debug(2, "Sending control $value to {$this->cmd}");
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
				if (posix_kill($status['pid'], $signal)) $this->debug(2, "Success sent signal $signal to {$this->cmd}");
				else $this->debug(2, "Failure sending signal $signal to {$this->cmd}");
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
						else $this->debug(1, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but struck ".gettype($line)." in the array.");
					}
					break;
			}
		}
		elseif(is_string($data)) $this->debug(2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings but got a string: \"$line\"");
		else $this->debug(2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but got ".gettype($data));
	}
}

?>

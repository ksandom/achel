<?php
# Copyright (c) 2012-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# Covert to and work with strings

class AchelString extends Module
{
	private $outputFile=false;

	function __construct()
	{
		parent::__construct(__CLASS__);
	}

	function event($event)
	{
		switch ($event)
		{
			case 'init':
				// This isn't ready for usage yet.
				$this->core->registerFeature($this, array('s', 'singleString'), 'singleString', 'Set final output to send the returned output as one large string. Each entry will be separated by a new line.', array('string'));
				$this->core->registerFeature($this, array('stringToFile'), 'stringToFile', 'Send returned output as a string to a file at the end of the processing. Each entry will be separated by a new line. --stringToFile=filename', array('string'));
				$this->core->registerFeature($this, array('singleStringNow'), 'singleStringNow', 'Send returned output as a string. Each entry will be separated by a new line. --singleStringNow[=filename] . If filename is omitted, stdout will be used instead.', array('string'));
				$this->core->registerFeature($this, array('getSingleString'), 'getSingleString', 'Return a single string containing all the results.', array('string'));
				$this->core->registerFeature($this, array('getSingleStringUsingSeparator'), 'getSingleStringUsingSeparator', 'Return a single string containing all the results with a custom separator --getSingleStringUsingSeparator=separator .', array('string'));
				$this->core->registerFeature($this, array('getSingleStringUsingSeparatorNoNL'), 'getSingleStringUsingSeparatorNoNL', 'Return a single string containing all the results with a custom separator, without an extra new line appended at the end. --getSingleStringUsingSeparatorNoNL=separator .', array('string'));
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'singleString':
				$this->stringToFile();
				break;
			case 'stringToFile':
				$this->stringToFile($this->core->get('Global', $event));
				break;
			case 'singleStringNow':
				$output=$this->singleStringNow($this->core->get('Global', $event), $this->core->getResultSet());;
				if (is_array($output))$output=implode(',', $output); # TODO decide if this is the best way to output it
				echo $output;
				break;
			case 'getSingleString':
				return $this->singleStringNow(false, $this->core->getResultSet());
				break;
			case 'getSingleStringUsingSeparator':
				return $this->singleStringNow(false, $this->core->getResultSet(), $this->core->get('Global', $event));
			case 'getSingleStringUsingSeparatorNoNL':
				return $this->singleStringNow(false, $this->core->getResultSet(), $this->core->get('Global', $event), '');
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

	function stringToFile($filename=false)
	{
		# perfom checks
		#if ($filename!==false) # TODO We could check for bad paths

		# set filename
		$this->outputFile=$filename;

		# set output type
		$this->core->setRef('General', 'outputObject', $this);
	}

	private function safeImplode($separator, $input)
	{
		foreach ($input as $key => $value)
		{
			if (is_array($value))
			{
				$input[$key]=$this->safeImplode($separator, $value);
			}
		}

		return implode($separator, $input);
	}

	function singleStringNow($filename, $output, $separator="\n", $endChar="\n")
	{
		$readyValue=(is_array($output))?$this->safeImplode($separator, $output).$endChar:$output;
		if ($filename)
		{
			$this->debug(3, "singleStringNow: Sending to $filename");
			file_put_contents($filename, $readyValue);
		}
		else
		{
			$this->debug(3, "singleStringNow: Returning value of length ".strlen($readyValue));
			return array($readyValue);
		}
	}

	function out($output)
	{
		$this->debug(4, "String: Writing output to {$this->outputFile}");
		$result=$this->singleStringNow($this->outputFile, $output);
		echo $result[0];
	}
}

$core=core::assert();
$achelString=new AchelString();
$core->registerModule($achelString);

?>

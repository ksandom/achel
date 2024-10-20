<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# For streaming data to(and from?) the filesystem.

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
				else $this->debug($this->l2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but struck ".gettype($line)." in the array.");
			}
		}
		else $this->debug($this->l2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but got ".gettype($data));
	}
}

?>

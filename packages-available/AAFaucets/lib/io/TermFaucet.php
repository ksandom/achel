<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# For interacting with a terminal.

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

?>

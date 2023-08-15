<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.

class StreamBasedFaucet extends Faucet
{
	function __construct($objectType)
	{
		parent::__construct($objectType);
	}

	function deconstruct()
	{
		if ($this->resource) fclose($this->resource);
	}
}

?>

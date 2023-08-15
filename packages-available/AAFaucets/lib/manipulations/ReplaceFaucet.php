<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Regex based search and replace.


class ReplaceFaucet extends ThroughBasedFaucet
{
	function __construct()
	{
		parent::__construct(__CLASS__);

		/*
			registerConfigItem($settingName, $subcategory, $description, $type='array')
			setConfigItem($settingName, $subcategory, $value)
			addConfigItemEntry($settingName, $subcategory, $entryName, $entryValue)
			getConfigItem($settingName, $subcategory=false)
			getConfig()
		*/

		# TODO The subcategory could be used for channel so that a single object can do different rules for different channels.
		$this->registerConfigItem('Rules', 'default', 'Regex rules for search and replace. key=search, value=replace.', 'array');
	}

	function preGet()
	{
		$gotSomething=false;
		$rules=$this->getConfigItem('Rules', 'default');

		# TODO It would be worth considering if the rebuild should be triggered only when the rules change.
		$search=array();
		$replace=array();
		foreach ($rules as $ruleSearch=>$ruleReplace)
		{
			$search[]="/$ruleSearch/";
			$replace[]=$ruleReplace[0];
		}

		foreach ($this->input as $channel=>$data)
		{
			$output=array();
			if (is_array($data))
			{
				foreach ($data as $line)
				{
					if (is_string($line))
					{
						$line=preg_replace($search, $replace, $line);
						$output[]="$line";
					}
					else
					{
						$this->debug(2, "ReplaceFaucet->preGet: Expected line to be a string, but got ".gettype($line));
					}
				}

				$this->outFill($output, $channel);
				$this->clearInput($channel);
				$gotSomething=true;
			}
			else
			{
				$this->debug(2, "ReplaceFaucet->preGet: Expected data to be an array, but got ".gettype($data));
			}
		}

		return $gotSomething;
	}
}

?>

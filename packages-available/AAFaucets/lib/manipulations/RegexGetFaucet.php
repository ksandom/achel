<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Match components of a line, and use those to determine a key and a value to send to set via setNested.

class RegexGetFaucet extends ThroughBasedFaucet
{
	function __construct()
	{
		parent::__construct(__CLASS__);

		/*
			Here is an example configuration

			Category - eg location tracking
				variable - eg instances
					rule0 - The first rule
						regex
							regexValue - The actual regex value
						destination
							destinationValue - Where values should be saved.
						0 - Mapping for the first value found
							valueName
								valueNameValue - What they key should be called. (See note below)
						1 - Mapping for the second value found
							valueName
								valueNameValue - What they key should be called. (See note below)
						2 etc

			Note:
				valueName can be any arbitrary name that is a valid variable name. However there are two special cases:
					* key - The value will define the valueName of an entry mapped to "value".
					* value - The item mapped to value to become the value of the entry that was defined by key.

				In this example, there should be a line after creating the faucet like this

					deliver faucetName,_control,setConfigSrc Category,variable
						# TODO This currently won't work because variable will be interpreted as a separate parameter. Double check this.

			In reality this looks something like this

			setNested ["AP","stateConfig","rule0","regex","^.*./(.*) = '(.*)'.*$"]
			setNested ["AP","stateConfig","rule0","destination","AP,state"]
			setNested ["AP","stateConfig","rule0","0","key"]
			setNested ["AP","stateConfig","rule0","1","value"]

			Note that the regex is slightly mangled in this example so that it doesn't interfere with the PHP comment syntax.
		*/
	}

	function preGet()
	{
		$gotSomething=false;

		foreach ($this->input as $channel=>$data)
		{
			if ($channel=='_control')
			{
				$this->callControl($data);
				$this->clearInput($channel);
				$gotSomething=true;
				continue;
			}

			if (is_array($data))
			{
				foreach ($data as $line)
				{
					if (is_string($line))
					{
						foreach ($this->config as $ruleName=>$rule)
						{
							if (!isset($rule['regex']))
							{
								$this->debug(2, "RegexGetFaucet->preGet: Missing regex in rule \"$ruleName\".");
								break;
							}
							if (!isset($rule['destination']))
							{
								$this->debug(2, "RegexGetFaucet->preGet: Missing destination in rule \"$ruleName\".");
								break;
							}

							$matches=array();
							if (preg_match('/'.$rule['regex'].'/', $line, $matches))
							{
								$this->debug(3, "RegexGetFaucet->preGet: Matched rule \"$ruleName\" on line \"$line\".");
								$key=false;
								$value=false;

                                foreach ($matches as $matchKey=>$match)
								{
									if (!isset($rule[$matchKey]))
									{
										$this->debug(4, "RegexGetFaucet->preGet: Skipping match $matchKey since there is nothing defined for it in the rule \"$ruleName\".");
										continue;
									}

									$mappingKey=$rule[$matchKey];
									switch ($mappingKey)
									{
										case 'key':
											$key=$match;
											break;
										case 'value':
											$value=$match;
											break;
										default:
											$destination=$rule['destination'].",$channel,$mappingKey";
											$this->core->setNestedViaPath($destination, $match);
											break;
									}
								}

								if ($key!==false and $value !==false)
								{
									$destination=$rule['destination'].",$channel,$key";
									$this->core->setNestedViaPath($destination, $value);
									$this->debug(3, "RegexGetFaucet->preGet: key=\"$key\" value=\"$value\" destination=\"$destination\"");
								}
							}
							else
							{
								$this->debug(3, "Did not match rule \"$ruleName\" ({$rule['regex']}) on line \"$line\".");
							}
						}
					}
					else
					{
						$this->debug(2, "RegexGetFaucet->preGet: Expected line to be a string, but got ".gettype($line));
					}
				}

				$this->clearInput($channel);
				$gotSomething=true;
			}
			else
			{
				$this->debug(2, "RegexGetFaucet->preGet: Expected data to be an array, but got ".gettype($data));
			}
		}

		return $gotSomething;
	}
}

?>

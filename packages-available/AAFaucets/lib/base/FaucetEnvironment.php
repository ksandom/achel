<?php
# Copyright (c) 2012-2023, Kevin Sandom under the GPL License. See LICENSE for full details.
# Keeps track of our perspective on the setup.

class FaucetEnvironment
{
	# The environment for containing everything.
	private static $environment=null;

	# For tracking nested faucets
	public $currentFaucet=null;
	public $rootFaucet=null;
	public $core=null;

	private $scopeTracker=array();
	private $scopeNumber=0;

	function __construct()
	{
		$this->core=core::assert();
	}

	public static function &assert()
	{
		if (!isset(self::$environment)) self::$environment=new FaucetEnvironment();
		return self::$environment;
	}

	function createEvironment($createEnvironment=true)
	{
		if ($createEnvironment)
		{
			$this->rootFaucet=new MetaFaucet('root');
			$this->currentFaucet=&$this->rootFaucet;
			$this->core->setRef('Achel','currentFaucet', $this->currentFaucet);
		}
		else
		{
			$this->currentFaucet=&$this->core->get('Achel','currentFaucet');
		}
	}

	function beginScopedEvent(&$faucet, $faucetTopicPath)
	{
		$oldTopic=$this->core->get("ScopedEvent", "topic");
		$this->scopeTracker[$this->scopeNumber]=array('faucet'=>$this->currentFaucet, 'topic'=>$oldTopic);
		$this->currentFaucet=$faucet->getParent();
		$this->core->setRef('Achel','currentFaucet', $this->currentFaucet);
		$this->core->set("ScopedEvent", "topic", $faucetTopicPath);
		$this->scopeNumber++;

		$this->core->debug(3, "Environment/beginScopedEvent: ".$this->currentFaucet->getFullPath());
	}

	function endScopedEvent()
	{
		if ($this->scopeNumber > 0)
		{
			$this->scopeNumber--;
			$this->currentFaucet=&$this->scopeTracker[$this->scopeNumber]['faucet'];
			$this->core->setRef('Achel','currentFaucet', $this->currentFaucet);
			$this->core->set("ScopedEvent", "topic", $this->scopeTracker[$this->scopeNumber]['topic']);
			$this->core->debug(3, "Environment/endScopedEvent: ".$this->currentFaucet->getFullPath());
		}
		else
		{
			$this->core->debug(0, "Environment/endScopedEvent - No more scope nestings. This is a bug.");
		}
	}
}


?>

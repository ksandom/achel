<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# Faucets that help with building an intellegent UI

include 'lib/ui/BasicDiffFaucet.php';
include 'lib/ui/DynamicLastSeenFaucet.php';

class UIFaucets extends Faucets
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
				$this->core->registerFeature($this, array('createBasicDiffFaucet'), 'createBasicDiffFaucet', "Diffs the input from each channel. This diff does not try to sync the data. --createBasicDiffFaucet=faucetName", array());
				$this->core->registerFeature($this, array('createDynamicLastSeenFaucet'), 'createDynamicLastSeenFaucet', "Create a faucet that keeps track of the channels that it recieved data on. If it hasn't recieved data in greater than secondsSinceChannel seconds, that channel name will be displayed will be displayed along with the number of seconds it has been absent. If it hasn't heard from any channel in secondsSinceAnyChannel seconds, it will fall silent. secondsSinceChannel defaults to 5 seconds. secondsSinceAnyChannel defaults to 30 seconds. --createDynamicLastSeenFaucet=faucetName,[secondsSinceChannel][,secondsSinceAnyChannel]", array());

				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'createBasicDiffFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$this->environment->currentFaucet->createFaucet($parms[0], 'basicDiff', new BasicDiffFaucet());
				break;
			case 'createDynamicLastSeenFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 3, 1);
				$dlsf=new DynamicLastSeenFaucet($parms[1], $parms[2]);
				$this->environment->currentFaucet->createFaucet($parms[0], 'dynamicLastSeen', $dlsf);
				break;

			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

$core=core::assert();
$uiFaucets=new UIFaucets();
$core->registerModule($uiFaucets);

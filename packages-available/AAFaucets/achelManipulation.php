<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.
# Faucets for manipulating the data.

/*
	More specific notes here.
*/

include 'lib/manipulations/RegexFaucet.php';
include 'lib/manipulations/DumbReplaceFaucet.php';
include 'lib/manipulations/DumbInsertFaucet.php';
include 'lib/manipulations/LabelFaucet.php';
include 'lib/manipulations/ReplaceFaucet.php';
include 'lib/manipulations/RegexGetFaucet.php';

class ManipulationFaucets extends Faucets
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
				$this->core->registerFeature($this, array('createRegexFaucet'), 'createRegexFaucet', "Create a faucet that directs flow based on regex rules. Further configuration with --setFaucetConfigItem will be needed for this faucet to be useful. --createRegexFaucet=faucetName", array());
				$this->core->registerFeature($this, array('createDumbReplaceFaucet'), 'createDumbReplaceFaucet', "Create a faucet that sends specified output everytime it recieves input. --createDumbReplaceFaucet=faucetName,thingToInsert", array());
				$this->core->registerFeature($this, array('createDumbInsertFaucetAfter'), 'createDumbInsertFaucetAfter', "Create a faucet that inserts specified output (after the input) everytime it recieves input. --createDumbReplaceFaucet=faucetName,thingToInsert", array());
				$this->core->registerFeature($this, array('createDumbInsertFaucetBefore'), 'createDumbInsertFaucetBefore', "Create a faucet that inserts specified output (before the input) everytime it recieves input. --createDumbReplaceFaucet=faucetName,thingToInsert", array());
				$this->core->registerFeature($this, array('createLabelFaucet'), 'createLabelFaucet', "This faucet prepends 'channelName: ' to each line it recieves and sends everything out to the default channel. --createLabelFaucet=faucetName", array());
				$this->core->registerFeature($this, array('createReplaceFaucet'), 'createReplaceFaucet', "This faucet searches for regex and replaces it with the text you designate. You will also need to add rules to define the search and replace. --createReplaceFaucet=faucetName --addFaucetConfigItemEntry=faucetName,Rules,,good,awesome", array());
				$this->core->registerFeature($this, array('createRegexGetFaucet'), 'createRegexGetFaucet', "This faucet pulls out values from input strings using a regex and places them in a destination store.", array());

				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'createRegexFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$newFaucet=new RegexFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'regex', $newFaucet);
				break;
			case 'createDumbReplaceFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$dumbReplaceFaucet=new DumbReplaceFaucet($parms[1]);
				$this->environment->currentFaucet->createFaucet($parms[0], 'dumbReplace', $dumbReplaceFaucet);
				break;
			case 'createDumbInsertFaucetAfter':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$newFaucet=new DumbInsertFaucet($parms[1], false);
				$this->environment->currentFaucet->createFaucet($parms[0], 'dumbInsertAfter', $newFaucet);
				break;
			case 'createDumbInsertFaucetBefore':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$newFaucet=new DumbInsertFaucet($parms[1], true);
				$this->environment->currentFaucet->createFaucet($parms[0], 'dumbInsertBefore', $newFaucet);
				break;
			case 'createLabelFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$labelFaucet=new LabelFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'label', $labelFaucet);
				break;
			case 'createReplaceFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$newFaucet=new ReplaceFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'replace', $newFaucet);
				break;
			case 'createRegexGetFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$newFaucet=new RegexGetFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'regexGet', $newFaucet);
				break;

			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

$core=core::assert();
$achelManipulation=new ManipulationFaucets();
$core->registerModule($achelManipulation);

<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

# Provides General IO Faucets. Eg file, terminal etc

/*
	FileFaucet
	TermFaucet
	ProcFaucet
*/

include 'lib/io/FileBasedFaucet.php';
include 'lib/io/FileFaucet.php';
include 'lib/io/TermFaucet.php';
include 'lib/io/ProcFaucet.php';

class GeneralIOFaucets extends Faucets
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
				$this->core->registerFeature($this, array('createFileFaucet'), 'createFileFaucet', "Create a faucet to/from a file. --createFileFaucet=faucetName,fileName", array());
				$this->core->registerFeature($this, array('createTermFaucet'), 'createTermFaucet', "Create a faucet to/from a terminal. --createTermFaucet=faucetName", array());
				$this->core->registerFeature($this, array('createProcFaucet'), 'createProcFaucet', "Create a faucet to/from a process. --createProcFaucet=faucetName,command", array());

				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'createFileFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$fileFaucet=new FileFaucet($parms[1]);
				$this->environment->currentFaucet->createFaucet($parms[0], 'file', $fileFaucet);
				break;
			case 'createTermFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$termFaucet=new TermFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'term', $termFaucet);
				break;
			case 'createProcFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2);
				$procFaucet=new ProcFaucet($parms[1]);
				$this->environment->currentFaucet->createFaucet($parms[0], 'proc', $procFaucet);
				break;

			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

$core=core::assert();
$generalIO=new GeneralIOFaucets();
$core->registerModule($generalIO);

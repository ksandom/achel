<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.
# Provides the ability to call Achel features from Faucets.

include 'lib/call/CallFaucet.php';
include 'lib/call/MappedCallFaucet.php';
include 'lib/call/InlineCallFaucet.php';

class CallFaucets extends Faucets
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
				$this->core->registerFeature($this, array('create1-1CallFaucet', 'createCallFaucet'), 'create1-1CallFaucet', "Create a faucet to/from a call to a feature. Each channel is processed individually, which gives an explicit 1-1 relatinship between the input and output channels. --createCallFaucet=faucetName,feature,argument", array());
				$this->core->registerFeature($this, array('createMappedCallFaucet'), 'createMappedCallFaucet', "Create a faucet to/from a call to a feature. All channels are processed as one blob of data keyed by channel name. --createCallFaucet=faucetName,feature,argument", array());
				$this->core->registerFeature($this, array('createSemiInlineCallFaucet'), 'createSemiInlineCallFaucet', "Create a faucet to/from a call to a feature, but using a variable as the/an extra parameter.. --createSemiInlineCallFaucet=faucetName,feature,[argument]", array());

				$this->core->registerFeature($this, array('createInlineCallFaucet'), 'createInlineCallFaucet', "Create a faucet that will call what ever feature is passed to it. The result will be it's output . --createInlineCallFaucet=faucetName", array());

				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'create1-1CallFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2, true);
				$callFaucet=new CallFaucet($parms[1], $parms[2]);
				$this->environment->currentFaucet->createFaucet($parms[0], '1-1Call', $callFaucet);
				break;
			case 'createMappedCallFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2, true);
				$mappedCallFaucet=new MappedCallFaucet($parms[1], $parms[2]);
				$this->environment->currentFaucet->createFaucet($parms[0], 'mappedCall', $mappedCallFaucet);
				break;
			case 'createSemiInlineCallFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2, true);
				$callFaucet=new CallFaucet($parms[1], $parms[2], true);
				$this->environment->currentFaucet->createFaucet($parms[0], 'semiInlineCall', $callFaucet);
				break;
			case 'createInlineCallFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 1, 1);
				$inlineCallFaucet=new InlineCallFaucet();
				$this->environment->currentFaucet->createFaucet($parms[0], 'inlineCall', $inlineCallFaucet);
				break;

			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

$core=core::assert();
$callFaucets=new CallFaucets();
$core->registerModule($callFaucets);

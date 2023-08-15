<?php
# Copyright (c) 2019, Kevin Sandom under the GPL License. See LICENSE for full details.

class BalancePID extends BalanceAlgorithm
{
	function __construct()
	{
		$this->setIdentity('PID', array(
				'description'=>'Provides a balance between 3 techniques to give a tunable method that can achieve a quick and stable operation.',
			'pros'=>array(
				'Can get a result quickly.',
				'Can be stable.',
				'Can be tuned.'
				),
			'cons'=>array(
				'Requires some tuning to get the best results.'
				)
			));
		
		
		parent::__construct();
	}
	
	public function resetState(&$rule)
	{
		parent::resetState($rule);
		
		if (!isset($rule['debug'])) $rule['debug']=false;
		
		$this->state[$rule['ruleName']]=array(
			'debug' => $rule['debug'],
			'p' => array (
				'last' => 0
			),
			'w' => array (
        'incrementor' => 1/$rule['pid']['wanderingTime'],
        'position' => 0
			),
			'history' => new TimedDataHistory(10, 1) # TODO Make this configurable.
		);
	}
	
	
	function process($ruleName, &$rule)
	{
		// Make sure we have an initial state.
		if (!isset($rule['output']['live']['value'])) $this->resetState($rule);
		if (!isset($this->state[$ruleName])) $this->resetState($rule);
		
		// Sanity check data
		if (!isset($rule['input']['live']['scaledInputGoal']))
		{
			$this->debug(1, __CLASS__.'->'.__FUNCTION__.": No input. If we've got here, this shouldn't ever happen.");
			return false;
		}
		
		# TODO inputGoal as the input value to compute against is not intuitive, even when reading a working example. Either change it, or well-document it.
		
		$this->state[$ruleName]['value']=$rule['input']['live']['scaledValue'];
		$this->state[$ruleName]['goal']=$rule['input']['live']['scaledGoal'];
		$this->state[$ruleName]['error']=$this->state[$ruleName]['value']-$this->state[$ruleName]['goal'];
		$this->state[$ruleName]['errorValue']=abs($this->state[$ruleName]['error']);
		$this->state[$ruleName]['errorDirection']=($this->state[$ruleName]['error']<0)?-1:1;
		
		$this->state[$ruleName]['history']->addItem($this->state[$ruleName]['error']);
		
		$kP=$rule['pid']['kP'];
		$iP=$rule['pid']['iP'];
		$kW=$rule['pid']['kW'];
		$iW=$rule['pid']['iW'];
		$kI=$rule['pid']['kI'];
		$iI=$rule['pid']['iI'];
		$kD=$rule['pid']['kD'];
		$iD=$rule['pid']['iD'];
		
		# TODO Make sure each section is going in the expected direction.
		$p=$this->cap(-1, $this->calculateP($ruleName, $iP), 1);
		$w=$this->cap(-1, $this->calculateW($ruleName, $iW), 1);
		$i=$this->cap(-1, $this->calculateI($ruleName, $iI), 1);
		$d=$this->cap(-1, $this->calculateD($ruleName, $iD, 10), 1); # TODO Make the cutOff configurable.
		
		$combinedValue=($p*$kP) + ($w*$kW) + ($i*$kI) + ($d*$kD);
		$out=$this->cap(-1, $combinedValue, 1);
		$rule['output']['live']['value']=$out;
		
		if ($this->state[$rule['ruleName']]['debug'])
		{
			# $this->debug(1,"ruleName=$ruleName scaledValue=".$this->state[$ruleName]['value']."(".$rule['input']['live']['value'].") goal=".$this->state[$ruleName]['goal']."(".$rule['input']['live']['goal'].") P=($p*$kP) out=$out");
			$r=3;
			$error=round($this->state[$ruleName]['error'], $r);
			$errorDirection=$this->state[$ruleName]['errorDirection'];
			$rp=round($p, $r);
			$rw=round($w, $r);
			$ri=round($i, $r);
			$rd=round($d, $r);
			$rcv=round($combinedValue, $r);
			$ro=round($out, $r);
			$errorColour=$this->core->get('Color', ($error>0)?'brightPurple':'purple');
			$pColour=$this->core->get('Color', 'green');
			$wColour=$this->core->get('Color', ($w>0)?'brightYellow':'yellow');
			$iColour=$this->core->get('Color', 'cyan');
			$dColour=$this->core->get('Color', 'brightBlue');
			$default=$this->core->get('Color', 'default');
			$this->debug(1,"ruleName=$ruleName {$errorColour}error=$error $errorDirection$default {$pColour}p=$rp{$default}*$kP*$iP {$wColour}w=$rw{$default}*$kW*$iW {$iColour}i=$ri{$default}*$kI*$iI {$dColour}d=$rd{$default}*$kD*$iD {$default}combinedValue=$rcv out=$ro");
		}
	}
	
	private function calculateP($ruleName, $iP)
	{
		# return $this->getSomeDifference($this->cap(-1, $this->state[$ruleName]['error'], 1), $iP, $ruleName, 'P');
		return $this->getSomeDifference($this->state[$ruleName]['error'], $iP, $ruleName, 'P');
	}
	
	private function calculateW($ruleName, $iW)
	{
		if ($this->state[$ruleName]['errorDirection'] > 0)
		{
      $this->state[$ruleName]['w']['position']+=$this->state[$ruleName]['w']['incrementor'];
		}
		else
		{
      $this->state[$ruleName]['w']['position']-=$this->state[$ruleName]['w']['incrementor'];
		}
		
		$this->state[$ruleName]['w']['position']=$this->cap(-1, $this->state[$ruleName]['w']['position'], 1);
		
		$correction=$this->getSomeDifference($this->state[$ruleName]['w']['position'], $iW, $ruleName, 'W');
		return $correction;
	}
	
	private function calculateI($ruleName, $iI)
	{
		$mean=$this->getSomeDifference($this->state[$ruleName]['history']->meanLast(-1), $iI, $ruleName, 'I');
		return $mean*-1;
	}
	
	private function calculateD($ruleName, $iD, $cutOff=10)
	{
		# TODO scale linearly instead of exponentially.
		# TODO make (0, 2) configurable.
		$stepsUntilOverrun=$this->state[$ruleName]['history']->iterationsUntilOverrun(0, 2);
		if (abs($stepsUntilOverrun)>$cutOff) return 0;
		$proportionalError=$stepsUntilOverrun/$cutOff;
		
		if ($stepsUntilOverrun==0) $force=1;
		else
		{
			$force=$this->state[$ruleName]['errorDirection']*$proportionalError*-1;
		}
		
		return $this->getSomeDifference($force, $iD, $ruleName, 'D');
	}
}


$core=core::assert();
$balancePID=new BalancePID();
$core->registerSubModule($balancePID);

?>

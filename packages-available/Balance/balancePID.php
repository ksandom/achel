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
	
	private function resetState($ruleName)
	{
		$this->state[$ruleName]=array(
			'p' => array (
				'last' => 0
			),
			'history' => array(
			)
		);
	}
	
	
	function process($ruleName, &$rule)
	{
		// Make sure we have an initial state.
		if (!isset($rule['output']['live']['value'])) $this->resetState($ruleName);
		
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
		
		$kP=$rule['pid']['kP'];
		$iP=$rule['pid']['iP'];
		$kI=$rule['pid']['kI'];
		$iI=$rule['pid']['iI'];
		$kD=$rule['pid']['kD'];
		$iD=$rule['pid']['iD'];
		
		$p=$this->calculateP($ruleName, $iP);
		$i=$this->calculateI($ruleName, $iI);
		$d=$this->calculateD($ruleName, $iD);
		
		$combinedValue=($p*$kP*-1) + ($i*$kI) + ($d*$kD);
		$out=$this->cap(-1, $combinedValue, 1);
		$rule['output']['live']['value']=$out;
		
		if ($ruleName=='yaw')
		{
			$this->core->debug(1,"ruleName=$ruleName scaledValue=".$this->state[$ruleName]['value']."(".$rule['input']['live']['value'].") goal=".$this->state[$ruleName]['goal']."(".$rule['input']['live']['goal'].") P=($p*$kP) out=$out");
		}
	}
	
	private function calculateP($ruleName, $iP)
	{
		# return $this->getSomeDifference($this->cap(-1, $this->state[$ruleName]['error'], 1), $iP, $ruleName, 'P');
		return $this->state[$ruleName]['error'];
	}
	
	private function calculateI($ruleName, $iI)
	{
		# TODO Write this.
		return 0;
	}
	
	private function calculateD($ruleName, $iD)
	{
		# TODO Write this.
		return 0;
	}
}


$core=core::assert();
$balancePID=new BalancePID();
$core->registerSubModule($balancePID);

?>

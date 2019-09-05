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
		
		$this->state=array();
		
		parent::__construct();
	}
	
	private function resetState($rule)
	{
		$$this->state[$rule]=array(
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
		if (!isset($rule['output']['live']['value'])) $this->resetState($rule);
		
		// Sanity check data
		if (!isset($rule['input']['live']['value']))
		{
			$this->debug(1, __CLASS__.'->'.__FUNCTION__.": No input. If we've got here, this shouldn't ever happen.");
			return false;
		}
		
		$this->state[$ruleName]['value']=$rule['input']['live']['value'];
		$this->state[$ruleName]['goal']=$rule['input']['live']['goal'];
		$this->state[$ruleName]['error']=$this->state[$ruleName]['goal']-$this->state[$ruleName]['value'];
		
		$p=$this->calculateP($ruleName);
		$i=$this->calculateI($ruleName);
		$d=$this->calculateD($ruleName);
		# TODO Combine them.
		
		
		#$rule['input']['live']['inputGoal'];
		#$rule['output']['live']['value']=$output;
	}
	
	private function calculateP($ruleName)
	{
		
	}
	
	private function calculateI($ruleName)
	{
		
	}
	
	private function calculateD($ruleName)
	{
		
	}
}


$core=core::assert();
$balancePID=new BalancePID();
$core->registerSubModule($balancePID);

?>

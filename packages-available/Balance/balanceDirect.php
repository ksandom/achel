<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

class BalanceDirect extends BalanceAlgorithm
{
	function __construct()
	{
		$this->setIdentity('direct', array(
			'description'=>'Provides a pretty direct relationship between input and output.',
			'pros'=>array(
					'This has some beautiful characteristics like a controlled stall using the elevator of an aircraft.'
				),
			'cons'=>array(
					"Will never reach it's goal whenever there is opposing acceleration.",
					"Is sloppy around it's goal. Ie at it's goal it doesn't try very hard to achieve the goal. This can lead to oscilations. These can be reduced with careful tuning, but it generally remains a trade off between slopiness and oscilations.",
					"The nature of the calculations means that the effort for each direction is not necessarily symmetrical, which could lead to surprising results. Eg it doesn't try as hard as you expect to achieve its goal. Or it tries harder."
				)
			));
		
		parent::__construct();
	}
	
	function process($ruleName, &$rule)
	{
		$this->economyTests($ruleName, $rule);
	}
	
	function economyTests($ruleName, &$rule)
	{
		// First some economy tests. If if any of these are true, we don't need to do any calculations.
		if ($rule['input']['live']['inputGoal']<=$rule['input']['min'])
		{ // Input is at or below lower boundary.
			$this->debug(3, __CLASS__.'->'.__FUNCTION__.": $ruleName: branch: lower boundary {$rule['input']['live']['inputGoal']}<={$rule['input']['min']}");
			$output=$rule['output']['min'];
			$branch='lower boundary';
		}
		elseif ($rule['input']['live']['inputGoal']==$rule['input']['center'])
		{ // Input is at center.
			$this->debug(3, __CLASS__.'->'.__FUNCTION__.": $ruleName: branch: center {$rule['input']['live']['inputGoal']}=={$rule['input']['center']}");
			$output=$rule['output']['center'];
			$branch='center';
		}
		elseif ($rule['input']['live']['inputGoal']>=$rule['input']['max'])
		{ // Input is at or above upper boundary.
			$this->debug(3, __CLASS__.'->'.__FUNCTION__.": $ruleName: branch: upper boundary {$rule['input']['live']['inputGoal']}>={$rule['input']['max']}");
			$output=$rule['output']['max'];
			$branch='upper boundary';
		}
		elseif ($rule['input']['live']['inputGoal']<$rule['input']['center'])
		{ // Input is between center and lower boundary.
			$this->debug(3, __CLASS__.'->'.__FUNCTION__.": $ruleName: branch: lower {$rule['input']['live']['inputGoal']}<{$rule['input']['center']}");
			$output=$this->getBetween($rule['input']['live']['inputGoal'], $rule, 'min', 'center');
			$branch='lower';
		}
		elseif ($rule['input']['live']['inputGoal']>$rule['input']['center'])
		{ // Input is between center and upper boundary.
			$this->debug(3, __CLASS__.'->'.__FUNCTION__.": $ruleName: branch: upper {$rule['input']['live']['inputGoal']}>{$rule['input']['center']}");
			$output=$this->getBetween($rule['input']['live']['inputGoal'], $rule, 'center', 'max');
			$branch='lower';
		}
		
		$rule['output']['live']['value']=$output;
	}
}


$core=core::assert();
$balanceDirect=new BalanceDirect();
$core->registerSubModule($balanceDirect);

?>

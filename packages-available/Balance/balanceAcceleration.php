<?php
# Copyright (c) 2014, Kevin Sandom under the BSD License. See LICENSE for full details.

class BalanceAcceleration extends BalanceVectorAlgorithm
{
	function __construct()
	{
		$this->setIdentity('acceleration', array(
			'description'=>'Accelerates towards a goal.',
			'pros'=>array(
					'Should be more able to cope with torque role and other opposing accelerations.'
				),
			'cons'=>array(
					"Could accelerate past it's goal easily."
				)
			));
		
		parent::__construct();
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
		
		$currentPosition=$rule['input']['live']['value'];
		$destination=$rule['input']['live']['goal'];
		
		// Figure out if this should be treated as a fresh request
		if (!isset($rule['input']['live']['vector']))
		{ // We haven't done anything yet. We definitely need to do it.
			$this->resetState($rule);
			$this->setRequestVector($rule, $currentPosition, $destination);
			$this->updateRequestVector($rule, $currentPosition);
			
			$rule['output']['live']['value']=$this->calculateSeed($rule);
			
			$this->checkOutputBounds($rule);
			
			// We have done enough in this iteration and don't want to accelerate any further.
			return true;
		}
		elseIf($rule['input']['live']['lastGoal']==$rule['input']['live']['goal'])
		{ // The goal hasn't changed
			$this->updateRequestVector($rule, $currentPosition);
		}
		else
		{ // The goal has changed
			$direction=$this->getDirection($rule, $currentPosition, $destination);
			if ($direction==0)
			{ // We need to stay put. Potentially different code could be put here to make it more stable, but for now: just don't reset.
				$this->updateRequestVector($rule, $currentPosition);
			}
			elseIf ($direction==$rule['input']['live']['vector']['direction'])
			{ // The direction is the same. Don't reset.
				$this->updateRequestVector($rule, $currentPosition);
			}
			else
			{ // The direction has changed. Reset.
				# TODO reset more gracefully.
				$this->resetState($rule);
				$this->setRequestVector($rule, $currentPosition, $destination);
				$this->updateRequestVector($rule, $currentPosition);
				
				# TODO reset more gracefully.
				$rule['output']['live']['value']=$this->calculateSeed($rule);
				
				$this->checkOutputBounds($rule);
				
				// We have done enough in this iteration and don't want to accelerate any further.
				return true;
			}
		}
		
		# TODO write this stuff
		/*
			Will we overshoot?
				no
					Are we at max speed?
						no
							accelerate
						yes
							decelerate?
				yes
					decelerate
		
		*/
		
		if ($rule['input']['overshootTime']>$rule['input']['live']['vector']['timeRemaining'])
		{ // We will overshoot
			if ($rule['input']['live']['vector']['distanceRemaining']>0)
			{ // not overshot
				$this->core->debug(2, "$ruleName: v  pan {$rule['input']['live']['vector']['distanceRemaining']} -- {$rule['output']['live']['value']} will overshoot ({$rule['input']['overshootTime']}>{$rule['input']['live']['vector']['timeRemaining']}) and {$rule['input']['live']['vector']['distanceRemaining']}>0");
				#$this->decelerate($rule);
				$this->panicDecelerate($rule);
			}
			else
			{ // overshot
				# $this->core->debug(1, "$ruleName: <  dec {$rule['input']['live']['vector']['distanceRemaining']} -- {$rule['output']['live']['value']} have overshot ({$rule['input']['overshootTime']}>{$rule['input']['live']['vector']['timeRemaining']}) and {$rule['input']['live']['vector']['distanceRemaining']}!>0");
				# $this->decelerate($rule);
				
				
				$this->core->debug(2, "$ruleName:  ^ res {$rule['input']['live']['vector']['distanceRemaining']} -- {$rule['output']['live']['value']} have overshot ({$rule['input']['overshootTime']}>{$rule['input']['live']['vector']['timeRemaining']}) and {$rule['input']['live']['vector']['distanceRemaining']}!>0");
				
				//$this->resetState($rule);
				$this->setRequestVector($rule, $currentPosition, $destination);
				$this->updateRequestVector($rule, $currentPosition);
				
				# TODO reset more gracefully.
				$rule['output']['live']['value']=$this->calculateSeed($rule);
				
			}
		}
		else
		{
			if ($rule['input']['live']['vector']['speed']<$rule['input']['maxChangePerSecond'])
			{ // We are below max speed
				$this->core->debug(2, "$ruleName:  > acc {$rule['input']['live']['vector']['distanceRemaining']} -- {$rule['output']['live']['value']} speed ({$rule['input']['live']['vector']['speed']}<{$rule['input']['maxChangePerSecond']})");
				$this->accelerate($rule);
			}
			else
			{ // We have crossed max speed
				$this->core->debug(2, "$ruleName: <  dec {$rule['input']['live']['vector']['distanceRemaining']} -- {$rule['output']['live']['value']} speed ({$rule['input']['live']['vector']['speed']}!<{$rule['input']['maxChangePerSecond']})");
				$this->decelerate($rule);
			}
		}
		
		$this->checkOutputBounds($rule);
	}
	
	public function accelerate(&$rule)
	{
		# $rule['output']['live']['value']=$rule['output']['live']['value']*$rule['output']['accelerateMultiplier'];
		
		/*$seed=$this->calculateSeed($rule);
		if (abs($rule['output']['live']['value']) < abs($seed))
		{
			$rule['output']['live']['value']=$seed;
		}*/
		
		/*
			distanceRemaining/inputRange * outputRange * accelerateMultiplier * direction
			
		*/
		
		# TODO record incrementor for debugging
		# TODO record acc/dec/pan/res
		
		$rule['output']['live']['lastAction']='acc';
		$rule['output']['live']['incrementor']=
			$rule['input']['live']['vector']['distanceRemaining']
			/$rule['input']['range']
			*$rule['output']['range']
			*$rule['output']['accelerateMultiplier']
			*$rule['input']['live']['vector']['direction']
			*$rule['output']['rangeDirection']*-1;
		
		$rule['output']['live']['value']=$rule['output']['live']['value']+$rule['output']['live']['incrementor'];
		
		/*$rule['output']['live']['value']=$rule['output']['live']['value']
			+$rule['output']['seedPercent']
			*$rule['output']['accelerateMultiplier']
			*$rule['input']['live']['vector']['direction'];*/
		
		# $this->core->debug(1, "acc value={$rule['output']['live']['value']} {$rule['output']['accelerateMultiplier']}");
	}
	
	public function decelerate(&$rule)
	{
		#$rule['output']['live']['value']=$rule['output']['live']['value']/$rule['output']['decelerateMultiplier'];
		
		#$rule['output']['live']['value']=$rule['output']['live']['value']-$rule['output']['seedPercent']*$rule['output']['decelerateMultiplier']*$rule['input']['live']['vector']['direction'];
		
		# $this->core->debug(1, "dec value={$rule['output']['live']['value']} {$rule['output']['decelerateMultiplier']}");
		
		$rule['output']['live']['lastAction']='dec';
		$rule['output']['live']['incrementor']=
			$rule['input']['live']['vector']['distanceRemaining']
			/$rule['input']['range']
			*$rule['output']['range']
			*$rule['output']['decelerateMultiplier']
			*$rule['input']['live']['vector']['direction']
			*$rule['output']['rangeDirection']*-1;
		
		$rule['output']['live']['value']=$rule['output']['live']['value']-$rule['output']['live']['incrementor'];
		
		$this->roundOutput($rule);
	}
	
	public function panicDecelerate(&$rule)
	{
		/* Use this when we need to decelerate in a hurry. */
		if ($rule['output']['allowPanic'])
		{
			$rule['output']['live']['lastAction']='pan';
			$rule['output']['live']['incrementor']=$rule['input']['live']['vector']['iterationsRemaining']*2;
			
			$rule['output']['live']['value']=$rule['output']['live']['value']/$rule['output']['live']['incrementor'];
			
			$this->roundOutput($rule);
		}
		else
		{
			$this->core->debug(2, __CLASS__.'->'.__FUNCTION__.": Would have panicked, but it is not allowed for this rule. So doing a decelerate instead.");
			$this->decelerate($rule);
		}
	}
}


$core=core::assert();
$balanceAcceleration=new BalanceAcceleration();
$core->registerSubModule($balanceAcceleration);

?>
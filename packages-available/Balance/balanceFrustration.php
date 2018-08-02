<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

class BalanceFrustration extends BalanceVectorAlgorithm
{
	function __construct()
	{
		$this->setIdentity('frustration', array(
			'description'=>'Accelerates towards a goal, but behaves like someone who is frustrated. This is unlikely to be the right choice for many situations, but was an interesting experiment.',
			'pros'=>array(
					"If we aren't getting the result, it will try harder.",
					'Should be more able to cope with torque role and other opposing accelerations.'
				),
			'cons'=>array(
					'Does not behave well when there is a noticeable delay between taking an action, and getting a measureable result.',
					"Tends to accelerate past it's goal.",
					"Will likely require a lot of tuning to get the desired result."
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
				$this->resetState($rule);
				$this->setRequestVector($rule, $currentPosition, $destination);
				$this->updateRequestVector($rule, $currentPosition);
				
				$rule['output']['live']['value']=$this->calculateSeed($rule);
				
				$this->checkOutputBounds($rule);
				
				// We have done enough in this iteration and don't want to accelerate any further.
				return true;
			}
		}
		
		if ($rule['input']['overshootTime']>$rule['input']['live']['vector']['timeRemaining'])
		{ // We will overshoot
			if ($rule['input']['live']['vector']['distanceRemaining']>0)
			{ // not overshot
				$this->core->debug(2, "v  pan {$rule['input']['live']['vector']['distanceRemaining']} -- {$rule['output']['live']['value']} will overshoot ({$rule['input']['overshootTime']}>{$rule['input']['live']['vector']['timeRemaining']}) and {$rule['input']['live']['vector']['distanceRemaining']}>0");
				#$this->decelerate($rule);
				$this->panicDecelerate($rule);
			}
			else
			{ // overshot
				$this->core->debug(2, " ^ res {$rule['input']['live']['vector']['distanceRemaining']} -- {$rule['output']['live']['value']} have overshot ({$rule['input']['overshootTime']}>{$rule['input']['live']['vector']['timeRemaining']}) and {$rule['input']['live']['vector']['distanceRemaining']}!>0");
				
				//$this->resetState($rule);
				$this->setRequestVector($rule, $currentPosition, $destination);
				$this->updateRequestVector($rule, $currentPosition);
				
				$rule['output']['live']['value']=$this->calculateSeed($rule);
				
			}
		}
		else
		{
			if ($rule['input']['live']['vector']['speed']<$rule['input']['maxChangePerSecond'])
			{ // We are below max speed
				$this->core->debug(2, " > acc {$rule['input']['live']['vector']['distanceRemaining']} -- {$rule['output']['live']['value']} speed ({$rule['input']['live']['vector']['speed']}<{$rule['input']['maxChangePerSecond']})");
				$this->accelerate($rule);
			}
			else
			{ // We have crossed max speed
				$this->core->debug(2, "<  dec {$rule['input']['live']['vector']['distanceRemaining']} -- {$rule['output']['live']['value']} speed ({$rule['input']['live']['vector']['speed']}!<{$rule['input']['maxChangePerSecond']})");
				$this->decelerate($rule);
			}
		}
		
		$this->checkOutputBounds($rule);
	}
	
	public function accelerate(&$rule)
	{
		$rule['output']['live']['value']=$rule['output']['live']['value']*$rule['output']['accelerateMultiplier'];
		
		$seed=$this->calculateSeed($rule);
		if (abs($rule['output']['live']['value']) < abs($seed))
		{
			$rule['output']['live']['value']=$seed;
		}
		
		# $rule['output']['live']['value']=$rule['output']['live']['value']+$rule['output']['seedPercent']*$rule['output']['accelerateMultiplier']*$rule['input']['live']['vector']['direction'];
		
		# $this->core->debug(1, "acc value={$rule['output']['live']['value']} {$rule['output']['accelerateMultiplier']}");
	}
	
	public function panicDecelerate(&$rule)
	{
		/* Use this when we need to decelerate in a hurry. */
		$rule['output']['live']['value']=$rule['output']['live']['value']/($rule['input']['live']['vector']['iterationsRemaining']+1);
		
		$this->roundOutput($rule);
	}

	
	public function decelerate(&$rule)
	{
		$rule['output']['live']['value']=$rule['output']['live']['value']/$rule['output']['decelerateMultiplier'];
		
		#$rule['output']['live']['value']=$rule['output']['live']['value']-$rule['output']['seedPercent']*$rule['output']['decelerateMultiplier']*$rule['input']['live']['vector']['direction'];
		
		# $this->core->debug(1, "dec value={$rule['output']['live']['value']} {$rule['output']['decelerateMultiplier']}");
		
		$this->roundOutput($rule);
	}
}


$core=core::assert();
$balanceFrustration=new BalanceFrustration();
$core->registerSubModule($balanceFrustration);

?>
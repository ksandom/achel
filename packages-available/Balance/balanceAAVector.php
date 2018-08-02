<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

class BalanceVectorAlgorithm extends BalanceAlgorithm
{
	function __construct()
	{
		parent::__construct();
	}
	
	protected function roundOutput(&$rule)
	{
		if ($rule['output']['live']['value']<$rule['output']['seedPercent']/10)
		{
			$rule['output']['live']['value']=0;
		}
	}
	
	protected function checkOutputBounds(&$rule)
	{
		if ($rule['output']['max']>$rule['output']['min'])
		{
			if ($rule['output']['live']['value']>$rule['output']['max'])
			{
				$rule['output']['live']['value']=$rule['output']['max'];
			}
			elseIf ($rule['output']['live']['value']<$rule['output']['min'])
			{
				$rule['output']['live']['value']=$rule['output']['min'];
			}
		}
		else
		{
			if ($rule['output']['live']['value']<$rule['output']['max'])
			{
				$rule['output']['live']['value']=$rule['output']['max'];
			}
			elseIf ($rule['output']['live']['value']>$rule['output']['min'])
			{
				$rule['output']['live']['value']=$rule['output']['min'];
			}
		}
	}
	
	protected function getDirection($origin, $destination)
	{
		if ($destination==$origin)
		{
			$direction=0;
		}
		elseIf ($destination>$origin)
		{
			$direction=1;
		}
		else
		{
			$direction=-1;
		}
		
		return $direction;
	}
	
	protected function setRequestVector(&$rule, $origin, $destination)
	{
		// Determine the direction of the vector.
		$direction=$this->getDirection($origin, $destination);
		
		// Determine the size of the vector.
		$size=$destination-$origin;
		
		// Record it.
		$rule['input']['live']['vector']=array(
			'origin'=>$origin,
			'destination'=>$destination,
			'direction'=>$direction,
			'size'=>$size
			);
	}
	
	protected function updateRequestVector(&$rule, $currentPosition)
	{
		// Housekeeping.
		$now=microtime(true);
		$multiplier=1000000;
		$aReallyBigNumber=abs($rule['input']['max']*1000000);
		
		$rule['input']['live']['vector']['timeMultiplier']=$multiplier;
		
		// Track the change.
		if (isset($rule['input']['live']['vector']['position']))
		{
			$rule['input']['live']['vector']['lastPosition']=$rule['input']['live']['vector']['position'];
			$rule['input']['live']['vector']['lastPositionTimeStamp']=$rule['input']['live']['vector']['positionTimeStamp'];
		}
		else
		{
			$rule['input']['live']['vector']['lastPosition']=$currentPosition;
			$rule['input']['live']['vector']['lastPositionTimeStamp']=$now;
		}
		
		// Record the actual postion.
		$rule['input']['live']['vector']['position']=$currentPosition;
		$rule['input']['live']['vector']['positionTimeStamp']=$now;
		
		
		
		# Calculate stuff
		
		// How long were the iterations separated.
		$rule['input']['live']['vector']['iterationDuration']=$rule['input']['live']['vector']['positionTimeStamp']-$rule['input']['live']['vector']['lastPositionTimeStamp'];
		
		if (!isset($rule['input']['range']))
		{
			$rule['input']['range']=$rule['input']['max']-$rule['input']['min'];
		}
		if (!isset($rule['output']['range']))
		{
			$rule['output']['range']=abs($rule['output']['max']-$rule['output']['min']);
		}
		
		# Calculate or default stuff that relies on the difference.
		if ($rule['input']['live']['vector']['lastPosition']==$rule['input']['live']['vector']['position'] or $rule['input']['live']['vector']['direction']==0)
		{
			$rule['input']['live']['vector']['difference']=0;
			$rule['input']['live']['vector']['ABSDifference']=0;
			$rule['input']['live']['vector']['speed']=0;
			
			# NOTE A different value may need to be used for these. Currently a really big number is used so that decisions can be made simply.
			$rule['input']['live']['vector']['timeRemaining']=$aReallyBigNumber;
			$rule['input']['live']['vector']['iterationsRemaining']=$aReallyBigNumber;
			
		}
		else
		{
			// Calculate difference.
			$rule['input']['live']['vector']['difference']=
				$rule['input']['live']['vector']['position']
				-$rule['input']['live']['vector']['lastPosition'];
			$rule['input']['live']['vector']['ABSDifference']=
				abs(
					$rule['input']['live']['vector']['position']
					-$rule['input']['live']['vector']['lastPosition']);
			
			// Estimate speed.
			if (!isset($rule['input']['live']['vector']['iterationDuration'])) # TODO revise this. It may be that the isset should not be there and this should just be an else.
			{
				$rule['input']['live']['vector']['speed']=0;
			}
			elseIf ($rule['input']['live']['vector']['iterationDuration']!=0)
			{
				$rule['input']['live']['vector']['speed']=
					$rule['input']['live']['vector']['difference']
					/$rule['input']['live']['vector']['iterationDuration']
					*$rule['input']['live']['vector']['direction'];
			}
			$rule['input']['live']['vector']['ABSSpeed']=abs($rule['input']['live']['vector']['speed']);
			
			// Calculate distance remaining.
				// >0 = We haven't arrived yet.
				// <0 = We have overshot.
			$rule['input']['live']['vector']['distanceRemaining']=
				($rule['input']['live']['vector']['destination']-$rule['input']['live']['value'])
				*$rule['input']['live']['vector']['direction'];
			$rule['input']['live']['vector']['distanceTravelled']=
				($rule['input']['live']['value']-$rule['input']['live']['vector']['origin'])
				*$rule['input']['live']['vector']['direction'];
			
			// Estimate overshoot time remaining.
			if ($rule['input']['live']['vector']['ABSSpeed']!=0)
			{
				$rule['input']['live']['vector']['timeRemaining']=$rule['input']['live']['vector']['distanceRemaining']/$rule['input']['live']['vector']['ABSSpeed'];
			}
			
			// Estimate overshoot iterations remaining.
			if (!isset($rule['input']['live']['vector']['ABSDifference']))
			{
				$rule['input']['live']['vector']['iterationsRemaining']=0;
			}
			elseIf ($rule['input']['live']['vector']['ABSDifference']==0)
			{
				$rule['input']['live']['vector']['iterationsRemaining']=$rule['input']['live']['vector']['distanceRemaining']/$rule['input']['live']['vector']['ABSDifference'];
			}
		}
	}
	
	protected function calculateSeed(&$rule)
	{
		$rule['output']['live']['lastAction']='calculateSeed';
		if (!isset($rule['input']['live']['vector']['ABSDifference']))
		{
			$this->core->debug(1, __CLASS__.'->'.__FUNCTION__.": ABSDifference not ready. Can not calculate seed yet.");
			
			# NOTE if the incrementor ever gets numerical actions taken on it, this line will need to be amended.
			$rule['output']['live']['incrementor']='not ready';
			return false;
		}
		
		if ($rule['input']['live']['vector']['ABSDifference'])
		{
			$offCourseIndex=$rule['input']['live']['vector']['ABSDifference']/$rule['input']['range'];
			$this->core->debug(3, __CLASS__.'->'.__FUNCTION__.": Set offCourseIndex to $offCourseIndex.");
		}
		else
		{
			// This is a guestimated value
			$offCourseIndex=0.01;
			$this->core->debug(2, __CLASS__.'->'.__FUNCTION__.": Guesstimated offCourseIndex at $offCourseIndex.");
		}
		
		if ($rule['input']['live']['vector']['direction']==1)
		{
			$rule['output']['live']['incrementor']=
			
			$rule['output']['live']['seed']=$rule['output']['seedPercent']*$rule['output']['min']*$offCourseIndex;
			
			# NOTE if the incrementor ever gets numerical actions taken on it, this line will need to be amended.
			$rule['output']['live']['incrementor']='positive';
		}
		else
		{
			$rule['output']['live']['seed']=$rule['output']['seedPercent']*$rule['output']['max']*$offCourseIndex;
			
			# NOTE if the incrementor ever gets numerical actions taken on it, this line will need to be amended.
			$rule['output']['live']['incrementor']='negative';
		}
		
		return $rule['output']['live']['seed'];
	}
	
	public function resetState(&$rule)
	{
		$rule['output']['live']['value']=$rule['output']['center'];
		if (isset($rule['output']['live']['vector']))
		{
			unset($rule['output']['live']['vector']);
		}
	}
}

?>
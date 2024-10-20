<?php
# Copyright (c) 2014-2023, Kevin Sandom under the GPL License. See LICENSE for full details.

# Provides Balance Faucets.


class BalanceFaucets extends Faucets
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
				$this->core->registerFeature($this, array('createBalanceFaucet'), 'createBalanceFaucet', "A faucet for balancing input and output with goals. --createBalanceFaucet=faucetName", array('balance', 'faucet'));

				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'createBalanceFaucet':
				$parms=$this->core->get('Global', $event);
				$faucet=new BalanceFaucet($parms);

				$this->environment->currentFaucet->createFaucet($parms, 'Balance', $faucet);
				break;

			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

class BalanceFaucet extends ThroughBasedFaucet
{
	private $configDefinition=false;
	private $instanceName='default';

	function __construct()
	{
		parent::__construct(__CLASS__);

		/*
			The configuration is an array of rules. Each rule is structured as defined below.
		*/


		$this->configDefinition=array(
			'rule'=>array(
				'lotsOfThese'=>'1',

				'algorithm'=>array(
						'default'=>'direct',
						'description'=>'Which algorithm to use to calculate the output.'
					),

				'input'=>array(
					'variable'=>array(
						'description'=>'The input variable to monitor.'
						),
					'min'=>array(
						'default'=>'-1',
						'description'=>'Minimum valid input value. Default -1.'
						),
					'center'=>array(
						'default'=>'0',
						'description'=>'Resting input position. Default 0.'
						),
					'max'=>array(
						'default'=>'1',
						'description'=>'Maximum valid input value. Default 1.'
						),
					'multiplier'=>array(
						'default'=>'1',
						'description'=>'A number to amplify the input. Think "gain".'
						),
					'goal'=>array(
						'default'=>'-1',
						'description'=>'The input value we are trying to achieve'
						),
					'overshootTime'=>array(
						'default'=>'2',
						'description'=>'The number of seconds until we overshoot at the current velocity. Typically this is used in algorithms that want to slow down gracefully before attaining the goal.'
						),
					'maxChangePerSecond'=>array(
						'default'=>'0.1',
						'description'=>"The maximum change in input per second. If the motion is really really slow, this setting is a very likely candidate. Typically this should be a fraction of the total range. eg you typically wouldn't want to do an entire motion in one sample. So let's say you have a range of -1 to 1 (=2) and you want the maximum speed to be 10% per second. You would set this value to 0.2."
						),
          'expo'=>array(
						'default'=>'1',
						'description'=>'Expos are for making the controls more, or less sensitive in the middle. The final deflection at the ends will still be the same; what changes is where the bias of the control happens compared to a 1:1 mapping. See readme.md for information about setting this correctly.'
						),
					'autoTighten'=>array(
						'optional'=>'1',
						'enabled'=>array(
							'default'=>'0',
							'description'=>'Automatically tighten the acceptable input ranges. This has the effect of honing in on the goal. 0(default) or 1.'
							),
						'checkInterval'=>array(
							'default'=>'1',
							'description'=>'Automatically check autoTighten no more than once every x seconds.'
							),
						'increment'=>array(
							'default'=>'0.01',
							'description'=>'How much to increment/decrement the max and min values with each tick. Generally you want this to be small so that the adjustment is gentle.'
							),
						'debug'=>array(
							'default'=>'0',
							'description'=>'Output when autoTighten decisions are made.'
							),
						'tightenThreshold'=>array(
							'default'=>'0.8',
							'description'=>'The threshold that must be we must be inside of to trigger auto tighten. Expressed as a 0-1 decimal percentage. Eg if the input range is -10 to 10, and the autoTighten-tightenThreshold=0.8, then values that would trigger a tighten can be expressed as -0.8<x<0.8. The range will not tighten beyond the tightenedMin and tightenedMax values. Default 0.8.'
							),
						'loosenThreshold'=>array(
							'default'=>'0.95',
							'description'=>'The threshold that must be we must be outside of to trigger auto loosen. Expressed as a 0-1 decimal percentage. Eg if the input range is -10 to 10, and the autoTighten-loosenThreshold=0.95, then values that would trigger a tighten can be expressed as -0.95>x>0.95. The range will not loosen beyond the min and max values. Default 0.95.'
							),
						'tightenedMin'=>array(
							'default'=>'-0.2',
							'description'=>'Minimum valid input value once the range has been tightened. Default -0.2.'
							),
						'tightenedMax'=>array(
							'default'=>'0.2',
							'description'=>'Maximum valid input value once the range has been tightened. Default 0.2.'
							),
						),
					'events'=>array(
						'optional'=>'1',
						'event'=>array(
							'optional'=>'1',
							'lotsOfThese'=>'1',
							'description'=>array(
								'description'=>'Describe what has just happened when this event gets triggered.',
								),
							'operator'=>array(
								'description'=>'One of ==, >, <, >=, <=',
								),
							'testValue'=>array(
								'description'=>'The value on the right of the operator to test the value against.',
								),
							'eventName'=>array(
								'description'=>'The event category and name to trigger. Eg Example,itRained. Note that the instanceName will be passed as the value for the event. If you have not set it, you can do so by using the setInstanceName command on the _control channel of this faucet.',
								),
							)
						)
					),
				'output'=>array(
					'min'=>array(
						'default'=>'-1',
						'description'=>'minimum valid output value.'
						),
					'center'=>array(
						'default'=>'0',
						'description'=>'resting output position.'
						),
					'max'=>array(
						'default'=>'1',
						'description'=>'maximum valid output value.'
						),
					'multiplier'=>array(
						'default'=>'1',
						'description'=>'A number to amplify the output/effort.'
						),
					'accelerateMultiplier'=>array(
						'default'=>'2',
						'description'=>'This is the multiplier to increase the effort. This is used by algorithms that accelerate the output rather than explicitly set it. Not to be confused with the normal amplitude "multiplier" above.'
						),
					'decelerateMultiplier'=>array(
						'default'=>'2',
						'description'=>'This is the multiplier to increase the effort. This is used by algorithms that accelerate the output rather than explicitly set it. Not to be confused with the normal amplitude "multiplier" above.'
						),
					'seedPercent'=>array(
						'default'=>'0.1',
						'description'=>'This is the initial output incrementor used for algorithms that accelerate (such as balanceAcceleration) and need a first value. Other algorithms such as balanceDirect can ignore this.'
						),
					'allowPanic'=>array(
						'default'=>true,
						'description'=>"Normally when we are getting too close to the goal to be able to stop in time using graceful deceleration, we want to panic to reduce our speed quickly. Sometimes it's more important slow down more gracefully and overshoot."
						),
          'expo'=>array(
						'default'=>'1',
						'description'=>'Expos are for making the controls more, or less sensitive in the middle. The final deflection at the ends will still be the same; what changes is where the bias of the control happens compared to a 1:1 mapping. See readme.md for information about setting this correctly.'
						),
					'events'=>array(
						'optional'=>'1',
						'event'=>array(
							'optional'=>'1',
							'lotsOfThese'=>'1',
							'description'=>array(
								'description'=>'Describe what has just happened when this event gets triggered.',
								),
							'operator'=>array(
								'description'=>'One of ==, >, <, >=, <=',
								),
							'testValue'=>array(
								'description'=>'The value on the right of the operator to test the value against.',
								),
							'eventName'=>array(
								'description'=>'The event category and name to trigger. Eg Example,itRained. Note that the instanceName will be passed as the value for the event. If you have not set it, you can do so by using the setInstanceName command on the _control channel of this faucet.',
								),
							'debugLevel'=>array(
								'description'=>'The debug level to notify when an event is triggered. It would be unusual to set this to anything less than 2 other than temporarily debugging an urgent issue.',
								'default'=>'3'
								)
							)
						)
					),
				'destination'=>array(
					'variable'=>array(
						'description'=>'Where to save the resulting value.'
						),
					'textOutput'=>array(
						'description'=>'The text to be sent the channel. ~%value%~ will be replaced with what ever the output currently is.'
						),
					'changeOnly'=>array(
						'default'=>'1',
						'description'=>'only send output when the value has changed.'
						),
					'channel'=>array(
						'default'=>'default',
						'description'=>'which channel to send to.'
						)
					),
				'pid'=>array(
					'optional'=>true,
					'kP'=>array(
						'description'=>'Proportional aspect of the PID controller. This gives a very direct response to how far off the goal we are. It will do a good job of getting in the general vascinity of the goal, but will be sloppy once close.',
						'default'=>'0.5'
						),
					'iP'=>array(
						'description'=>'How much of the difference between the previous goal, and the new goal should we apply. 1 Will apply everything straight away. 0.5 will apply half this time. And then half of the remaining difference next time.',
						'default'=>'1'
						),
					'kW'=>array(
						'description'=>'Wandering aspect of the PID controller. This looks at recent history, and will increase or decrease effort accordingly to precisely get us to the goal.',
						'default'=>'0.3'
						),
					'iW'=>array(
						'description'=>'How much of the difference between the previous goal, and the new goal should we apply. 1 Will apply everything straight away. 0.5 will apply half this time. And then half of the remaining difference next time.',
						'default'=>'1'
						),
					'kI'=>array(
						'description'=>'Integral aspect of the PID controller. This looks at recent history, and will increase or decrease effort accordingly to precisely get us to the goal.',
						'default'=>'0'
						),
					'iI'=>array(
						'description'=>'How much of the difference between the previous goal, and the new goal should we apply. 1 Will apply everything straight away. 0.5 will apply half this time. And then half of the remaining difference next time.',
						'default'=>'1'
						),
					'kD'=>array(
						'description'=>'Derivitive aspect of the PID controller. This looks for if we are going to overshoot, and applies pressure accordingly.',
						'default'=>'0'
						),
					'iD'=>array(
						'description'=>'How much of the difference between the previous goal, and the new goal should we apply. 1 Will apply everything straight away. 0.5 will apply half this time. And then half of the remaining difference next time.',
						'default'=>'1'
						),
					'wanderingTime'=>array(
						'description'=>'How long it takes to wander from 0 to full deflection. From this, the incrementor is derived to increment in the appropriate direction to correct our current error. What is a short time, and what is a long time depends on your application. Eg 5 seconds is probably plenty for yaw during a takeoff roll. Yet 60 seconds is probably more appropriate for a slow altitude climb.',
						'default'=>'60'
						)
					)
				)
			);
	}

	function validateConfig()
	{
		$this->validateSpecificConfig($this->config, $this->configDefinition);
	}

	function validateSpecificConfig(&$config, $definition=false, $context=__CLASS__)
	{
		$result=true;
		foreach ($definition as $key=>$subDefinition)
		{
			if (isset($subDefinition['description']))
			{
				// test locally
				if (!isset($config[$key]))
				{
					if (isset($subDefinition['default']))
					{
						$this->debug($this->l3, "$context: Required field \"$key\" was set to it's default value of {$subDefinition['default']}");
						$config[$key]=$subDefinition['default'];
					}
					elseif (isset($subDefinition['optional']))
					{
						$this->debug($this->l3, "$context: Optional field \"$key\" is not set and not required.");
					}
					else
					{
						$this->debug($this->l1, "$context: Required field \"$key\" is not set and has no default. Therefore validation fails. The field is described as \"{$subDefinition['description']}\"");
						$result=false;
					}
				}
			}
			else
			{	//
				switch ($key)
				{
					case 'lotsOfThese': # Make sure we don't process this.
					case 'default': # Make sure we don't process this.
					case 'description': # Make sure we don't process this.
					case 'optional': # Make sure we don't process this.
						break;
					default:
						if (isset($subDefinition['lotsOfThese'])) # TODO Test for true. This currently does not test for true. This could cause unexpected results if it were set to false.
						{
							foreach($config as $configKey=>&$subConfig)
							{
								if (!$this->validateSpecificConfig($subConfig, $subDefinition, "$context,$configKey"))
								{
									$this->debug($this->l1, __CLASS__.'->'.__FUNCTION__.": Failed sub validation for key \"$key\" in config in a \"lotsOfThese\" section.");
									$result=false;
								}
							}
						}
						else
						{
							if (isset($config[$key]))
							{
								if (!$this->validateSpecificConfig($config[$key], $subDefinition, "$context,$key"))
								{
									$this->debug($this->l1, __CLASS__.'->'.__FUNCTION__.": Failed sub validation for key \"$key\" in config.");
									$result=false;
								}
							}
							else
							{
								// Missing config
								if (!isset($definition[$key]['optional']))
								{
									#print_r($definition[$key]);
									$this->debug($this->l1, __CLASS__.'->'.__FUNCTION__.": No key \"$key\" in config.");
									$result=false;
								}
							}
						}
						break;
				}
			}
		}

		return $result;
	}

	function handelEvents($ruleName, &$rule, $channel, $value)
	{
		if (!is_array($rule[$channel]['events']))
		{
			$this->debug($this->l1, __CLASS__.'->'.__FUNCTION__.": Events exists but is not an array.");
			return false;
		}

		foreach ($rule[$channel]['events'] as $eventName=>&$event)
		{
			if (strpos($event['testValue'], ',')!==false)
			{
				# TODO consider caching this!
				$testValue=$this->core->getNested(explode(',', $event['testValue']));
			}
			else
			{
				$testValue=$event['testValue'];
			}
			switch ($event['operator'])
			{
				case '==':
					$triggered=($value==$testValue);
					break;
				case '>':
					$triggered=($value>$testValue);
					break;
				case '<':
					$triggered=($value<$testValue);
					break;
				case '>=':
					$triggered=($value>=$testValue);
					break;
				case '<=':
					$triggered=($value<=$testValue);
					break;
				default:
					$triggered=false;
					$this->debug($this->l1, __CLASS__.'->'.__FUNCTION__.": Unknown operator \"{$event['operator']}\" in event \"$eventName\" in rule \"$ruleName\"");
					break;
			}

			if ($triggered and !isset($event['triggered']))
			{
				$this->debug($event['debugLevel'], __CLASS__.'->'.__FUNCTION__.": Triggered event \"{$event['description']}\" on rule \"$ruleName\" $value{$event['operator']}\"$testValue ({$event['testValue']})\"");
				$this->core->callFeature('triggerEvent', "{$event['eventName']},{$this->instanceName}");
				$event['triggered']=true;
			}
		}
	}

	function control($feature, $value)
	{
		switch ($feature)
		{
			case "reset":
				$this->reset();
			break;
			default:
				parent::control($feature, $value);
			break;
		}
	}

	function reset($ruleName=null)
	{
		# TODO Is this taking the partial applies into account?

		if ($ruleName)
		{
			if (isset($this->config[$ruleName]))
			{
				$this->debug($this->l1, __CLASS__.'->'.__FUNCTION__.' Resetting the state of the rule $ruleName.');
				$obj=getAlgorithmObject($ruleName);
				$obj->resetState($this->config[$ruleName]);
			}
			else
			{
				$this->debug($this->l1, __CLASS__.'->'.__FUNCTION__." $ruleName doesn't exist.");
			}
		}
		else
		{
			$this->debug($this->l1, __CLASS__.'->'.__FUNCTION__.' Resetting the state of all rules.');
			foreach ($this->config as $ruleName=>&$rule)
			{
				$this->debug($this->l1, "Resetting $ruleName.");
				$obj=$this->getAlgorithmObject($ruleName);
				$obj->resetState($this->config[$ruleName]);
			}
		}
	}

	function &getAlgorithm($ruleName)
	{
		$algorithm=$this->config[$ruleName]['algorithm'];

		$algorithmDefinition=$this->core->get('BalanceAlgorithm', $this->config[$ruleName]['algorithm']);
		if (!is_object($algorithmDefinition['obj']))
		{
			$algorithmDefinition=$this->core->get('BalanceAlgorithm', 'direct');
			if (!is_object($algorithmDefinition['obj']))
			{
				$this->debug($this->l1, __CLASS__.'->'.__FUNCTION__.": algorithm \"$algorithm\" not found and fallback algorithm \"direct\" not found. Rule \"$ruleName\" can not be processed.");
				$false=false;
				return $false;
			}
			else
			{
				$this->debug($this->l1, __CLASS__.'->'.__FUNCTION__.": algorithm \"$algorithm\" not found but \"direct\" was found, so using that. This will likely cause unexpected behavior.");
			}
		}

		return $algorithmDefinition;
	}

	function &getAlgorithmObject($ruleName)
	{
		$algorithmDefinition=$this->getAlgorithm($ruleName);
		if ($algorithmDefinition) return $algorithmDefinition['obj'];
		$false=false;
		return $false;
	}

	function preGet()
	{
		$gotSomething=false;

		foreach ($this->input as $channel=>$data)
		{
			if ($channel=='_control')
			{
				$this->callControl($data);
				$this->clearInput($channel);
				$gotSomething=true;
				$this->validateConfig();
				continue;
			}
		}

		if (!is_array($this->config))
		{
			$this->debug($this->l1, __CLASS__.'->'.__FUNCTION__.": No config for {$this->instanceName}!");
			return false;
		}

		foreach ($this->config as $ruleName=>&$rule)
		{
			// Check that the rule is valid before processing it.
			# TODO This really doesn't need to be run every time, but it does need to be run at least once per rule and will need to be run again when ever a rule changes... which we currently don't have any way of tracking. A compromise could be to check each rule once if it hasn't been checked/passed before.
			if (!isset($rule['ruleName'])) $rule['ruleName']=$ruleName;

			if (!$this->validateSpecificConfig($rule, $this->configDefinition['rule'], $ruleName))
			{
				$this->debug($this->l1, "Rule \"$ruleName\" failed validation.");
				continue;
			}

			$valueProgression=array();


			$input=$this->core->getNested(explode(',', $rule['input']['variable']));
			if (!is_numeric($input))
			{
				# $this->debug($this->l1, "No input for rule \"$ruleName\".");
				continue;
			}

			# TODO Remove this.
			// When to show the extra debugging.
			$showDebug=($ruleName == 'yaw');


			// Prep the rule
			if (!isset($rule['name'])) $rule['name']=$ruleName;
			if (!isset($rule['input']['live'])) $rule['input']['live']=array();
			if (!isset($rule['output']['live'])) $rule['output']['live']=array();
			if (!isset($rule['output']['rangeDirection']))
			{
				$rule['output']['rangeDirection']=($rule['output']['max']>$rule['output']['min'])?1:-1;
			}

			// Add our vanilla value
			$rule['input']['live']['vanillaValue']=$input;
			$rule['input']['live']['value']=$input;

			if ($showDebug) $valueProgression['A1']=$rule['input']['live']['vanillaValue'];
			if ($showDebug) $valueProgression['A2']=$rule['input']['live']['value'];

			// Add our goal
			$goal=$this->core->getNested(explode(',', $rule['input']['goal']));
			if (isset($rule['input']['live']['goal']))
			{
				$rule['input']['live']['lastGoal']=$rule['input']['live']['goal'];
			}
			else
			{
				$rule['input']['live']['lastGoal']=$goal;
			}
			$rule['input']['live']['goal']=$goal;
			if ($showDebug) $valueProgression['g']=$rule['input']['live']['goal'];

			// AutoTighten
			if (!isset($rule['autoTighten']))
			{
				$rule['autoTighten']=array();
			}
			if (!isset($rule['autoTighten']['enabled'])) $rule['autoTighten']['enabled']=0;
			if ($rule['autoTighten']['enabled'])
			{
				if (!isset($rule['autoTighten']['obj']))
				{
					$rule['autoTighten']['obj'] = new AutoTighten($rule, $this->core);
				}
			}


			// Get the algorithm.
			$algorithmObject=$this->getAlgorithmObject($ruleName);


			# Apply input multiplier and expo.
			$rule['input']['live']['value']=$algorithmObject->applyMultiplierAndExpo(
				$rule['input']['live']['value'],
				$rule['input']['multiplier'],
				$rule['input']['expo'],
				$rule['input']['center']);


			# Handel any input events.
			if (isset($rule['input']['events']))
			{
				$this->handelEvents($ruleName, $rule, 'input', $rule['input']['live']['value']);
			}

			if (!isset($rule['input']['lastInput']))
			{
				$this->debug($this->l3, __CLASS__.'->'.__FUNCTION__.": Set first time lastInput to \"{$rule['input']['live']['value']}\". This should only happen once per config change. Input: {$rule['input']['variable']}");
			}
			elseif ($rule['input']['lastInput']==$rule['input']['live']['value'])
			{
				# TODO This will certainly be a bug for anything that needs to do analysis of changes over time. **Come back to this.**
				#$this->debug($this->l1, "No change for rule \"$ruleName\".");
				continue;
			}

			$rule['input']['lastInput']=$rule['input']['live']['value'];
			if ($showDebug) $valueProgression['C1']=$rule['input']['live']['value'];
			$rule['output']['live']['previousMultipliedValue']=$this->core->getNested(explode(',', $rule['destination']['variable']));
			$rule['output']['live']['multipliedValue']='';
			if ($showDebug) $valueProgression['C1']=$rule['output']['live']['multipliedValue'];

			/*
			For the simplicity the following assumption is made for the input:
				min < center < max

			For the output, either of these assumptions will work
				min < center < max
				min > center > max
			*/


			// Mangle the input with the goal.
			# TODO Change inputGoal to error, and adapt all algorithms to use it.
			# $this->debug($this->l0, "wooooort? ={$rule['input']['live']['value']}-{$rule['input']['live']['goal']};");
			if (is_array($rule['input']['live']['goal']))
			{
				print_r($rule['input']['live']['goal']);
			}
			$rule['input']['live']['inputGoal']=$rule['input']['live']['value']-$rule['input']['live']['goal'];
			if ($showDebug) $valueProgression['IG1']=$rule['input']['live']['inputGoal'];


			// Test that we aren't out of bounds.
			$rule['input']['live']['inputGoal']=$algorithmObject->cap($rule['input']['min'], $rule['input']['live']['inputGoal'], $rule['input']['max']);
			if ($showDebug) $valueProgression['IG2']=$rule['input']['live']['inputGoal'];

			# TODO I think this is a raw-ish value. I thought there was some scaling. If not, do it.
			# protected function scaleData($value, $inMin, $inCenter, $inMax, $outMin=-1, $outCenter=0, $outMax=1)
			$value=$rule['input']['live']['value'];
			$inputGoal=$rule['input']['live']['inputGoal'];
			$goal=$rule['input']['live']['goal'];
			$inMin=$rule['input']['min'];
			$inCenter=$rule['input']['center'];
			$inMax=$rule['input']['max'];
			$rule['input']['live']['scaledInputGoal']=$algorithmObject->scaleData($inputGoal, $inMin, $inCenter, $inMax); // We need to do this before AutoTighten so we can use the value.
			if ($rule['autoTighten']['enabled'])
			{
				$rule['autoTighten']['obj']->tick();
				$rule['input']['live']['scaledInputGoal']=$algorithmObject->scaleData($inputGoal, $inMin, $inCenter, $inMax); // Max and Min have likely changed. We therefore need to recalculate this.
			}

			$rule['input']['live']['scaledValue']=$algorithmObject->scaleData($value, $inMin, $inCenter, $inMax);
			$rule['input']['live']['scaledGoal']=$algorithmObject->scaleData($goal, $inMin, $inCenter, $inMax);

			#   //
			#  //
			# //
			#//
			// Run the data through the algorithm
			$algorithmObject->process($ruleName, $rule);
			#\
			#\\
			# \\
			#  \\
			#   \\



			// Calculate value after multiplier
			$rule['output']['live']['multipliedValue']=$algorithmObject->applyMultiplierAndExpo(
				$rule['output']['live']['value'],
				$rule['output']['multiplier'],
				$rule['output']['expo'],
				$rule['output']['center']);

			// Dump the current rule state for debugging.
			$this->core->set('AP', 'rule-'.$ruleName, $rule);


			// Correct bounds if necessary
			$rule['output']['live']['multipliedValue']=$algorithmObject->cap(
				$rule['output']['min'],
				$rule['output']['live']['multipliedValue'],
				$rule['output']['max']);
			if ($showDebug) $valueProgression['MVO4']=$rule['output']['live']['multipliedValue'];

			// Make sure the output value is safe to output
			$rule['output']['live']['multipliedValue']=round($rule['output']['live']['multipliedValue'], 4);
			if ($showDebug) $valueProgression['MVO5']=$rule['output']['live']['multipliedValue'];

			$rule['output']['live']['shouldUpdate']=($rule['output']['live']['multipliedValue']!=$rule['output']['live']['previousMultipliedValue']); //$rule['destination']['changeOnly']!=1 or

			if ($rule['output']['live']['shouldUpdate'])
			{
				# Handel any output events
				if (isset($rule['output']['events']))
				{
					$this->handelEvents($ruleName, $rule, 'output', $rule['output']['live']['multipliedValue']);
				}

				if ($rule['destination']['variable'])
				{
					$this->core->setNestedViaPath($rule['destination']['variable'], $rule['output']['live']['multipliedValue']);
				}


				if ($rule['destination']['textOutput'])
				{
					$outLine=implode($rule['output']['live']['multipliedValue'], explode('~%value%~', $rule['destination']['textOutput']));
					if ($ruleName == 'groundspeed' or $ruleName == 'altitude')
					{
						# TODO include this in the debugging output.
						$this->debug($this->l3,"text=$outLine");
					}

					$this->outFill(array($outLine), $rule['destination']['channel']);
				}

				$gotSomething=true;
			}

			if (isset($rule['debug']))
			{
				if ($rule['debug'])
				{
					$shouldUpdateString=($rule['output']['live']['shouldUpdate'])?'true':'false';

					$this->debug($this->l0, __CLASS__.'->'.__FUNCTION__.": $ruleName: algorithm={$rule['algorithm']} input={$rule['input']['live']['value']} goal={$rule['input']['live']['goal']} inputGoal={$rule['input']['live']['inputGoal']} output={$rule['output']['live']['multipliedValue']}  shouldUpdate=$shouldUpdateString");
				}
			}

			$rule['output']['live']['value']=$rule['output']['live']['multipliedValue'];

			# TODO remove or abstract this.
			// Write debugging to a file.
			if ($showDebug)
			{
				#$valueProgression
				$fileName='/tmp/valueProgression.csv';
				$csvOut='';
				if (!file_exists($fileName)) $csvOut=implode(',', array_keys($valueProgression))."\n";
				$csvOut.=implode(',', $valueProgression)."\n";

				$filePointer=fopen($fileName, 'a+');
				fwrite($filePointer, $csvOut);
				fclose($filePointer);
			}

		}

		return $gotSomething;
	}
}




class BalanceAlgorithm extends SubModule
{
	protected $state;

	function __construct()
	{
		parent::__construct('BalanceAlgorithm');
		$this->state=array();
	}

	public function resetState(&$rule)
	{
		if (!isset($rule['ruleName'])) return false;
		if (isset($this->state[$rule['ruleName']])) unset($this->state[$rule['ruleName']]);
	}

	public function process($ruleName, &$rule)
	{
		/*
			All the required input will be provided by $rule.
		*/
	}

	protected function processExpo($value, $expo)
	{
		/*
		Expo working:

		0.5^2 = 0.25 => Middle is more gentle, while the edges are more extreme.
		0.5^1 = 0.5 => Normal.
		0.5^0.5 = 0.75
		0.5^0 = 1

		input^expo=result
		*/

		if ($expo==1) return $value; # Don't do any work if it is set to 1.


		# We need to take out any negative value (abs()), and add it back in afterwards ($multiplier) so that we can correctly apply the exponent.
		$multiplier=($value<0)?-1:1;
		$result=(abs($value)^$expo)*$multiplier;

		return $result;
	}

	public function applyMultiplierAndExpo($value, $multiplier=1, $expo=1, $center=0)
	{
			if (!is_numeric($value)) $this->debug($this->l0, "value ($value) is not numeric.");
			if (!is_numeric($center)) $this->debug($this->l0, "center ($center) is not numeric.");
			if (!is_numeric($multiplier)) $this->debug($this->l0, "multiplier ($multiplier) is not numeric.");
			if (!is_numeric($expo)) $this->debug($this->l0, "expo ($expo) is not numeric.");

			$value=$value-$center;
			$value=$value*$multiplier;
			$value=$this->processExpo($value, $expo);
			$value=$value+$center;

			return $value;
	}

	protected function getBetween($input, $rule, $inRangeBeginName, $inRangeEndName)
	{
		$outRangeBeginName=$inRangeBeginName;
		$outRangeEndName=$inRangeEndName;

		$inValueDiff=$input-$rule['input'][$inRangeBeginName];
		$inRangeDiff=$rule['input'][$inRangeEndName]-$rule['input'][$inRangeBeginName];
		$inValuePercent=$inValueDiff/$inRangeDiff;

		$outRangeDiff=$rule['output'][$outRangeEndName]-$rule['output'][$outRangeBeginName];
		$outValue=$outRangeDiff*$inValuePercent+$rule['output'][$outRangeBeginName];

		return $outValue;
	}

	public function cap($min, $value, $max)
	{ // Cap the value to a specific range.
		$out=$value;

		if ($min<$max)
		{ // Normal use-case.
			if ($out>$max) $out=$max;
			if ($out<$min) $out=$min;
		}
		else
		{ // We have an inverted range.
			if ($out<$max) $out=$max;
			if ($out>$min) $out=$min;
		}

		return $out;
	}

	protected function calculateSomeDifference($currentGoal, $previousGoal, $incrementorPercent)
	{ //Use this for the raw calculation. You may want calculateSomeDifference instead.
		$difference=$currentGoal-$previousGoal;
		$out=$previousGoal+$difference*$incrementorPercent;

		return $out;
	}

	public function getSomeDifference($goal, $incrementorPercent, $ruleName, $differenceName)
	{ // Apply some of the requested goal, and keep track of it.
		// Assert that the data structure is set up.

		if (!isset($this->state[$ruleName])) $this->state[$ruleName]=array();
		if (!isset($this->state[$ruleName][$differenceName])) $this->state[$ruleName][$differenceName]=array();
		if (!isset($this->state[$ruleName][$differenceName]['previousGoal'])) $this->state[$ruleName][$differenceName]['previousGoal']=$goal;

		// Do the actual calculations.
		$newGoal=$this->calculateSomeDifference($goal, $this->state[$ruleName][$differenceName]['previousGoal'], $incrementorPercent);
		$this->state[$ruleName][$differenceName]['previousGoal']=$newGoal;

		return $newGoal;
	}

	public function scaleData($value, $inMin, $inCenter, $inMax, $outMin=-1, $outCenter=0, $outMax=1)
	{
		/*
		This function takes an input of a given range, and converts it to another range.
		This is more complicated than you'd likely initially guess because we need to cope with both

		* Inverted input.
		* Non-zero center.

		At this time, inverted output is not supported, but that can be achieved by inverting the input.
		*/

		if ($value==$inCenter) return $outCenter;

		# Figure out which side of the center the input is on.
		if ($inMax > $inMin)
		{ # Normal input.
			if ($value>=$inMax) return $outMax;
			elseif ($value<=$inMin) return $outMin;
			elseif ($value>$inCenter)
			{ # Value is on the right.
				$out=$this->calculateScaleData($value, $inMax, $inCenter, 1, $outMax, $outCenter);
				$path="top right";
			}
			else
			{ # Value is on the left.
				$out=$this->calculateScaleData($value, $inMin, $inCenter, 1, $outMin, $outCenter);
				$path="top left";
			}
		}
		else
		{ # Inverted input.
			if ($value>=$inMin) return $outMax;
			elseif ($value<=$inMax) return $outMin;
			elseif ($value>$inCenter)
			{ # Value is on the left.
				$out=$this->calculateScaleData($value, $inMin, $inCenter, -1, $outCenter, $outMin);
				$path="bottom left";
			}
			else
			{ # Value is on the right.
				$out=$this->calculateScaleData($value, $inMax, $inCenter, -1, $outCenter, $outMax);
				$path="bottom right";
			}
		}

		$this->debug($this->l2, "$path. . (v=$value, in=$inMin, ic=$inCenter, ix=$inMax, on=$outMin=-1, oc=$outCenter=0, ox=$outMax=1) OUT=$out");

		return $out;
	}

	private function calculateScaleData($value, $inRange1, $inRange2, $multiplier, $outRange1, $outRange2)
	{
		$inBase=$inRange1-$inRange2;
		$outBase=$outRange1-$outRange2;
		$inValue=$value-$inRange2;

		return $inValue/$inBase*$outBase*$multiplier;
	}
}

class AutoTighten
{
	private $rule=null;
	private $core=null;

	function __construct(&$rule, &$core)
	{
		$this->rule=&$rule;
		$this->core=&$core;

		$this->rule['autoTighten']['loosenedMin']=$this->rule['input']['min'];
		$this->rule['autoTighten']['loosenedMax']=$this->rule['input']['max'];

		$this->tock();

		$this->debug($this->l1, "Initialised AutoTighten on {$rule['name']}");
	}

	private function now()
	{
		return microtime($get_as_float=true);
	}

	public function tick()
	{
		$timeSinceLastTick=$this->now()-$this->rule['autoTighten']['lastTick'];
		if ($timeSinceLastTick>$this->rule['autoTighten']['checkInterval'])
		{
			$value=abs($this->rule['input']['live']['scaledInputGoal']);

			if ($value<$this->rule['autoTighten']['tightenThreshold'])
			{
				$this->tighten();
			}
			elseif ($value>$this->rule['autoTighten']['loosenThreshold'])
			{
				$this->loosen();
			}

			$this->tock();
		}
	}

	private function modify($destinationBoundary, $direction, $boundaryLimit)
	{
		$inc=$this->rule['autoTighten']['increment']*$direction;

		$this->rule['input'][$destinationBoundary]+=$inc;
		# $this->debug($this->l1, "TMP0001: {$this->rule['name']} {$this->rule['input'][$destinationBoundary]}+=$inc destinationBoundary=$destinationBoundary direction=$direction boundaryLimit=$boundaryLimit");

		if ($direction<0) # Min
		{
			if ($this->rule['input'][$destinationBoundary]<=$boundaryLimit)
			{
				$this->rule['input'][$destinationBoundary]=$boundaryLimit;
				return false;
			}
		}
		else # Max
		{
			if ($this->rule['input'][$destinationBoundary]>=$boundaryLimit)
			{
				$this->rule['input'][$destinationBoundary]=$boundaryLimit;
				return false;
			}
		}

		return true;
	}

	private function tighten()
	{
		$minChange=$this->modify('min', 1, $this->rule['autoTighten']['tightenedMin']);
		$maxChange=$this->modify('max', -1, $this->rule['autoTighten']['tightenedMax']);

		if ($this->rule['autoTighten']['debug'] and ($minChange or $maxChange)) $this->debug($this->l1, "{$this->rule['name']}: Tightened boundaries to {$this->rule['input']['min']}-{$this->rule['input']['max']}.");
	}

	private function loosen()
	{
		$minChange=$this->modify('min', -1, $this->rule['autoTighten']['loosenedMin']);
		$maxChange=$this->modify('max', 1, $this->rule['autoTighten']['loosenedMax']);

		if ($this->rule['autoTighten']['debug'] and ($minChange or $maxChange)) $this->debug($this->l1, "{$this->rule['name']}: Loosened boundaries to {$this->rule['input']['min']}-{$this->rule['input']['max']}. inc={$this->rule['autoTighten']['increment']}");
	}

	private function tock()
	{
		$this->rule['autoTighten']['lastTick']=$this->now();
	}
}

class TimedDataHistory
{
	# TODO This should be in made into a package that can be used by achel programs.

	private $history=array();
	private $minimumSeparation=0;
	private $position=0;
	private $size=0;

	function __construct($size, $minimumSeparation)
	{
		$this->minimumSeparation=$minimumSeparation;
		$this->size=$size;

		$then=$this->now()-$minimumSeparation;
		$item=$this->newItem(0, $then);
		for ($position=0;$position<$size;$position++)
		{
			$this->history[$position]=$item;
		}
	}

	private function newItem($value, $then)
	{
		return array('value'=>$value, 'when'=>$then);
	}

	private function now()
	{
		return microtime($get_as_float=true);
	}

	private function lastEntry()
	{
		return $this->history[$this->position];
	}

	private function readyForNextValue()
	{
		$lastEntry=$this->lastEntry();
		$now=$this->now();

		return ($now-$lastEntry['when'] > $this->minimumSeparation);
	}

	public function addItem($value)
	{
		/*
		Add a new value to the rolling history, but only if the time since the last value is greater than the $minimumSeparation time.
		Return true if it was added. Otherwise false.
		*/

		if ($this->readyForNextValue())
		{
			$this->position++;
			if ($this->position>=$this->size) $this->position=0;
			$this->history[$this->position]=$this->newItem($value, $this->now());
			return true;
			# TODO check that now() is in the expected unit
		}
		else return false;
	}

	public function item($offset=0)
	{
		if ($offset<$this->size*-1) $offset=$this->size*-1;

		$index=$this->position+$offset;
		$ttl=3;
		while ($index<0 and $ttl>=0)
		{
			$index +=$this->size;
			$ttl--;
		}
		if ($index>=$this->size) $index=$index%$this->size;

		return $this->history[$index]['value'];
	}

	public function mean($from, $to)
	{
		$total=0;
		$stop=$to+1;
		for ($i=$from;$i<$stop;$i++)
		{
			$total+=$this->item($i*-1);
		}

		return $total/($to-$from+1);
	}

	public function meanLast($numberOfItems)
	{
		$querySize=($numberOfItems>$this->size or $numberOfItems==-1)?$this->size:$numberOfItems;

		return $this->mean(0, $querySize-1);
	}

	public function iterationsUntilOverrun($goal, $lookBackSteps=2)
	{
		# TODO consider making a time-based (vs step based) version of this. It will be much more accurate with incosistent sampling.
		$now=$this->item(0);
		$previous=$this->item($lookBackSteps*-1);

		$distanceToGoal=$goal-$now;
		$progress=$now-$previous;

		if ($progress==0) return false; // If we aren't making progress, further calculations are meaningless.

		$progressPerIteration=$progress/$lookBackSteps;

		$stepsToGoal=$distanceToGoal/$progressPerIteration;

		return $stepsToGoal;
	}
}




$core=core::assert();
$balanceFaucets=new BalanceFaucets();
$core->registerModule($balanceFaucets);
